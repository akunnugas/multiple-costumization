<?php

namespace Modules\SPMI\Data\PenilaianMatriks;

class IAPSS3 extends MigrateFormula
{
    protected string $assessmentGuideCode = 'IAPS-S3';

    protected function getRumus(): array
    {
        return [
            // BUTIR INDICATOR
            '9.a' => [
                'rumus_penilaian' =>
                "N1 = COUNT('1a', C1, {X2}{Y1}:{X2});
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
                "NI = COUNT_WHERE('1a', null, {X3}{Y1}:{X10},
                        {X3} == 1
                    );
                    NN = COUNT_WHERE('1a', null, {X4}{Y1}:{X10},
                        {X4} == 1
                    );
                    NW = COUNT_WHERE('1a', null, {X5}{Y1}:{X10},
                        {X5} == 1
                    );
                    a = 4;
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
            '15.b' => [
                'rumus_penilaian' =>
                "MAF = SUM('2b', null, {X6}{Y1}:{X8});
                    MAP = SUM('2b', null, {X9}{Y1}:{X11});
                    NM = SUM('2b', null, {X3}{Y1}:{X5});
                    PMA = ([MAF] + [MAP]) / [NM];
                    PMA_PERCENTAGE = 0.05;
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
                                2 + (40 * [PMA])
                            )',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                [PMA] < [PMA_PERCENTAGE],
                                2 + (40 * [PMA])
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
                    NDTPS = COUNT_IF('3a.1', null, {X7}{Y1}:{X7}, '1');
                    PGB_RAW = ([NDGB] / [NDTPS]) * 1;
                    PGB = CONCAT([PGB_RAW] * 100, '%');
                    __SHOW__ = [NDGB], [NDTPS], [PGB];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' => 'X = CASES(([NDGB] >= 2 & [PGB_RAW] >= 0.7), 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' => 'X = CASES(([NDGB] >= 2 & [PGB_RAW] < 0.7), 2 + ((20 * [PGB_RAW]) / 7))',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' => 'X = CASES(([NDGB] >= 2 & [PGB_RAW] < 0.7), 2 + ((20 * [PGB_RAW]) / 7))',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' => 'X = 0',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' => 'X = CASES([NDGB] < 2, 0)',
                        'kriteria' => 'X'
                    ]
                ]
            ],
            '19' => IAPSS1::getRumusPenilaianByCode('21'),
            '20' => IAPSS1::getRumusPenilaianByCode('22'),
            '21' => IAPSS1::getRumusPenilaianByCode('23'),
            '22' => [
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
                        'X = CASES([RRD] >= 2, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                [RRD] < 2,
                                2 + [RRD]
                            )',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                [RRD] < 2,
                                2 + [RRD]
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
            '23' => [
                'rumus_penilaian' =>
                "NL = SUM('3b.2', null, {X3}{Y1}:{X5}{Y1});
                    NN = SUM('3b.2', null, {X3}{Y2}:{X5}{Y2});
                    NI = SUM('3b.2', null, {X3}{Y3}:{X5}{Y3});
                    NDTPS = COUNT_IF('3a.1', null, {X7}{Y1}:{X7}, '1');
                    RI = [NI] / 3 / [NDTPS];
                    RN = [NN] / 3 / [NDTPS];
                    RL = [NL] / 3 / [NDTPS];
                    a = 0.1;
                    b = 1;
                    c = 2;
                    __SHOW__ = [NDTPS], [NI], [NN], [NL], [RI], [NN], [RL], [RN];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' => 'X = CASES([RI] >= [a], 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' => 'X = CASES(
                            ([RI] < [a] & [RN] >= [b]), 3 + ([RI] / [a]),
                            ([RI] > 0 & [RI] < [a]) & ([RN] > 0 & [RN] < [b]), 2 + (2 * ([RI] / [a])) + ([RN] / [b]) - (([RI] * [RN]) / ([a] * [b]))
                        )',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' => 'X = CASES(
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
            '24' => [
                'rumus_penilaian' =>
                "NI = SUM('3b.3', null, {X3}{Y3}:{X5}{Y3});
                    NN = SUM('3b.3', null, {X3}{Y2}:{X5}{Y2});
                    NL = SUM('3b.3', null, {X3}{Y1}:{X5}{Y1});
                    NDTPS = COUNT_IF('3a.1', null, {X7}{Y1}:{X7}, '1');
                    RI = [NI] / 3 / [NDTPS];
                    RN = [NN] / 3 / [NDTPS];
                    RL = [NL] / 3 / [NDTPS];
                    a = 0.1;
                    b = 1;
                    c = 2;
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
            '25' => [
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
                    a = 0.2;
                    b = 2;
                    c = 4;
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
            '26' => [
                'rumus_penilaian' =>
                "NAS = COUNT('3b.5', null, {X3}{Y1}:{X3});
                    NDTPS = COUNT_IF('3a.1', null, {X7}{Y1}:{X7}, '1');
                    RS = [NAS] / [NDTPS];
                    __SHOW__ = [NAS], [NDTPS], [RS];",
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
            '27' => [
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
                    RLP = ((4 * [NA]) + (2 * ([NB] + [NC])) + [ND]) / [NDTPS];
                    __SHOW__ = [RLP], [NA], [NB], [NC], [ND], [NDTPS];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' =>
                        'X = CASES([RLP] >= 2, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                [RLP] < 2,
                                2 + [RLP]
                            )',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES(
                                [RLP] < 2,
                                2 + [RLP]
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
            '30' => [
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
                        'X = CASES([DOP] >= 40, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = CASES([DOP] < 40, [DOP] / 10)',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES([DOP] < 40, [DOP] / 10)',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' =>
                        'X = CASES([DOP] < 40, [DOP] / 10)',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' =>
                        'X = CASES([DOP] < 40, [DOP] / 10)',
                        'kriteria' => 'X'
                    ],
                ]
            ],
            '31' => [
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
                        'X = CASES([DPD] >= 30, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = CASES([DPD] < 30, (2 * [DPD]) / 15)',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES([DPD] < 30, (2 * [DPD]) / 15)',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' =>
                        'X = CASES([DPD] < 30, (2 * [DPD]) / 15)',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' =>
                        'X = CASES([DPD] < 30, (2 * [DPD]) / 15)',
                        'kriteria' => 'X'
                    ],
                ]
            ],
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
            '46' => [
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
                        'X = CASES([PPDM_RAW] >= 0.75, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = CASES([PPDM_RAW] < 0.75, 2 + ((8 * [PPDM_RAW]) / 3))',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES([PPDM_RAW] < 0.75, 2 + ((8 * [PPDM_RAW]) / 3))',
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
            '47' => [
                'rumus_penilaian' => "
                    TS-0 = YEAR(-1);
                    TS-1 = YEAR(-2);
                    TS-2 = YEAR(-3);
                    NDM = COUNT_IFS('6b', null, {X6}{Y1}:{X6}, [TS-0], [TS-1], [TS-2]);
                    NPD = SUM('3b.2', null, {X3}{Y1}:{X5});
                    PPDM_RAW = ([NDM] / [NPD]) * 1;
                    PPDM = CONCAT([PPDM_RAW] * 100, '%');
                    __SHOW__ = [NDM], [NPD], [PPDM];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' => 'X = CASES([PPDM_RAW] >= 0.5, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' => 'X = CASES([PPDM_RAW] < 0.5, 1 + (6 * [PPDM_RAW]))',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' => 'X = CASES([PPDM_RAW] < 0.5, 1 + (6 * [PPDM_RAW]))',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' => 'X = CASES([PPDM_RAW] < 0.5, 1 + (6 * [PPDM_RAW]))',
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
            '51' => [
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
                    a = 0.01;
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
            '52' => [
                "rumus_penilaian" =>
                    "MS_TS-1 = SUM('8c.4', null, {X7}{Y1}:{X7}{Y1}) * SUM('8c.4', null, {X8}{Y1}:{X8}{Y1});
                    MS_TS-2 = SUM('8c.4', null, {X7}{Y2}:{X7}{Y2}) * SUM('8c.4', null, {X8}{Y2}:{X8}{Y2});
                    MS_TS-3 = SUM('8c.4', null, {X7}{Y3}:{X7}{Y3}) * SUM('8c.4', null, {X8}{Y3}:{X8}{Y3});
                    MS = ([MS_TS-1] + [MS_TS-2] + [MS_TS-3]) / SUM('8c.4', null, {X7}{Y1}:{X7});

                    __SHOW__ = [MS];",
                "rumus_skor" => [
                    '4' => [
                        'rumus_penilaian' => 'X = CASES([MS] > 2.5 & [MS] <= 3.5, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' => '
                        X = CASES(
                            [MS] > 2 & [MS] <= 2.5,
                            (8 * [MS]) - 16,
                            [MS] > 3.5 & [MS] <= 7,
                            (56 - (8 * [MS])) / 7
                        )',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' => '
                        X = CASES(
                            [MS] > 2 & [MS] <= 2.5,
                            (8 * [MS]) - 16,
                            [MS] > 3.5 & [MS] <= 7,
                            (56 - (8 * [MS])) / 7
                        )',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' => '
                        X = CASES(
                            [MS] > 2 & [MS] <= 2.5,
                            (8 * [MS]) - 16,
                            [MS] > 3.5 & [MS] <= 7,
                            (56 - (8 * [MS])) / 7
                        )',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' => 'X = CASES([MS] <= 2, 0)',
                        'kriteria' => 'X'
                    ]
                ]
            ],
            '53' => [
                'rumus_penilaian' =>
                "MGR = SUM('8c.4', null, {X10}{Y1}:{X10});
                    MGA = SUM('8c.4', null, {X2}{Y1}:{X2});
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
            '54' => [
                'rumus_penilaian' => "
                    MGR = SUM('8c.4', null, {X7}{Y1}:{X7});
                    MGA = SUM('8c.4', null, {X2}{Y1}:{X2});
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
            '56' => [
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
                    a = 0.03;
                    b = 0.3;
                    c = 0.9;
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
            '57' => [
                'rumus_penilaian' => "
                    NAS = COUNT('8f.2', null, {X2}{Y1}:{X2});

                    __SHOW__ = [NAS];",
                'rumus_skor' => [
                    '4' => [
                        'rumus_penilaian' => 'X = CASES([NAS] >= 3, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' => 'X = CASES(([NAS] < 3 & [NAS] > 0), 3)',
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
            '58' => [
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
                        'X = CASES([NLP] >= 3, 4)',
                        'kriteria' => 'X'
                    ],
                    '3' => [
                        'rumus_penilaian' =>
                        'X = CASES([NLP] == 2, 3)',
                        'kriteria' => 'X'
                    ],
                    '2' => [
                        'rumus_penilaian' =>
                        'X = CASES([NLP] == 1, 2)',
                        'kriteria' => 'X'
                    ],
                    '1' => [
                        'rumus_penilaian' =>
                        'X = CASES([NLP] == 0, 1)',
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' =>
                        'X = 0',
                        'kriteria' => 'X'
                    ]
                ]
            ]
        ];
    }
}
