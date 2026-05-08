@props([
    'kontak' => [],
    'title' => 'Pilih Penanggung Jawab Dari Kontak Mitra',
    'description' => 'Anda dapat memilih kontak mitra sebagai penanggungjawab',
    'emptyWording' => 'Tidak ada kontak tersedia'
])

@php
    $attributes = $attributes->merge([
        'title' => $title,
        'descriptions' => $description
    ]);
@endphp

<x-core::quantum-3.modal {{ $attributes }}>
    <x-core::quantum-3.modal.container.content-modal>
        <x-slot:content>
            @empty($kontak)
                {{ $emptyWording }}
            @endempty
            @foreach ($kontak as $item)
                <div data-kontakid="{{ $item['id'] }}" class="box-switch">
                    <h5>
                        {{ $item['nama_kontak'] }}
                        <br>
                        <span style="font-weight: 400;font-size:0.875rem">{{ $item['jabatan'] }}</span>
                    </h5>
                </div>
            @endforeach
        </x-slot:content>
    </x-core::quantum-3.modal.container.content-modal>
</x-core::quantum-3.modal>