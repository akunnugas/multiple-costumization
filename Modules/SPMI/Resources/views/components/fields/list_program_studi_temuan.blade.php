@php
    $listProdi = $data['list_program_studi_temuan'];
    $listProdi = '["' . str_replace('{"', '', str_replace('"}', '', $listProdi)) . '"]';
    $listProdi = json_decode($listProdi, true);
@endphp

<div style="display: flex; gap:0.25rem; flex-direction:column">
    @foreach($listProdi as $prodi)
        @php
            $arrayProdi = explode('|', $prodi, 2);
            $namaProdi = $arrayProdi[1];
            $idPenilaian = $arrayProdi[0];
        @endphp
        <div style="display: flex">
            <x-core::button style="font-weight:normal" trailing-icon="arrow-right-circle-solid" variant="outline" size="xs" :href="route('spmi.hasil-audit-temuan.show', $idPenilaian)">
                {{$namaProdi}} 
            </x-core::button>
        </div>
    @endforeach
</div>
