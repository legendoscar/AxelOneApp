<?php

namespace Modules\CRM\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\CRM\Database\Seeders\OrganizationModelSeeder;
use Modules\CRM\Database\Factories\OrganizationModelFactory;

class CRMDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $this->call([]);
        // \Modules\CRM\App\Models\OrganizationModel::factory(2)->create();
        // $this->call(OrganizationModelSeeder::class);
        $this->call(OrganizationModelSeeder::class);



    }
}
