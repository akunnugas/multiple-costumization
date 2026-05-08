@php
    $permission = request()->permission;
    $isCanAction = $permission['post'] || $permission['put'];
    $isCreated = true;

    $createOrUpdateURL = Page::detailURL($data['id']);
@endphp

<x-core::button :href="$createOrUpdateURL" size="xs" :variant="($isCreated && !empty($data['total_tinjauan_terisi'])) || !$isCanAction ? 'outline' : 'primary'" @style([
    'color: #fff' => !$isCreated || empty($data['total_tinjauan_terisi']),
    'color: #466bec; font-weight: 500' => ($isCreated && !empty($data['total_tinjauan_terisi'])) || !$isCanAction,
    'border: 1px solid #466bec' => ($isCreated && !empty($data['total_tinjauan_terisi'])) || !$isCanAction,
    'width: 120px',
])>
    @if ($isCanAction)
        {{ $isCreated && !empty($data['total_tinjauan_terisi']) ? 'Perbarui Tinjauan' : 'Isi Tinjauan' }}
    @else
        Lihat Tinjauan
    @endif
</x-core::button>
