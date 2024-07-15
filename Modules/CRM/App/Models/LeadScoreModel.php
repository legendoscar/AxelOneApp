<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\CRM\Database\factories\LeadScoreModelFactory;

class LeadScoreModel extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    protected static function newFactory(): LeadScoreModelFactory
    {
        //return LeadScoreModelFactory::new();
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
