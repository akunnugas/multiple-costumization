<?php

namespace Modules\SPMI\Data\PenilaianMatriks;

class IAPSD3 extends MigrateFormula
{
    protected string $assessmentGuideCode = 'IAPS-D3';

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
                    b = 1;
                    c = 3;
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
            '9.b' => IAPSS1::getRumusPenilaianByCode('9.b'),
            '14.b' => [
                'rumus_penilaian' =>
                "JP = SUM('2a', null, {X3}{Y1}:{X3});
                    JD = SUM('2a', null, {X4}{Y1}:{X4});
                    RATIO = [JP] / [JD];

                    __SHOW__ = [JP], [JD], [RATIO _AS_ Rasio]",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' =>
                            'X = CASES([RATIO] >= 3, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                            'X = CASES([RATIO] < 3, (4 * [RATIO]) / 3)',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                            'X = CASES([RATIO] < 3, (4 * [RATIO]) / 3)',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' =>
                            'X = CASES([RATIO] < 3, (4 * [RATIO]) / 3)',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' =>
                            'X = CASES([RATIO] < 3, (4 * [RATIO]) / 3)',
                        'kriteria' => 'X'
                    ],
                ]
            ],
            '17' => IAPSS1::getRumusPenilaianByCode('17'),
            '18' => IAPSS1::getRumusPenilaianByCode('18'),
            '19' => [
                'rumus_penilaian' =>
                "NDSK = COUNT('3a.1', null, {X10}{Y1}:{X10});
                NDTPS = COUNT_IF('3a.1', null, {X7}{Y1}:{X7}, '1');
                PDSK_RAW = ([NDSK] / [NDTPS]) * 1;
                PDSK = CONCAT([PDSK_RAW] * 100, '%');
                __SHOW__ = [NDTPS], [NDSK], [PDSK];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' =>
                        'X = CASES([PDSK_RAW] >= 0.5, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = CASES([PDSK_RAW] < 0.5, 1 + (6 * [PDSK_RAW]))',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES([PDSK_RAW] < 0.5, 1 + (6 * [PDSK_RAW]))',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' =>
                        'X = CASES([PDSK_RAW] < 0.5, 1 + (6 * [PDSK_RAW]))',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' => 'X = 0',
                        'kriteria' => 'X'
                    ],
                ]
            ],
            '20' => IAPSS1::getRumusPenilaianByCode('19'),
            '21' => [
                'rumus_penilaian' =>
                "NM =  SUM('2a', null, {X7}{Y5}:{X8}{Y5});
                NDTPS = COUNT_IF('3a.1', null, {X7}{Y1}:{X7}, '1');
                RMD = [NM] / [NDTPS];
                GROUP = USE('unit_kerja', 'kelompok_prodi');
                IS_SOCIAL = CASES([GROUP] == 'SH', 1);
                IS_SCIENCE = CASES([GROUP] == 'ST', 1);
                __SHOW__ = [NM], [NDTPS], [RMD];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                            ([IS_SCIENCE] == 1) & ([RMD] >= 10 & [RMD] <= 20),
                            4,
                            ([IS_SOCIAL] == 1) & ([RMD] >= 15 & [RMD] <= 25),
                            4
                        )',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                            ([IS_SCIENCE] == 1) & ([RMD] < 10),
                            (2 * [RMD]) / 5,
                            ([IS_SCIENCE] == 1) & ([RMD] > 20 & [RMD] <= 30),
                            (60 - (2 * [RMD])) / 5,
                            ([IS_SOCIAL] == 1) & ([RMD] < 15),
                            (4 * [RMD]) / 15,
                            ([IS_SOCIAL] == 1) & ([RMD] > 25 & [RMD] <= 35),
                            (70 - (2 * [RMD])) / 5
                        )',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                            ([IS_SCIENCE] == 1) & ([RMD] < 10),
                            (2 * [RMD]) / 5,
                            ([IS_SCIENCE] == 1) & ([RMD] > 20 & [RMD] <= 30),
                            (60 - (2 * [RMD])) / 5,
                            ([IS_SOCIAL] == 1) & ([RMD] < 15),
                            (4 * [RMD]) / 15,
                            ([IS_SOCIAL] == 1) & ([RMD] > 25 & [RMD] <= 35),
                            (70 - (2 * [RMD])) / 5
                        )',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                            ([IS_SCIENCE] == 1) & ([RMD] < 10),
                            (2 * [RMD]) / 5,
                            ([IS_SCIENCE] == 1) & ([RMD] > 20 & [RMD] <= 30),
                            (60 - (2 * [RMD])) / 5,
                            ([IS_SOCIAL] == 1) & ([RMD] < 15),
                            (4 * [RMD]) / 15,
                            ([IS_SOCIAL] == 1) & ([RMD] > 25 & [RMD] <= 35),
                            (70 - (2 * [RMD])) / 5
                        )',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                            ([IS_SCIENCE] == 1) & ([RMD] > 30),
                            0,
                            ([IS_SOCIAL] == 1) & ([RMD] > 35),
                            0
                        )',
                        'kriteria' => 'X'
                    ]
                ]
            ],
            '22' => IAPSS1::getRumusPenilaianByCode('21'),
            '23' => IAPSS1::getRumusPenilaianByCode('22'),
            '24' => IAPSS1::getRumusPenilaianByCode('23'),
            '25' => [
                'rumus_penilaian' =>
                "MKKI = COUNT('3a.5', null, {X8}{Y1}:{X8});
                MKK = COUNT_IF('5a', null, {X5}{Y1}:{X5}, '1');
                PMKI = ([MKKI] / [MKK]) * 1;
                PMKI_PERCENT = CONCAT([PMKI] * 100, '%');
                __SHOW__ = [MKKI], [MKK], [PMKI_PERCENT _AS_ PMKI];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' =>
                        'X = CASES([PMKI] >= 0.2, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                        [PMKI] < 0.2,
                        2 + (10 * [PMKI])
                    )',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                        [PMKI] < 0.2,
                        2 + (10 * [PMKI])
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
                    ],
                ]
            ],
            '26' => IAPSS1::getRumusPenilaianByCode('24'),
            '27' => IAPSS1::getRumusPenilaianByCode('25'),
            '28' => IAPSS1::getRumusPenilaianByCode('26'),
            '29' => IAPSS1::getRumusPenilaianByCode('27', replaceWords: [
                '3b.4a' => '3b.4b',
            ]),
            '30' => [
                'rumus_penilaian' =>
                "TS-0 = YEAR(-1);
                TS-1 = YEAR(-2);
                TS-2 = YEAR(-3);
                NAPJ = COUNT_IFS('3b.6', null, {X6}{Y1}:{X6}, [TS-0], [TS-1], [TS-2]);
                NDTPS = COUNT_IF('3a.1', null, {X7}{Y1}:{X7}, '1');
                RS = [NAPJ] / [NDTPS];
                __SHOW__ = [NAPJ], [NDTPS], [RS];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' =>
                        'X = CASES([RS] >= 1, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                            [RS] < 1,
                            2 + (2 * [RS])
                        )',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                            [RS] < 1,
                            2 + (2 * [RS])
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
                    ],
                ]
            ],
            '31' => IAPSS1::getRumusPenilaianByCode('29'),
            '34' => IAPSS1::getRumusPenilaianByCode('32'),
            '35' => IAPSS1::getRumusPenilaianByCode('33'),
            '36' => IAPSS1::getRumusPenilaianByCode('34'),
            '44' => IAPSS1::getRumusPenilaianByCode('42'),
            '47' => IAPSS1::getRumusPenilaianByCode('45'),
            '49.a' => IAPSS1::getRumusPenilaianByCode('47.a'),
            '52' => IAPSS1::getRumusPenilaianByCode('51'),
            '54' => IAPSS1::getRumusPenilaianByCode('53'),
            '55' => IAPSS1::getRumusPenilaianByCode('54'),
            '56' => IAPSS1::getRumusPenilaianByCode('55'),
            '57' => [
                'rumus_penilaian' =>
                "MS_TS-3 = SUM('8c.1', null, {X8}{Y1}:{X8}{Y1}) * SUM('8c.1', null, {X9}{Y1}:{X9}{Y1});
                    MS_TS-2 = SUM('8c.1', null, {X8}{Y2}:{X8}{Y2}) * SUM('8c.1', null, {X9}{Y2}:{X9}{Y2});
                    MS_TS-1 = SUM('8c.1', null, {X8}{Y3}:{X8}{Y3}) * SUM('8c.1', null, {X9}{Y3}:{X9}{Y3});

                    MS = ([MS_TS-3] + [MS_TS-2] + [MS_TS-1]) / SUM('8c.1', null, {X8}{Y1}:{X8});
                    __SHOW__ = [MS];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                [MS] >= 3 & [MS] <= 3.5,
                                4
                            )',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                [MS] > 3 & [MS] <= 5,
                                (40 - (8 * [MS])) / 3
                            )',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                [MS] > 3 & [MS] <= 5,
                                (40 - (8 * [MS])) / 3
                            )',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                [MS] > 3 & [MS] <= 5,
                                (40 - (8 * [MS])) / 3
                            )',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                [MS] < 3,
                                0
                            )',
                        'kriteria' => 'X'
                    ],
                ]
            ],
            '58' => [
                'rumus_penilaian' =>
                "MGR = SUM('8c.1', null, {X8}{Y1}:{X8});
                    MGA = SUM('8c.1', null, {X2}{Y1}:{X2});
                    PTW_RAW = [MGR] / [MGA];
                    PTW = CONCAT([PTW_RAW] * 100, '%');
                    __SHOW__ = [PTW], [MGR _AS_ NL], [MGA _AS_ ND];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' =>
                        'X = CASES([PTW_RAW] >= 0.7, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = CASES([PTW_RAW] < 0.7, 1 + (30 * [PTW_RAW]) / 7)',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES([PTW_RAW] < 0.7, 1 + (30 * [PTW_RAW]) / 7)',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' =>
                        'X = CASES([PTW_RAW] < 0.7, 1 + (30 * [PTW_RAW]) / 7)',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' => 'X = 0',
                        'kriteria' => 'X'
                    ]
                ]
            ],
            '59' => [
                'rumus_penilaian' =>
                "MGR = SUM('8c.1', null, {X8}{Y1}:{X8});
                    MGA = SUM('8c.1', null, {X2}{Y1}:{X2});
                    PPS_RAW = [MGR] / [MGA];
                    PPS = CONCAT([PPS_RAW] * 100, '%');
                    __SHOW__ = [PPS];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' =>
                        'X = CASES([PPS_RAW] >= 0.85, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                [PPS_RAW] >= 0.3 & [PPS_RAW] < 0.85,
                                ((80 * [PPS_RAW]) - 24) / 11
                            )',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                [PPS_RAW] >= 0.3 & [PPS_RAW] < 0.85,
                                ((80 * [PPS_RAW]) - 24) / 11
                            )',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                [PPS_RAW] >= 0.3 & [PPS_RAW] < 0.85,
                                ((80 * [PPS_RAW]) - 24) / 11
                            )',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' =>
                        'X = CASES([PPS_RAW] < 0.3, 0)',
                        'kriteria' => 'X'
                    ]
                ]
            ],
            '61' => [
                'rumus_penilaian' =>
                "NL = SUM('8d.1a', null, {X2}{Y1}:{X2});
                    NJ = SUM('8d.1a', null, {X3}{Y1}:{X3});
                    Prmin = CASES(
                        [NL] >= 300,
                        30 / 100,
                        1 == 1,
                        ((50 / 100) - (([NL] / 300) * (20 / 100)))
                    );
                    Prmin_PERCENT = CONCAT([Prmin] * 100, '%');
                    PJ = ([NL] / [NJ]);
                    PJ_PERCENT = CONCAT([PJ] * 100, '%');
                    B1 = 3;
                    B2 = 6;
                    B3 = 12;
                    MID1 = [B1] / 2;
                    MID2 = ([B1] + [B2]) / 2;
                    MID3 = ([B2] + [B3]) / 2;
                    WT0 = SUM('8d.1a', null, {X5}{Y1}:{X5});
                    WT1 = SUM('8d.1a', null, {X6}{Y1}:{X6});
                    WT2 = SUM('8d.1a', null, {X7}{Y1}:{X7});
                    WT = (([WT0] * [MID1]) + ([WT1] * [MID2]) + ([WT2] * [MID3])) / ([WT0] + [WT1] + [WT2]);
                    SCORE_WT = CASES(
                        [WT] < 3,
                        4,
                        [WT] >= 3 & [WT] <= 6,
                        (24 - (4 * [WT])) / 3,
                        [WT] > 6,
                        0
                    );
                    FIN_SCORE = CASES(
                        [PJ] < [Prmin],
                        ([PJ] / [Prmin]) * [SCORE_WT],
                        1 == 1,
                        [SCORE_WT]
                    );
                    __SHOW__ = [NL], [NJ], [Prmin_PERCENT _AS_ Prmin], [WT], [PJ_PERCENT _AS_ PJ], [FIN_SCORE _AS_ SKOR]
                        [WT0], [WT1], [WT2];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' =>
                        'X = [FIN_SCORE]',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = [FIN_SCORE]',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = [FIN_SCORE]',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' =>
                        'X = [FIN_SCORE]',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' =>
                        'X = [FIN_SCORE]',
                        'kriteria' => 'X'
                    ]
                ]
            ],
            '62' => [
                'rumus_penilaian' =>
                "NL = SUM('8d.2', null, {X2}{Y1}:{X2});
                    NJ = SUM('8d.2', null, {X3}{Y1}:{X3});
                    Prmin = CASES(
                        [NL] >= 300,
                        30 / 100,
                        1 == 1,
                        ((50 / 100) - (([NL] / 300) * (20 / 100)))
                    );
                    Prmin_PERCENT = CONCAT([Prmin] * 100, '%');
                    PJ = ([NL] / [NJ]);
                    PJ_PERCENT = CONCAT([PJ] * 100, '%');
                    LL = SUM('8d.2', null, {X4}{Y1}:{X4});
                    LM = SUM('8d.2', null, {X5}{Y1}:{X5});
                    LH = SUM('8d.2', null, {X6}{Y1}:{X6});
                    LS = [LL] + [LM] + [LH];
                    PBS = CASES(
                        [LS] > 0,
                        (([LL] * 0.3) + ([LM] * 0.7) + ([LH] * 1)) / [LS]
                    );
                    PBS_PERCENT = CONCAT([PBS] * 100, '%');
                    SCORE_PBS = CASES(
                        [PBS] >= 0.8,
                        4,
                        1 == 1,
                        (5 * [PBS])
                    );
                    FIN_SCORE = CASES(
                        [PJ] < [Prmin],
                        ([PJ] / [Prmin]) * [SCORE_PBS],
                        1 == 1,
                        [SCORE_PBS]
                    );
                    __SHOW__ = [NL], [NJ], [Prmin_PERCENT _AS_ Prmin], [PJ_PERCENT _AS_ PJ],
                        [LL], [LM], [LH], [LS], [PBS_PERCENT _AS_ PBS], [FIN_SCORE _AS_ SKOR];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' =>
                        'X = [FIN_SCORE]',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = [FIN_SCORE]',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = [FIN_SCORE]',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' =>
                        'X = [FIN_SCORE]',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' =>
                        'X = [FIN_SCORE]',
                        'kriteria' => 'X'
                    ]
                ]
            ],
            '63' => IAPSS1::getRumusPenilaianByCode('62'),
            '64' => IAPSS1::getRumusPenilaianByCode('63'),
            '65' => [
                'rumus_penilaian' =>
                "TS-0 = YEAR(-1);
                TS-1 = YEAR(-2);
                TS-2 = YEAR(-3);
                NAPJ = COUNT_IFS('8f.3', null, {X6}{Y1}:{X6}, [TS-0], [TS-1], [TS-2]);
                __SHOW__ = [NAPJ];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' =>
                        'X = CASES([NAPJ] >= 2, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = CASES([NAPJ] == 1, 3)',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES([NAPJ] == 1, 2)',
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
        ];
    }
}
