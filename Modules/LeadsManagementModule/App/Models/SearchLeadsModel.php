<?php

namespace Modules\LeadsManagementModule\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\LeadsManagementModule\Database\factories\SearchLeadsModelFactory;
use App\Models\User;
use Spatie\ModelStatus\HasStatuses;



class SearchLeadsModel extends Model
{
    use HasFactory, HasStatuses, SoftDeletes;

    protected $table = 'search_leads';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'search_request_id',
        'user_id',
        'data',
        'status'
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function searchRequest()
    {
        return $this->belongsTo(SearchRequestModel::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function newFactory(): SearchLeadsModelFactory
    {
        //return SearchLeadsModelFactory::new();
    }


}
