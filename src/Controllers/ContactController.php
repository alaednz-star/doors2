<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use App\Middleware\SecurityHeaders;

class ContactController
{
    public function __construct()
    {
        SecurityHeaders::apply();
    }

    public function show(): void
    {
        Session::start();

        $settings = $this->settings();
        $token    = $this->csrfToken();

        require APP_ROOT . '/src/Views/contact.php';
    }

    public function submit(): void
    {
        header('Content-Type: application/json');
        $this->verifyCsrf();

        $body = json_decode(file_get_contents('php://input') ?: '{}', true) ?? [];

        // Honeypot — bots fill hidden fields; humans never see it.
        if (!empty($body['company_website'])) {
            echo json_encode(['success' => true]); // pretend success, store nothing
            return;
        }

        $name    = trim((string)($body['name'] ?? ''));
        $email   = strtolower(trim((string)($body['email'] ?? '')));
        $phone   = trim((string)($body['phone'] ?? ''));
        $subject = trim((string)($body['subject'] ?? ''));
        $message = trim((string)($body['message'] ?? ''));

        $errors = [];
        if ($name === '')                    $errors['name'] = 'Your name is required.';
        elseif (strlen($name) > 120)         $errors['name'] = 'Name is too long.';

        if ($email === '')                   $errors['email'] = 'Email address is required.';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 180)
                                             $errors['email'] = 'Please enter a valid email address.';

        if ($phone !== '' && strlen($phone) > 30) $errors['phone'] = 'Phone number is too long.';
        if ($subject !== '' && strlen($subject) > 160) $errors['subject'] = 'Subject is too long.';

        if ($message === '')                 $errors['message'] = 'Please write a message.';
        elseif (strlen($message) < 10)       $errors['message'] = 'Your message is a little short.';
        elseif (strlen($message) > 4000)     $errors['message'] = 'Message must not exceed 4000 characters.';

        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'errors' => $errors]);
            return;
        }

        try {
            Database::conn()->prepare(
                'INSERT INTO contact_messages (name, email, phone, subject, message, ip, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, NOW())'
            )->execute([
                $name,
                $email,
                $phone !== '' ? $phone : null,
                $subject !== '' ? $subject : null,
                $message,
                $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'We could not send your message. Please try again or reach us on WhatsApp.']);
            return;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Thank you. Your message has been received — our team will respond within 24 hours.',
        ]);
    }

    private function settings(): array
    {
        $defaults = [
            'contact_email'   => 'contact@portes.dz',
            'contact_phone'   => '+213 5 12 34 56 78',
            'contact_address' => '123 Showroom Boulevard, Algiers, Algeria',
        ];
        try {
            $rows = Database::conn()->query(
                "SELECT setting_key, setting_value FROM settings
                 WHERE setting_key IN ('contact_email','contact_phone','contact_address')"
            )->fetchAll(\PDO::FETCH_KEY_PAIR);
            foreach ($defaults as $k => $v) {
                if (!empty($rows[$k])) {
                    $defaults[$k] = $rows[$k];
                }
            }
        } catch (\Throwable $e) {
            // fall back to defaults
        }
        return $defaults;
    }

    private function csrfToken(): string
    {
        Session::start();
        if (empty($_SESSION['_cfg_csrf'])) {
            $_SESSION['_cfg_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_cfg_csrf'];
    }

    private function verifyCsrf(): void
    {
        Session::start();
        $expected = $_SESSION['_cfg_csrf'] ?? '';
        $provided = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if ($expected === '' || !hash_equals($expected, $provided)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Invalid request token.']);
            exit;
        }
    }
}
