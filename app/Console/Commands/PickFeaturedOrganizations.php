<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\TenantOrgModule\App\Models\OrganizationModel;

class PickFeaturedOrganizations extends Command
{
    protected $signature = 'organizations:pick-featured';
    protected $description = 'Pick 3 random organizations to feature every 6 hours';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Unmark all previously featured organizations
        OrganizationModel::currentStatus('featured')->each(function ($organization) {
            $organization->deleteStatus('featured');
        });

        // Pick 3 random organizations
        $organizations = OrganizationModel::inRandomOrder()->take(3)->get();

        // Mark them as featured
        foreach ($organizations as $organization) {
            $organization->setStatus('featured');
        }

        $this->info('Picked 3 random organizations to feature.');
    }
}
