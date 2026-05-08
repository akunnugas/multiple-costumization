@props([
    'data' => []
])
@php
    // FIXME: get dinamis news dari backend nya belum
@endphp
<div class="grid">
    <div class="col-12 col-sm-4 col-md-4 col-lg-3">
        <a href="#" class="card card-news">
            <div class="card__body">
                <div class="empty">
                    <img loading="lazy" src="{{ Page::quantumAsset('images/logo-kampus.png') }}" alt="banner">
                </div>
            </div>
            <div class="card__footer">
                <p class="date">10 Oktober 2023</p>
                <h1 class="title-news">asdasda</h1>
                <x-core::badge :variant="'warning'">Pengumuman</x-core::badge>
            </div>
        </a>
    </div>
    <div class="col-12 col-sm-4 col-md-4 col-lg-3">
        <a href="#" class="card card-news">
            <div class="card__body">
                <div class="empty">
                    <img loading="lazy" src="{{ Page::quantumAsset('images/logo-kampus.png') }}" alt="banner">
                </div>
            </div>
            <div class="card__footer">
                <p class="date">23 April 2023</p>
                <h1 class="title-news">Ini contoh Brosur yang tulisannya panjang bisa dua baris nanti disingkat seharusnya</h1>
                <x-core::badge variant="warning" type="outline">Informasi</x-core::badge>
            </div>
        </a>
    </div>
</div>
