<?php

namespace Modules\TenantOrgModule\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use Modules\TenantOrgModule\App\Models\OrganizationModel;

class OrganizationModelFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = OrganizationModel::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'creator_id' => User::create()->id, // Assuming creator_id is a User model
            'org_name' => $this->faker->company,
            'org_bio' => $this->faker->text(200), // Adjust the number for bio length
            'address' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'state' => $this->faker->state,
            'zipcode' => $this->faker->postcode,
            'country' => $this->faker->country,
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->email,
            'website' => $this->faker->url,
            'industry' => $this->faker->randomElement(['Agriculture', 'Automotive', 'Construction', 'Education', 'Energy',
            'Finance', 'Food and Beverages', 'Healthcare', 'Hospitality',
            'Information Technology', 'Manufacturing', 'Media', 'Pharmaceuticals',
            'Retail', 'Telecommunications', 'Transportation', 'Utilities']),
            'size' => $this->faker->randomElement(['Small', 'Medium', 'Large']),
        ];
    }
}

