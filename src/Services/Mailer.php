<?php

namespace App\Services;

use App\Core\Database;

/**
 * Outbound email for the quote workflow.
 *
 * Live sending is intentionally disabled (no SMTP configured yet). Every message
 * is rendered and logged to storage/mail/ so nothing is lost, and the hooks are
 * already in place — flip SEND_LIVE to true (and configure mail()/SMTP) to enable.
 */
class Mailer
{
    private const SEND_LIVE = false;
    private const LOG_DIR   = APP_ROOT . '/storage/mail';

    /** Customer confirmation. Returns true if handled (sent or logged). */
    public function customerConfirmation(array $quote, array $pricing, array $summary): bool
    {
        $subject = 'Your PORTES Quote Request — ' . $quote['reference'];
        $body    = $this->renderCustomer($quote, $pricing, $summary);

        return $this->dispatch($quote['customer_email'] ?? '', $subject, $body, 'customer');
    }

    /** Admin alert. Returns true if handled. */
    public function adminAlert(array $quote, array $pricing, array $summary): bool
    {
        $to = $this->notificationAddress();
        $subject = 'New Quote Request — ' . $quote['reference'];
        $body    = $this->renderAdmin($quote, $pricing, $summary);

        return $this->dispatch($to, $subject, $body, 'admin');
    }

    private function dispatch(string $to, string $subject, string $body, string $kind): bool
    {
        if (self::SEND_LIVE && $to !== '' && filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $headers = implode("\r\n", [
                'From: PORTES <no-reply@portes.dz>',
                'Content-Type: text/plain; charset=UTF-8',
                'MIME-Version: 1.0',
            ]);
            return @mail($to, $subject, $body, $headers);
        }

        // Not sending live: persist the message so no lead is ever lost.
        return $this->log($kind, $to, $subject, $body);
    }

    private function log(string $kind, string $to, string $subject, string $body): bool
    {
        if (!is_dir(self::LOG_DIR)) {
            @mkdir(self::LOG_DIR, 0775, true);
        }
        $file = self::LOG_DIR . '/' . date('Y-m-d') . '-' . $kind . '.log';
        $entry = '['. date('Y-m-d H:i:s') ."] TO: {$to}\nSUBJECT: {$subject}\n\n{$body}\n"
               . str_repeat('=', 60) . "\n";
        return @file_put_contents($file, $entry, FILE_APPEND | LOCK_EX) !== false;
    }

    private function notificationAddress(): string
    {
        try {
            $stmt = Database::conn()->query(
                "SELECT setting_value FROM settings WHERE setting_key = 'notification_email' LIMIT 1"
            );
            $val = (string) ($stmt->fetchColumn() ?: '');
            if ($val === '') {
                $stmt = Database::conn()->query(
                    "SELECT setting_value FROM settings WHERE setting_key = 'contact_email' LIMIT 1"
                );
                $val = (string) ($stmt->fetchColumn() ?: '');
            }
            return $val;
        } catch (\Throwable $e) {
            return '';
        }
    }

    private function renderCustomer(array $q, array $pricing, array $summary): string
    {
        $lines = [
            'Dear ' . ($q['customer_name'] ?? 'Customer') . ',',
            '',
            'Thank you for your interest in PORTES. We have received your configuration and our specialists will contact you within 24–48 hours with a tailored quotation.',
            '',
            'YOUR REFERENCE: ' . $q['reference'],
            '',
            'CONFIGURATION SUMMARY',
            str_repeat('-', 40),
        ];
        foreach ($summary as $label => $value) {
            $lines[] = str_pad($label . ':', 18) . $value;
        }
        $lines[] = str_repeat('-', 40);
        $lines[] = 'Estimated price: ' . ($pricing['total_price_fmt'] ?? '');
        $lines[] = '';
        $lines[] = 'This estimate is indicative; your final quotation will be confirmed by our team.';
        $lines[] = '';
        $lines[] = 'With our compliments,';
        $lines[] = 'The PORTES Atelier — Algiers';

        return implode("\n", $lines);
    }

    private function renderAdmin(array $q, array $pricing, array $summary): string
    {
        $lines = [
            'NEW QUOTE REQUEST — ' . $q['reference'],
            'Submitted: ' . ($q['submitted_at'] ?? date('Y-m-d H:i:s')),
            '',
            'CUSTOMER',
            str_repeat('-', 40),
            'Name:    ' . ($q['customer_name'] ?? ''),
            'Email:   ' . ($q['customer_email'] ?? ''),
            'Phone:   ' . ($q['customer_phone'] ?? ''),
            'Company: ' . ($q['customer_company'] ?? '—'),
            'Country: ' . ($q['customer_country'] ?? ''),
            'City:    ' . ($q['customer_city'] ?? ''),
            'Project: ' . ($q['project_type'] ?? ''),
            'Qty:     ' . ($q['quantity'] ?? 1),
            'Install: ' . ($q['install_date'] ?? '—'),
            '',
            'CONFIGURATION',
            str_repeat('-', 40),
        ];
        foreach ($summary as $label => $value) {
            $lines[] = str_pad($label . ':', 18) . $value;
        }
        $lines[] = str_repeat('-', 40);
        $lines[] = 'Estimated price: ' . ($pricing['total_price_fmt'] ?? '');
        $lines[] = '';
        if (!empty($q['notes'])) {
            $lines[] = 'NOTES: ' . $q['notes'];
            $lines[] = '';
        }
        $lines[] = 'Manage: /door-showroom/admin/quotes';

        return implode("\n", $lines);
    }
}
