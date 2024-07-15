<?php

namespace Modules\TenantOrgModule\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\CRM\App\Models\LeadsModel;
use Modules\LeadsManagementModule\App\Models\SearchRequestModel;
use Modules\TenantOrgModule\Database\factories\OrganizationModelFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;


/* Models */
use App\Models\User;
use Modules\TenantOrgModule\App\Models\BusinessCategory;
use Modules\TenantOrgModule\App\Models\BusinessSubUnit;

/* 3rd party */
use phpDocumentor\Reflection\Location;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\ModelStatus\HasStatuses;
use Overtrue\LaravelFavorite\Traits\Favoriteable;
use ESolution\DBEncryption\Traits\EncryptedAttribute;
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Traits\HasWallets;
use Bavix\Wallet\Interfaces\Wallet;

class OrganizationModel extends Model implements Wallet
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles, LogsActivity, HasStatuses, Favoriteable, EncryptedAttribute, HasWallet, HasWallets;

    /**
     * The table name for the model.
     */

    protected $table = 'organizations';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'creator_id',
        'org_name',
        'org_bio',
        'logo',
        'cover_image',
        'msg_id',
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

    protected $casts = [
        'location' => 'array',
        'products' => 'array',
        'services' => 'array',
        'business_hours' => 'array',
        'website_social_media' => 'array',
        'contact_info' => 'array',
        'reviews_ratings' => 'array',
        'pricing' => 'array',
        'certifications_accreditations' => 'array',
        'languages_spoken' => 'array',
        'payment_methods' => 'array',
        'nearby_landmarks' => 'array',
        'parking_info' => 'array',
        'pet_policy' => 'array',
        'dress_code' => 'array',
        'special_instructions' => 'array',
        'accessibility' => 'array',
        'events_promotions' => 'array',
        'cancellation_policy' => 'array',
        'environmental_practices' => 'array',
        'awards_nominations' => 'array',
        'user_generated_contents' => 'array',

    ];

    protected $encryptable = [];

    // The minimum amount to pay for
    const MIN_AMOUNT = 5000;

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


    // public function categories()
    // {
    //     return $this->belongsToMany(BusinessCategory::class, 'organization_category');
    // }

    /* attach org to cat & sub units */
    public function businessCategories()
    {
        return $this->belongsToMany(BusinessCategory::class, 'business_category_organization', 'organization_id', 'business_category_id');
    }


    public function businessSubUnits()
    {
        return $this->belongsToMany(BusinessSubUnit::class, 'business_sub_unit_organization', 'organization_id', 'business_sub_unit_id');
    }

    public function subCategories()
    {
        return $this->belongsToMany(BusinessSubUnit::class, 'organization_sub_category');
    }

    public function searchRequests()
    {
        return $this->belongsToMany(SearchRequestModel::class);
    }

    public function bids()
    {
        return $this->hasMany(BidsModel::class);
    }

    // Define the relationship with leads
    public function leads()
    {
        return $this->hasMany(LeadsModel::class);
    }

    public function winningBids()
    {
        return $this->hasMany(SearchRequestModel::class, 'winning_organization_id');
    }

    public function locations()
    {
        return $this->hasMany(OrganizationLocationsModel::class, 'organization_id');
    }
    public function getLocationAttribute()
    {
        return json_decode($this->attributes['location'], true);
    }

    public function getProductAttribute()
    {
        return json_decode($this->attributes['products'], true);
    }

    public function getServiceAttribute()
    {
        return json_decode($this->attributes['services'], true);
    }

    public function getBusinessHoursAttribute()
    {
        return json_decode($this->attributes['business_hours'], true);
    }

    public function getSocialMediaAttribute()
    {
        return json_decode($this->attributes['website_social_media'], true);
    }

    public function getContactInfoAttribute()
    {
        return json_decode($this->attributes['contact_info'], true);
    }

    public function getReviewsRatingAttribute()
    {
        return json_decode($this->attributes['reviews_ratings'], true);
    }

    public function getPricingAttribute()
    {
        return json_decode($this->attributes['pricing'], true);
    }

    public function getCertificationAttribute()
    {
        return json_decode($this->attributes['certifications_accreditations'], true);
    }

    public function getLangaugeAttribute()
    {
        return json_decode($this->attributes['languages_spoken'], true);
    }

    public function getPaymentmethodAttribute()
    {
        return json_decode($this->attributes['payment_methods'], true);
    }

    public function getNearbyLandmarkAttribute()
    {
        return json_decode($this->attributes['nearby_landmarks'], true);
    }

    public function getParkingInfoAttribute()
    {
        return json_decode($this->attributes['parking_info'], true);
    }

    public function getPetPolicyAttribute()
    {
        return json_decode($this->attributes['pet_policy'], true);
    }

    public function getDressCodeAttribute()
    {
        return json_decode($this->attributes['dress_code'], true);
    }

    public function getSpecialInstructionAttribute()
    {
        return json_decode($this->attributes['special_instructions'], true);
    }

    public function getAccessibilityAttribute()
    {
        return json_decode($this->attributes['accessibility'], true);
    }

    public function getEventPromotionAttribute()
    {
        return json_decode($this->attributes['events_promotions'], true);
    }

    public function getCancellationPolicyAttribute()
    {
        return json_decode($this->attributes['cancellation_policy'], true);
    }

    public function getEnvironmentalPracticesAttribute()
    {
        return json_decode($this->attributes['environmental_practices'], true);
    }

    public function getAwardsNominationsAttribute()
    {
        return json_decode($this->attributes['awards_nominations'], true);
    }

    public function getUserGeneratedContentsAttribute()
    {
        return json_decode($this->attributes['user_generated_contents'], true);
    }

}
