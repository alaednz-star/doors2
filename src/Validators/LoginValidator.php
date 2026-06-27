<?php

namespace App\Validators;

class LoginValidator
{
    private array $errors = [];
    private array $clean  = [];

    public function validate(array $input): bool
    {
        $this->errors = [];
        $email        = trim($input['email'] ?? '');
        $password     = $input['password'] ?? '';

        if ($email === '') {
            $this->errors['email'] = 'Email address is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Enter a valid email address.';
        } elseif (strlen($email) > 254) {
            $this->errors['email'] = 'Email address is too long.';
        }

        if ($password === '') {
            $this->errors['password'] = 'Password is required.';
        } elseif (strlen($password) < 8) {
            $this->errors['password'] = 'Password must be at least 8 characters.';
        } elseif (strlen($password) > 200) {
            $this->errors['password'] = 'Password is too long.';
        }

        $this->clean = [
            'email'    => strtolower($email),
            'password' => $password,
            'remember' => !empty($input['remember']),
        ];

        return empty($this->errors);
    }

    public function errors(): array { return $this->errors; }
    public function data(): array   { return $this->clean; }
}
