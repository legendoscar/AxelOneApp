<?php

namespace Modules\UserManagement\App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GeneratePasswordController extends Controller
{
    public function generatePassword()
    {
        $password = $this->generateSecurePassword();

        return response()->json(['password' => $password]);
    }

    private function generateSecurePassword($length = 32)
    {
        $upperCase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowerCase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $specialCharacters = '!@#$%^&*()_+{}[]|:;<>,.?/~`-=';

        $allCharacters = $upperCase . $lowerCase . $numbers . $specialCharacters;
        $password = '';

        // Ensure the password contains at least one character from each character set
        $password .= $upperCase[rand(0, strlen($upperCase) - 1)];
        $password .= $lowerCase[rand(0, strlen($lowerCase) - 1)];
        $password .= $numbers[rand(0, strlen($numbers) - 1)];
        $password .= $specialCharacters[rand(0, strlen($specialCharacters) - 1)];

        // Fill the rest of the password length with random characters
        for ($i = 4; $i < $length; $i++) {
            $password .= $allCharacters[rand(0, strlen($allCharacters) - 1)];
        }

        // Shuffle the password to ensure randomness
        return str_shuffle($password);
    }

}
