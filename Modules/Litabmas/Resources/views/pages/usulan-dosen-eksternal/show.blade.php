@props([
    'data' => [],
    'header' => [],
    'menu' => [],
    'submenu' => [],
    'subtitle' => null,
    'title' => null,
    'action' => null,
])
@php
    use Modules\Litabmas\Models\DosenEksternal;
    use Modules\Gate\Models\Role;

    // default title
    $title ??= $resourceTitle;
    if (empty($subtitle) && !empty($title)) {
        $subtitle = 'Detail ' . $title;
    }

    // custom action
    $data = $data[0];
    $statusUsulan = collect($data['items'])->where('field', 'status_usulan')->first()['original'];

    // cek user role
    $userRole = auth()->user()->kode_role;
    $roleDosen = [Role::ROLE_DOSEN, Role::ROLE_DOSEN_EKSTERNAL];
    $isPeneliti = in_array($userRole, $roleDosen);
    $showSetujuiAjuan = !$isPeneliti && $statusUsulan === DosenEksternal::STATUS_MENUNGGU_PERSETUJUAN;

    if ($showSetujuiAjuan) {
        $idResource = Page::showURLInfo('id');
    }

    $showButtonAction = !empty($data['edit_url']) && $statusUsulan === DosenEksternal::STATUS_MENUNGGU_PERSETUJUAN;
@endphp
<x-core::layouts.main :$menu :$title :$subtitle :$action>
    <x-slot:sidebar>
        <x-core::layouts.outer.sidebar :data="$submenu"/>
    </x-slot:sidebar>
    <x-core::layouts.html.alert/>

    @php
        $attributes = Page::buildAttributes(Arr::only($data, ['title', 'subtitle', 'icon', 'page_conf']) + ['data' => $data['items']]);
    @endphp
    <x-core::layouts.detail.card {{ $attributes }}>
        <x-slot:action>
            <div class="util_d-flex">
                @if ($showSetujuiAjuan)
                    <x-core::button href="#" variant="primary" onclick="showConfirm()"
                                    leading-icon="check-circle" class="util_mr-8">
                        Setujui Ajuan Akun
                    </x-core::button>
                @endif
                @if($statusUsulan !== DosenEksternal::STATUS_BERHASIL_DIBUAT)
                    <x-core::button href="{{ $data['edit_url'] }}" variant="outline"
                                    leading-icon="{{ $data['edit_icon'] ?? 'pencil-square-solid' }}">
                        {{ $data['edit_label'] ?? 'Ubah Data' }}
                    </x-core::button>
                @endif
            </div>
        </x-slot:action>
    </x-core::layouts.detail.card>

    @if($showSetujuiAjuan)
        <x-core::modal title="Konfirmasi" variant="primary" id="modal-confirm-approve-account">
            <x-core::modal.body>
                Apakah anda yakin ingin menyetujui usulan anggota ini?
                User akan dibuatkan akun dan diberi akses role sebagai 'Peneliti'.
                <x-slot:footer>
                    <form action="{{ route('litabmas.usulan-dosen-eksternal.approve', $idResource) }}" method="post">
                        <div class="grid cols-1 cols-sm-2">
                            @csrf
                            @method('PUT')
                            <x-core::button variant="outline" data-dismiss="modal">
                                Batal
                            </x-core::button>
                            <x-core::button type="submit" variant="primary">
                                Ya, Yakin
                            </x-core::button>
                        </div>
                    </form>
                </x-slot:footer>
            </x-core::modal.body>
        </x-core::modal>
    @endif

    @if($showSetujuiAjuan)
        @pushonce('scripts')
            <script>
                // Konfirmasi hapus approve account, show modal
                function showConfirm() {
                    document.getElementById("modal-confirm-approve-account").classList.add("is-visible")
                }

                function approveAccount() {
                    const modal = document.querySelector('#modal-confirm-approve-account');
                    const form = modal.querySelector('form');

                    // submit
                    form.submit();
                }
            </script>
        @endpushonce
    @endif
</x-core::layouts.main>
