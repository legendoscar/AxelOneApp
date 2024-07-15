<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Faker\Factory;
use Faker\Generator;
use App\Providers\IndustryProvider;
use App\Models\User;
use Modules\UserManagement\App\Observers\UserObserver;

use Modules\TenantOrgModule\App\Models\OrganizationModel;
use Modules\TenantOrgModule\App\Observers\OrganizationObserver;


class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        // ...

        $faker = Factory::create();
        $generator = new Generator();
        $faker->addProvider(new IndustryProvider($generator));
    }

    public function boot()
    {
        User::observe(UserObserver::class);
        OrganizationModel::observe(OrganizationObserver::class);

    }
}
