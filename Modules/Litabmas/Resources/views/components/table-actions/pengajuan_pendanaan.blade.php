@props([
    'data' => [],
    'showDetail' => false,
    'canDelete' => false,
    'header' => [],
    'definer' => null,
    'definerField' => null,
])
@php
    $statusAgendaKegiatan = $data['status'] ?? null;
    $isKetua = auth()->user()?->biodata?->id === ($data['id_ketua'] ?? null);
    $canDelete = $canDelete && $isKetua && $statusAgendaKegiatan === \Modules\Litabmas\Models\PengajuanPendanaanStatus2::LEVEL1_DRAFT;

    $userRole = auth()->user()?->kode_role;
    $isDosen = in_array($userRole, [Modules\Gate\Models\Role::ROLE_DOSEN, Modules\Gate\Models\Role::ROLE_DOSEN_EKSTERNAL]);
@endphp

<div class="dropdown-group" style="display: flex; align-items: center; gap: 4px;">
    @if ($showDetail)
        {{-- @if (!$isDosen)
            <div class="dropdown">
                <button type="button" class="btn btn_outline btn_icon" data-toggle="dropdown">
                    <span class="icon icon-ellipsis-vertical"></span>
                </button>
                <ul class="dropdown__list dropdown__list_menu-end">
                    <li class="dropdown__item">
                        <a href="{{ route('litabmas.pengajuan-pendanaan.show', $data['id']) . '/similarity' }}">Penilaian Similarity & AI</a>
                    </li>
                    <li class="dropdown__item">
                        <a href="{{ route('litabmas.pengajuan-pendanaan.show', $data['id']) . '/reviewer' }}">Reviewer</a>
                    </li>
                    <li class="dropdown__item">
                        <a href="{{ route('litabmas.pengajuan-pendanaan.show', $data['id']) . '/revproposal' }}">Review Proposal</a>
                    </li>
                    <li class="dropdown__item">
                        <a href="{{ route('litabmas.pengajuan-pendanaan.show', $data['id']) . '/jadwalproposal' }}">Jadwal Presentasi</a>
                    </li>
                    <li class="dropdown__item">
                        <a href="{{ route('litabmas.pengajuan-pendanaan.show', $data['id']) . '/nilaipresensi' }}">Nilai Presentasi</a>
                    </li>
                    <li class="dropdown__item">
                        <a href="{{ route('litabmas.pengajuan-pendanaan.show', $data['id']) . '/pembimbing' }}">Pembimbing</a>
                    </li>
                    <li class="dropdown__item">
                        <a href="{{ route('litabmas.pengajuan-pendanaan.show', $data['id']) . '/progreport' }}">Laporan Antara</a>
                    </li>
                    <li class="dropdown__item">
                        <a href="{{ route('litabmas.pengajuan-pendanaan.show', $data['id']) . '/outputpenelitian' }}">Luaran Penelitian</a>
                    </li>
                    <li class="dropdown__item">
                        <a href="{{ route('litabmas.pengajuan-pendanaan.show', $data['id']) . '/publikasi' }}">Publikasi</a>
                    </li>
                </ul>
            </div>
        @endif --}}

        <x-core::button leading-icon="eye-solid" variant="outline" size="xs"
            :href="route('litabmas.pengajuan-pendanaan.show', $data['id']) . '/overview'"
        />
    @endif

    @if ($canDelete)
        @php
            // NOTE: Untuk mengambil value dari field yang dijadikan definer, ketika menggunakan option model
            foreach ($header as $item) {
                if ($definerField !== $item['field']) {
                    continue;
                }

                $options = $item['options'] ?? null;

                $value = $data['text'] ?? $definer;

                // options diambil dari model
                if (!empty($options) && !is_array($options) && is_numeric($value)) {
                    // jika $value bukan int maka tidak perlu diubah dari optionValue()
                    $value = $options::optionValue($value);
                }

                // get value by options
                if (!empty($options) && is_array($options)) {
                    $value = $options[$value];
                }
            }

            $encoded = base64_encode(
                json_encode([
                    'id' => $data['id'],
                    'text' => $data['text'] ?? ($value ?? $definer),
                ]),
            );
        @endphp
        <x-core::button leading-icon="trash-solid" variant="outline" size="xs"
                        href="javascript:deleteRecord('{{ $encoded }}')" data-btn-label="Hapus" />
    @endif
</div>
