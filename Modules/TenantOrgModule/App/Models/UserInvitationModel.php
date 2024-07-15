<?php

namespace Modules\TenantOrgModule\App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\TenantOrgModule\Database\factories\UserInvitationModelFactory;
use Spatie\ModelStatus\HasStatuses;

class UserInvitationModel extends Model
{
    use HasFactory, HasStatuses, SoftDeletes;

    protected $table = 'user_invitations';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'inviter_id',
        'invitee_id',
        'organization_id',
        'invitation_message',
        'accepted_at',
        'declined_at',
        'response_message',
    ];

    protected static function newFactory(): UserInvitationModelFactory
    {
        //return UserInvitationModelFactory::new();
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'inviter_id');
    }

    public function invitee()
    {
        return $this->belongsTo(User::class, 'invitee_id');
    }

    public function organization()
    {
        return $this->belongsTo(OrganizationModel::class);
    }
}
