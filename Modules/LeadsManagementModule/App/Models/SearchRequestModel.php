<?php

namespace Modules\LeadsManagementModule\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\LeadsManagementModule\App\Models\SearchLeadsModel;
use Modules\TenantOrgModule\App\Models\OrganizationModel;
use Spatie\ModelStatus\HasStatuses;

class SearchRequestModel extends Model
{
    use HasStatuses, SoftDeletes;

    protected $table = 'search_requests';


    protected $fillable = [
        'user_id',
        'search_term',
        'ip_address',
        'user_agent',
        'org_matched',
        'search_filters',
        'results_count',
        'duration'
    ];

    protected $casts = [
        'search_filters' => 'array',
        'org_matched' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // SearchRequest model
    public function searchLeads()
    {
        return $this->hasMany(SearchLeadsModel::class);
    }

    public function organizations()
    {
        return $this->belongsToMany(OrganizationModel::class);
    }

    public function bids()
    {
        return $this->hasMany(BidsModel::class, 'search_request_id');
    }

    public function leads()
    {
        return $this->hasOne(LeadsModel::class, 'search_request_id');
    }

    public function winningOrganization()
    {
        return $this->belongsTo(OrganizationModel::class, 'winning_organization_id');
    }


}
