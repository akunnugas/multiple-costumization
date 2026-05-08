<?php

namespace Modules\Admission\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Modules\Admission\Emails\ProofOfRegistrationEmail;
use Modules\Core\Helpers\Error;
use Modules\PMB\Services\Helpers\PendaftarHelper;

class RegistrationService
{
    public function save($registrantData, $registrationPeriodData)
    {
        DB::beginTransaction();

        [$user, $person, $registrant, $error] = $this->saveRegistrant($registrantData);

        // send email bukti pendaftaran ke pendaftar
        Mail::to($person['email'])->send(
            new ProofOfRegistrationEmail($registrant, $person, $registrationPeriodData)
        );

        DB::commit();

        return [$user, $person, $registrant, $error];
    }

    /**
     * Save pendaftar
     *
     * @param $data
     * @return array
     */
    private function saveRegistrant($data)
    {
        // karena butuh before dan after save, maka panggil helper
        $registrantHelper = new PendaftarHelper();

        try {
            [$user, $person, $registrant, $error] = $registrantHelper->create($data);

            if (Error::isError($error)) {
                DB::rollBack();

                return [null, null, null, $error];
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            // default error message yg tak terhandle
            $messageError = new Error('Gagal menyimpan data pendaftar');

            // error message yang terhandle
            if (Error::isError($th)) {
                $messageError = $th;
            }

            return [null, null, null, $messageError];
        }

        return [$user, $person, $registrant, null];
    }
}
