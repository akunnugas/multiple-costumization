<?php

namespace Modules\SPMI\Data\PenilaianMatriks;

class IAPSS1 extends MigrateFormula
{
    protected string $assessmentGuideCode = 'IAPS-S1';

    protected function getRumus(): array
    {
        return [
            // AKUMULASI SKOR
            'C.2.4.a' => [
                'rumus_penilaian' => '__ACCUMULATION__ = ([A] + (2 * [B])) / 3;'
            ],
            'C.2.4.b' => [
                'rumus_penilaian' => '__ACCUMULATION__ = ([A] + (2 * [B])) / 3;'
            ],
            '1a' => [
                'rumus_penilaian' => '__ACCUMULATION__ = ((2 * [A]) + [B]) / 3;'
            ],
            'C.3.4.b' => [
                'rumus_penilaian' => '__ACCUMULATION__ = ((2 * [A]) + [B]) / 3;'
            ],
            'C.3.4.c' => [
                'rumus_penilaian' => '__ACCUMULATION__ = ([A] + (2 * [B])) / 3;'
            ],
            'C.4.4.d' => [
                'rumus_penilaian' => '__ACCUMULATION__ = ([A] + [B]) / 2;'
            ],
            'C.6.4.a' => [
                'rumus_penilaian' => '__ACCUMULATION__ = ([A] + (2 * [B]) + (2 * [C])) / 5;'
            ],
            'C.6.4.c' => [
                'rumus_penilaian' => '__ACCUMULATION__ = ([A] + (2 * [B])) / 3;'
            ],
            'C.6.4.d' => [
                'rumus_penilaian' => '__ACCUMULATION__ = ([A] + (2 * [B]) + (2 * [C]) + (2 * [D]) + (2 * [E])) / 9;'
            ],
            'C.6.4.f' => [
                'rumus_penilaian' => '__ACCUMULATION__ = ([A] + (2 * [B]) + (2 * [C])) / 5;'
            ],
            'C.6.4.i' => [
                'rumus_penilaian' => '__ACCUMULATION__ = ([A] + (2 * [B])) / 3;'
            ],

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
                    a = 3;
                    b = 2;
                    c = 1;
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
                    a = 2;
                    b = 6;
                    c = 9;
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
            '14' => [
                'rumus_penilaian' =>
                "JP = SUM('2a', null, {X3}{Y1}:{X3});
                    JD = SUM('2a', null, {X4}{Y1}:{X4});
                    COUNT_JP = COUNT('2a', null, {X3}{Y1}:{X3});
                    EMPTY_JP = COUNT_EMPTY('2a', null, {X3}{Y1}:{X3});
                    COUNT_TS = COUNT_CATEGORY('2a');
                    RATIO = [JP] / [JD];

                    REQUIRED_GRADUATE = USE('unit_kerja', 'kebutuhan_lulusan');
                    IS_HIGH_GRADE = CASES([REQUIRED_GRADUATE] == 'HI', 1);
                    IS_LOW_GRADE = CASES([REQUIRED_GRADUATE] == 'LW', 1);
                    __SHOW__ = [JP], [JD], [RATIO _AS_ Rasio]",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                ([IS_HIGH_GRADE] == 1) & ([RATIO] >= 5),
                                4,
                                ([IS_LOW_GRADE] == 1) & ([COUNT_JP] == [COUNT_TS]),
                                4
                            )',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                ([IS_HIGH_GRADE] == 1) & ([RATIO] < 5),
                                (4 * [RATIO]) / 5,
                                ([IS_LOW_GRADE] == 1),
                                0
                            )',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                ([IS_HIGH_GRADE] == 1) & ([RATIO] < 5),
                                (4 * [RATIO]) / 5,
                                ([IS_LOW_GRADE] == 1) & ([EMPTY_JP] < [COUNT_TS]),
                                2
                            )',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                ([IS_HIGH_GRADE] == 1) & ([RATIO] < 5),
                                (4 * [RATIO]) / 5,
                                ([IS_LOW_GRADE] == 1),
                                0
                            )',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                ([IS_HIGH_GRADE] == 1) & ([RATIO] < 5),
                                (4 * [RATIO]) / 5,
                                ([IS_LOW_GRADE] == 1) & ([EMPTY_JP] == [COUNT_TS]),
                                0
                            )',
                        'kriteria' => 'X'
                    ],
                ]
            ],
            '15.b' => [
                'rumus_penilaian' =>
                "MAF = SUM('2b', null, {X6}{Y1}:{X8});
                    MAP = SUM('2b', null, {X9}{Y1}:{X11});
                    NM = SUM('2b', null, {X3}{Y1}:{X5});
                    PMA = ([MAF] + [MAP]) / [NM];
                    PMA_PERCENTAGE = 0.01;
                    PMA_FORMAT = CONCAT([PMA] * 100, '%');
                    __SHOW__ = [MAF], [MAP], [NM], [PMA_FORMAT _AS_ PMA]",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                [PMA] >= [PMA_PERCENTAGE],
                                4
                            )',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                [PMA] < [PMA_PERCENTAGE],
                                2 + (200 * [PMA])
                            )',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                [PMA] < [PMA_PERCENTAGE],
                                2 + (200 * [PMA])
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
            '17' => [
                'rumus_penilaian' =>
                "NDTPS = COUNT_IF('3a.1', null, {X7}{Y1}:{X7}, '1');
                    __SHOW__ = [NDTPS]",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' =>
                        'X = CASES([NDTPS] >= 12, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                ([NDTPS] >= 3 & [NDTPS] < 12),
                                ((2 * [NDTPS]) + 12) / 9
                            )',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                ([NDTPS] >= 3 & [NDTPS] < 12),
                                ((2 * [NDTPS]) + 12) / 9
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
                'rumus_penilaian' =>
                "NDS3 = COUNT('3a.1', null, {X5}{Y1}:{X5});
                    NDTPS = COUNT_IF('3a.1', null, {X7}{Y1}:{X7}, '1');
                    PDS3_RAW = ([NDS3] / [NDTPS]) * 1;
                    PDS3 = CONCAT([PDS3_RAW] * 100, '%');
                    __SHOW__ = [NDTPS], [NDS3], [PDS3]",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' =>
                        'X = CASES([PDS3_RAW] >= 0.5, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = CASES([PDS3_RAW] < 0.5, 2 + (4 * [PDS3_RAW]))',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES([PDS3_RAW] < 0.5, 2 + (4 * [PDS3_RAW]))',
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
                ],
            ],
            '19' => [
                'rumus_penilaian' =>
                "NDGB = COUNT_IF('3a.1', null, {X8}{Y1}:{X8}, 'Guru Besar');
                    NDLK = COUNT_IF('3a.1', null, {X8}{Y1}:{X8}, 'Lektor Kepala');
                    NDL = COUNT_IF('3a.1', null, {X8}{Y1}:{X8}, 'Lektor');
                    NDTPS = COUNT_IF('3a.1', null, {X7}{Y1}:{X7}, '1');
                    PGBLKL_RAW = (([NDGB] + [NDLK] + [NDL]) / [NDTPS]) * 1;
                    PGBLKL = CONCAT([PGBLKL_RAW] * 100, '%');
                    __SHOW__ = [NDGB], [NDLK], [NDL], [NDTPS], [PGBLKL];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' =>
                        'X = CASES([PGBLKL_RAW] >= 0.7, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = CASES([PGBLKL_RAW] < 0.7, 2 + ((20 * [PGBLKL_RAW]) / 7))',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES([PGBLKL_RAW] < 0.7, 2 + ((20 * [PGBLKL_RAW]) / 7))',
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
            '20' => [
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

                    REQUIRED_GRADUATE = USE('unit_kerja', 'kebutuhan_lulusan');
                    IS_HIGH_GRADE = CASES([REQUIRED_GRADUATE] == 'HI', 1);
                    IS_LOW_GRADE = CASES([REQUIRED_GRADUATE] == 'LW', 1);

                    __SHOW__ = [NM], [NDTPS], [RMD];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                ([IS_HIGH_GRADE] == 1) & ([IS_SCIENCE] == 1) & ([RMD] >= 15 & [RMD] <= 25),
                                4,
                                ([IS_HIGH_GRADE] == 1) & ([IS_SOCIAL] == 1) & ([RMD] >= 25 & [RMD] <= 35),
                                4,
                                ([IS_LOW_GRADE] == 1) & ([COUNT_JP] == [COUNT_TS]),
                                4
                            )',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                ([IS_HIGH_GRADE] == 1) & ([IS_SCIENCE] == 1) & ([RMD] < 15),
                                (4 * [RMD]) / 15,
                                ([IS_HIGH_GRADE] == 1) & ([IS_SCIENCE] == 1) & ([RMD] > 25 & [RMD] <= 35),
                                (70 - (2 * [RMD])) / 5,
                                ([IS_HIGH_GRADE] == 1) & ([IS_SOCIAL] == 1) & ([RMD] < 25),
                                (4 * [RMD]) / 25,
                                ([IS_HIGH_GRADE] == 1) & ([IS_SOCIAL] == 1) & ([RMD] > 35 & [RMD] <= 50),
                                (200 - (4 * [RMD])) / 15,
                                ([IS_LOW_GRADE] == 1),
                                0
                            )',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                ([IS_HIGH_GRADE] == 1) & ([IS_SCIENCE] == 1) & ([RMD] < 15),
                                (4 * [RMD]) / 15,
                                ([IS_HIGH_GRADE] == 1) & ([IS_SCIENCE] == 1) & ([RMD] > 25 & [RMD] <= 35),
                                (70 - (2 * [RMD])) / 5,
                                ([IS_HIGH_GRADE] == 1) & ([IS_SOCIAL] == 1) & ([RMD] < 25),
                                (4 * [RMD]) / 25,
                                ([IS_HIGH_GRADE] == 1) & ([IS_SOCIAL] == 1) & ([RMD] > 35 & [RMD] <= 50),
                                (200 - (4 * [RMD])) / 15,
                                ([IS_LOW_GRADE] == 1) & ([EMPTY_JP] < [COUNT_TS]),
                                2
                            )',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                ([IS_HIGH_GRADE] == 1) & ([IS_SCIENCE] == 1) & ([RMD] < 15),
                                (4 * [RMD]) / 15,
                                ([IS_HIGH_GRADE] == 1) & ([IS_SCIENCE] == 1) & ([RMD] > 25 & [RMD] <= 35),
                                (70 - (2 * [RMD])) / 5,
                                ([IS_HIGH_GRADE] == 1) & ([IS_SOCIAL] == 1) & ([RMD] < 25),
                                (4 * [RMD]) / 25,
                                ([IS_HIGH_GRADE] == 1) & ([IS_SOCIAL] == 1) & ([RMD] > 35 & [RMD] <= 50),
                                (200 - (4 * [RMD])) / 15,
                                ([IS_LOW_GRADE] == 1),
                                0
                            )',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                ([IS_HIGH_GRADE] == 1) & ([IS_SCIENCE] == 1) & ([RMD] > 35),
                                [RMD],
                                ([IS_HIGH_GRADE] == 1) & ([IS_SOCIAL] == 1) & ([RMD] > 50),
                                [RMD],
                                ([IS_LOW_GRADE] == 1) & ([EMPTY_JP] == [COUNT_TS]),
                                0
                            )',
                        'kriteria' => 'X'
                    ]
                ]
            ],
            '21' => [
                'rumus_penilaian' =>
                "AVG_PS_ACCREDITATION = AVG_Y('3a.2', null, {X3}{Y1}:{X5});
                    AVG_PS_OTHER = AVG_Y('3a.2', null, {X7}{Y1}:{X9});
                    RDPU = ([AVG_PS_ACCREDITATION] + [AVG_PS_OTHER]) / 2;
                    __SHOW__ = [RDPU];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' => 'X = CASES([RDPU] > 0 & [RDPU] <= 6, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' => 'X = CASES([RDPU] > 6 & [RDPU] <= 10, 7 - ([RDPU] / 2))',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' => 'X = CASES([RDPU] > 6 & [RDPU] <= 10, 7 - ([RDPU] / 2))',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' => 'X = 0',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' => 'X = CASES([RDPU] <= 0, 0, [RDPU] > 10, 0)',
                        'kriteria' => 'X'
                    ],
                ]
            ],
            '22' => [
                'rumus_penilaian' =>
                "EWMP = AVG_X_WHERE('3a.3', null, {X3}{Y1}:{X9}, NULL,
                    {X3} == '1'
                ) / 2;
                    __SHOW__ = [EWMP];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' =>
                        'X = CASES(([EWMP] >= 12 & [EWMP] <= 16), 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                ([EWMP] >= 6 & [EWMP] < 12),
                                ((2 * [EWMP]) - 12) / 3,
                                ([EWMP] > 16 & [EWMP] <= 18),
                                36 - (2 * [EWMP])
                            )',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                ([EWMP] >= 6 & [EWMP] < 12),
                                ((2 * [EWMP]) - 12) / 3,
                                ([EWMP] > 16 & [EWMP] <= 18),
                                36 - (2 * [EWMP])
                            )',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                ([EWMP] >= 6 & [EWMP] < 12),
                                ((2 * [EWMP]) - 12) / 3,
                                ([EWMP] > 16 & [EWMP] <= 18),
                                36 - (2 * [EWMP])
                            )',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' => 'X = CASES(([EWMP] < 6 | [EWMP] > 18), 0)',
                        'kriteria' => 'X'
                    ],
                ]
            ],
            '23' => [
                'rumus_penilaian' =>
                "NDTT = COUNT('3a.4', null, {X9}{Y1}:{X9});
                    NDT = COUNT('3a.1', null, {X11}{Y1}:{X11});
                    PDTT_RAW = ([NDTT] / ([NDT] + [NDTT])) * 1;
                    PDTT = CONCAT([PDTT_RAW] * 100, '%');
                    __SHOW__ = [NDTT], [NDT], [PDTT];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' =>
                        'X = CASES([PDTT_RAW] <= 0.1, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                ([PDTT_RAW] > 0.1 & [PDTT_RAW] <= 0.4),
                                (14 - (20 * [PDTT_RAW])) / 3
                            )',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                ([PDTT_RAW] > 0.1 & [PDTT_RAW] <= 0.4),
                                (14 - (20 * [PDTT_RAW])) / 3
                            )',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' =>
                        'X = 0',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' => 'X = CASES([PDTT_RAW] > 0.4, 0)',
                        'kriteria' => 'X'
                    ],
                ]
            ],
            '24' => [
                'rumus_penilaian' =>
                "TS-0 = YEAR(-1);
                    TS-2 = YEAR(-3);
                    NRD = COUNT_WHERE('3b.1', null, {X4}{Y1}:{X8},
                        {X4} != null,
                        {X8} >= [TS-2],
                        {X8} <= [TS-0]
                    );
                    NDTPS = COUNT_IF('3a.1', null, {X7}{Y1}:{X7}, '1');
                    RRD = [NRD] / [NDTPS];
                    __SHOW__ = [NRD], [NDTPS], [RRD];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' =>
                        'X = CASES([RRD] >= 0.5, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                [RRD] < 0.5,
                                2 + (4 * [RRD])
                            )',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                [RRD] < 0.5,
                                2 + (4 * [RRD])
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
            '25' => [
                'rumus_penilaian' =>
                "NL = SUM('3b.2', null, {X3}{Y1}:{X5}{Y1});
                    NN = SUM('3b.2', null, {X3}{Y2}:{X5}{Y2});
                    NI = SUM('3b.2', null, {X3}{Y3}:{X5}{Y3});
                    NDTPS = COUNT_IF('3a.1', null, {X7}{Y1}:{X7}, '1');
                    RI = [NI] / 3 / [NDTPS];
                    RN = [NN] / 3 / [NDTPS];
                    RL = [NL] / 3 / [NDTPS];
                    a = 0.05;
                    b = 0.3;
                    c = 1;
                    __SHOW__ = [NDTPS], [NI], [NN], [NL], [RI], [NN], [RL], [RN];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' => 'X = CASES([RI] >= [a], 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>'X = CASES(
                            ([RI] < [a] & [RN] >= [b]), 3 + ([RI] / [a]),
                            ([RI] > 0 & [RI] < [a]) & ([RN] > 0 & [RN] < [b]), 2 + (2 * ([RI] / [a])) + ([RN] / [b]) - (([RI] * [RN]) / ([a] * [b]))
                        )',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>'X = CASES(
                            ([RI] < [a] & [RN] >= [b]), 3 + ([RI] / [a]),
                            ([RI] > 0 & [RI] < [a]) & ([RN] > 0 & [RN] < [b]), 2 + (2 * ([RI] / [a])) + ([RN] / [b]) - (([RI] * [RN]) / ([a] * [b])),
                            ([RI] == 0 & [RN] == 0 & [RL] >= [c]), 2
                        )',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' => 'X = CASES(([RI] == 0 & [RN] == 0 & [RL] < [c]), (2 * [RL]) / [c])',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' => 'X = 0',
                        'kriteria' => 'X'
                    ],
                ]
            ],
            '26' => [
                'rumus_penilaian' =>
                "NI = SUM('3b.3', null, {X3}{Y3}:{X5}{Y3});
                    NN = SUM('3b.3', null, {X3}{Y2}:{X5}{Y2});
                    NL = SUM('3b.3', null, {X3}{Y1}:{X5}{Y1});
                    NDTPS = COUNT_IF('3a.1', null, {X7}{Y1}:{X7}, '1');
                    RI = [NI] / 3 / [NDTPS];
                    RN = [NN] / 3 / [NDTPS];
                    RL = [NL] / 3 / [NDTPS];
                    a = 0.05;
                    b = 0.3;
                    c = 1;
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
                            ([RI] >= 0 & [RI] < [a]) & ([RN] > 0 & [RN] < [b]), 2 + (2 * ([RI] / [a])) + ([RN] / [b]) - (([RI] * [RN]) / ([a] * [b]))
                        )',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES([RI] < [a] & [RN] >= [b], 3 + ([RI] / [a]),
                            ([RI] >= 0 & [RI] < [a]) & ([RN] > 0 & [RN] < [b]), 2 + (2 * ([RI] / [a])) + ([RN] / [b]) - (([RI] * [RN]) / ([a] * [b]))
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
                            [RI] == 0 & [RN] == 0 & [RL] < [c], (2 * [RL]) / [c]
                        )',
                        'kriteria' => 'X'
                    ],
                ]
            ],
            '27' => [
                'rumus_penilaian' =>
                "NA1 = SUM('3b.4a', null, {X3}{Y1}:{X5}{Y1});
                    NA2 = SUM('3b.4a', null, {X3}{Y2}:{X5}{Y2});
                    NA3 = SUM('3b.4a', null, {X3}{Y3}:{X5}{Y3});
                    NA4 = SUM('3b.4a', null, {X3}{Y4}:{X5}{Y4});
                    NB1 = SUM('3b.4a', null, {X3}{Y5}:{X5}{Y5});
                    NB2 = SUM('3b.4a', null, {X3}{Y6}:{X5}{Y6});
                    NB3 = SUM('3b.4a', null, {X3}{Y7}:{X5}{Y7});
                    NC1 = SUM('3b.4a', null, {X3}{Y8}:{X5}{Y8});
                    NC2 = SUM('3b.4a', null, {X3}{Y9}:{X5}{Y9});
                    NC3 = SUM('3b.4a', null, {X3}{Y10}:{X5}{Y10});
                    a = 0.1;
                    b = 1;
                    c = 2;
                    NDTPS = COUNT_IF('3a.1', null, {X7}{Y1}:{X7}, '1');
                    RW = ([NA1] + [NB1] + [NC1]) / [NDTPS];
                    RN = ([NA2] + [NA3] + [NB2] + [NC2]) / [NDTPS];
                    RI = ([NA4] + [NB3] + [NC3]) / [NDTPS];
                    SCORE = CASES(
                        [RI] >= [a],
                        4,
                        [RI] < [a] & [RN] >= [b],
                        3 + ([RI] / [a]),
                        ([RI] > 0 & [RI] < [a]) & ([RN] > 0 & [RN] < [b]),
                        2 + (2 * ([RI] / [a])) + ([RN] / [b]) - (([RI] * [RN]) / ([a] * [b])),
                        ([RI] > 0 & [RI] < [a]) & ([RN] > 0 & [RN] < [b]),
                        2 + (2 * ([RI] / [a])) + ([RN] / [b]) - (([RI] * [RN]) / ([a] * [b])),
                        [RI] == 0 & [RN] == 0 & [RW] >= [c],
                        2,
                        [RI] == 0 & [RN] == 0 & [RW] < [c],
                        (2 * [RW]) / [c]
                    );
                    __SHOW__ = [NA1], [NA2], [NA3], [NA4], [NB1], [NB2], [NB3], [NC1], [NC2], [NC3], [NDTPS], [RW], [RN], [RI];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' => 'X = [SCORE]',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' => 'X = [SCORE]',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' => 'X = [SCORE]',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' => 'X = [SCORE]',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' => 'X = [SCORE]',
                        'kriteria' => 'X'
                    ]
                ]
            ],
            '28' => [
                'rumus_penilaian' =>
                "NAS = COUNT('3b.5', null, {X3}{Y1}:{X3});
                    NDTPS = COUNT_IF('3a.1', null, {X7}{Y1}:{X7}, '1');
                    RS = [NAS] / [NDTPS];
                    __SHOW__ = [NAS], [NDTPS], [RS];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' =>
                        'X = CASES([RS] >= 0.5, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                [RS] < 0.5,
                                2 + (4 * [RS])
                            )',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                [RS] < 0.5,
                                2 + (4 * [RS])
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
            '29' => [
                'rumus_penilaian' =>
                "TS-0 = YEAR(-1);
                    TS-2 = YEAR(-3);
                    NA = COUNT_WHERE('3b.7-1', null, {X2}{Y1}:{X3},
                        {X3} >= [TS-2],
                        {X3} <= [TS-0]
                    );
                    NB = COUNT_WHERE('3b.7-2', null, {X2}{Y1}:{X3},
                        {X3} >= [TS-2],
                        {X3} <= [TS-0]
                    );
                    NC = COUNT_WHERE('3b.7-3', null, {X2}{Y1}:{X3},
                        {X3} >= [TS-2],
                        {X3} <= [TS-0]
                    );
                    ND = COUNT_WHERE('3b.7-4', null, {X2}{Y1}:{X3},
                        {X3} >= [TS-2],
                        {X3} <= [TS-0]
                    );
                    NDTPS = COUNT_IF('3a.1', null, {X7}{Y1}:{X7}, '1');
                    RLP = (2 * ([NA] + [NB] + [NC]) + [ND]) / [NDTPS];
                    __SHOW__ = [RLP], [NA], [NB], [NC], [ND], [NDTPS];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' =>
                        'X = CASES([RLP] >= 1, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                [RLP] < 1,
                                2 + (2 * [RLP])
                            )',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                [RLP] < 1,
                                2 + (2 * [RLP])
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
            '32' => [
                'rumus_penilaian' =>
                "BD = AVG_Y('4', null, {X7}{Y1}:{X9}{Y1}, WITH_NULL);
                    BTK = AVG_Y('4', null, {X7}{Y2}:{X9}{Y2}, WITH_NULL);
                    BOP = AVG_Y('4', null, {X7}{Y3}:{X9}{Y3}, WITH_NULL);
                    BOTL = AVG_Y('4', null, {X7}{Y4}:{X9}{Y4}, WITH_NULL);
                    BOK = AVG_Y('4', null, {X7}{Y5}:{X9}{Y5}, WITH_NULL);
                    DO = [BD] + [BTK] + [BOP] + [BOTL] + [BOK];
                    DO_TOTAL = CASES(
                        [DO] >= 1000000,
                        [DO] / 1000000,
                        [DO] < 1000000,
                        [DO]
                    );
                    NM =  SUM('2a', null, {X7}{Y5}:{X8}{Y5});
                    DOP = [DO_TOTAL] / [NM];
                    __SHOW__ = [DOP], [NM], [DO];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' =>
                        'X = CASES([DOP] >= 20, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = CASES([DOP] < 20, [DOP] / 5)',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES([DOP] < 20, [DOP] / 5)',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' =>
                        'X = CASES([DOP] < 20, [DOP] / 5)',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' =>
                        'X = CASES([DOP] < 20, [DOP] / 5)',
                        'kriteria' => 'X'
                    ],
                ]
            ],
            '33' => [
                // DTPS diambilkan dari jumlah semua dosen, referensi kolom yang dihitung dari kolom NIDN/NIDK
                'rumus_penilaian' =>
                "BP = AVG_Y('4', null, {X7}{Y6}:{X9}{Y6}, WITH_NULL);
                    BP_TOTAL = CASES(
                        [BP] >= 1000000,
                        [BP] / 1000000,
                        [BP] < 1000000,
                        [BP]
                    );
                    DTPS = COUNT_IF('3a.1', null, {X7}{Y1}:{X7}, '1');
                    DPD = [BP_TOTAL] / [DTPS];
                    __SHOW__ = [DPD], [DTPS];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' =>
                        'X = CASES([DPD] >= 10, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = CASES([DPD] < 10, (2 * [DPD]) / 5)',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES([DPD] < 10, (2 * [DPD]) / 5)',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' =>
                        'X = CASES([DPD] < 10, (2 * [DPD]) / 5)',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' =>
                        'X = CASES([DPD] < 10, (2 * [DPD]) / 5)',
                        'kriteria' => 'X'
                    ],
                ]
            ],
            '34' => [
                // DTPS diambilkan dari jumlah semua dosen, referensi kolom yang dihitung dari kolom NIDN/NIDK
                'rumus_penilaian' =>
                "BPkM = AVG_Y('4', null, {X7}{Y7}:{X9}{Y7}, WITH_NULL);
                    BPkM_TOTAL = CASES(
                        [BPkM] >= 1000000,
                        [BPkM] / 1000000,
                        [BPkM] < 1000000,
                        [BPkM]
                    );
                    DTPS = COUNT_IF('3a.1', null, {X7}{Y1}:{X7}, '1');
                    DPkMD = [BPkM_TOTAL] / [DTPS];
                    __SHOW__ = [DTPS], [DPkMD];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' =>
                        'X = CASES([DPkMD] >= 5, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = CASES([DPkMD] < 5, (4 * [DPkMD]) / 5)',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES([DPkMD] < 5, (4 * [DPkMD]) / 5)',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' =>
                        'X = CASES([DPkMD] < 5, (4 * [DPkMD]) / 5)',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' =>
                        'X = CASES([DPkMD] < 5, (4 * [DPkMD]) / 5)',
                        'kriteria' => 'X'
                    ],
                ]
            ],
            '42' => [
                'rumus_penilaian' =>
                "JP_RAW = SUM('5a', null, {X8}{Y1}:{X8});
                    JP = ([JP_RAW] * 170) / 60;
                    JB = SUM('5a', null, {X9}{Y1}:{X9});
                    PJP_RAW = ([JP] / [JB]) * 1;
                    PJP = CONCAT([PJP_RAW] * 100, '%');
                    __SHOW__ = [JP], [JB], [PJP];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' =>
                        'X = CASES([PJP_RAW] >= 0.2, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = CASES([PJP_RAW] < 0.2, 20 * [PJP_RAW])',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES([PJP_RAW] < 0.2, 20 * [PJP_RAW])',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' =>
                        'X = CASES([PJP_RAW] < 0.2, 20 * [PJP_RAW])',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' =>
                        'X = CASES([PJP_RAW] < 0.2, 20 * [PJP_RAW])',
                        'kriteria' => 'X'
                    ],
                ]
            ],
            '45' => [
                'rumus_penilaian' =>
                "TS-0 = YEAR(-1);
                    TS-1 = YEAR(-2);
                    TS-2 = YEAR(-3);
                    NMKI = COUNT_IFS('5b', null, {X6}{Y1}:{X6}, [TS-0], [TS-1], [TS-2]);
                    __SHOW__ = [NMKI];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' =>
                        'X = CASES([NMKI] > 3, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = CASES(([NMKI] >= 2 & [NMKI] <= 3), 3)',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES([NMKI] == 1, 2)',
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
            '47.a' => [
                'rumus_penilaian' =>
                "AI = SUM('5c', null, {X3}{Y1}:{X3});
                    BI = SUM('5c', null, {X4}{Y1}:{X4});
                    CI = SUM('5c', null, {X5}{Y1}:{X5});
                    DI = SUM('5c', null, {X6}{Y1}:{X6});
                    TKMi = (([AI] * 4) + ([BI] * 3) + ([CI] * 2) + ([DI] * 1)) / 4;
                    TKM = ([TKMi] / 5) / 100;
                    TKM_PERCENT = CONCAT([TKM] * 100, '%');
                    __SHOW__ = [TKMi], [TKM_PERCENT _AS_ TKM];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' =>
                        'X = CASES([TKM] >= 0.75, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                ([TKM] >= 0.25 & [TKM] < 0.75),
                                (8 * [TKM]) - 2
                            )',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                ([TKM] >= 0.25 & [TKM] < 0.75),
                                (8 * [TKM]) - 2
                            )',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                ([TKM] >= 0.25 & [TKM] < 0.75),
                                (8 * [TKM]) - 2
                            )',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                [TKM] < 0.25,
                                0
                            )',
                        'kriteria' => 'X'
                    ]
                ]
            ],
            '49' => [
                'rumus_penilaian' =>
                "TS-0 = YEAR(-1);
                    TS-1 = YEAR(-2);
                    TS-2 = YEAR(-3);
                    NPM = COUNT_IFS('6a', null, {X6}{Y1}:{X6}, [TS-0], [TS-1], [TS-2]);
                    NPD = SUM('3b.2', null, {X3}{Y1}:{X5});
                    PPDM_RAW = ([NPM] / [NPD]) * 1;
                    PPDM = CONCAT([PPDM_RAW] * 100, '%');
                    __SHOW__ = [NPM], [NPD], [PPDM];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' =>
                        'X = CASES([PPDM_RAW] >= 0.25, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = CASES([PPDM_RAW] < 0.25, 2 + (8 * [PPDM_RAW]))',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES([PPDM_RAW] < 0.25, 2 + (8 * [PPDM_RAW]))',
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
            '51' => [
                'rumus_penilaian' =>
                "TS-0 = YEAR(-1);
                    TS-1 = YEAR(-2);
                    TS-2 = YEAR(-3);
                    NPkMM = COUNT_IFS('7', null, {X6}{Y1}:{X6}, [TS-0], [TS-1], [TS-2]);
                    NPkMD = SUM('3b.3', null, {X3}{Y1}:{X5});
                    PPkMDM_RAW = ([NPkMM] / [NPkMD]) * 1;
                    PPkMDM = CONCAT([PPkMDM_RAW] * 100, '%');
                    __SHOW__ = [NPkMM], [NPkMD], [PPkMDM];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' =>
                        'X = CASES([PPkMDM_RAW] >= 0.25, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = CASES([PPkMDM_RAW] < 0.25, 2 + (8 * [PPkMDM_RAW]))',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES([PPkMDM_RAW] < 0.25, 2 + (8 * [PPkMDM_RAW]))',
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
            '53' => [
                'rumus_penilaian' =>
                "RIPK = AVG_X('8a', null, {X4}{Y1}:{X4});
                    __SHOW__ = [RIPK];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' =>
                        'X = CASES([RIPK] >= 3.25, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                [RIPK] >= 2.00 & [RIPK] < 3.25,
                                ((8 * [RIPK]) - 6) / 5
                            )',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                [RIPK] >= 2.00 & [RIPK] < 3.25,
                                ((8 * [RIPK]) - 6) / 5
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
            '54' => [
                'rumus_penilaian' =>
                "TS-0 = YEAR(-1);
                    TS-2 = YEAR(-3);

                    NI = COUNT_WHERE('8b.1', null, {X3}{Y1}:{X6},
                        {X3} >= [TS-2],
                        {X3} <= [TS-0],
                        {X6} == '1'
                    );

                    NN = COUNT_WHERE('8b.1', null, {X3}{Y1}:{X5},
                        {X3} >= [TS-2],
                        {X3} <= [TS-0],
                        {X5} == '1'
                    );

                    NW = COUNT_WHERE('8b.1', null, {X3}{Y1}:{X4},
                        {X3} >= [TS-2],
                        {X3} <= [TS-0],
                        {X4} == '1'
                    );

                    NM =  SUM('2a', null, {X7}{Y5}:{X8}{Y5});
                    RI = [NI] / [NM];
                    RN = [NN] / [NM];
                    RW = [NW] / [NM];
                    a = 0.001;
                    b = 0.01;
                    c = 0.02;

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
                            ([RI] > 0 & [RI] < [a]) & ([RN] > 0 & [RN] < [b]),
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
            '55' => [
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
                    RI_PERCENT = CONCAT([RI] * 100, '%');
                    RN_PERCENT = CONCAT([RN] * 100, '%');
                    RW_PERCENT = CONCAT([RW] * 100, '%');
                    SCORE = CASES(
                        [RI] >= [a],
                        4,
                        [RI] < [a] & [RN] >= [b],
                        3 + ([RI] / [a]),
                        ([RI] >= 0 & [RI] < [a]) & ([RN] > 0 & [RN] < [b]),
                        2 + (2 * ([RI] / [a])) + ([RN] / [b]) - (([RI] * [RN]) / ([a] * [b])),
                        ([RI] > 0 & [RI] < [a]) & ([RN] >= 0 & [RN] < [b]),
                        2 + (2 * ([RI] / [a])) + ([RN] / [b]) - (([RI] * [RN]) / ([a] * [b])),
                        [RI] == 0 & [RN] == 0 & [RW] >= [c],
                        2,
                        [RI] == 0 & [RN] == 0 & [RW] < [c],
                        (2 * [RW]) / [c]
                    );
                    __SHOW__ = [NI], [NN], [NW], [NM], [RI_PERCENT _AS_ RI], [RN_PERCENT _AS_ RN], [RW_PERCENT _AS_ RW];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' =>
                        'X = [SCORE]',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = [SCORE]',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = [SCORE]',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' =>
                        'X = [SCORE]',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' =>
                        'X = [SCORE]',
                        'kriteria' => 'X'
                    ]
                ]
            ],
            '56' => [
                'rumus_penilaian' =>
                "MS_TS-4 = SUM('8c.2', null, {X10}{Y1}:{X10}{Y1}) * SUM('8c.2', null, {X11}{Y1}:{X11}{Y1});
                    MS_TS-5 = SUM('8c.2', null, {X10}{Y2}:{X10}{Y2}) * SUM('8c.2', null, {X11}{Y2}:{X11}{Y2});
                    MS_TS-6 = SUM('8c.2', null, {X10}{Y3}:{X10}{Y3}) * SUM('8c.2', null, {X11}{Y3}:{X11}{Y3});
                    MS_TS-7 = SUM('8c.2', null, {X10}{Y4}:{X10}{Y4}) * SUM('8c.2', null, {X11}{Y4}:{X11}{Y4});

                    MS = ([MS_TS-4] + [MS_TS-5] + [MS_TS-6] + [MS_TS-7]) / SUM('8c.2', null, {X10}{Y1}:{X10});
                    __SHOW__ = [MS];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                [MS] > 3.5 & [MS] <= 4.5,
                                4
                            )',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                [MS] > 3 & [MS] <= 3.5,
                                (8 * [MS]) - 24,
                                [MS] > 4.5 & [MS] <= 7,
                                (56 - (8 * [MS])) / 5
                            )',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                [MS] > 3 & [MS] <= 3.5,
                                (8 * [MS]) - 24,
                                [MS] > 4.5 & [MS] <= 7,
                                (56 - (8 * [MS])) / 5
                            )',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                [MS] > 3 & [MS] <= 3.5,
                                (8 * [MS]) - 24,
                                [MS] > 4.5 & [MS] <= 7,
                                (56 - (8 * [MS])) / 5
                            )',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                [MS] <= 3,
                                0
                            )',
                        'kriteria' => 'X'
                    ]
                ]
            ],
            '57' => [
                'rumus_penilaian' =>
                "MGR = SUM('8c.2', null, {X10}{Y1}:{X10});
                    MGA = SUM('8c.2', null, {X2}{Y1}:{X2});
                    PTW_RAW = [MGR] / [MGA];
                    PTW = CONCAT([PTW_RAW] * 100, '%');
                    __SHOW__ = [PTW], [MGR _AS_ NL], [MGA _AS_ ND];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' =>
                        'X = CASES([PTW_RAW] >= 0.5, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = CASES([PTW_RAW] < 0.5, 1 + (6 * [PTW_RAW]))',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES([PTW_RAW] < 0.5, 1 + (6 * [PTW_RAW]))',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' =>
                        'X = CASES([PTW_RAW] < 0.5, 1 + (6 * [PTW_RAW]))',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' => 'X = 0',
                        'kriteria' => 'X'
                    ]
                ]
            ],
            '58' => [
                'rumus_penilaian' =>
                "MGR = SUM('8c.2', null, {X10}{Y1}:{X10});
                    MGA = SUM('8c.2', null, {X2}{Y1}:{X2});
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
            '60' => [
                'rumus_penilaian' =>
                "NL = SUM('8d.1b', null, {X2}{Y1}:{X2});
                    NJ = SUM('8d.1b', null, {X3}{Y1}:{X3});
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
                    WT0 = SUM('8d.1b', null, {X4}{Y1}:{X4});
                    WT1 = SUM('8d.1b', null, {X5}{Y1}:{X5});
                    WT2 = SUM('8d.1b', null, {X6}{Y1}:{X6});
                    WT = (([WT0] * [MID1]) + ([WT1] * [MID2]) + ([WT2] * [MID3])) / ([WT0] + [WT1] + [WT2]);
                    SCORE_WT = CASES(
                        [WT] < 6,
                        4,
                        [WT] >= 6 & [WT] <= 18,
                        (18 - [WT]) / 3,
                        [WT] > 18,
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
            '61' => [
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
                        [PBS] >= 0.6,
                        4,
                        1 == 1,
                        (20 * [PBS]) / 3
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
            '62' => [
                'rumus_penilaian' =>
                "NI = SUM('8e.1', null, {X6}{Y1}:{X6});
                    NN = SUM('8e.1', null, {X5}{Y1}:{X5});
                    NW = SUM('8e.1', null, {X4}{Y1}:{X4});
                    NL = SUM('8e.1', null, {X2}{Y1}:{X2});
                    RI = ([NI] / [NL]) * 1;
                    RN = ([NN] / [NL]) * 1;
                    RW = ([NW] / [NL]) * 1;
                    RI_PERCENT = CONCAT([RI] * 100, '%');
                    RN_PERCENT = CONCAT([RN] * 100, '%');
                    RW_PERCENT = CONCAT([RW] * 100, '%');
                    a = 0.05;
                    b = 0.2;
                    c = 0.9;
                    NJ = [NN] + [NW] + [NI];
                    PJ = ([NL] / [NJ]);
                    PJ_PERCENT = CONCAT([PJ] * 100, '%');
                    Prmin = CASES(
                        [NL] < 300,
                        (50 / 100) - (([NL] / 300) * (20 / 100)),
                        1 == 1,
                        30 / 100
                    );
                    SCORE = CASES(
                        [RI] >= [a],
                        4,
                        [RI] < [a] & [RN] >= [b],
                        3 + ([RI] / [a]),
                        ([RI] >= 0 & [RI] < [a]) & ([RN] > 0 & [RN] < [b]),
                        2 + (2 * ([RI] / [a])) + ([RN] / [b]) - (([RI] * [RN]) / ([a] * [b])),
                        ([RI] > 0 & [RI] < [a]) & ([RN] >= 0 & [RN] < [b]),
                        2 + (2 * ([RI] / [a])) + ([RN] / [b]) - (([RI] * [RN]) / ([a] * [b])),
                        [RI] == 0 & [RN] == 0 & [RW] >= [c],
                        2,
                        [RI] == 0 & [RN] == 0 & [RW] < [c],
                        (2 * [RW]) / [c]
                    );
                    FIN_SCORE = CASES(
                        [PJ] < [Prmin],
                        ([PJ] / [Prmin]) * [SCORE],
                        1 == 1,
                        [SCORE]
                    );
                    Prmin_PERCENT = CONCAT([Prmin] * 100, '%');
                    __SHOW__ = [NI], [NN], [NW], [NL], [RI_PERCENT _AS_ RI],
                                [RN_PERCENT _AS_ RN], [RW_PERCENT _AS_ RW], [NJ],
                                [PJ_PERCENT _AS_ PJ], [Prmin_PERCENT _AS_ Prmin], [FIN_SCORE _AS_ SKOR];",
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
            '63' => [
                'rumus_penilaian' =>
                "NL = SUM('8e.2.Ref', null, {X2}{Y1}:{X2});
                    NJ = SUM('8e.2.Ref', null, {X3}{Y1}:{X3});
                    AI = SUM('8e.2', null, {X3}{Y1}:{X3});
                    BI = SUM('8e.2', null, {X4}{Y1}:{X4});
                    CI = SUM('8e.2', null, {X5}{Y1}:{X5});
                    DI = SUM('8e.2', null, {X6}{Y1}:{X6});
                    TKi = ((4 * [AI]) + (3 * [BI]) + (2 * [CI]) + (1 * [DI])) / 100;
                    Prmin = CASES(
                        [NL] >= 300,
                        30 / 100,
                        1 == 1,
                        ((50 / 100) - (([NL] / 300) * (20 / 100)))
                    );
                    Prmin_PERCENT = CONCAT([Prmin] * 100, '%');
                    PJ = ([NL] / [NJ]);
                    PJ_PERCENT = CONCAT([PJ] * 100, '%');
                    SCORE = [TKi] / 7;

                    FIN_SCORE = CASES(
                        [PJ] < [Prmin],
                        ([PJ] / [Prmin]) * [SCORE],
                        1 == 1,
                        [SCORE]
                    );
                    __SHOW__ = [NJ], [NL], [PJ_PERCENT _AS_ PJ], [Prmin_PERCENT _AS_ Prmin],
                        [TKi], [FIN_SCORE _AS_ SKOR];",
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
            '64' => [
                'rumus_penilaian' =>
                "NA1 = SUM('8f.1a', null, {X3}{Y1}:{X5}{Y1});
                    NA2 = SUM('8f.1a', null, {X3}{Y2}:{X5}{Y2});
                    NA3 = SUM('8f.1a', null, {X3}{Y3}:{X5}{Y3});
                    NA4 = SUM('8f.1a', null, {X3}{Y4}:{X5}{Y4});
                    NB1 = SUM('8f.1a', null, {X3}{Y5}:{X5}{Y5});
                    NB2 = SUM('8f.1a', null, {X3}{Y6}:{X5}{Y6});
                    NB3 = SUM('8f.1a', null, {X3}{Y7}:{X5}{Y7});
                    NC1 = SUM('8f.1a', null, {X3}{Y8}:{X5}{Y8});
                    NC2 = SUM('8f.1a', null, {X3}{Y9}:{X5}{Y9});
                    NC3 = SUM('8f.1a', null, {X3}{Y10}:{X5}{Y10});
                    NM =  SUM('2a', null, {X7}{Y5}:{X8}{Y5});
                    RL =  (([NA1] + [NB1] + [NC1]) / [NM]) * 1;
                    RN = (([NA2] + [NA3] + [NB2] + [NC2]) / [NM]) * 1;
                    RI = (([NA4] + [NB3] + [NC3]) / [NM]) * 1;
                    RL_PERCENT = CONCAT([RL] * 100, '%');
                    RN_PERCENT = CONCAT([RN] * 100, '%');
                    RI_PERCENT = CONCAT([RI] * 100, '%');
                    a = 0.01;
                    b = 0.1;
                    c = 0.5;
                    SCORE = CASES(
                        [RI] >= [a],
                        4,
                        [RI] < [a] & [RN] >= [b],
                        3 + ([RI] / [a]),
                        ([RI] >= 0 & [RI] < [a]) & ([RN] > 0 & [RN] < [b]),
                        2 + (2 * ([RI] / [a])) + ([RN] / [b]) - (([RI] * [RN]) / ([a] * [b])),
                        ([RI] > 0 & [RI] < [a]) & ([RN] >= 0 & [RN] < [b]),
                        2 + (2 * ([RI] / [a])) + ([RN] / [b]) - (([RI] * [RN]) / ([a] * [b])),
                        [RI] == 0 & [RN] == 0 & [RL] >= [c],
                        2,
                        [RI] == 0 & [RN] == 0 & [RL] < [c],
                        (2 * [RL]) / [c]
                    );
                    __SHOW__ = [NA1], [NB1], [NC1], [NA2], [NA3],
                        [NB2], [NC2], [NA4], [NB3], [NC3], [NM],
                        [RL_PERCENT _AS_ RL], [RN_PERCENT _AS_ RN],
                        [RI_PERCENT _AS_ RI];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' =>
                        'X = [SCORE]',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = [SCORE]',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = [SCORE]',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' =>
                        'X = [SCORE]',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' =>
                        'X = [SCORE]',
                        'kriteria' => 'X'
                    ],
                ]
            ],
            '65' => [
                'rumus_penilaian' =>
                "TS-0 = YEAR(-1);
                    TS-1 = YEAR(-2);
                    TS-2 = YEAR(-3);
                    NA = COUNT_IFS('8f.4-1', null, {X3}{Y1}:{X3}, [TS-0], [TS-1], [TS-2]);
                    NB = COUNT_IFS('8f.4-2', null, {X3}{Y1}:{X3}, [TS-0], [TS-1], [TS-2]);
                    NC = COUNT_IFS('8f.4-3', null, {X3}{Y1}:{X3}, [TS-0], [TS-1], [TS-2]);
                    ND = COUNT_IFS('8f.4-4', null, {X3}{Y1}:{X3}, [TS-0], [TS-1], [TS-2]);
                    NLP = 2 * ([NA] + [NB] + [NC]) + [ND];
                    __SHOW__ = [NA] + [NB] + [NC] + [ND], [NLP];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' =>
                        'X = CASES([NLP] >= 1, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                [NLP] < 1,
                                2 + (2 * [NLP])
                            )',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                [NLP] < 1,
                                2 + (2 * [NLP])
                            )',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' =>
                        'X = 0',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' =>
                        'X = 0',
                        'kriteria' => 'X'
                    ]
                ]
            ],
        ];
    }
}
