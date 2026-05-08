<x-core::layouts.detail-v2 :$data>
    @push('head')
        @vite('Modules/SPMI/Resources/assets/sass/indicator-table.scss')
    @endpush

    <x-slot:outer>
        @if (!empty($generateTable))
            <x-slot:action>
                <button class="btn btn_outline btn_xs" data-toggle="modal" data-target="#preview">
                    <i class="icon icon-eye-solid"></i> Preview
                </button>
            </x-slot:action>
        @endif
        <x-core::modal title="Preview Table" id="preview" width="width: 80%">
            <x-core::modal.body @style(['overflow: auto !important'])>
                {!! @$generateTable !!}
            </x-core::modal.body>
        </x-core::modal>
    </x-slot:outer>
</x-core::layouts.detail-v2>
