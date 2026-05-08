@props([
    'data' => [],
])

@php
    // tambahan kembali ke list
    if (!empty($resourceId) && empty($data)) {
        $backUrl = request()->query('backUrl') ?? Page::backURL();
        array_unshift($data, [
            'items' => [['label' => 'Kembali ke Daftar', 'path' => $backUrl, 'icon' => 'arrow-circle-left-solid']],
        ]);
    }
@endphp

<aside class="qn-sidebar offcanvas offcanvas-start position-fixed overflow-x-hidden overflow-y-auto start-0 bg-white border-end"
    data-bs-scroll="false" tabindex="-1" id="sidebar">
    <div class="offcanvas-body">
        <div class="d-flex flex-column pb-5">
            @foreach ($data as $item)
                <ul class="nav nav-pills flex-column gap-2" style="--bs-nav-link-color: var(--bs-body-color);">
                    @if (!empty($item['label']))
                        <li class="nav-item-title">
                            {{ $item['label'] }}
                        </li>
                    @endif

                    @foreach ($item['items'] as $sub)
                        <li @class(['nav-item'])>
                            <a href="{{ url($sub['path']) }}" @class(['nav-link', 'bg-primary-subtle text-primary fw-medium' => !empty($sub['active']) && empty($sub['isBackButton'])])>
                                <div class="d-flex align-items-center">
                                    @if (!empty($sub['icon']))
                                        <x-core::quantum-3.icon icon="{{ $sub['icon'] ?? null }}" style="padding-right: 7px;" />
                                    @endif
                                    <span class="align-self-end">
                                        {{ $sub['label'] }}
                                    </span>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endforeach
        </div>
    </div>
</aside>
<div class="qn-sidebar-toggle position-fixed d-block d-xl-none mt-3">
    <button type="button" class="btn btn-light btn-sm rounded-3 border rounded-start-0 bg-white"
        data-bs-toggle="offcanvas" data-bs-target="#sidebar"
        aria-label="Toggle sidebar menu"
    >
        <i class="sym sym-chevron-right-double show"></i>
        <i class="sym sym-chevron-left-double"></i>
    </button>
</div>
