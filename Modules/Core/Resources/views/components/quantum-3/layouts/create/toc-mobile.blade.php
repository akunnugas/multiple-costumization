@props([
    'data' => []
])

<div class="w-100 border-bottom collapse" id="tocMobile">
    <div class="qn-toc p-3 bg-white rounded-top-4" style="width: 652px;">
        <div class="d-flex gap-2 justify-content-between">
            <h6 class="mb-3">Table of Content</h6>
            <button type="button" class="btn-close" aria-label="Close Table of Content" data-bs-toggle="collapse" data-bs-target="#tocMobile" aria-expanded="true">
            </button>
        </div>
        <ul class="list-unstyled m-0 ps-3 d-flex flex-column gap-1 overflow-hidden">
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