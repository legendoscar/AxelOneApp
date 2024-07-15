<?php
namespace Modules\TenantOrgModule\App\Observers;


use Modules\TenantOrgModule\App\Models\OrganizationModel;
use Illuminate\Support\Str;

class OrganizationObserver
{
    /**
     * Handle the Organization "creating" event.
     *
     * @param  \Modules\TenantOrgModule\App\Models\OrganizationModel  $organization
     * @return void
     */
    public function creating(OrganizationModel $organization)
    {
        $organization->msg_id = $this->generateUniqueMsgId();
    }

    protected function generateUniqueMsgId()
    {
        do {
            $msg_id = Str::random(20);
        } while (OrganizationModel::where('msg_id', $msg_id)->exists());

        return $msg_id;
    }
}
