<?php

namespace Modules\Admission\Livewire;

use Modules\PMB\Services\PeriodePendaftaranManagementService;

class RegistrationPath extends FrontComponent
{
    protected $view = 'admission::livewire.registration-path';

    public string $title = 'Jalur Pendaftaran';

    public $parentNav = [];

    public $registrationPaths;

    public function mount()
    {
        $this->title = __('admission::home.registration_path') ?? $this->title;
        $this->registrationPaths = (new PeriodePendaftaranManagementService())->getActiveRegistrationPeriod();
    }

    public function render()
    {

        return $this->buildView($this->view);
    }

    public function registerRegistrationPath($key = null)
    {
        // validate key
        $reRender = $this->validateKey($key);
        if ($reRender) {
            return $reRender;
        }

        // set session registration path
        session()->put('admission.old_registration_path', session('admission.registration_path'));
        session()->put('admission.registration_path', $key);

        // redirect to registration path detail
        return redirect()->route('admission.registration-path-detail');
    }

    private function validateKey($key = null)
    {
        // nggk boleh kosong, kenapa di set default null? biar nggk ada error di tampilan usernya
        if (empty($key)) {
            return $this->render();
        }

        // harus berupa string, dan mengandung 4 slash (/)
        if (!is_string($key) || substr_count($key, '/') != 4) {
            return $this->render();
        }

        // pecah, lalu cek semua value harus berupa angka (karena merupakan id)
        $key = explode('/', $key);
        foreach ($key as $value) {
            if (!is_numeric($value)) {
                return $this->render();
            }
        }
    }
}
