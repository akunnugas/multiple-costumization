<?php

namespace Modules\PMB\Helpers;

class Menu
{
    /**
     * Navbar atas.
     *
     * @return array
     */
    public static function navbar(): array
    {
        return [
//            ['label' => 'Beranda', 'path' => '/'],
            ['label' => __('pmb::registrants.main'), 'path' => 'registrants'],
            ['label' => 'Pengaturan', 'items' => [
                ['label' => __('pmb::registration_periods.main'), 'path' => 'registration-periods'],
                ['label' => '(Belum) Program Studi', 'path' => '/'],
                ['label' => '(Belum) Seleksi Pendaftaran', 'path' => '/'],
                ['label' => '(Belum) Syarat Seleksi', 'path' => '/'],
                ['label' => '(Belum) Jadwal Seleksi', 'path' => '/'],
                ['label' => '(Belum) UKT (Uang Kuliah Tunggal)', 'path' => '/'],
                ['label' => '(Belum) Pengaturan PMB', 'path' => '/'],
            ]],
            ['label' => 'Seleksi', 'items' => [
                ['label' => '(Belum) Nilai Seleksi', 'path' => '/'],
                ['label' => '(Belum) Syarat Seleksi', 'path' => '/'],
                ['label' => '(Belum) Pengisian Nilai', 'path' => '/'],
                ['label' => '(Belum) Kelengkapan Syarat', 'path' => '/'],
                ['label' => '(Belum) UKT Pendaftar', 'path' => '/'],
                ['label' => '(Belum) KIP Kuliah', 'path' => '/'],
            ]],
            ['label' => 'Kelulusan', 'items' => [
                ['label' => '(Belum) Rekomendasi Prodi', 'path' => '/'],
                ['label' => '(Belum) Registrasi Ulang', 'path' => '/'],
                ['label' => '(Belum) Generate Mahasiswa', 'path' => '/'],
                ['label' => '(Belum) Pembatalan NIM', 'path' => '/'],
            ]],
            ['label' => 'Referensi', 'items' => [
                ['label' => 'Pendaftaran', 'path' => 'batches'],
                ['label' => 'Berita', 'path' => 'announcements'],
                ['label' => 'Seleksi', 'path' => 'assessment-types'],
                ['label' => '(BELUM 4 Sub Menu) Pendidikan', 'path' => '/'],
                ['label' => 'Wilayah', 'path' => 'countries'],
                ['label' => '(BELUM 4 Sub Menu) Biodata', 'path' => '/'],
                ['label' => 'Pelengkap', 'path' => 'religions'],
            ]],
            ['label' => 'Lainnya (Sementara)', 'items' => [
                ['label' => __('pmb::periods.main'), 'path' => 'periods'],
                ['label' => __('pmb::activities.main'), 'path' => 'activities']
            ]],
        ];
    }

    /**
     * Sidebar untuk menu-menu level 3.
     *
     * @param $key
     * @return array|null
     */
    public static function masterSidebar($key): ?array
    {
        return match ($key) {
            'reference-registration' => [
                'parent' => 'batches', 'items' => [
                    [
                        'label' => 'Pendaftaran', 'items' => [
                            ['label' => __('pmb::batches.main'), 'path' => 'batches'],
                            ['label' => __('pmb::registration_paths.main'), 'path' => 'registration-paths'],
                            ['label' => __('pmb::lecture_systems.main'), 'path' => 'lecture-systems'],
                        ]
                    ],
                ]
            ],
            'reference-announcement' => [
                'parent' => 'announcements', 'items' => [
                    [
                        'label' => 'Berita', 'items' => [
                            ['label' => __('pmb::announcements.main'), 'path' => 'announcements'],
                            ['label' => __('pmb::related_links.main'), 'path' => 'related-links'],
                            ['label' => __('pmb::broadcasts.main'), 'path' => 'broadcasts'],
                        ]
                    ],
                ]
            ],
            'reference-selection' => [
                'parent' => 'assessment-types', 'items' => [
                    [
                        'label' => 'Seleksi', 'items' => [
                            ['label' => __('pmb::assessment_types.main'), 'path' => 'assessment-types'],
                            ['label' => __('pmb::assessment_compositions.main'), 'path' => 'assessment-compositions'],
                            ['label' => __('pmb::requirement_types.main'), 'path' => 'requirement-types'],
                            ['label' => __('pmb::assessment_requirements.main'), 'path' => 'assessment-requirements'],
                            ['label' => __('pmb::assessment_selections.main'), 'path' => 'assessment-selections'],
                            ['label' => __('pmb::subjects.main'), 'path' => 'subjects'],
                            ['label' => __('pmb::report_evaluations.main'), 'path' => 'report-evaluations']
                        ]
                    ]
                ],
            ],
            'region' => [
                'parent' => 'countries', 'items' => [
                    [
                        'label' => 'Wilayah', 'items' => [
                            ['label' => __('pmb::countries.main'), 'path' => 'countries'],
                            ['label' => __('pmb::provinces.main'), 'path' => 'provinces'],
                            ['label' => __('pmb::cities.main'), 'path' => 'cities'],
                            ['label' => __('pmb::districts.main'), 'path' => 'districts']
                        ]
                    ],
                ],
            ],
            'other' => [
                'parent' => 'religions', 'items' => [
                    [
                        'label' => 'Personal', 'items' => [
                            ['label' => __('pmb::religions.main'), 'path' => 'religions'],
                            // ['label' => __('pmb::languages.main'), 'path' => 'languages'],
                            ['label' => __('pmb::jobs.main'), 'path' => 'jobs'],
                            ['label' => __('pmb::salaries.main'), 'path' => 'salaries'],
                            ['label' => __('pmb::family_statuses.main'), 'path' => 'family_statuses'],
                            // ['label' => __('pmb::marriage_statuses.main'), 'path' => 'marriage-statuses'],
                            // ['label' => __('pmb::banks.main'), 'path' => 'banks'],
                            // ['label' => __('pmb::ethnics.main'), 'path' => 'ethnics'],
                            // ['label' => __('pmb::blood_types.main'), 'path' => 'blood-types'],
                        ],
                    ],
                    [
                        'label' => 'Pendidikan', 'items' => [
                            ['label' => __('pmb::degrees.main'), 'path' => 'degrees'],
                            ['label' => __('pmb::institution_types.main'), 'path' => 'institution-types'],
                            ['label' => __('pmb::schools.main'), 'path' => 'schools'],
                            ['label' => __('pmb::general_universities.main'), 'path' => 'general-universities'],
                            ['label' => __('pmb::general_programs.main'), 'path' => 'general-programs'],

                        ],
                    ],
                    [
                        'label' => 'Ruangan', 'items' => [
                            ['label' => __('pmb::room_types.main'), 'path' => 'room-types'],
                            ['label' => __('pmb::campuses.main'), 'path' => 'campuses'],
                            ['label' => __('pmb::buildings.main'), 'path' => 'buildings'],
                            ['label' => __('pmb::rooms.main'), 'path' => 'rooms'],
                            ['label' => __('pmb::assessment_rooms.main'), 'path' => 'assessment-rooms'],
                        ],
                    ],
                ],
            ],
            default => null,
        };
    }

