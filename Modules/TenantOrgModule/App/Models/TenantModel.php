<?php

namespace Modules\TenantOrgModule\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\TenantOrgModule\Database\factories\TenantModelFactory;

class TenantModel extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];
    
    protected static function newFactory(): TenantModelFactory
    {
        //return TenantModelFactory::new();
    }
}
