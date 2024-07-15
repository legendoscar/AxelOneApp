<?php

namespace Modules\LeadsManagementModule\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\LeadsManagementModule\Database\factories\SearchLeadsModelFactory;
use App\Models\User;
use Modules\TenantOrgModule\App\Models\OrganizationModel;
use Spatie\ModelStatus\HasStatuses;
use Illuminate\Database\Eloquent\SoftDeletes;


class BidsModel extends Model
{
    use HasFactory, HasStatuses, SoftDeletes;

    protected $table = 'bids';

    protected $fillable = [
        'organization_id',
        'search_request_id',
        'bid_amount',
        'bid_currency'
    ];

    // The maximum number of bidders on a search request
    const MAX_BIDDERS = 5;

    // Define the relationship with organization
    public function searchRequest()
    {
        return $this->belongsTo(SearchRequestModel::class, 'search_request_id');
    }

    public function organization()
    {
        return $this->belongsTo(OrganizationModel::class, 'organization_id');
    }

    public function lead()
    {
        return $this->hasOne(LeadsModel::class, 'bid_id');
    }


}
