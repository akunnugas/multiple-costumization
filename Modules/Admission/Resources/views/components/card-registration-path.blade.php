@props([
    'data' => []
])

<div>
    @foreach($data as $row)
        @php
            $key = $row['period_id'] . '/' . $row['batch_id'] . '/' . $row['registration_path_id'] . '/' . $row['lecture_system_id'] . '/' . $row['registration_period_id'];
            $isOpen = \Modules\Core\Helpers\Date::isDateInRange($row['rp_opened_at'], $row['rp_closed_at']);

            // description
            $description = null;
            if (!empty($row['rp_description'])) {
                $description = strip_tags($row['rp_description'], '<ol><ul><li><br>');
                if (strlen($description) > 280) {
                    $description = substr($description, 0, strpos($description, ' ', 280)) . '...';
                }
            }

            // make from '2023-12-11 00:00:00+07' to '11 Des 2023'
            $row['opened_at'] = \Carbon\Carbon::parse($row['rp_opened_at'])->translatedFormat('d M Y');
            $row['closed_at'] = \Carbon\Carbon::parse($row['rp_closed_at'])->translatedFormat('d M Y');

            // tarif formulir
            $rates = 'Gratis';
            // TODO: pengecekan tarif belum ada karena belum konek ke keuangan
            if (!empty($row['rates']) && $row['rp_is_paid']) { // jika berbayar
                $minRate = number_format($row['rates']['min'], 0, ',', '.');
                $maxRate = number_format($row['rates']['max'], 0, ',', '.');

                $rates = ($row['rates']['min'] == $row['rates']['max'])
                    ? "Rp. $minRate"
                    : "<br class='d-none d-sm-none d-md-block' /> Rp. $minRate - Rp. $maxRate";
            }
        @endphp

        <div class="card card-registration-path">
            @if(!$isOpen)
                <h6 class="closed-status">{{ __('admission::program_detail.close') }}</h6>
            @endif
            <div @class(['card__body', 'closed' => !$isOpen])>
                <div class="left-section">
                    <div class="title-registration-path">
                        <h1 class="title">
                            {{ $row['rp_name'] . ' - ' . $row['registration_path_name'] . ' ' . $row['batch_name'] }}
                        </h1>
                        <p class="description">
                            {{ $description }}
                        </p>
                    </div>
                    <x-core::badge variant="default" type="secondary">{{ $row['lecture_system_name'] }}</x-core::badge>
                </div>
                <form method="post" wire:submit="registerRegistrationPath('{{ $key }}')">
                    @csrf
                    @method('POST')
                    <div class="right-section">
                        <div class="date-price">
                            <div class="items">
                                <span class="icon icon-calendar"></span>
                                <p>{{ $row['opened_at'] }} - {{ $row['closed_at'] }}</p>
                            </div>
                            <div class="items">
                                <span class="icon icon-banknotes"></span>

                                <p>Biaya Daftar <span>{!! $rates !!}</span></p>
                            </div>
                        </div>
                        @if($isOpen)
                            <x-core::button type="submit">
                                {{ __('admission::program_detail.register_now') }}
                            </x-core::button>
                        @else
                            <x-core::button type="button" variant="destructive">
                                {{ __('admission::program_detail.see_detail') }}
                            </x-core::button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    @endforeach


    @php
        // TODO: hapus data dummy dibawah ini
    @endphp
    <div class="card card-registration-path">
        <div class="card__body">
            <div class="left-section">
                <div class="title-registration-path">
                    <h1 class="title">(DUMMY) PENDAFTARAN REGULER - Prestasi Akademik (Pagi)</h1>
                    <p class="description">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc efficitur risus ac tempor maximus. Vivamus vestibulum libero malesuada orci tincidunt, ut ultricies dolor tincidunt. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Nam sit amet faucibus nibh, placerat venenatis lectus.
                    </p>
                </div>
                <x-core::badge variant="default" type="secondary">Pagi</x-core::badge>
            </div>
            <form method="post">
                <div class="right-section">
                    <div class="date-price">
                        <div class="items">
                            <span class="icon icon-calendar"></span>
                            <p>1 Maret 2020 - 5 September 2024</p>
                        </div>
                        <div class="items">
                            <span class="icon icon-banknotes"></span>
                            <p>Biaya Daftar <span>Gratis</span></p>
                        </div>
                    </div>
                    <x-core::button type="button" href="#">
                        {{ __('admission::program_detail.register_now') }}
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-registration-path">
        <h6 class="closed-status">{{ __('admission::program_detail.close') }}</h6>
        <div class="card__body closed">
            <div class="left-section">
                <div class="title-registration-path">
                    <h1 class="title">(DUMMY) PENDAFTAR REGULER - Umum (Pagi)</h1>
                    <p class="description">
                        Jalur Beasiswa Regular : Prestasi Akademik harus melalui seleksi raport yang akan di periksa secara manual saat melakukan pendaftarandi Kampus UKDC
                    </p>
                </div>
                <x-core::badge variant="default" type="secondary">Pagi</x-core::badge>
            </div>
            <form method="post">
                <div class="right-section">
                    <div class="date-price">
                        <div class="items">
                            <span class="icon icon-calendar"></span>
                            <p>1 September 2022 - 28 Februari 2023</p>
                        </div>
                        <div class="items">
                            <span class="icon icon-banknotes"></span>
                            <p>Biaya Daftar <span>Rp. 250.000</span></p>
                        </div>
                    </div>
                    <x-core::button type="button" variant="destructive">
                        Lihat Detail
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>
</div>
