<?php

namespace Modules\Core\Services;

use Modules\Gate\Models\Api\UserSso;
use Modules\Gate\Models\User;

class SsoService
{
    /**
     * Mengirimkan undangan ke email user utk create akun di SSO dan user menginput password sendiri.
     *
     * @param User $user
     * @param bool $reInvite
     * @param string|null $redirectUri
     * @return bool
     */
    public function sendEmailInvitationSso(User $user, bool $reInvite = false, string $redirectUri = null): bool
    {
        try {
            $data = [
                'name' => $user->nama_user,
                'email' => $user->email_user,
                'phone' => $user->telepon_user ?? null,
            ];

            if (!$reInvite) { // jika bukan invite ulang, cek dulu di invitation ada atau nggk biar nggk error
                $invitation = UserSso::findInvitationByEmail($user->email_user);
                $reInvite = !empty($invitation['data'][0]['id']); // jika sudah ada, dianggap send ulang
            }

            $invitationId = $user->id_undangan_sso ?? $invitation['data'][0]['id'] ?? null;

            // send email invitation
            $userSso = $reInvite
                ? UserSSO::reinviteEmailUser($invitationId, $data, $redirectUri)
                : UserSSO::inviteEmailUser($data, $redirectUri);

            // update id_undangan_sso dan waktu_undangan_sso
            $user->update([
                'id_undangan_sso' => $userSso['data']['id'],
                'waktu_undangan_sso' => now(),
            ]);
        } catch (\Throwable $th) {
            return false; // error
        }

        // berhasil
        return true;
    }
}
