<?php

namespace Modules\Admission\Helpers;

use Illuminate\Support\Facades\Auth;

class Menu
{
    /**
     * Navbar atas.
     *
     * @return array
     */
    public static function navbar(): array
    {
        // FIXME: bukan cek user tapi cek pendaftar
        $user = Auth::user();

        if ($user) {
            $navbar = [
                ['label' => __('admission::home.registration'), 'path' => 'registration'],
                ['label' => __('admission::home.finance'), 'path' => 'finance']
            ];
        } else {
            $navbar = [
                ['label' => __('admission::home.main'), 'path' => '/'],
                ['label' => __('admission::home.registration_path'), 'path' => 'registration-path']
            ];
        }

        $navbar[] = ['label' => __('admission::home.information'), 'items' => [
            ['label' => __('admission::home.general_program'), 'path' => '/'],
            ['label' => __('admission::home.information_and_announcement'), 'path' => 'announcements'],
        ]];

        return $navbar;
    }

    /**
     * Sidebar untuk mobile.
     *
     * @return array[]
     */
    public static function mobileSidebar()
    {
        // FIXME: bukan cek user tapi cek pendaftar
        $user = Auth::user();

        if ($user) {
            $sidebar = [
                ['label' => __('admission::home.registration'), 'path' => 'registration'],
                ['label' => __('admission::home.finance'), 'path' => 'finance']
            ];
        } else {
            $sidebar = [
                ['label' => __('admission::home.main'), 'path' => '/'],
                ['label' => __('admission::home.registration_path'), 'path' => 'registration-path']
            ];
        }

        $sidebar[] = ['label' => __('admission::home.general_program'), 'path' => '/'];
        $sidebar[] = ['label' => __('admission::home.information_and_announcement'), 'path' => 'announcements'];

        return [
            'items' => [
                [
                    'items' => $sidebar
                ]
            ]
        ];
    }

