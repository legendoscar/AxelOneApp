<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\CRM\Database\factories\OrganizationModelFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use App\Models\User;


/* 3rd party */
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\ModelStatus\HasStatuses;
use ESolution\DBEncryption\Traits\EncryptedAttribute;
use Bavix\Wallet\Traits\HasWallet;



class OrganizationModel extends Model
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles, LogsActivity, HasStatuses, EncryptedAttribute, HasWallet;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'creator_id',
        'org_name',
        'org_bio',
        'database_name',
        'subdomain',
        'address',
        'city',
        'state',
        'zipcode',
        'country',
        'phone',
        'email',
        'website',
        'industry',
        'size',
        'created_at',
        'updated_at'
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

    /* many-to-many relationship with users model */
    public function users()
    {
        return $this->belongsToMany(User::class, 'organization_user', 'organization_id', 'user_id');
    }


    public function posts()
    {
        return $this->hasMany(PostsModel::class);
    }




}
