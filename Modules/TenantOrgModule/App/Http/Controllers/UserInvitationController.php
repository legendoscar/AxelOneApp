<?php

namespace Modules\TenantOrgModule\App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\TenantOrgModule\App\Models\BusinessCategory;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Modules\TenantOrgModule\App\Models\OrganizationModel;
use Modules\TenantOrgModule\App\Models\UserInvitationModel;

class UserInvitationController extends Controller
{
    public function sendInvitations(Request $request)
    {

        // return $request->all();
        $request->validate([
            'emails' => 'required|array',
            'emails.*' => 'required|email',
            'message' => 'nullable|string',
        ]);

        $inviter = auth()->user();
        $organizationId = auth()->user()->organization_id;
        $organization = OrganizationModel::findOrFail($organizationId);

        // Check if the inviter belongs to the organization
        if (!$inviter->organizations()->where('organizations.id', $organizationId)->exists()) {
            return response()->json(['error' => 'You do not belong to the specified organization'], 403);
        }

        $alreadyInOrg = [];
        $invitationsSent = [];

        foreach ($request->emails as $email) {
            $invitee = User::where('email', $email)->first();

            // Check if the invitee is already in the organization
            if ($invitee) {
                if ($organization->users()->where('users.id', $invitee->id)->exists()) {
                    $alreadyInOrg[] = $invitee->email;
                    continue;
                }

                // Create the invitation
                $invitation = UserInvitationModel::create([
                    'inviter_id' => $inviter->id,
                    'invitee_id' => $invitee->id,
                    'organization_id' => $organization->id,
                    'invitation_message' => $request->message,
                ]);

                $invitation->setStatus('pending');
                $invitationsSent[] = $email;
            } else {
                // User with this email does not exist
                $userNotFound[] = $email;
            }
        }


        return response()->json([
            'message' => 'Invitations sent successfully',
            'invitations_sent' => $invitationsSent,
            'already_in_organization' => $alreadyInOrg,
            'users_not_found' => $userNotFound,
        ]);
    }


    public function acceptInvitation(Request $request)
    {
        $validatedData = $request->validate([
            'invitationId' => 'required|integer|exists:user_invitations,id',
            'response_message' => 'nullable|string',
        ]);

        $invitation = UserInvitationModel::findOrFail($validatedData['invitationId']);

        if ($invitation) {
            if ($invitation->invitee_id !== auth()->id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Check if the invitee is already in the organization
            $existingRecord = DB::table('organization_user')
                ->where('user_id', $invitation->invitee_id)
                ->where('organization_id', $invitation->organization_id)
                ->exists();

            if ($existingRecord) {
                return response()->json(['message' => 'You are already in the organization'], 400);
            }

            $invitation->update([
                'accepted_at' => now(),
                'response_message' => $request->response_message,
            ]);

            $invitation->setStatus('accepted');

            // Update the organization_user table
            DB::table('organization_user')->insert([
                'user_id' => $invitation->invitee_id,
                'organization_id' => $invitation->organization_id,
            ]);

            // Switch the user into the new organization
            $user = User::findOrFail($invitation->invitee_id);
            $user->organization_id = $invitation->organization_id;
            $user->save();

            return response()->json(['message' => 'Invitation accepted']);
        }
        return response()->json([
            'message' => 'Error. Invitation not successfully accepted.',
        ]);
    }

    public function declineInvitation(Request $request)
    {
        $validatedData = $request->validate([
            'invitationId' => 'required|integer|exists:user_invitations,id',
            'response_message' => 'nullable|string',
        ]);

        $invitation = UserInvitationModel::findOrFail($validatedData['invitationId']);
        if ($invitation) {


            if ($invitation->invitee_id !== auth()->id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $invitation->update([
                'declined_at' => now(),
                'response_message' => $request->response_message,
            ]);

            $invitation->setStatus('declined');

        }

        return response()->json(['message' => 'Invitation declined']);
    }

    public function getOrganizationInvitationsHistory()
    {
        $organizationId = auth()->user()->organization_id;
        // $organization = OrganizationModel::findOrFail($organizationId);

        // Ensure the authenticated user belongs to this organization
        if (!auth()->user()->organizations()->where('organizations.id', $organizationId)->exists()) {
            return response()->json(['error' => 'User does not belong to the specified organization'], 403);
        }

        $invitations = UserInvitationModel::with(['inviter', 'invitee'])
            ->where('organization_id', $organizationId)->get([
                    'id',
                    'inviter_id',
                    'invitee_id',
                    'organization_id',
                    'invitation_message',
                    'accepted_at',
                    'declined_at',
                    'response_message',
                ]);

        // Extract usernames from the inviter and invitee models
        $invitations->transform(function ($invitation) {
            $invitation->inviter_username = $invitation->inviter->username;
            // $invitation->inviter_email = $invitation->inviter->email;
            $invitation->invitee_username = $invitation->invitee->username;
            unset($invitation->inviter);
            unset($invitation->invitee);
            return $invitation;
        });

        return response()->json(['invitations' => $invitations]);
    }

    public function getUserInvitationHistory()
    {
        $user = auth()->user();

        $invitations = UserInvitationModel::where('invitee_id', $user->id)->get([
            'id',
            'inviter_id',
            'invitee_id',
            'organization_id',
            'invitation_message',
            'accepted_at',
            'declined_at',
            'response_message',
        ]);

        // Extract usernames from the inviter and invitee models
        $invitations->transform(function ($invitation) {
            $invitation->inviter_username = $invitation->inviter->username;
            // $invitation->inviter_email = $invitation->inviter->email;
            $invitation->invitee_username = $invitation->invitee->username;
            unset($invitation->inviter);
            unset($invitation->invitee);
            return $invitation;
        });

        return response()->json(['invitations' => $invitations]);
    }

    public function getUserJoinedOrganizations()
    {
        $user = auth()->user();

        $joinedOrganizations = $user->invitations()
            ->whereHas('statuses', function ($query) {
                $query->where('name', 'accepted');
            })
            ->with('organization')
            ->get([
                'id',
                'inviter_id',
                'invitee_id',
                'organization_id',
                'invitation_message',
                'accepted_at',
                'declined_at',
                'response_message',
            ]);

        return response()->json([
            'status' => 'success',
            'message' => count($joinedOrganizations) . ' organizations found',
            'organizations' => $joinedOrganizations
        ]);
    }

    public function getUserDeclinedOrganizations()
    {
        $user = auth()->user();

        $joinedOrganizations = $user->invitations()
            ->whereHas('statuses', function ($query) {
                $query->where('name', 'declined');
            })
            ->with('organization')
            ->get([
                'id',
                'inviter_id',
                'invitee_id',
                'organization_id',
                'invitation_message',
                'accepted_at',
                'declined_at',
                'response_message',
            ]);

        return response()->json([
            'status' => 'success',
            'message' => count($joinedOrganizations) . ' organizations found',
            'organizations' => $joinedOrganizations
        ]);
    }

    public function getUsersJoinedViaInvitations($organizationId)
    {
        $organization = OrganizationModel::findOrFail($organizationId);

        // Ensure the authenticated user belongs to this organization
        if (!auth()->user()->organizations()->where('organizations.id', $organizationId)->exists()) {
            return response()->json(['error' => 'User does not belong to the specified organization'], 403);
        }

        $joinedUsers = UserInvitationModel::where('organization_id', $organizationId)
            ->where('status', 'accepted')
            ->with('invitee')
            ->get();

        return response()->json(['users' => $joinedUsers]);
    }



}
