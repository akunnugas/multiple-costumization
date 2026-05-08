<?php

namespace Modules\Admission\Livewire;

use Modules\Core\Helpers\Page;

class AnnouncementDetail extends FrontComponent
{
    protected $view = 'admission::livewire.announcement-detail';

    public $id;
    public $new;
    public array $newFiles;

    public function mount($id)
    {
        $this->id = $id;
    }

    public function render()
    {
        // FIXME: datanya belum dinamis dari db
        $this->new['created_by'] = 'Sevima';
        $this->new['created_at'] = '14 November 2023';
        $this->new['title'] = 'asdasda';
        $this->new['thumbnail'] = Page::quantumAsset('images/logo-kampus.png');
        $this->new['information'] = "<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc efficitur risus ac tempor maximus. Vivamus vestibulum libero malesuada orci tincidunt, ut ultricies dolor tincidunt. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Nam sit amet faucibus nibh, placerat venenatis lectus. Etiam ut nisi lorem. Praesent dignissim semper ultricies. Etiam ornare dui ac metus porttitor feugiat. Proin iaculis felis dui, et porta nisl interdum venenatis.</p>";

        $this->newFiles[] = [
            'name' => 'Pengumuman Penerimaan Mahasiswa Baru 2023.png',
            'url' => Page::quantumAsset('images/logo-kampus.png')
        ];

        return $this->buildView($this->view);
    }
}
