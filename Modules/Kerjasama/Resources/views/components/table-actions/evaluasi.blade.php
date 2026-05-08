
<div class="text-center d-flex gap-1 justify-content-center">
        <x-core::quantum-3.button leading-icon="eye-solid" variant="outline-secondary" icon="true" size="sm" :href="route('kerjasama.evaluasi-kuesioner.show', ['evaluasi_kuesioner' => $data['id']]) . '?backUrl=' . url()->current()" data-bs-toggle="tooltip" title="Lihat" />

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
        <x-core::quantum-3.button leading-icon="copy-solid" variant="outline-secondary" icon="true" size="sm"
            data-bs-toggle="tooltip" title="Duplikat"
            href="javascript:duplicateRecord({{ $data['id'] }})" />
        <x-core::quantum-3.button leading-icon="trash-solid" variant="outline-secondary" icon="true" size="sm"
            href="javascript:deleteRecord('{{ $encoded }}')" data-bs-toggle="tooltip" title="Hapus" />
    @endif
</div>

@once
@push('scripts')
<form id="duplicateForm" method="POST" style="display: none;">
    @csrf
</form>
<script>
    function duplicateRecord(id) {
        console.log('Duplicate clicked for ID:', id);
        if (confirm('Apakah Anda yakin ingin menduplikat data ini?')) {
            var form = document.getElementById('duplicateForm');
            var url = "{{ route('kerjasama.evaluasi-kuesioner-kerjasama.duplicate', ['id' => '__id__']) }}";
            form.action = url.replace('__id__', id);
            console.log('Submitting to:', form.action);
            form.submit();
        }
    }
</script>
@endpush
@endonce