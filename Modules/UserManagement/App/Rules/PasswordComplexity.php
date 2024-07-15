<?php

namespace Modules\UserManagement\App\Rules;
// namespace App\Http\Controllers\Auth;


use Illuminate\Contracts\Validation\Rule;

class PasswordComplexity implements Rule
{
    protected $username;
    protected $email;

    public function __construct($username, $email)
    {
        $this->username = $username;
        $this->email = $email;
        $this->message = 'The password does not meet the complexity requirements.';

    }

    public function passes($attribute, $value)
    {
        // Check for minimum length
        if (strlen($value) < 8) {
            $this->message = 'The password must be at least 8 characters long.';
            return false;
        }

        // Check for uppercase letter
        if (!preg_match('/[A-Z]/', $value)) {
            $this->message = 'The password must contain at least one uppercase letter.';
            return false;
        }

        // Check for number
        if (!preg_match('/[0-9]/', $value)) {
            $this->message = 'The password must contain at least one number.';
            return false;
        }

        // Check for special character
        if (!preg_match('/[\W]/', $value)) {
            $this->message = 'The password must contain at least one special character.';
            return false;
        }

        // Check if the password is similar to the username or email
        if (stripos($value, $this->username) !== false || stripos($value, $this->email) !== false) {
            $this->message = 'The password must not be similar to your username or email.';
            return false;
        }

        return true;
    }

    public function message()
    {
        return $this->message;
    }
}
