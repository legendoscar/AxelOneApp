<?php

namespace Modules\CRM\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\CRM\App\Models\OrganizationModel;
use Modules\CRM\Database\Factories\OrganizationModelFactory;
use Modules\Core\Database\Seeders\ModuleSeeder;



class OrganizationModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // $this->call([]);
        OrganizationModel::factory()->count(5)->create();

        // OrganizationModel::factory(2)->create()
    }
}
