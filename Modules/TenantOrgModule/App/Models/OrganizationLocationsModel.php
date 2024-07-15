<?php

namespace Modules\TenantOrgModule\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\CRM\Database\factories\PostsModelFactory;
use App\Models\User;
use Modules\TenantOrgModule\App\Models\OrganizationModel;

class OrganizationLocationsModel extends Model
{
    use HasFactory;

    protected $table = 'organization_locations';
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'organization_id',
        'location_name',
        'country',
        'state',
        'city',
        'address',
        'zipcode',
        'lat',
        'long'
    ];

    protected static function newFactory(): OrganizationLocationsModelFactory
    {
        //return PostsModelFactory::new();
    }

    // Define the relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Define the relationship with Organization
    public function organization()
    {
        return $this->belongsTo(OrganizationModel::class, 'organization_id');
    }

}
