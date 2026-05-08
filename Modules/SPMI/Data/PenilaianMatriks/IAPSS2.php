<?php

namespace Modules\SPMI\Data\PenilaianMatriks;

class IAPSS2 extends MigrateFormula
{
    protected string $assessmentGuideCode = 'IAPS-S2';

    protected function getRumus(): array
    {
        return [
            // AKUMULASI SKOR
            'C.2.4.a' => IAPSS1::getRumusPenilaianByCode('C.2.4.a'),
            'C.2.4.b' => IAPSS1::getRumusPenilaianByCode('C.2.4.b'),
            '1a' => IAPSS1::getRumusPenilaianByCode('1a'),
            'C.3.4.b' => IAPSS1::getRumusPenilaianByCode('C.3.4.b'),
            'C.3.4.c' => IAPSS1::getRumusPenilaianByCode('C.3.4.c'),
            'C.4.4.d' => IAPSS1::getRumusPenilaianByCode('C.4.4.d'),
            'C.6.4.a' => IAPSS1::getRumusPenilaianByCode('C.6.4.a'),
            'C.6.4.c' => IAPSS1::getRumusPenilaianByCode('C.6.4.c'),
            'C.6.4.d' => IAPSS1::getRumusPenilaianByCode('C.6.4.d'),
            'C.6.4.f' => IAPSS1::getRumusPenilaianByCode('C.6.4.f'),
            'C.6.4.i' => IAPSS1::getRumusPenilaianByCode('C.6.4.i'),

            // BUTIR INDICATOR
            '9.a' => [
                'rumus_penilaian' =>
                "TS-0 = YEAR(-1);
                    TS-1 = YEAR(-2);
                    TS-2 = YEAR(-3);
                    N1 = COUNT('1a', C1, {X2}{Y1}:{X2});
                    N2 = COUNT('1a', C2, {X2}{Y1}:{X2});
                    N3 = COUNT('1a', C3, {X2}{Y1}:{X2});
                    NDTPS = COUNT_IF('3a.1', null, {X7}{Y1}:{X7}, '1');
                    a = 2;
                    b = 4;
                    c = 0;
                    RK = (([a] * [N1]) + ([b] * [N2]) + ([c] * [N3])) / [NDTPS];
                    __SHOW__ = [RK], [N1], [N2], [N3], [NDTPS], [RK];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' => 'X = CASES([RK] >= 4, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' => 'X = CASES([RK] < 4, [RK])',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' => 'X = CASES([RK] < 4, [RK])',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' => 'X = CASES([RK] < 4, [RK])',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' => 'X = CASES([RK] < 4, [RK])',
                        'kriteria' => 'X'
                    ]
                ]
            ],
            '9.b' => [
                'rumus_penilaian' =>
                "TS-0 = YEAR(-1);
                    TS-2 = YEAR(-3);
                    NI = COUNT_WHERE('1a', null, {X3}{Y1}:{X10},
                        {X3} == 1
                    );
                    NN = COUNT_WHERE('1a', null, {X4}{Y1}:{X10},
                        {X4} == 1
                    );
                    NW = COUNT_WHERE('1a', null, {X5}{Y1}:{X10},
                        {X5} == 1
                    );
                    a = 3;
                    b = 9;
                    c = 12;
                    __SHOW__ = [NI], [NN], [NW];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' => 'X = CASES([NI] >= [a], 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                [NI] < [a] & [NN] >= [b],
                                3 + ([NI] / [a]),
                                ([NI] > 0 & [NI] < [a]) & ([NN] > 0 & [NN] < [b]),
                                2 + (2 * ([NI] / [a])) + ([NN] / [b]) - (([NI] * [NN]) / ([a] * [b])),
                            )',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                [NI] < [a] & [NN] >= [b],
                                3 + ([NI] / [a]),
                                ([NI] > 0 & [NI] < [a]) & ([NN] > 0 & [NN] < [b]),
                                2 + (2 * ([NI] / [a])) + ([NN] / [b]) - (([NI] * [NN]) / ([a] * [b])),
                            )',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                ([NI] == 0) & ([NN] == 0) & ([NW] >= [c]),
                                2,
                                ([NI] == 0) & ([NN] == 0) & ([NW] < [c]),
                                (2 * [NW]) / [c]
                            )',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                ([NI] == 0) & ([NN] == 0) & ([NW] >= [c]),
                                2,
                                ([NI] == 0) & ([NN] == 0) & ([NW] < [c]),
                                (2 * [NW]) / [c]
                            )',
                        'kriteria' => 'X'
                    ]
                ]
            ],
            '15.b' => IAPSS1::getRumusPenilaianByCode('15.b'),
            '17' => [
                'rumus_penilaian' =>
                "NDTPS = COUNT_IF('3a.1', null, {X7}{Y1}:{X7}, '1');
                    __SHOW__ = [NDTPS]",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' =>
                        'X = CASES([NDTPS] >= 6, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                ([NDTPS] >= 3 & [NDTPS] < 6),
                                (2 * [NDTPS]) / 3
                            )',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                ([NDTPS] >= 3 & [NDTPS] < 6),
                                (2 * [NDTPS]) / 3
                            )',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' => 'X = 0',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' => 'X = CASES(
                            [NDTPS] < 3,
                            0
                        )',
                        'kriteria' => 'X'
                    ]
                ]
            ],

            '18' => [
                'rumus_penilaian' => "
                    NDGB = COUNT_IF('3a.1', null, {X8}{Y1}:{X8}, 'Guru Besar');
                    NDLK = COUNT_IF('3a.1', null, {X8}{Y1}:{X8}, 'Lektor Kepala');
                    NDTPS = COUNT_IF('3a.1', null, {X7}{Y1}:{X7}, '1');
                    PGBLKL_RAW = (([NDGB] + [NDLK]) / [NDTPS]) * 1;
                    PGBLKL = CONCAT([PGBLKL_RAW] * 100, '%');
                    __SHOW__ = [NDGB], [NDLK], [NDTPS], [PGBLKL];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' => 'X = CASES([PGBLKL_RAW] >= 0.7, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' => 'X = CASES([PGBLKL_RAW] < 0.7, 2 + ((20 * [PGBLKL_RAW]) / 7))',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' => 'X = CASES([PGBLKL_RAW] < 0.7, 2 + ((20 * [PGBLKL_RAW]) / 7))',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' => 'X = 0',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' => 'X = 0',
                        'kriteria' => 'X'
                    ]
                ]
            ],
            '19' => IAPSS1::getRumusPenilaianByCode('21'),
            '20' => IAPSS1::getRumusPenilaianByCode('22'),
            '21' => IAPSS1::getRumusPenilaianByCode('23'),
            '22' => IAPSS1::getRumusPenilaianByCode('24'),
            '23' => IAPSS1::getRumusPenilaianByCode('25'),
            '24' => [
                'rumus_penilaian' =>
                "NI = SUM('3b.3', null, {X3}{Y3}:{X5}{Y3});
                    NN = SUM('3b.3', null, {X3}{Y2}:{X5}{Y2});
                    NL = SUM('3b.3', null, {X3}{Y1}:{X5}{Y1});
                    NDTPS = COUNT_IF('3a.1', null, {X7}{Y1}:{X7}, '1');
                    RI = [NI] / 3 / [NDTPS];
                    RN = [NN] / 3 / [NDTPS];
                    RL = [NL] / 3 / [NDTPS];
                    a = 0.07;
                    b = 0.5;
                    c = 1.5;
                    __SHOW__ = [NDTPS], [NI], [NN], [NL], [RI], [NN], [RL], [RN];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' =>
                        'X = CASES([RI] >= [a], 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = CASES([RI] < [a] & [RN] >= [b], 3 + ([RI] / [a]),
                            ([RI] > 0 & [RI] < [a]) & ([RN] > 0 & [RN] < [b]), 2 + (2 * ([RI] / [a])) + ([RN] / [b]) - (([RI] * [RN]) / ([a] * [b]))
                        )',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES([RI] < [a] & [RN] >= [b], 3 + ([RI] / [a]),
                            ([RI] > 0 & [RI] < [a]) & ([RN] > 0 & [RN] < [b]), 2 + (2 * ([RI] / [a])) + ([RN] / [b]) - (([RI] * [RN]) / ([a] * [b]))
                        )',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' =>
                        'X = CASES([RI] == 0 & [RN] == 0 & [RL] >= [c], 2,
                            [RI] == 0 & [RN] == 0 & [RL] < [c], (2 * [RL]) / [c]
                        )',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' =>
                        'X = CASES([RI] == 0 & [RN] == 0 & [RL] >= [c], 2,
                            [RI] == 0 & [RN] == 0 & [RL] < [c], (2 * [RL]) / [c],
                            1 == 1,
                            0
                        )',
                        'kriteria' => 'X'
                    ],
                ]
            ],
            '25' => IAPSS1::getRumusPenilaianByCode('27'),
            '26' => IAPSS1::getRumusPenilaianByCode('28'),
            '27' => IAPSS1::getRumusPenilaianByCode('29'),
            '30' => IAPSS1::getRumusPenilaianByCode('32'),
            '31' => IAPSS1::getRumusPenilaianByCode('33'),
            '32' => IAPSS1::getRumusPenilaianByCode('34'),
            '42' => [
                'rumus_penilaian' => "
                    TS-0 = YEAR(-1);
                    TS-1 = YEAR(-2);
                    TS-2 = YEAR(-3);
                    NMKI = COUNT_IFS('5b', null, {X6}{Y1}:{X6}, [TS-0], [TS-1], [TS-2]);
                    NMK = COUNT('5a', null, {X15}{Y1}:{X15});
                    PMKI_RAW = ([NMKI] / [NMK]) * 1;
                    PMKI = CONCAT([PMKI_RAW] * 100, '%');
                    __SHOW__ = [NMKI], [NMK], [PMKI];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' => 'X = CASES([PMKI_RAW] >= 0.5, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' => 'X = CASES(([PMKI_RAW] < 0.5 & [PMKI_RAW] > 0.25), 8 * [PMKI_RAW])',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' => 'X = CASES([PGBLKL_RAW] <= 0.25, 2)',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' => 'X = 0',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' => 'X = 0',
                        'kriteria' => 'X'
                    ]
                ]
            ],
            '44.a' => IAPSS1::getRumusPenilaianByCode('47.a'),
            '46' => IAPSS1::getRumusPenilaianByCode('49'),
            '47' => [
                'rumus_penilaian' => "
                    TS-0 = YEAR(-1);
                    TS-1 = YEAR(-2);
                    TS-2 = YEAR(-3);
                    NTM = COUNT_IFS('6b', null, {X6}{Y1}:{X6}, [TS-0], [TS-1], [TS-2]);
                    NPD = SUM('3b.2', null, {X3}{Y1}:{X5});
                    PPTM_RAW = ([NTM] / [NPD]) * 1;
                    PPTM = CONCAT([PPTM_RAW] * 100, '%');
                    __SHOW__ = [NTM], [NPD], [PPTM];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' => 'X = CASES([PPTM_RAW] >= 0.25, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' => 'X = CASES([PPTM_RAW] < 0.25, 1 + (12 * [PPTM_RAW]))',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' => 'X = CASES([PPTM_RAW] < 0.25, 1 + (12 * [PPTM_RAW]))',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' => 'X = CASES([PPTM_RAW] < 0.25, 1 + (12 * [PPTM_RAW]))',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' => 'X = 0',
                        'kriteria' => 'X'
                    ]
                ]
            ],
            '50' => [
                'rumus_penilaian' =>
                "RIPK = AVG_X('8a', null, {X4}{Y1}:{X4});
                    __SHOW__ = [RIPK];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' =>
                        'X = CASES([RIPK] >= 3.5, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                [RIPK] >= 3.00 & [RIPK] < 3.5,
                                (4 * [RIPK]) - 10
                            )',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                [RIPK] >= 3.00 & [RIPK] < 3.5,
                                (4 * [RIPK]) - 10
                            )',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' => 'X = 0',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' => 'X = 0',
                        'kriteria' => 'X'
                    ]
                ]
            ],
            '51' => IAPSS1::getRumusPenilaianByCode('54'),
            '52' => [
                "rumus_penilaian" => "
                    MS_TS-1 = SUM('8c.3', null, {X7}{Y1}:{X7}{Y1}) * SUM('8c.3', null, {X8}{Y1}:{X8}{Y1});
                    MS_TS-2 = SUM('8c.3', null, {X7}{Y2}:{X7}{Y2}) * SUM('8c.3', null, {X8}{Y2}:{X8}{Y2});
                    MS_TS-3 = SUM('8c.3', null, {X7}{Y3}:{X7}{Y3}) * SUM('8c.3', null, {X8}{Y3}:{X8}{Y3});
                    MS = ([MS_TS-1] + [MS_TS-2] + [MS_TS-3]) / SUM('8c.3', null, {X7}{Y1}:{X7});

                    __SHOW__ = [MS];",
                "rumus_skor" => [
                    '4' => [
                        'rumus_penilaian' => 'X = CASES([MS] > 1.5 & [MS] <= 2.5, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' => '
                        X = CASES(
                            [MS] > 1 & [MS] <= 1.5,
                            (8 * [MS]) - 8,
                            [MS] > 2.5 & [MS] <= 4,
                            (32 - (8 * [MS])) / 3
                        )',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' => '
                        X = CASES(
                            [MS] > 1 & [MS] <= 1.5,
                            (8 * [MS]) - 8,
                            [MS] > 2.5 & [MS] <= 4,
                            (32 - (8 * [MS])) / 3
                        )',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' => '
                        X = CASES(
                            [MS] > 1 & [MS] <= 1.5,
                            (8 * [MS]) - 8,
                            [MS] > 2.5 & [MS] <= 4,
                            (32 - (8 * [MS])) / 3
                        )',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' => 'X = CASES([MS] <= 1, 0)',
                        'kriteria' => 'X'
                    ]
                ]
            ],
            '53' => IAPSS1::getRumusPenilaianByCode('57'),
            '54' => [
                'rumus_penilaian' => "
                    MGR = SUM('8c.3', null, {X7}{Y1}:{X7});
                    MGA = SUM('8c.3', null, {X2}{Y1}:{X2});
                    PPS_RAW = [MGR] / [MGA];
                    PPS = CONCAT([PPS_RAW] * 100, '%');
                    __SHOW__ = [PPS];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' => 'X = CASES([PPS_RAW] >= 0.85, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' => 'X = CASES(([PPS_RAW] >= 0.3 & [PPS_RAW] < 0.85), ((80 * [PPS_RAW]) - 24) / 11)',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' => 'X = CASES(([PPS_RAW] >= 0.3 & [PPS_RAW] < 0.85), ((80 * [PPS_RAW]) - 24) / 11)',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' => 'X = CASES(([PPS_RAW] >= 0.3 & [PPS_RAW] < 0.85), ((80 * [PPS_RAW]) - 24) / 11)',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' => 'X = CASES([PPS_RAW] < 0.3, 0)',
                        'kriteria' => 'X'
                    ]
                ]
            ],
            '56' => IAPSS1::getRumusPenilaianByCode('61'),
            '57' => IAPSS1::getRumusPenilaianByCode('63'),
            '58' => IAPSS1::getRumusPenilaianByCode('64'),
            '59' => [
                'rumus_penilaian' => "
                    NAS = COUNT('8f.2', null, {X2}{Y1}:{X2});

                    __SHOW__ = [NAS];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' => 'X = CASES([NAS] >= 2, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' => 'X = CASES([NAS] == 1, 3)',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' => 'X = CASES([NAS] == 0, 2)',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' => 'X = 0',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' => 'X = 0',
                        'kriteria' => 'X'
                    ],
                ]
            ],
            '60' => IAPSS1::getRumusPenilaianByCode('65'),
        ];
    }
}
