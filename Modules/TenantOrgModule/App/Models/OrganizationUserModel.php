<?php

namespace Modules\TenantOrgModule\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\TenantOrgModule\Database\factories\OrganizationModelFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use App\Models\User;


/* 3rd party */
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\ModelStatus\HasStatuses;
use Overtrue\LaravelFavorite\Traits\Favoriteable;
use ESolution\DBEncryption\Traits\EncryptedAttribute;
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Traits\HasWallets;
use Bavix\Wallet\Interfaces\Wallet;






class OrganizationUserModel extends Model implements Wallet
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles, LogsActivity, HasStatuses, Favoriteable, EncryptedAttribute, HasWallet, HasWallets;

    /**
     * The table name for the model.
     */

    protected $table = 'organization_user';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'organization_id',
    ];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    protected static function newFactory(): OrganizationModelFactory
    {
        return OrganizationModelFactory::new();
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

}
