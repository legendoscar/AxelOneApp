<?php

namespace Modules\LeadsManagementModule\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CRM\Database\factories\LeadsModelFactory;

use app\Models\User;
use Modules\LeadsManagementModule\App\Models\SearchRequestModel;
use Modules\TenantOrgModule\App\Models\OrganizationModel;


class LeadsModel extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'search_request_id',
        'bid_id',
        'organization_id',
        'user_id',
        'assigned_to_user_id',
    ];

    protected $table = 'leads';

    protected static function newFactory(): LeadsModelFactory
    {
        //return LeadsModelFactory::new();
    }

    /**
     * Customize Log options.
     *
     * @return Spatie\Activitylog\LogOptions
     */

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()
            ->setDescriptionForEvent(fn(string $eventName) => "This model has been {$eventName}");
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Define the relationship with organization
    public function searchRequest()
    {
        return $this->belongsTo(SearchRequestModel::class, 'search_request_id');
    }

    public function bid()
    {
        return $this->belongsTo(BidsModel::class, 'bid_id');
    }

    public function organization()
    {
        return $this->belongsTo(OrganizationModel::class, 'organization_id');
    }
}
