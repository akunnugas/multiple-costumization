<?php

namespace Modules\Admission\Livewire;

use Modules\Core\Models\UnitKerja;

class ProgramDetail extends FrontComponent
{
    protected $view = 'admission::livewire.program-detail';

    public string $title = 'Detail Prodi';

    public $parentNav = [];

    public $organizationId;

    public $program;

    protected $accreditationList = UnitKerja::ACCREDITATION_LIST;

    public function mount($id)
    {
        $this->organizationId = $id;
    }

    public function render()
    {
        // FIXME: data dibawah seharusnya dinamis dari settingan pmb
        $this->program = [];
        $this->program['degree_id'] = 'S1';
        $this->program['name'] = 'Manajemen';
        $this->program['website'] = 'https://sevima.com/manajemen';
        $this->program['accreditation'] = $this->accreditationList['U'];
        $this->program['image'] = asset('images/admissions/dummy-profile-program-studi-s1-manajemen-bg.jpeg');
        $this->program['description'] = "<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc efficitur risus ac tempor maximus. Vivamus vestibulum libero malesuada orci tincidunt, ut ultricies dolor tincidunt. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Nam sit amet faucibus nibh, placerat venenatis lectus. Etiam ut nisi lorem. Praesent dignissim semper ultricies. Etiam ornare dui ac metus porttitor feugiat. Proin iaculis felis dui, et porta nisl interdum venenatis.</p>";
        $this->program['career_prospects'] = "<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc efficitur risus ac tempor maximus. Vivamus vestibulum libero malesuada orci tincidunt, ut ultricies dolor tincidunt. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Nam sit amet faucibus nibh, placerat venenatis lectus. Etiam ut nisi lorem. Praesent dignissim semper ultricies. Etiam ornare dui ac metus porttitor feugiat. Proin iaculis felis dui, et porta nisl interdum venenatis.</p>";
        $this->program['learning_materials'] = "<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc efficitur risus ac tempor maximus. Vivamus vestibulum libero malesuada orci tincidunt, ut ultricies dolor tincidunt. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Nam sit amet faucibus nibh, placerat venenatis lectus. Etiam ut nisi lorem. Praesent dignissim semper ultricies. Etiam ornare dui ac metus porttitor feugiat. Proin iaculis felis dui, et porta nisl interdum venenatis.</p>";

        return $this->buildView($this->view);
    }
}
