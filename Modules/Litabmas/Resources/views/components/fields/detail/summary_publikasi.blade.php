@if(!empty($item['original']['artikel']) || !empty($item['original']['buku']))
    @php
        $dataArtikel = $item['original']['artikel'];
        $dataBuku = $item['original']['buku'];

        $headerArtikel = [
            ['field' => 'judul_artikel'],
            ['field' => 'volume_dan_nomor_terbitan', 'label' => 'Volume & Nomor Terbitan'],
            ['field' => 'url_artikel', 'label' => 'URL Artikel', 'component' => 'link_redirect_with_name',
                'link_name' => 'Link Publikasi'],
            ['field' => 'status_publikasi', 'label' => 'Status', 'component' => 'status_publikasi']
        ];

        $headerBuku = [
            ['field' => 'judul_buku'],
            ['field' => 'isbn', 'label' => 'ISBN'],
            ['field' => 'penerbit_buku', 'label' => 'Penerbit'],
            ['field' => 'tahun_terbit_buku', 'label' => 'Tahun Terbit'],
            ['field' => 'status_publikasi', 'label' => 'Status', 'component' => 'status_publikasi']
        ];
    @endphp

    @if(!empty($dataArtikel))
        <div class="summary_publikasi">
            <x-core::table>
                <x-core::table.data :header="$headerArtikel" :data="$dataArtikel" :showNumber="true" />
            </x-core::table>
        </div>
    @endif

    @if(!empty($dataBuku))
        <div class="summary_publikasi buku">
            <x-core::table>
                <x-core::table.data :header="$headerBuku" :data="$dataBuku" :showNumber="true" />
            </x-core::table>
        </div>
    @endif

    @pushonce('head')
        <style>
            .summary_publikasi .box-table__content {
                padding: unset;
                border-top: unset;
            }
            .summary_publikasi .box-table__footer {
                padding: unset;
            }
            .summary_publikasi.buku {
                padding-top: 1rem;
            }
        </style>
    @endpushonce
@else
    -- Belum ada publikasi --
@endif

