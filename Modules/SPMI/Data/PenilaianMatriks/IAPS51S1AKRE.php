<?php

namespace Modules\SPMI\Data\PenilaianMatriks;

class IAPS51S1AKRE extends MigrateFormula
{
    protected string $assessmentGuideCode = 'IAPS5.1-S1-Akre';

    protected function getRumus(): array
    {
        return [
            'B106D' => [
                'rumus_penilaian' =>
                "NDPR = COUNT('1.1.a.1', null, {X2}{Y1}:{X2});
                    NDTT = COUNT('1.1.a.2', null, {X2}{Y1}:{X2});
                    PDTT = ([NDTT] / ([NDPR] + [NDTT])) * 100;
                    __SHOW__ = [NDTT], [NDPR], [PDTT];
                ",
                'rumus_skor' => [
                    '1' => [
                        'rumus_penilaian' => "X = CASES([PDTT] <= 40, 1);",
                        'kriteria' => "X"
                    ],
                    '0' => [
                        'rumus_penilaian' => "X = CASES([PDTT] > 40, 0);",
                        'kriteria' => "X"
                    ],
                ],
            ],

            'B106B' => [
                'rumus_penilaian' =>
                "DPR = COUNT('1.1.a.1', null, {X2}{Y1}:{X2});
                    __SHOW__ = [DPR];
                ",
                'rumus_skor' => [
                    '1' => [
                        'rumus_penilaian' => "X = CASES([DPR] >= 5, 1);",
                        'kriteria' => "X"
                    ],
                    '0' => [
                        'rumus_penilaian' => "X = CASES([DPR] < 5, 0);",
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
                        'rumus_penilaian' => "X = CASES([INV] >= 5 & [OP_MHS] >= 10000000, 1);",
                        'kriteria' => "X"
                    ],
                    '0' => [
                        'rumus_penilaian' => "X = CASES([INV] < 5 & [OP_MHS] < 10000000, 0);",
                        'kriteria' => "X"
                    ],
                ],
            ],

            'B106E' => [
                'rumus_penilaian' =>
                "TSKS = SUM('1.1.a.4', null, {X4}{Y1}:{X9});
                TSKS_COUNT = COUNT('1.1.a.4', null, {X2}{Y1}:{X2});
                    TSKS_SEMESTER = [TSKS] / [TSKS_COUNT];
                    EWMP = [TSKS_SEMESTER] / 2;
                    __SHOW__ = [EWMP];
                ",
                'rumus_skor' => [
                    '1' => [
                        'rumus_penilaian' => "X = CASES(([EWMP] >= 12 & [EWMP] <= 16), 1);",
                        'kriteria' => "X"
                    ],
                    '0' => [
                        'rumus_penilaian' => "X = 0;",
                        'kriteria' => "X"
                    ],
                ],
            ],

            'B110C' => [
                'rumus_penilaian' =>
                "N_10sks = COUNT_WHERE('1.1.b.6.1', null, {X4}{Y1}:{X4}, {X4} >= 10);
                    N_total = SUM('XREF.1', null, {X2}{Y1}:{X2}{Y1});
                    P = ([N_10sks] / [N_total]) * 100;
                    P_FORMAT = CONCAT([P], '%');
                    TS-0 = YEAR(-1);
                    UNIT_DATE = USE('unit_kerja', 'tanggal_berdiri');
                    TBERDIRI = DATE_DIFF([UNIT_DATE], [TS-0], 'Y');
                    TBERDIRI_FORMAT = CONCAT([TBERDIRI], ' Tahun');
                    __SHOW__ = [N_10sks _AS_ 'NM_10sks'], [N_total _AS_ 'NM'], [P_FORMAT _AS_ 'Persentase'], [TBERDIRI_FORMAT _AS_ 'Usia Prodi'];",
                'rumus_skor' => [
                    '1' => [
                        'rumus_penilaian' => "X = CASES([P] <= 10, 1, [TBERDIRI] < 2, 1);",
                        'kriteria' => "X"
                    ],
                    '0' => [
                        'rumus_penilaian' => "X = CASES([P] > 10 & [TBERDIRI] >= 2, 0);",
                        'kriteria' => "X"
                    ],
                ]
            ],

            'B112A' => [
                'rumus_penilaian' =>
                "RePL = SUM('1.1.c.7', null, {X5}{Y1}:{X5}{Y1});
                    RePL_FORMAT = CONCAT([RePL], '%');
                    A = SUM('1.1.c.8d', null, {X4}{Y5}:{X4}{Y5});
                    B = SUM('1.1.c.8d', null, {X2}{Y5}:{X2}{Y5});
                    PK1MTK = ([A] / [B]) * 100;
                    PK1MTK_FORMAT = CONCAT([PK1MTK], '%');
                    C = SUM('1.1.c.8d', null, {X5}{Y2}:{X5}{Y2});
                    D = SUM('1.1.c.8d', null, {X2}{Y2}:{X2}{Y2});
                    PK2MTK = ([C] / [D]) * 100;
                    PK2MTK_FORMAT = CONCAT([PK2MTK], '%');
                    TS-0 = YEAR(-1);
                    TS-1 = YEAR(-2);
                    TS-2 = YEAR(-3);
                    NPRESTASI = COUNT_IFS('1.1.d.13', null, {X3}{Y1}:{X3}, [TS-0], [TS-1], [TS-2]);
                    NMHS = SUM('XREF.1', null, {X2}{Y1}:{X2}{Y1});
                    RPMP = ([NPRESTASI] / [NMHS]) * 100;
                    RPMP_FORMAT = CONCAT([RPMP], '%');
                    UNIT_DATE = USE('unit_kerja', 'tanggal_berdiri');
                    TBERDIRI = DATE_DIFF([UNIT_DATE], [TS-0], 'Y');
                    TBERDIRI_FORMAT = CONCAT([TBERDIRI], ' Tahun');
                    __SHOW__ = [A], [B], [C], [D], [NPRESTASI _AS_ 'Jumlah mahasiswa aktif berprestasi'], [NMHS _AS_ 'Jumlah mahasiswa aktif saat TS'], [RePL_FORMAT _AS_ Re-PL], [PK1MTK_FORMAT _AS_ PK1MTK], [PK2MTK_FORMAT _AS_ PK2MTK], [RPMP_FORMAT _AS_ RPMP], [TBERDIRI_FORMAT _AS_ 'Usia Prodi'];",
                'rumus_skor' => [
                    '1' => [
                        'rumus_penilaian' => "X = CASES([RePL] <= 20 & [PK1MTK] >= 0 & [PK2MTK] >= 60 & [RPMP] >= 0.1, 1, [TBERDIRI] < 2, 1);",
                        'kriteria' => "X"
                    ],
                    '0' => [
                        'rumus_penilaian' => "X = 0;",
                        'kriteria' => "X"
                    ],
                ],
            ],

            'B218C' => [
                'rumus_penilaian' =>
                "NA1 = SUM('1.2.2', null, {X3}{Y1}:{X5}{Y1});
                    NA2 = SUM('1.2.2', null, {X3}{Y2}:{X5}{Y2});
                    NA3 = SUM('1.2.2', null, {X3}{Y3}:{X5}{Y3});
                    NA4 = SUM('1.2.2', null, {X3}{Y4}:{X5}{Y4});
                    NB1 = SUM('1.2.2', null, {X3}{Y5}:{X5}{Y5});
                    NB2 = SUM('1.2.2', null, {X3}{Y6}:{X5}{Y6});
                    NB3 = SUM('1.2.2', null, {X3}{Y7}:{X5}{Y7});
                    NC1 = SUM('1.2.2', null, {X3}{Y8}:{X5}{Y8});
                    NC2 = SUM('1.2.2', null, {X3}{Y9}:{X5}{Y9});
                    NC3 = SUM('1.2.2', null, {X3}{Y10}:{X5}{Y10});
                    NDPR = COUNT('1.1.a.1', null, {X2}{Y1}:{X2});
                    RLP = (([NA1] + [NA2] + [NA3] + [NA4] + [NB1] + [NB2] + [NB3] + [NC1] + [NC2] + [NC3]) / [NDPR]) * 100;
                    RLP_FORMAT = CONCAT([RLP], '%');
                    __SHOW__ = [NA1], [NA2], [NA3], [NA4], [NB1], [NB2], [NB3], [NC1], [NC2], [NC3], [NDPR], [RLP_FORMAT _AS_ RLP];
                ",
                'rumus_skor' => [
                    '1' => [
                        'rumus_penilaian' => "X = CASES([RLP] >= 10, 1);",
                        'kriteria' => "X"
                    ],
                    '0' => [
                        'rumus_penilaian' => "X = 0;",
                        'kriteria' => "X"
                    ],
                ],
            ],

            'B106C' => [
                'rumus_penilaian' =>
                "TS-0 = YEAR(-1);
                    GB = COUNT_IF('1.1.a.1', null, {X5}{Y1}:{X5}, 'Guru Besar');
                    LK = COUNT_IF('1.1.a.1', null, {X5}{Y1}:{X5}, 'Lektor Kepala');
                    L = COUNT_IF('1.1.a.1', null, {X5}{Y1}:{X5}, 'Lektor');
                    AA = COUNT_IF('1.1.a.1', null, {X5}{Y1}:{X5}, 'Asisten Ahli');
                    NDPR = COUNT('1.1.a.1', null, {X2}{Y1}:{X2});
                    PDJA = ([GB] + [LK] + [L] + [AA]) / [NDPR] * 100;
                    PDJA_FORMAT = CONCAT([PDJA], '%');
                    UNIT_DATE = USE('unit_kerja', 'tanggal_berdiri');
                    TBERDIRI = DATE_DIFF([UNIT_DATE], [TS-0], 'Y');
                    TBERDIRI_FORMAT = CONCAT([TBERDIRI], ' Tahun');
                    __SHOW__ = [GB], [LK], [L], [AA], [NDPR], [PDJA_FORMAT _AS_ 'PDJA'], [TBERDIRI_FORMAT _AS_ 'Usia Prodi'];
                ",
                'rumus_skor' => [
                    '1' => [
                        'rumus_penilaian' => "X = CASES([PDJA] >= 80, 1, [TBERDIRI] < 2, 1);",
                        'kriteria' => "X"
                    ],
                    '0' => [
                        'rumus_penilaian' => "X = 0;",
                        'kriteria' => "X"
                    ],
                ],
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
                        'rumus_penilaian' => "X = CASES([RRD] >= 10, 1);",
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
                        'rumus_penilaian' => "X = CASES([RHKI] >= 10, 1);",
                        'kriteria' => "X"
                    ],
                    '0' => [
                        'rumus_penilaian' => "X = 0;",
                        'kriteria' => "X"
                    ],
                ],
            ],

