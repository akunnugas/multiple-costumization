<?php

namespace Modules\HR\Helpers;

use Modules\Gate\Models\Modul;

class Menu
{
    /**
     * Navbar atas.
     *
     * @return array
     */
    public static function navbar()
    {
        return [
            ['label' => 'Beranda', 'path' => '/'],
            ['label' => 'Pegawai', 'path' => 'employees'],
            [
                'label' => 'Referensi', 'items' => [
                    ['label' => 'Kepegawaian', 'path' => 'organizations'],
                    ['label' => 'Aktifitas', 'path' => 'certification-types'],
                    ['label' => 'Wilayah', 'path' => 'countries'],
                    ['label' => 'Pelengkap', 'path' => 'religions'],
                ]
            ],
        ];
    }

    /**
     * Region sidebar.
     */
    public static function masterSidebar($key)
    {
        return match ($key) {
            'activity' => [
                'parent' => 'certification-types', 'items' => [
                    [
                        'label' => 'Aktifitas', 'items' => [
                            ['label' => self::getTitle('certification_types'), 'path' => 'certification-types'],
                            ['label' => self::getTitle('lecturer_dedication_types'), 'path' => 'lecturer-dedication-types'],
                            ['label' => self::getTitle('research_outputs'), 'path' => 'research-outputs'],
                            ['label' => self::getTitle('output_types'), 'path' => 'output-types'],
                        ]
                    ],
                ]
            ],
            'region' => [
                'parent' => 'countries', 'items' => [
                    [
                        'label' => 'Wilayah', 'items' => [
                            ['label' => self::getTitle('countries'), 'path' => 'countries'],
                            ['label' => self::getTitle('provinces'), 'path' => 'provinces'],
                            ['label' => self::getTitle('cities'), 'path' => 'cities'],
                            ['label' => self::getTitle('districts'), 'path' => 'districts']
                        ]
                    ],
                ],
            ],
            'employee' => [
                'parent' => 'organizations', 'items' => [
                    [
                        'label' => 'Kepegawaian', 'items' => [
                            ['label' => self::getTitle('organizations'), 'path' => 'organizations'],
                            ['label' => self::getTitle('employee_statuses'), 'path' => 'employee-statuses'],
                            ['label' => self::getTitle('work_relations'), 'path' => 'work-relations'],
                        ]
                    ],
                    [
                        'label' => 'Pangkat & Jabatan', 'items' => [
                            ['label' => self::getTitle('position_levels'), 'path' => 'position-levels'],
                            ['label' => self::getTitle('promotion_types'), 'path' => 'promotion-types'],
                            ['label' => self::getTitle('jabatan_akademik'), 'path' => 'jabatan-akademik'],
                            ['label' => self::getTitle('functional_positions'), 'path' => 'functional-positions'],
                            ['label' => self::getTitle('echelons'), 'path' => 'echelons'],
                            ['label' => self::getTitle('structural_position_types'), 'path' => 'structural-position-types'],
                            ['label' => self::getTitle('structural_positions'), 'path' => 'structural-positions'],
                            ['label' => self::getTitle('field_studies'), 'path' => 'field-studies'],
                        ]
                    ],
                    [
                        'label' => 'Administratif & Publikasi', 'items' => [
                            ['label' => self::getTitle('sk_types'), 'path' => 'sk-types'],
                            ['label' => self::getTitle('academic_titles'), 'path' => 'academic-titles'],
                            ['label' => self::getTitle('publication_medias'), 'path' => 'publication-medias'],
                        ]
                    ]
                ],
            ],
            'other' => [
                'parent' => 'religions', 'items' => [
                    [
                        'label' => 'Pelengkap', 'items' => [
                            ['label' => self::getTitle('religions'), 'path' => 'religions'],
                            ['label' => self::getTitle('languages'), 'path' => 'languages'],
                            ['label' => self::getTitle('jobs'), 'path' => 'jobs'],
                            ['label' => self::getTitle('marriage_statuses'), 'path' => 'marriage-statuses'],
                            ['label' => self::getTitle('degrees'), 'path' => 'degrees'],
                            ['label' => self::getTitle('general_universities'), 'path' => 'general-universities'],
                            ['label' => self::getTitle('banks'), 'path' => 'banks'],
                            ['label' => self::getTitle('ethnics'), 'path' => 'ethnics'],
                            ['label' => self::getTitle('blood_types'), 'path' => 'blood-types'],
                        ]
                    ],
                ],
            ],
            default => null,
        };
    }

    private static function getTitle(string $resource)
    {
        return __(Modul::CODE_KEPEGAWAIAN . '::' . $resource . '.main');
    }
}
