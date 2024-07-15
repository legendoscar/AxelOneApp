<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\CRM\Database\factories\CompanyModelFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

/* 3rd party */
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\ModelStatus\HasStatuses;

class CompanyModel extends Model
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles, LogsActivity, HasStatuses;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    protected static function newFactory(): CompanyModelFactory
    {
        //return CompanyModelFactory::new();
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

    public function contacts()
    {
        return $this->belongsToMany(ContactsModel::class, 'company_contacts', 'company_id', 'contact_id');
    }

    public function leads()
    {
        return $this->hasMany(LeadsModel::class);
    }

}