            'B115' => [
                'rumus_penilaian' =>
                "TS-0 = YEAR(-1);
                    TS-4 = YEAR(-5);
                    RPPM = SUM('1.1.d.12', null, {X5}{Y1}:{X5}{Y1});
                    N_absorb_1y = COUNT_WHERE('XREF.2', null, {X4}{Y1}:{X6},
                        {X6} <= 12,
                        {X4} >= [TS-4],
                        {X4} <= [TS-0]
                    );
                    N_traced = SUM('1.1.d.14', null, {X2}{Y3}:{X2}{Y3});
                    PLTLK = ([N_absorb_1y] / [N_traced]) * 100;
                    PLTLK_FORMAT = CONCAT([PLTLK], '%');
                    RPPM_FORMAT = CONCAT([RPPM], '%');
                    UNIT_DATE = USE('unit_kerja', 'tanggal_berdiri');
                    TBERDIRI = DATE_DIFF([UNIT_DATE], [TS-0], 'Y');
                    TBERDIRI_FORMAT = CONCAT([TBERDIRI], ' Tahun');
                    __SHOW__ = [N_absorb_1y], [N_traced], [TBERDIRI_FORMAT _AS_ 'Usia Prodi'], [PLTLK_FORMAT _AS_ PLTLK], [RPPM_FORMAT _AS_ RPPM];              ",
                'rumus_skor' => [
                    '1' => [
                        'rumus_penilaian' => "X = CASES([RPPM] <= 20 & [PLTLK] >= 20, 1, [TBERDIRI] < 2, 1);",
                        'kriteria' => "X"
                    ],
                    '0' => [
                        'rumus_penilaian' => "X = 0;",
                        'kriteria' => "X"
                    ],
                ],
            ]
        ];
    }
}
