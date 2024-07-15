<?php

namespace Modules\TenantOrgModule\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\CRM\Database\factories\PostsModelFactory;
use App\Models\User;
use Modules\CRM\App\Models\OrganizationModel;

class PostsModel extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'user_id',
        'organization_id',
        'title',
        'content',
        'slug',
        'excerpt',
        'is_published',
        'category_id',
        'created_at',
        'updated_at'
    ];

    protected static function newFactory(): PostsModelFactory
    {
        //return PostsModelFactory::new();
    }

    // Define the relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Define the relationship with Organization
    public function organization()
    {
        return $this->belongsTo(OrganizationModel::class);
    }

    public function post_category()
    {
        return $this->belongsTo(PostsCategoryModel::class);
    }

}
