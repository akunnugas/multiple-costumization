<?php

namespace Modules\SPMI\Data\PenilaianMatriks;

class IAPS51S2UNG extends MigrateFormula
{
    protected string $assessmentGuideCode = 'IAPS5.1-S2-Ung';

    protected function getRumus(): array
    {
        return [
            'B106B' => [
                'rumus_penilaian' =>
                "DPR = COUNT_WHERE('1.1.a.1', null, {X2}{Y1}:{X8}, {X3} == 'S3', {X8} == '1');
                    __SHOW__ = [DPR _AS_ 'DPRD'];
                ",
                'rumus_skor' => [
                    '1' => [
                        'rumus_penilaian' => "X = CASES([DPR] >= 7, 1);",
                        'kriteria' => "X"
                    ],
                    '0' => [
                        'rumus_penilaian' => "X = CASES([DPR] < 7, 0);",
                        'kriteria' => "X"
                    ],
                ],
            ],

            'B106C' => [
                'rumus_penilaian' =>
                "TS-0 = YEAR(-1);
                    GB = COUNT_IF('1.1.a.1', null, {X5}{Y1}:{X5}, 'Guru Besar');
                    LK = COUNT_IF('1.1.a.1', null, {X5}{Y1}:{X5}, 'Lektor Kepala');
                    NDPR = COUNT('1.1.a.1', null, {X2}{Y1}:{X2});
                    PDJA = (([GB] + [LK]) / [NDPR]) * 100;
                    __SHOW__ = [GB], [LK], [NDPR], [PDJA];
                ",
                'rumus_skor' => [
                    '1' => [
                        'rumus_penilaian' => "X = CASES([PDJA] >= 80, 1, 1);",
                        'kriteria' => "X"
                    ],
                    '0' => [
                        'rumus_penilaian' => "X = 0;",
                        'kriteria' => "X"
                    ],
                ],
            ],

            'B106D' => [
                'rumus_penilaian' =>
                "NDPR = COUNT('1.1.a.1', null, {X2}{Y1}:{X2});
                    NDTT = COUNT('1.1.a.2', null, {X2}{Y1}:{X2});
                    PDTT = ([NDTT] / ([NDPR] + [NDTT])) * 100;
                    __SHOW__ = [NDTT], [NDPR], [PDTT];
                ",
                'rumus_skor' => [
                    '1' => [
                        'rumus_penilaian' => "X = CASES([PDTT] <= 10, 1);",
                        'kriteria' => "X"
                    ],
                    '0' => [
                        'rumus_penilaian' => "X = CASES([PDTT] > 10, 0);",
                        'kriteria' => "X"
                    ],
                ],
            ],

            'B108C' => [
                'rumus_penilaian' =>
                "SUM_TS2A = SUM('1.1.a.6', null, {X3}{Y1}:{X3}{Y5});
                    SUM_TS1A = SUM('1.1.a.6', null, {X4}{Y1}:{X4}{Y5});
                    SUM_TSA = SUM('1.1.a.6', null, {X5}{Y1}:{X5}{Y5});
                    SUM_TS2B = SUM('1.1.a.6', null, {X3}{Y6}:{X3}{Y8});
                    SUM_TS1B = SUM('1.1.a.6', null, {X4}{Y6}:{X4}{Y8});
                    SUM_TSB = SUM('1.1.a.6', null, {X5}{Y6}:{X5}{Y8});
                    A = ([SUM_TS2A] + [SUM_TS1A] + [SUM_TSA]) / 3;
                    B = ([SUM_TS2B] + [SUM_TS1B] + [SUM_TSB]) / 3;
                    INV = ([B] / ([A] + [B])) * 100;
                    AVG_MHS = AVG_Y('XREF.1', null, {X2}{Y1}:{X4}{Y1}, WITH_NULL);
                    OP_MHS = [A] / [AVG_MHS];
                    INV_FORMAT = CONCAT([INV], '%');
                    __SHOW__ = [INV_FORMAT _AS_ 'Proporsi Investasi'], [A], [B], [OP_MHS _AS_ 'Biaya Operasional per Mahasiswa'], [AVG_MHS _AS_ 'Rata-rata Jumlah Mahasiswa Aktif (3 Tahun)'];",
                'rumus_skor' => [
                    '1' => [
                        'rumus_penilaian' => "X = CASES([INV] > 5 & [OP_MHS] >= 15000000, 1);",
                        'kriteria' => "X"
                    ],
                    '0' => [
                        'rumus_penilaian' => "X = CASES([INV] <= 5 & [OP_MHS] < 15000000, 0);",
                        'kriteria' => "X"
                    ],
                ],
            ],

            'B115' => [
                'rumus_penilaian' =>
                "NMA = SUM('1.1.d.12', null, {X2}{Y1}:{X4});
                    NA1 = SUM('1.2.2', null, {X3}{Y1}:{X5}{Y1});
                    NA2 = SUM('1.2.2', null, {X3}{Y2}:{X5}{Y2});
                    NA3 = SUM('1.2.2', null, {X3}{Y3}:{X5}{Y3});
                    NA4 = SUM('1.2.2', null, {X3}{Y4}:{X5}{Y4});
                    NB2 = SUM('1.2.2', null, {X3}{Y6}:{X5}{Y6});
                    NB3 = SUM('1.2.2', null, {X3}{Y7}:{X5}{Y7});
                    NC2 = SUM('1.2.2', null, {X3}{Y9}:{X5}{Y9});
                    NC3 = SUM('1.2.2', null, {X3}{Y10}:{X5}{Y10});
                    NDPRS = COUNT_WHERE('1.1.a.1', null, {X3}{Y1}:{X8}, {X8} == '1');
                    PPID = (([NA1] + [NA2] + [NA3] + [NA4] + [NB2] + [NB3] + [NC2] + [NC3]) / [NDPRS]) * 100;
                    PDIP_FORMAT = CONCAT([PPID], '%');
                    NKARYA = COUNT('1.2.6', null, {X3}{Y1}:{X3});
                    RPKID = ([NKARYA] / [NDPRS]) * 100;
                    RPKID_FORMAT = CONCAT([RPKID], '%');
                    __SHOW__ = [NA1], [NA2], [NA3], [NA4], [NB2], [NB3], [NC2], [NC3], [NMA], [NDPRS], [PDIP_FORMAT _AS_ 'PPID'], [RPKID_FORMAT _AS_ 'RPKID'], [NKARYA _AS_ 'Judul artikel yang disitasi'];",
                'rumus_skor' => [
                    '1' => [
                        'rumus_penilaian' => "X = CASES([NMA] >= 15 & [PPID] >= 30 & [RPKID] >= 30, 1);",
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' => 'X = 0;',
                        'kriteria' => 'X'
                    ]
                ]
            ],

            'B217C' => [
                'rumus_penilaian' =>
                "TS-0 = YEAR(-1);
                    TS-1 = YEAR(-2);
                    TS-2 = YEAR(-3);
                    NPD = COUNT_WHERE('XREF.3', null, {X2}{Y1}:{X6},
                        {X6} >= [TS-2],
                        {X6} <= [TS-0]
                    );
                    NPM = COUNT_WHERE('1.2.5', null, {X2}{Y1}:{X6},
                        {X4} != null,
                        {X6} >= [TS-2],
                        {X6} <= [TS-0]
                    );
                    PPDM = ([NPM] / [NPD]) * 100;
                    PPDM_FORMAT = CONCAT([PPDM], '%');
                    __SHOW__ = [NPM], [NPD], [PPDM_FORMAT _AS_ 'PPDM'];",
                'rumus_skor' => [
                    '1' => [
                        'rumus_penilaian' => "X = CASES([PPDM] >= 100, 1);",
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' => 'X = 0;',
                        'kriteria' => 'X'
                    ]
                ]
            ],

            'B219B' => [
                'rumus_penilaian' =>
                "NDPRS = COUNT_WHERE('1.1.a.1', null, {X3}{Y1}:{X8}, {X8} == '1');
                    NAS = COUNT_WHERE('1.2.6', null, {X3}{Y1}:{X4}, {X4} > 0);
                    RS = [NAS] / [NDPRS] * 100;
                    RS_FORMAT = CONCAT([RS], '%');
                    __SHOW__ = [NAS], [NDPRS _AS_ NDPRPS], [RS_FORMAT _AS_ 'RS'];",
                'rumus_skor' => [
                    '1' => [
                        'rumus_penilaian' => "X = CASES([RS] >= 100, 1);",
                        'kriteria' => 'X'
                    ],
                    '0' => [
                        'rumus_penilaian' => 'X = 0;',
                        'kriteria' => 'X'
                    ]
                ]
            ],

            'B323A' => [
                'rumus_penilaian' =>
                "TS-0 = YEAR(-1);
                    TS-2 = YEAR(-3);
                    NRD = COUNT_WHERE('1.2.4', null, {X4}{Y1}:{X8},
                        {X4} != null,
                        {X8} >= [TS-2],
                        {X8} <= [TS-0]
                    );
                    NDPR = COUNT('1.1.a.1', null, {X2}{Y1}:{X2});
                    RRD = [NRD] / [NDPR] * 100;
                    RRD_FORMAT = CONCAT([RRD], '%');
                    __SHOW__ = [NRD], [NDPR], [RRD_FORMAT _AS_ 'RRD'];
                ",
                'rumus_skor' => [
                    '1' => [
                        'rumus_penilaian' => "X = CASES([RRD] >= 100, 1);",
                        'kriteria' => "X"
                    ],
                    '0' => [
                        'rumus_penilaian' => "X = 0;",
                        'kriteria' => "X"
                    ],
                ],
            ],

            'B323B' => [
                'rumus_penilaian' =>
                "NA = COUNT('1.2.3', C1, {X2}{Y1}:{X2});
                    NB = COUNT('1.2.3', C2, {X2}{Y1}:{X2});
                    NC = COUNT('1.2.3', C3, {X2}{Y1}:{X2});
                    NDPR = COUNT('1.1.a.1', null, {X2}{Y1}:{X2});
                    RHKI = (([NA] + [NB] + [NC]) / [NDPR]) * 100;
                    RHKI_FORMAT = CONCAT([RHKI], '%');
                    __SHOW__ = [NA], [NB], [NC], [NDPR], [RHKI_FORMAT _AS_ RHKI];
                ",
                'rumus_skor' => [
                    '1' => [
                        'rumus_penilaian' => "X = CASES([RHKI] >= 100, 1);",
                        'kriteria' => "X"
                    ],
                    '0' => [
                        'rumus_penilaian' => "X = 0;",
                        'kriteria' => "X"
                    ],
                ],
            ],

            'B112A' => [
                'rumus_penilaian' =>
                "NL = SUM('1.1.c.7', null, {X2}{Y1}:{X4}{Y1});
                    A = SUM('1.1.c.8b', null, {X4}{Y3}:{X4}{Y3});
                    B = SUM('1.1.c.8b', null, {X2}{Y3}:{X2}{Y3});
                    PK1MTK = ([A] / [B]) * 100;
                    PK1MTK_FORMAT = CONCAT([PK1MTK], '%');
                    C = SUM('1.1.c.8b', null, {X5}{Y1}:{X5}{Y1});
                    D = SUM('1.1.c.8b', null, {X2}{Y1}:{X2}{Y1});
                    PK2MTK = ([C] / [D]) * 100;
                    PK2MTK_FORMAT = CONCAT([PK2MTK], '%');
                    TS-0 = YEAR(-1);
                    NA1 = SUM('1.2.2', null, {X3}{Y1}:{X5}{Y1});
                    NA2 = SUM('1.2.2', null, {X3}{Y2}:{X5}{Y2});
                    NA3 = SUM('1.2.2', null, {X3}{Y3}:{X5}{Y3});
                    NA4 = SUM('1.2.2', null, {X3}{Y4}:{X5}{Y4});
                    RPKID_PEMBANDING = SUM('1.2.2', null, {X3}{Y1}:{X5}{Y10});
                    RPKID = ([NA1] + [NA2] + [NA3] + [NA4]) / [RPKID_PEMBANDING] * 100;
                    RPKID_FORMAT = CONCAT([RPKID], '%');
                    __SHOW__ = [A], [B], [C], [D], [NA1], [NA2], [NA3], [NA4], [RPKID_PEMBANDING _AS_ 'Jumlah Keseluruhan Publikasi'], [RPKID_FORMAT _AS_ 'RPKID'], [NL], [PK1MTK_FORMAT _AS_ PK1MTK], [PK2MTK_FORMAT _AS_ PK2MTK];",
                'rumus_skor' => [
                    '1' => [
                        'rumus_penilaian' => "X = CASES([NL] <= 12 & [PK1MTK] >= 60 & [PK2MTK] >= 90 & [RPKID] >= 30, 1);",
                        'kriteria' => "X"
                    ],
                    '0' => [
                        'rumus_penilaian' => "X = 0;",
                        'kriteria' => "X"
                    ],
                ],
            ],
        ];
    }
}
