<?php

namespace Modules\Admission\Livewire;

use Modules\Core\Helpers\Page;
use Modules\Core\Services\UnitKerjaManagementService;
use Modules\PMB\Services\SebaranProdiManagementService;

class Home extends FrontComponent
{
    protected $view = 'admission::livewire.home';

    // FIXME: ambil dari setting sim: spmb_home_title
    public string $homeTitle = 'Portal Pendaftaran Mahasiswa Baru';

    // FIXME: ambil dari session: university_name
    public string $universityName = 'Universitas SEVIMA';

    // FIXME: ambil dari setting sim: university_logo
    public string $universityLogo;

    // FIXME: ambil dari setting sim: spmb_gambar_unggulan
    public $featuredImage = '';

    // FIXME: ambil dari setting sim: pmb_background
    public $admissionBackground = '';

    // FIXME: ambil dari database
    public $pmbBrochure = '';

    // option jenjang, program studi, dan sistem kuliah
    public $degreeOpt = []; // jenjang
    public $selectedDegree;
    public $studyProgramOpt = []; // program studi
    public $selectedStudyProgram;
    public $lectureSystemOpt = []; // sistem kuliah
    public $selectedLectureSystem;

    // jenjang prodi
    public $programDegrees;

    public $backgroundBannerSection;

    public $news;

    public function render()
    {
        if (empty($this->homeTitle)) {
            $this->homeTitle = 'Portal Pendaftaran Mahasiswa Baru';
        }

        $this->backgroundBannerSection = $this->featuredImage ?? $this->admissionBackground;
        $this->universityLogo = Page::quantumAsset('images/logo-kampus.png');
        $this->news = [];

        // [Start] get option jenjang, program studi, dan sistem kuliah
        $programDistributionService = new SebaranProdiManagementService();
        [$degrees, $studyPrograms, $lectureSystems] = $programDistributionService->getDegreeProgramDistributionAndLectureSystem();

        $this->degreeOpt = $degrees;
        $this->studyProgramOpt = !empty($studyPrograms) ? $studyPrograms[null] : []; // get program studi yg jenjangnya null (all)
        asort($this->studyProgramOpt);
        $this->lectureSystemOpt = $lectureSystems;
        // [End] get option jenjang, program studi, dan sistem kuliah

        // [Start] get jenjang prodi
        $organizationService = new UnitKerjaManagementService();
        $this->programDegrees = $organizationService->getStudyProgramWithDegree(true, true);

        return $this->buildView($this->view);
    }

    public function searchRegistrationPath()
    {
    }
}
