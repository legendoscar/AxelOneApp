<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Modules\CRM\App\Models\LeadsModel;
use Modules\LeadsManagementModule\App\Models\SearchLeadsModel;
use Modules\TenantOrgModule\App\Models\UserInvitationModel;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Laravel\Sanctum\HasApiTokens;
use Modules\TenantOrgModule\App\Models\OrganizationModel;
use Modules\CRM\App\Models\CompanyModel;
use Rappasoft\LaravelAuthenticationLog\Traits\AuthenticationLoggable;
use ESolution\DBEncryption\Traits\EncryptedAttribute;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Str;
use Overtrue\LaravelFavorite\Traits\Favoriter;
use Modules\LeadsManagementModule\App\Models\SearchRequestModel;
use Spatie\ModelStatus\HasStatuses;






class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, AuthenticationLoggable, EncryptedAttribute, Favoriter, HasStatuses;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'msg_id',
        'firstname',
        'lastname',
        'username',
        'profile_url',
        'organization_id',
        'email',
        'email_token',
        'email_token_created_at',
        'email_verified_at',
        'api_token',
        'password',
        'password_token',
        'password_token_expires_at',
        'phone_number',
        'address',
        'profile_photo_path',
        'identification_type',
        'identification_number',
        'date_of_birth',
        'country_of_residence',
        'country_of_citizenship',
        'occupation',
        'industry',
        'is_politically_exposed',
        'income_source',
        'estimated_annual_income',
        'interests'
    ];


    /**
     * The attributes that should be encrypted on save.
     *
     * @var array
     */
    protected $encryptable = [
        // 'firstname',
        // 'lastname',
        // 'username',
        // 'profile_url',
        // 'email',
        // 'email_verified_at',
        // 'phone_number',
        // 'address',
        // 'profile_photo_path',
        // 'identification_type',
        // 'identification_number',
        // 'date_of_birth',
        // 'country_of_residence',
        // 'country_of_citizenship',
        // 'occupation',
        // 'industry',
        // 'is_politically_exposed',
        // 'income_source',
        // 'estimated_annual_income'
        // 'created_at',
        // 'updated_at',
        // 'deleted_at',

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'email_token_created_at' => 'datetime',
        'password_token_expires_at' => 'datetime',
        'password' => 'hashed',
        'interests' => 'array'
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * A user belongs to an organization.
     *
     */
    public function organizations()
    {
        return $this->belongsToMany(OrganizationModel::class, 'organization_user', 'user_id', 'organization_id');
    }

    public function currentOrganization()
    {
        return $this->belongsTo(OrganizationModel::class, 'organization_id');
    }

    /**
     * A user can be assigned to many companies.
     *
     */
    public function companies()
    {
        return $this->belongsToMany(CompanyModel::class, 'company_user', 'user_id', 'company_id');
    }

    public function leads()
    {
        return $this->hasMany(LeadsModel::class);
    }

    public function getSlugAttribute()
    {
        return Str::slug(Str::of($this->firstname . '.' . $this->lastname)->limit(30)->append('-' . $this->id), '-');
    }

    public function searchRequests()
    {
        return $this->hasMany(SearchRequestModel::class);
    }

    public function searchLeads()
    {
        return $this->hasMany(SearchLeadsModel::class);
    }

    public function invitations()
    {
        return $this->hasMany(UserInvitationModel::class, 'invitee_id');
    }

}