    /**
     * Sidebar khusus untuk menu periode pendaftaran.
     *
     * @param int $registrationPeriodId
     * @return array
     */
    public static function registrationPeriodSidebar(int $registrationPeriodId): array
    {
        return [
            'parent' => 'registration-periods', 'items' => [
                [
                    'label' => __('pmb::registration_periods.main'), 'items' => [
                        [
                            'label' => 'Data ' . __('pmb::registration_periods.main'),
                            'path' => 'registration-periods/' . $registrationPeriodId
                        ],
                        [
                            'label' => '(Belum) Tarif Formulir',
                            'path' => 'registration-periods/' . $registrationPeriodId . '?test'
                        ],
                        [
                            'label' => __('pmb::registration_periods/program_distributions.main'),
                            'path' => 'registration-periods/' . $registrationPeriodId . '/program-distributions'
                        ],
                        [
                            'label' => __('pmb::registration_periods/program_assessments.main'),
                            'path' => 'registration-periods/' . $registrationPeriodId . '/program-assessments'
                        ],
                        [
                            'label' => __('pmb::registration_periods/program_compositions.main'),
                            'path' => 'registration-periods/' . $registrationPeriodId . '/program-compositions'
                        ],
                        [
                            'label' => __('pmb::registration_periods/registration_requirements.main'),
                            'path' => 'registration-periods/' . $registrationPeriodId . '/registration-requirements'
                        ],
                        [
                            'label' => '(BELUM) Kuesioner',
                            'path' => 'registration-periods/' . $registrationPeriodId . '?test2'
                        ],
                    ]
                ],
            ]
        ];
    }

    /**
     * Sidebar khusus untuk menu periode broadcast.
     *
     * @param int $broadcastId
     * @return array
     */
    public static function broadcastSidebar(int $broadcastId): array
    {
        return [
            'parent' => 'broadcasts', 'items' => [
                [
                    'label' => __('pmb::broadcasts.main'), 'items' => [
                        [
                            'label' => 'Data ' . __('pmb::broadcasts.main'),
                            'path' => 'broadcasts/' . $broadcastId
                        ],
                        [
                            'label' => '(Pending) ' . __('pmb::broadcasts/broadcast_recipients.main'),
                            'path' => 'broadcasts/' . $broadcastId . '/broadcast-recipients'
                        ]
                    ]
                ]
            ]
        ];
    }
    public static function registrantSidebar($id): ?array
    {
        return [
            'parent' => 'registrants', 'items' => [
                [
                    'label' => __('pmb::registrants.main'), 'items' => [
                        [
                            'label' => 'Data ' . __('pmb::registrants.main'),
                            'path' => 'registrants/' . $id
                        ],
                        [
                            'label' => __('pmb::registrants/families.main'),
                            'path' => 'registrants/' . $id . '/families'
                        ],
                    ]
                ],
            ]
        ];
    }
}
