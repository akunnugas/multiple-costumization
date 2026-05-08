<?php

namespace Modules\Admission\Livewire;

class StepAdministration extends FrontComponent
{
    protected $view = 'admission::livewire.registration-steps.administration';
    public string $title = 'BERKAS ADMINISTRASI';
    public string $subtitle = 'Lengkapi persyaratan administrasi untuk mendaftar jalur seleksi yang telah Anda pilih. Pastikan file yang Anda upload sesuai dengan berkas yang diminta.';

    public function render()
    {
        return $this->buildView($this->view);
    }
}
