@props([
    'data' => [],
])

<div class="qn-toc-collapse col-md-3 col-xxl-2 collapse collapse-horizontal show p-0">
    <div class="qn-toc d-none d-md-block ps-3 ps-xl-5 pe-1 py-3 sticky-top z-0" style="width: 336px;top: 50px;">
        <h6 class="mb-3">Table of Content</h6>
        <ul class="list-unstyled ps-3 d-flex flex-column gap-1 overflow-hidden">
            @foreach ($data as $anchor => $title)
            <li>
                <a href="#{{ Str::slug($anchor) }}" class="qn-toc-item position-relative text-reset text-decoration-none d-block rounded-2 p-2">
                    {{ $title }}
                </a>
            </li>
            @endforeach
        </ul>
    </div>
</div>
