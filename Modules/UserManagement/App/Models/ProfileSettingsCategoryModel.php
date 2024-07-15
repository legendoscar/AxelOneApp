<?php

namespace Modules\UserManagement\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\UserManagement\Database\factories\ProfileSettingsCategoryModelFactory;

class ProfileSettingsCategoryModel extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'name',
        'desc',
        'created_at',
        'updated_at'
    ];

    protected static function newFactory(): ProfileSettingsCategoryModelFactory
    {
        //return ProfileSettingsCategoryModelFactory::new();
    }

    public function profile_settings()
    {
        return $this->hasMany(ProfileSettingsModel::class);
    }
}
