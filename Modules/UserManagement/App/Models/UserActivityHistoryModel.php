<?php

namespace Modules\UserManagement\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\UserManagement\Database\factories\UserActivityHistoryModelFactory;

class UserActivityHistoryModel extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'user_activity_history';

    protected $fillable = [
        'user_id',
        'organization_id',
        'action',
        'ip_address',
    ];

    protected static function newFactory(): UserActivityHistoryModelFactory
    {
        //return UserActivityHistoryModelFactory::new();
    }
}
