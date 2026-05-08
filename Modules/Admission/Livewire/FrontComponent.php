<?php

namespace Modules\Admission\Livewire;

use Livewire\Component;

class FrontComponent extends Component
{
    protected $view = 'admission::livewire.home';
    protected string $title = 'Penerimaan Mahasiswa Baru';

    /**
     * Render adalah fungsi yang akan dijalankan setiap komponen dipanggil
     *
     * @return mixed
     */
    public function render()
    {
        return $this->buildView($this->view);
    }

    protected function buildView($pageView)
    {
        return view($pageView)
            ->layout('admission::layouts.front', [
                'title' => $this->title,
            ]);
    }
}
