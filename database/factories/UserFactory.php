<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'firstname' => $this->faker->firstName,
            'lastname' => $this->faker->lastName,
            'username' => $this->faker->unique()->username,
            'profile_url' => $this->faker->url,
            'email' => $this->faker->unique()->email,
            'email_token' => $this->faker->sha256,
            'email_verified_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'api_token' => $this->faker->sha256,
            'password' => $this->faker->password(8),
            'password_token' => $this->faker->sha256,
            'phone_number' => $this->faker->phoneNumber,
            'address' => $this->faker->streetAddress(true),
            'profile_photo_path' => $this->faker->imageUrl(640, 480),
            'identification_type' => $this->faker->word,
            'identification_number' => $this->faker->unique()->numerify('########'),
            'date_of_birth' => $this->faker->dateTimeBetween('-30 years', '-18 years'),
            'country_of_residence' => $this->faker->country,
            'country_of_citizenship' => $this->faker->country,
            'occupation' => $this->faker->jobTitle,
            'industry' => $this->faker->randomElement(['Agriculture', 'Automotive', 'Construction', 'Education', 'Energy',
            'Finance', 'Food and Beverages', 'Healthcare', 'Hospitality',
            'Information Technology', 'Manufacturing', 'Media', 'Pharmaceuticals',
            'Retail', 'Telecommunications', 'Transportation', 'Utilities']),
            'is_politically_exposed' => $this->faker->boolean,
            'income_source' => $this->faker->word,
            'estimated_annual_income' => $this->faker->numberBetween(1000, 500000)
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
