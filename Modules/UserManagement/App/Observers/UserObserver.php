<?php

namespace Modules\UserManagement\App\Observers;


use App\Models\User;
use Illuminate\Support\Str;

class UserObserver
{
    /**
     * Handle the User "creating" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function creating(User $user)
    {
        $user->msg_id = $this->generateUniqueMsgId();
    }

    protected function generateUniqueMsgId()
    {
        do {
            $msg_id = Str::random(20);
        } while (User::where('msg_id', $msg_id)->exists());

        return $msg_id;
    }
}
