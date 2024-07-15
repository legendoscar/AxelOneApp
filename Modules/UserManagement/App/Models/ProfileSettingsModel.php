<?php

namespace Modules\UserManagement\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\UserManagement\Database\factories\ProfileSettingsModelFactory;

class ProfileSettingsModel extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'user_id',
        'category_id',
        'name',
        'value',
        'created_at',
        'updated_at'
    ];

    protected static function newFactory(): ProfileSettingsModelFactory
    {
        //return ProfileSettingsModelFactory::new();
    }

    public function profile_settings_category()
    {
        return $this->belongsTo(ProfileSettingsCategoryModel::class);
    }
}
