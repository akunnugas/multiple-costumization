<div>
    @php
        // TODO: alert informasi
    @endphp
    <div class="col-12 text-white">
    </div>

    @php
        // FIXME: data belum dinamis
    @endphp
    <div class="col-12">
        <div class="cards-summary">
            <div class="grid">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="summary-attr">ID Pendaftar</div>
                    <div class="summary-value">2312381002</div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="summary-attr">Tanggal Daftar</div>
                    <div class="summary-value">21 Agustus 2023, 10:29:32</div>
                </div>
                <div class="col-12 col-sm-6">
                    <div class="summary-attr">Nama Lengkap</div>
                    <div class="summary-value" translate="no">ZAIN 2</div>
                </div>
                <div id="detail-info" class="col-12">
                    <div class="col-12">
                        <hr>
                    </div>
                    <div class="grid">
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="summary-attr">Jalur Pendaftaran</div>
                            <div class="summary-value">Mandiri</div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="summary-attr">Gelombang</div>
                            <div class="summary-value">Gelombang 2</div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="summary-attr">Periode</div>
                            <div class="summary-value">2023.1</div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="summary-attr">Sistem Kuliah</div>
                            <div class="summary-value">Pagi</div>
                        </div>
                        <div class="col-12">
                            <hr>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="summary-attr">Pilihan 1</div>
                            <div class="summary-value">
                                S1 - Ilmu Hukum
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="summary-attr">Pilihan 2</div>
                            <div class="summary-value">
                                S1 - Ilmu Informatika
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <hr style="margin: 0;">
                </div>
                <div class="col-xs-12 action">
                    <button type="button" class="close show-info" style="width: 100%; height: 24px; opacity: 0.5; display: none;">
                        <div style="display:inline-flex; align-items:center;">
                            <p>Selengkapnya</p>
                            <span class="icon icon-chevron-down"></span>
                        </div>
                    </button>
                    <button type="button" class="close hide-info" style="width: 100%; height: 24px; opacity: 0.5;">
                        <div style="display:inline-flex; align-items:center;">
                            <p>Sembunyikan</p>
                            <span class="icon icon-chevron-up"></span>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        // using vanilla javascript
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelector('.show-info').addEventListener('click', function () {
                document.querySelector('#detail-info').style.display = 'block';
                document.querySelector('.show-info').style.display = 'none';
                document.querySelector('.hide-info').style.display = 'block';
            });
            document.querySelector('.hide-info').addEventListener('click', function () {
                document.querySelector('#detail-info').style.display = 'none';
                document.querySelector('.show-info').style.display = 'block';
                document.querySelector('.hide-info').style.display = 'none';
            });
        });
    </script>
    <style>
        @php
            // FIXME: pindahkan ke file scss
        @endphp
        .berkas-list-item input[type="file"] {
            display: none;
        }
        .berkas-list-item .btn {
            padding: 4px 8px;
        }

        .elevation-1 {
            box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
            transition: all 0.3s cubic-bezier(.25,.8,.25,1);
        }

        .p-10 {
            padding: 40px 0;
        }

        .btn-default {
            color: #001B36;
            font-family: "Poppins", sans-serif;
            font-size: 12px;
            border-radius: 6px;
        }

        .color-grey {
            color: #667686 !important;
        }

        .label-diterima {
            background-color: #85C564;
            font-size: 12px;
            line-height: 18px;
            letter-spacing: 0.005em;
            text-transform: capitalize;
            color: #FFFFFF;
            padding: 4px 14px;
            border-radius: 4px;
        }

        .label-diproses {
            background-color: #F39C12;
            font-size: 12px;
            line-height: 18px;
            letter-spacing: 0.005em;
            text-transform: capitalize;
            color: #FFFFFF;
            padding: 4px 14px;
            border-radius: 4px;
        }

        .label-ditolak {
            background-color: #DB2D2F;
            font-size: 12px;
            line-height: 18px;
            letter-spacing: 0.005em;
            text-transform: capitalize;
            color: #FFFFFF;
            padding: 4px 14px;
            border-radius: 4px;
        }

        .table-title-mobile {
            padding: 12px 16px;
            color: #fff;
            font-weight: 500;
            font-size: 13px;
            letter-spacing: 0.005em;
            display: none;
        }

        @media screen and (max-width: 768px) {
            .table-title-mobile {
                display: block !important;
            }
        }

        .table-spmb thead tr {
            background-color: #004680 !important;
        }

        .table-spmb thead tr th {
            padding: 12px 16px;
            color: #fff;
            font-weight: 500;
            font-size: 13px;
            letter-spacing: 0.005em;
        }

        @media screen and (max-width: 768px) {
            .table-spmb thead tr th {
                display: none;
            }
        }

        .table-spmb tbody .body-title-res {
            width: 50%;
            display: inline-block;
        }

        .table-spmb tbody tr:nth-child(odd) > td {
            background-color: rgba(0, 0, 0, 0.03);
        }

        .table-spmb tbody tr td {
            padding: 16px;
            color: #001B36;
            font-weight: 500;
            font-size: 12px;
            line-height: 18px;
            letter-spacing: 0.005em;
        }

        @media screen and (max-width: 768px) {
            .table-spmb tbody tr td .desc-table,
            .table-spmb tbody tr td .body-title-res {
                width: 50%;
                min-width: 50%;
                max-width: 50%;
            }
        }

        @media screen and (max-width: 768px) {
            .table-spmb tbody tr td {
                display: -webkit-box;
                display: -ms-flexbox;
                display: flex;
                text-align: left;
                border-top: 0 !important;
            }
            .table-spmb tbody tr td:last-child {
                border-bottom: 1px solid #E9E9E9;
            }
        }

        .table-spmb tbody tr td img {
            min-width: 100px;
            height: 80px;
            -o-object-fit: contain;
            object-fit: contain;
            margin: 4px 0;
        }
    </style>
</div>
