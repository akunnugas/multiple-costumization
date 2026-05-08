<?php

namespace Modules\SPMI\Data\PenilaianMatriks;

class IAPS51S1UNG extends MigrateFormula
{
    protected string $assessmentGuideCode = 'IAPS5.1-S1-Ung';

    protected function getRumus(): array
    {
        return [
            'B106B' => [
                'rumus_penilaian' =>
                "DPRPS = COUNT_IF('1.1.a.1', null, {X8}{Y1}:{X8}, 1);
                N_S3 = COUNT_WHERE('1.1.a.1', null, {X3}{Y1}:{X8}, {X3} == 'S3', {X8} == '1');
                N_S3_PERCENT = ([N_S3] / [DPRPS]) * 100;
                N_S3_PERCENT_FORMAT = CONCAT([N_S3_PERCENT], '%');
                    __SHOW__ = [DPRPS], [N_S3 _AS_ 'DPRD Relevan'], [N_S3_PERCENT_FORMAT _AS_ 'Persentase Doktor'];
                ",
                'rumus_skor' => [
                    '1' => [
                        'rumus_penilaian' => "X = CASES([DPRPS] >= 9 & [N_S3_PERCENT] >= 25, 1);",
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
                "GB = COUNT_IF('1.1.a.1', null, {X5}{Y1}:{X5}, 'Guru Besar');
                    LK = COUNT_IF('1.1.a.1', null, {X5}{Y1}:{X5}, 'Lektor Kepala');
                    L = COUNT_IF('1.1.a.1', null, {X5}{Y1}:{X5}, 'Lektor');
                    NDPR = COUNT('1.1.a.1', null, {X2}{Y1}:{X2});
                    PDJA = ([GB] + [LK] + [L]) / [NDPR] * 100;
                    PDJA_FORMAT = CONCAT([PDJA], '%');
                    __SHOW__ = [GB], [LK], [L], [NDPR], [PDJA_FORMAT _AS_ 'PDJA'];
                ",
                'rumus_skor' => [
                    '1' => [
                        'rumus_penilaian' => "X = CASES([PDJA] >= 80, 1);",
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
                        'rumus_penilaian' => "X = 0;",
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
                    __SHOW__ = [N_10sks _AS_ 'NM_10sks'], [N_total _AS_ 'NM'], [P_FORMAT _AS_ 'Persentase'];",
                'rumus_skor' => [
                    '1' => [
                        'rumus_penilaian' => "X = CASES([P] > 10, 1);",
                        'kriteria' => "X"
                    ],
                    '0' => [
                        'rumus_penilaian' => "X = 0;",
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
                    __SHOW__ = [RePL_FORMAT _AS_ Re-PL], [PK1MTK_FORMAT _AS_ PK1MTK], [PK2MTK_FORMAT _AS_ PK2MTK], [RPMP_FORMAT _AS_ RPMP], [TBERDIRI_FORMAT _AS_ 'Usia Prodi'];",
                'rumus_skor' => [
                    '1' => [
                        'rumus_penilaian' => "X = CASES([RePL] <= 15 & [PK1MTK] >= 45 & [PK2MTK] >= 75 & [RPMP] >= 1, 1, [TBERDIRI] < 2, 1);",
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
                    __SHOW__ = [N_absorb_1y], [N_traced], [PLTLK_FORMAT _AS_ PLTLK], [RPPM_FORMAT _AS_ RPPM];              ",
                'rumus_skor' => [
                    '1' => [
                        'rumus_penilaian' => "X = CASES([RPPM] <= 15 & [PLTLK] >= 40, 1);",
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
                    NB = COUNT('1.2.3', C1, {X2}{Y2}:{X2});
                    NC = COUNT('1.2.3', C1, {X2}{Y3}:{X2});
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
            ]
        ];
    }
}
