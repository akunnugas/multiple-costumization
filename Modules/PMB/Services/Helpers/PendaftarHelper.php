<?php

namespace Modules\PMB\Services\Helpers;

use Illuminate\Support\Str;
use Modules\Core\Helpers\Error;
use Modules\Core\Models\Biodata;
use Modules\Gate\Models\Api\UserSso;
use Modules\Gate\Models\User;
use Modules\PMB\Models\Pendaftar;

class PendaftarHelper
{
    /**
     * Dipanggil sebelum create dan update.
     *
     * @param $data
     * @return mixed
     */
    private function beforeSave($data)
    {
        // set default
        $data['nama'] = Str::upper($data['nama']);
        $data['email'] = Str::lower($data['email']);
        $data['tempat_lahir'] = Str::upper($data['tempat_lahir']);

        return $data;
    }

    /**
     * Dipanggil sebelum create saja.
     *
     * @param $data
     * @return mixed
     */
    private function beforeCreate($data)
    {
        if (empty($data['kode_pendaftar'])) {
            $data['kode_pendaftar'] = $this->generateCode($data['id_periode_pendaftaran']);
        }

        if (empty($data['waktu_registrasi'])) {
            $data['waktu_registrasi'] = now();
        }

        return $data;
    }

    /**
     * Template utk menyimpan/create data pendaftar yang paling umum.
     * Bisa disesuaikan jika ada kebutuhan khusus, namun jika sangat custom, lebih baik buat fungsi baru.
     * Untuk transaction dipanggil di luar fungsi ini.
     *
     * @param $data
     * @return array
     */
    public function create($data)
    {
        $error = null;

        // [Start] before - prepare data
        $data = $this->beforeCreate($data);
        $data = $this->beforeSave($data);
        // [End] before - prepare data

        // cek user by email
        $user = User::firstOrCreate(['email_user' => $data['email']], [
            'nama_user' => $data['name'],
            'telepon' => $data['telepon'] ?? null,
        ]);

        try {
            // send invitation kalo belum terdaftar
            $alreadyRegisteredInSSO = UserSso::findUserByEmail($user->email_user);
            if (empty($alreadyRegisteredInSSO)) {
                $this->sendEmailInvitationSso($user);
            }
        } catch (\Throwable $th) {
            return [null, null, new Error('Gagal mengirim undangan melalui email')];
        }

        // cek person by user_id
        $person = Biodata::firstOrCreate(['id_user' => $user->id], [
            'nama' => $data['nama'],
            'jenis_kelamin' => $data['jenis_kelamin'],
            'telepon' => $data['telepon'],
            'email' => $data['email'],
            'tempat_lahir' => $data['tempat_lahir'],
            'tanggal_lahir' => $data['tanggal_lahir'],
            'nik' => $data['nik'],
            'id_negara' => $data['id_negara'],
        ]);

        // cek pendaftar by person_id
        $registrant = Pendaftar::firstOrCreate(['id_biodata' => $person->id], [
            'kode_pendaftar' => $data['kode_pendaftar'],
            'id_periode_pendaftaran' => $data['id_periode_pendaftaran'],
            'id_periode' => $data['id_periode_akademik'],
            'waktu_aktif' => $data['waktu_aktif'],
            'waktu_registrasi' => $data['waktu_registrasi'],
        ]);

        // [Start] after save
        // TODO: cek jika periode pendaftaran yang dipilih pendaftar itu berbayar maka generate va, dll (afterInsert spmb/models/m_pendaftar.php)
        // [End] after save

        return [$user, $person, $registrant, $error];
    }

    /**
     * Generate code pendaftar.
     * code: year + registration_period_id + sequence 5 digit
     *  example: 23 + 001 + 00001 = 2300100001
     *
     * @param $registrationPeriodId
     * @return string
     */
    public function generateCode($registrationPeriodId)
    {
        $codeYear = date('y');
        $codePeriodId = str_pad($registrationPeriodId, 3, '0', STR_PAD_LEFT);
        $codeRegistrant = Pendaftar::where('id_periode_pendaftaran', $registrationPeriodId)
            ->orderBy('id', 'desc')
            ->first();
        if ($codeRegistrant) {
            $codeSequence = str_pad($codeRegistrant->id + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $codeSequence = '00001';
        }

        return $codeYear . $codePeriodId . $codeSequence;
    }

    /**
     * Mengirimkan undangan ke email user utk create akun di SSO dan user menginput password sendiri.
     *
     * @param User $user
     * @param bool $reInvite
     * @return bool
     */
    private function sendEmailInvitationSso(User $user, bool $reInvite = false): bool
    {
        try {
            $data = [
                'name' => $user->nama_user,
                'email' => $user->email_user,
                'phone' => $user->telepon ?? null,
            ];

            if (!$reInvite) { // jika bukan invite ulang, cek dulu di invitation ada atau nggk biar nggk error
                $invitation = UserSso::findInvitationByEmail($user->email_user);
                $reInvite = !empty($invitation['data'][0]['id']); // jika sudah ada, dianggap send ulang
            }

            $invitationId = $user->invitation_id ?? $invitation['data'][0]['id'] ?? null;

            $redirectUri = request()->getSchemeAndHttpHost() . '/admission/auth';
            $userSso = $reInvite
                ? UserSSO::reinviteEmailUser($invitationId, $data, $redirectUri)
                : UserSSO::inviteEmailUser($data, $redirectUri);

            // update invitation_id dan invitation_at
            $user->update([
                'id_undangan_sso' => $userSso['data']['id'],
                'waktu_undangan_sso' => now(),
            ]);
        } catch (\Throwable $th) {
            return false;
        }

        return true;
    }
}
