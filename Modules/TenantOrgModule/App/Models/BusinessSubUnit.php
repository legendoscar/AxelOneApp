<?php

namespace Modules\TenantOrgModule\App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\TenantOrgModule\App\Models\OrganizationModel;

class BusinessSubUnit extends Model
{
    use HasFactory;

    protected $fillable = ['category_id', 'name'];

    public function category()
    {
        return $this->belongsTo(BusinessCategory::class, 'category_id');
    }

    // public function category1()
    // {
    //     return $this->belongsTo(BusinessCategory::class);
    // }

    public function organizations()
    {
        return $this->belongsToMany(OrganizationModel::class, 'organization_sub_category');
    }

    // public function organizations1()
    // {
    //     return $this->belongsToMany(OrganizationModel::class, 'business_sub_unit_organization', 'business_sub_unit_id', 'organization_id');
    // }

    public function businessCategories()
    {
        return $this->belongsToMany(BusinessCategory::class, 'business_category_organization');
    }

    public function businessSubUnits()
    {
        return $this->belongsToMany(BusinessSubUnit::class, 'business_sub_unit_organization');
    }
}