    /**
     * Menu untuk langkah pendaftaran (after peserta login).
     *
     * @return array
     */
    public static function stepRegistrationStepMenu()
    {
        // FIXME: ambil dinamis
        $registrant = $period = $selections = [];
        $isRPL = $isCBT = false;
        // title, translate, isvalid, visible,

        // * Biodata
        $step['biodata'] = [
            'title' => 'Biodata',
            'path' => 'registration-steps/biodata',
            'visible' => true,
        ];
//        if (!empty($registrant['is_valid'])) {
//            $step['biodata']['checked'] = true;
//        }
        $step['biodata']['checked'] = true;

        // * Administrasi
        $step['administration'] = [
            'title' => 'Administrasi',
            'path' => 'registration-steps/administration',
            'visible' => true,
//            'visible' => !empty($registrant['is_valid'])
        ];
        // TODO: checck jika syarat pendaftar lengkap maka adminisrasinya checked
//        if(mSyaratPendaftar::isLengkap($conn, $pendaftar['idpendaftar'] , $periode['idperiodedaftar'], array(mJenisSyarat::ADMINISTRASI, mJenisSyarat::UKT), $pendaftar) && $pendaftar['isvalid']){
            $step['administration']['checked'] = true;
        // TODO: belum mengakomodir RPL
//            $isAllowRpl = true;

        // * Nilai Rapor
        if (!empty($period['penilaianrapor'])) {
            $step['rapor'] = [
                'title' => 'Nilai Rapor',
                'path' => 'registration-steps/rapor',
                'visible' => !empty($registrant['is_valid'])
            ];
        }

        // * Nilai Transkrip Sementara
        if (!empty($period['istransfer']) && !$isRPL) {
            $step['nilaitransfer'] = [
                'title' => 'Nilai Transkrip Sementara',
                'visible' => !empty($registrant['is_valid'])
            ];
        }

        // * Bidik Misi / KIP Kuliah
        // TODO: pengecekan bidikmisi
        $datawali = [];
        $poinbidikmisi = 0;
        if (!empty($period['bukabidikmisi']) && $poinbidikmisi <= $period['maxpoinbidikmisi'] && !empty($datawali)) {
            $step['bidikmisi'] = [
                'title' => 'KIP Kuliah',
                'path' => 'registration-steps/bidikmisi',
                'visible' => true
            ];
//            if(mSyaratPendaftar::isLengkap($conn, $pendaftar['idpendaftar'], $pendaftar['idperiodedaftar'], array(mJenisSyarat::BIDIK_MISI)) && $pendaftar['isvalid'] && $pendaftar['isbidikmisi']){
//                $step['bidikmisi']['checked'] = true;
        }

        // * Finalisasi Data Pendaftar
        $visible = !empty($period['isbisaedit']);
//        if (!mSyaratPendaftar::isLengkap($conn, $pendaftar['idpendaftar'] , $periode['idperiodedaftar'], array(mJenisSyarat::ADMINISTRASI, mJenisSyarat::UKT), $pendaftar) or empty($pendaftar['isvalid'])) {
//            $visibleFinalisasi = false;
//        }
        if ($visible and (empty($period['tglakhirfinalisasi']) or date('Y-m-d') <= $period['tglakhirfinalisasi'])) {
            if($isRPL && $registrant['isvalidrpl'] != 2){
                $visibleFinalisasi = false;
            }
            $step['finalization'] = [
                'title' => 'Pengumpulan Data',
                'path' => 'registration-steps/finalization',
                'visible' => $visibleFinalisasi
            ];
        }

        // TODO: pengecekan checked untuk nilai rapor, nilaitransfer, dan finalisasi belum

        // * Jadwal Seleksi
//        if (!empty($selections)) {
//            if (empty($registrant['isvalid']) || $registrant['isfinal'] == '0') {
//                $visibleSeleksi = false;
//            }
//            $step['selection'] = [
//                'title' => 'Jadwal Seleksi',
//                'path' => 'registration-steps/selection',
//                'visible' => $visibleSeleksi
//            ];
//        }
        $step['selection'] = [
            'title' => 'Jadwal Seleksi',
            'path' => 'registration-steps/selection',
            'visible' => false
        ];

        // * Login CBT
//        $step['logincbt'] = array('title' => 'Login ke CBT', 'add' => 'target="_blank"');

        // * Hasil Seleksi
        $step['selection-result'] = [
            'title' => 'Hasil Seleksi',
            'path' => 'registration-steps/selection-result',
            'visible' => !empty($registrant['isvalid']) && !empty($registrant['isfinal']),
            'childMenu' => 'selection-result-2' // hasil-seleksi-2 kalo yg lama
        ];

        // * Daftar Ulang
        $step['re-register'] = [
            'title' => 'Daftar Ulang',
            'path' => 'registration-steps/re-register',
//            'visible' => !empty($registrant['isfinal']) && isset($_COOKIE['s&k_hs2_'.$registrant['idpendaftar']]) && $registrant['isditerima']
        ];

        //        $step = [
//            'biodata' => [
//                'title' => 'Biodata',
//                'path' => 'registration-steps/biodata'
//            ],
//            'administration' => [
//                'title' => 'Administrasi',
//                'path' => 'registration-steps/administration'
//            ],
//            'selection' => [
//                'title' => 'Seleksi',
//                'path' => 'registration-steps/selection'
//            ],
//            'selection-result' => [
//                'title' => 'Hasil Seleksi',
//                'path' => 'registration-steps/selection-result'
//            ],
//            're-register' => [
//                'title' => 'Daftar Ulang',
//                'path' => 'registration-steps/re-register',
//                'visible' => false
//            ]
//        ];

        // * Prepared untuk frontend
        $i = 0;
        $currentUrl = request()->segment(2) . '/' . request()->segment(3);
        $activeMenu = null;
        foreach ($step as $key => $val) {
            $isActive = $currentUrl == $val['path'] || (!empty($val['childMenu']) && $val['childMenu'] == $currentUrl);

            $step[$key]['active'] = $isActive;
            $step[$key]['disabled'] = empty($val['visible']);
            $step[$key]['stepNumber'] = ++$i;

            if ($isActive) {
                $activeMenu = $step[$key];
            }
        }

        return [
            'steps' => $step,
            'activeMenu' => $activeMenu
        ];
    }
}
