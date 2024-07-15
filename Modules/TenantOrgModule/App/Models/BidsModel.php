<?php

namespace Modules\TenantOrgModule\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\TenantOrgModule\App\Models\OrganizationModel;

class BidsModel extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function subUnits()
    {
        return $this->hasMany(BusinessSubUnit::class, 'category_id');
    }

    // public function subUnits1()
    // {
    //     return $this->hasMany(BusinessSubUnit::class);
    // }

    public function organizations()
    {
        return $this->belongsToMany(OrganizationModel::class, 'business_category_organization', 'business_category_id', 'organization_id');
    }
}
