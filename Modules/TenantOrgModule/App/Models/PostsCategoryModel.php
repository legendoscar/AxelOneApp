<?php

namespace Modules\TenantOrgModule\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\CRM\Database\factories\PostsCategoryModelFactory;

class PostsCategoryModel extends Model
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

    protected static function newFactory(): PostsCategoryModelFactory
    {
        //return PostsCategoryModelFactory::new();
    }

    public function posts()
    {
        return $this->hasMany(PostsModel::class);
    }
}
