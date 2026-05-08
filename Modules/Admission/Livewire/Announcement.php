<?php

namespace Modules\Admission\Livewire;

class Announcement extends FrontComponent
{
    protected $view = 'admission::livewire.announcements';

    public $news;

    public function render()
    {
        // FIXME: data belum dinamis
        $this->news = [];

        return $this->buildView($this->view);
    }
}
