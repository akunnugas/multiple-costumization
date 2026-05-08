<?php

namespace Modules\SPMI\Data\PenilaianMatriks;

class IAPSD4 extends MigrateFormula
{
    protected string $assessmentGuideCode = 'IAPS-D4';

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
            '9.a' => IAPSS1::getRumusPenilaianByCode('9.a'),
            '9.b' => IAPSS1::getRumusPenilaianByCode('9.b'),
            '14.b' => [
                'rumus_penilaian' =>
                "JP = SUM('2a', null, {X3}{Y1}:{X3});
                    JD = SUM('2a', null, {X4}{Y1}:{X4});
                    COUNT_JP = COUNT('2a', null, {X3}{Y1}:{X3});
                    EMPTY_JP = COUNT_EMPTY('2a', null, {X3}{Y1}:{X3});
                    COUNT_TS = COUNT_CATEGORY('2a');
                    RATIO = [JP] / [JD];

                    __SHOW__ = [JP], [JD], [RATIO _AS_ Rasio]",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' => 'X = CASES([RATIO] >= 5, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' => 'X = CASES([RATIO] < 5, (4 * [RATIO]) / 5)',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' => 'X = CASES([RATIO] < 5, (4 * [RATIO]) / 5)',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' => 'X = CASES([RATIO] < 5, (4 * [RATIO]) / 5)',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' => 'X = CASES([RATIO] < 5, (4 * [RATIO]) / 5)',
                        'kriteria' => 'X'
                    ],
                ]
            ],
            '15.b' => IAPSS1::getRumusPenilaianByCode('15.b'),
            '17' => IAPSS1::getRumusPenilaianByCode('17'),
            '18' => IAPSS1::getRumusPenilaianByCode('18'),
            '19' => IAPSD3::getRumusPenilaianByCode('19'),
            '20' => IAPSS1::getRumusPenilaianByCode('19'),
            '21' => [
                'rumus_penilaian' =>
                "NM =  SUM('2a', null, {X7}{Y5}:{X8}{Y5});
                    NDTPS = COUNT_IF('3a.1', null, {X7}{Y1}:{X7}, '1');
                    RMD = [NM] / [NDTPS];
                    GROUP = USE('unit_kerja', 'kelompok_prodi');
                    IS_SOCIAL = CASES([GROUP] == 'SH', 1);
                    IS_SCIENCE = CASES([GROUP] == 'ST', 1);

                    JP = SUM('2a', null, {X3}{Y1}:{X3});
                    JD = SUM('2a', null, {X4}{Y1}:{X4});
                    COUNT_JP = COUNT('2a', null, {X3}{Y1}:{X3});
                    EMPTY_JP = COUNT_EMPTY('2a', null, {X3}{Y1}:{X3});
                    COUNT_TS = COUNT_CATEGORY('2a');
                    RATIO = [JP] / [JD];

                    __SHOW__ = [NM], [NDTPS], [RMD];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                ([IS_SCIENCE] == 1) & ([RMD] >= 15 & [RMD] <= 25),
                                4,
                                ([IS_SOCIAL] == 1) & ([RMD] >= 25 & [RMD] <= 35),
                                4
                            )',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                ([IS_SCIENCE] == 1) & ([RMD] < 15),
                                (4 * [RMD]) / 15,
                                ([IS_SCIENCE] == 1) & ([RMD] > 25 & [RMD] <= 35),
                                (70 - (2 * [RMD])) / 5,
                                ([IS_SOCIAL] == 1) & ([RMD] < 25),
                                (4 * [RMD]) / 25,
                                ([IS_SOCIAL] == 1) & ([RMD] > 35 & [RMD] <= 50),
                                (200 - (4 * [RMD])) / 15
                            )',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                ([IS_SCIENCE] == 1) & ([RMD] < 15),
                                (4 * [RMD]) / 15,
                                ([IS_SCIENCE] == 1) & ([RMD] > 25 & [RMD] <= 35),
                                (70 - (2 * [RMD])) / 5,
                                ([IS_SOCIAL] == 1) & ([RMD] < 25),
                                (4 * [RMD]) / 25,
                                ([IS_SOCIAL] == 1) & ([RMD] > 35 & [RMD] <= 50),
                                (200 - (4 * [RMD])) / 15
                            )',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                ([IS_SCIENCE] == 1) & ([RMD] < 15),
                                (4 * [RMD]) / 15,
                                ([IS_SCIENCE] == 1) & ([RMD] > 25 & [RMD] <= 35),
                                (70 - (2 * [RMD])) / 5,
                                ([IS_SOCIAL] == 1) & ([RMD] < 25),
                                (4 * [RMD]) / 25,
                                ([IS_SOCIAL] == 1) & ([RMD] > 35 & [RMD] <= 50),
                                (200 - (4 * [RMD])) / 15
                            )',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                ([IS_SCIENCE] == 1) & ([RMD] > 35),
                                [RMD],
                                ([IS_SOCIAL] == 1) & ([RMD] > 50),
                                [RMD]
                            )',
                        'kriteria' => 'X'
                    ]
                ]
            ],
            '22' => IAPSS1::getRumusPenilaianByCode('21'),
            '23' => IAPSS1::getRumusPenilaianByCode('22'),
            '24' => IAPSS1::getRumusPenilaianByCode('23'),
            '25' => IAPSD3::getRumusPenilaianByCode('25'),
            '26' => IAPSS1::getRumusPenilaianByCode('24'),
            '27' => IAPSS1::getRumusPenilaianByCode('25'),
            '28' => IAPSS1::getRumusPenilaianByCode('26'),
            '29' => IAPSD3::getRumusPenilaianByCode('29'),
            '30' => IAPSS1::getRumusPenilaianByCode('28'),
            '31' => IAPSD3::getRumusPenilaianByCode('30'),
            '32' => IAPSS1::getRumusPenilaianByCode('29'),
            '35' => IAPSS1::getRumusPenilaianByCode('32'),
            '36' => IAPSS1::getRumusPenilaianByCode('33'),
            '37' => IAPSS1::getRumusPenilaianByCode('34'),
            '45' => IAPSS1::getRumusPenilaianByCode('42'),
            '48' => IAPSS1::getRumusPenilaianByCode('45'),
            '50.a' => IAPSS1::getRumusPenilaianByCode('47.a'),
            '52' => IAPSS1::getRumusPenilaianByCode('49'),
            '54' => IAPSS1::getRumusPenilaianByCode('51'),
            '56' => IAPSS1::getRumusPenilaianByCode('53'),
            '57' => IAPSS1::getRumusPenilaianByCode('54'),
            '58' => [
                'rumus_penilaian' =>
                "TS-0 = YEAR(-1);
                    TS-2 = YEAR(-3);

                    NI = COUNT_WHERE('8b.2', null, {X3}{Y1}:{X6},
                        {X3} >= [TS-2],
                        {X3} <= [TS-0],
                        {X6} == '1'
                    );

                    NN = COUNT_WHERE('8b.2', null, {X3}{Y1}:{X5},
                        {X3} >= [TS-2],
                        {X3} <= [TS-0],
                        {X5} == '1'
                    );

                    NW = COUNT_WHERE('8b.2', null, {X3}{Y1}:{X4},
                        {X3} >= [TS-2],
                        {X3} <= [TS-0],
                        {X4} == '1'
                    );

                    NM =  SUM('2a', null, {X7}{Y5}:{X8}{Y5});
                    RI = [NI] / [NM];
                    RN = [NN] / [NM];
                    RW = [NW] / [NM];
                    a = 0.002;
                    b = 0.02;
                    c = 0.04;

                    __SHOW__ = [NI], [NN], [NW], [NM], [RI], [RN], [RW];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' => 'X = CASES([RI] >= [a], 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                            [RI] < [a] & [RN] >= [b],
                            3 + ([RI] / [a]),
                            ([RI] >= 0 & [RI] < [a]) & ([RN] > 0 & [RN] < [b]),
                            2 + (2 * ([RI] / [a])) + ([RN] / [b]) - (([RI] * [RN]) / ([a] * [b]))
                        )',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                            [RI] < [a] & [RN] >= [b],
                            3 + ([RI] / [a]),
                            ([RI] >= 0 & [RI] < [a]) & ([RN] > 0 & [RN] < [b]),
                            2 + (2 * ([RI] / [a])) + ([RN] / [b]) - (([RI] * [RN]) / ([a] * [b]))
                        )',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                            [RI] == 0 & [RN] == 0 & [RW] >= [c],
                            2,
                            [RI] == 0 & [RN] == 0 & [RW] < [c],
                            (2 * [RW]) / [c]
                        )',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                            [RI] == 0 & [RN] == 0 & [RW] >= [c],
                            2,
                            [RI] == 0 & [RN] == 0 & [RW] < [c],
                            (2 * [RW]) / [c]
                        )',
                        'kriteria' => 'X'
                    ]
                ]
            ],
            '59' => IAPSS1::getRumusPenilaianByCode('56'),
            '60' => [
                'rumus_penilaian' =>
                "MGR = SUM('8c.2', null, {X10}{Y1}:{X10});
                    MGA = SUM('8c.2', null, {X2}{Y1}:{X2});
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
                        'X = CASES([PTW_RAW] < 0.7, 1 + ((30 * [PTW_RAW]) / 7))',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES([PTW_RAW] < 0.7, 1 + ((30 * [PTW_RAW]) / 7))',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' =>
                        'X = CASES([PTW_RAW] < 0.7, 1 + ((30 * [PTW_RAW]) / 7))',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' => 'X = 0',
                        'kriteria' => 'X'
                    ]
                ]
            ],
            '61' => IAPSS1::getRumusPenilaianByCode('58'),
            '63' => [
                'rumus_penilaian' =>
                "NL = SUM('8d.1c', null, {X2}{Y1}:{X2});
                    NJ = SUM('8d.1c', null, {X3}{Y1}:{X3});
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
                    WT0 = SUM('8d.1c', null, {X4}{Y1}:{X4});
                    WT1 = SUM('8d.1c', null, {X5}{Y1}:{X5});
                    WT2 = SUM('8d.1c', null, {X6}{Y1}:{X6});
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
            '64' => IAPSS1::getRumusPenilaianByCode('61'),
            '65' => IAPSS1::getRumusPenilaianByCode('62'),
            '66' => IAPSS1::getRumusPenilaianByCode('63'),
            '67' => IAPSS1::getRumusPenilaianByCode('64', replaceWords: [
                '8f.1a' => '8f.1b'
            ]),
            '68' => IAPSD3::getRumusPenilaianByCode('65'),
            '69' => IAPSS1::getRumusPenilaianByCode('65'),
        ];
    }
}
