@php
    use Illuminate\Support\Facades\Storage;

    $submenu ??= [];

    // hak akses
    $permission = request()->permission;

    // default title
    $title = 'Kelola Dokumen Mutu SPMI';

    $authUser = auth()->user();
@endphp

@if (config('app.env') !== 'local' && !$authUser->is_internal)
    @php
        $isLiveChatEnabled = in_array($authUser->kode_role, [
            Modules\Gate\Models\Role::ROLE_ADMINPT,
            Modules\Gate\Models\Role::ROLE_ADMIN_PENJAMINAN_MUTU,
            Modules\Gate\Models\Role::ROLE_LITABMAS_ADMIN_LPPM,
            Modules\Gate\Models\Role::ROLE_ADMIN_KERJASAMA
        ]);
    @endphp

    @if ($isLiveChatEnabled)
        @include('core::components.layouts.livechat')
    @endif
@endif

<x-core::layouts.main :$menu :$title>
    @if ($submenu)
        <x-slot:sidebar>
            <x-core::layouts.outer.sidebar :data="$submenu" />
        </x-slot:sidebar>
    @endif
    @if (!empty(session('success')) || !empty(session('error')))
        <x-core::layouts.html.alert style="margin-bottom:1rem" />
    @else
        <div class="alert alert alert_helper">
            <div class="alert__content">
                <p>
                    Lengkapi 4 dokumen utama SPMI (Kebijakan, Pedoman, Standar, dan Dokumentasi) sebagai dasar pelaksanaan Audit Mutu Internal (AMI). Pastikan dokumen-dokumen ini selalu diperbarui dan berstatus Berlaku agar dapat digunakan dalam penilaian mutu berikutnya.
                </p>
            </div>
            <span class="icon icon-x-mark-mini" data-dismiss="alert"></span>
        </div>
    @endif
    @php
        $templateWritings = [
            1 => 'Kebijakan',
            2 => 'Standar',
            3 => 'Pedoman',
            4 => 'Dokumentasi'
        ];
    @endphp
    @foreach ($data as $key => $item)
        @php($templateUrl = Storage::disk('local')->temporaryUrl($item['alamat_berkas'], now()->addMinutes(60)))
        {{-- Card --}}
        <div class="card card_table">
            <div class="card__body">
                {{-- Table --}}
                <div class="box-table">
                    <div class="box-table__header">
                        <div class="grid" style="align-items: center">
                            <div class="col-8 col-sm-6 col-md-6">
                                <h5 class="main__title" style="font-size: 17px">{{ $item['nama_spmi_jenis_dokumen'] }}
                                </h5>
                                <p class="main__subtitle" style="font-size: 15px; font-weight: 400; color:gray;">
                                    {{ $item['deskripsi_singkat'] }}</p>
                            </div>
                            <div class="col-4 col-sm-6 col-md-6">
                                <div class="main__action" style="float: inline-end;">
                                    @if (!empty($permission['post']))
                                        <x-core::button leading-icon="arrow-down-tray" variant="outline" size="xs"
                                            style="min-width: 170px" data-trigger-modal="preview-template-kebijakan"
                                            data-link="{{ $templateUrl }}"
                                            data-title="{{ $item['nama_spmi_jenis_dokumen'] }}">
                                            <span class="btn__text">Template {{ $templateWritings[$item['id']] }}</span>
                                        </x-core::button>
                                        <x-core::button
                                            href="{{ route('spmi.spmi-dokumen.create', ['id_jenis' => $item['id']]) }}"
                                            leading-icon="plus-solid" variant="primary" size="xs">
                                            <span class="btn__text">Tambah Dokumen</span>
                                        </x-core::button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @php($data = $item['data'])
                    @if (!empty($data->items))
                        @php($sortVal = $sort[$item['id']] ?? ['sort' => 0, 'sortDesc' => 0])
                        <div class="box-table__content" style="border: none; padding-top: 0px">
                            {{-- <x-core::layouts.html.alert style="margin-bottom:1rem" /> --}}
                            <x-core::form id="form_list">
                                <x-spmi::table.quality-document :$header :data="$data->items" :sortable="true"
                                    :sort="$sortVal['sort']" :keySort="$item['id']" :$permission :id="$item['id']"
                                    :sortDesc="$sortVal['sortDesc']" />
                            </x-core::form>
                            <div style="padding-top: 18px">
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach

    @if (!empty($permission['delete']))
        @pushOnce('end')
            <x-core::modal.delete id="modal_delete_checked">
                <x-core::modal.delete-body>
                    yang dipilih
                    <x-slot:button id="btn_delete_checked">
                        Hapus
                    </x-slot:button>
                </x-core::modal.delete-body>
            </x-core::modal.delete>
        @endPushOnce
    @endif

    @if (!empty($permission['delete']))
        @pushOnce('end')
            <x-core::modal.delete id="modal_delete">
                <x-core::form method="DELETE">
                    <x-core::modal.delete-body>
                        <x-slot:button type="submit">
                            Hapus
                        </x-slot:button>
                    </x-core::modal.delete-body>
                </x-core::form>
            </x-core::modal.delete>
        @endPushOnce
    @endif

    @pushOnce('scripts')
        <script>
            @if (!empty($permission['delete']))
                document.getElementById("btn_delete").addEventListener("click", function() {
                    document.getElementById("modal_delete_checked").classList.add("is-visible");
                });
                document.getElementById("btn_delete_checked").addEventListener("click", function() {
                    const form = document.getElementById("form_list");

                    form.action = "{{ Page::indexURL() }}/delete";
                    form.submit();
                });
            @endif
        </script>
    @endPushOnce

    <x-spmi::detail.document-modal prefixTitle="Template - " />
</x-core::layouts.main>
