<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\CRM\Database\factories\ContactsModelFactory;
use Modules\CRM\App\Models\OrganizationModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

/* 3rd party */
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\ModelStatus\HasStatuses;


class ContactsModel extends Model
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles, LogsActivity, HasStatuses;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    protected static function newFactory(): ContactsModelFactory
    {
        //return ContactsModelFactory::new();
    }

    public function organizations()
    {
        return $this->belongsToMany(OrganizationModel::class, 'organization_contacts', 'contact_id', 'organization_id');
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

    public function lead()
    {
        return $this->hasMany(LeadsModel::class);
    }

}
