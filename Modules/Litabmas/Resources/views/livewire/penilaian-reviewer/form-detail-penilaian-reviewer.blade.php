<div class="container">
    @php
        function checkListSVG() {
            echo '<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M7.00156 13.3996C10.5362 13.3996 13.4016 10.5342 13.4016 6.99961C13.4016 3.46499 10.5362 0.599609 7.00156 0.599609C3.46694 0.599609 0.601562 3.46499 0.601562 6.99961C0.601562 10.5342 3.46694 13.3996 7.00156 13.3996ZM10.0868 5.55251C10.2817 5.28452 10.2225 4.90927 9.95446 4.71437C9.68647 4.51946 9.31122 4.57871 9.11632 4.84671L6.32932 8.67884L4.82583 7.17534C4.59151 6.94103 4.21161 6.94103 3.9773 7.17534C3.74298 7.40966 3.74298 7.78956 3.9773 8.02387L5.9773 10.0239C6.10138 10.148 6.27356 10.2115 6.44849 10.1978C6.62343 10.184 6.78359 10.0944 6.8868 9.95251L10.0868 5.55251Z" fill="#15B79E"/></svg>';
        }

        function crossListSVG() {
            echo '<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M7.00156 13.3996C10.5362 13.3996 13.4016 10.5342 13.4016 6.99961C13.4016 3.46499 10.5362 0.599609 7.00156 0.599609C3.46694 0.599609 0.601562 3.46499 0.601562 6.99961C0.601562 10.5342 3.46694 13.3996 7.00156 13.3996ZM5.62583 4.77534C5.39151 4.54103 5.01161 4.54103 4.7773 4.77534C4.54298 5.00966 4.54298 5.38956 4.7773 5.62387L6.15303 6.99961L4.7773 8.37534C4.54298 8.60966 4.54298 8.98956 4.7773 9.22387C5.01161 9.45819 5.39151 9.45819 5.62583 9.22387L7.00156 7.84814L8.3773 9.22387C8.61161 9.45819 8.99151 9.45819 9.22583 9.22387C9.46014 8.98956 9.46014 8.60966 9.22583 8.37534L7.85009 6.99961L9.22583 5.62387C9.46014 5.38956 9.46014 5.00966 9.22583 4.77534C8.99151 4.54103 8.61161 4.54103 8.3773 4.77534L7.00156 6.15108L5.62583 4.77534Z" fill="#EC3D27"/></svg>';
        }
    @endphp

    <div class="full-page-loader" wire:loading.flex wire:loading.delay.longest
        wire:target="pembimbing_submitForm,similarity_submitForm,reviewer_submitForm,jadwalProposal_submitForm,submitRemoveForm,overview_submitDokumenSK">
        <div class="loader">
            <span class="loader__spinner"></span>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <div class="card__header">
                <ul class="breadcrumb">
                    <li class="breadcrumb__item">
                        <span class="icon icon-home-mini"></span>
                    </li>
                    <li class="breadcrumb__item active">
                        <a href="{{ Page::indexURL() }}">
                            Penilaian PPM
                        </a>
                    </li>
                    <li class="breadcrumb__item"></li>
                    <li class="breadcrumb__item active">
                        {{ $subtitle }}
                    </li>
                    <li class="breadcrumb__item active">
                        {{ $title }}
                    </li>
                </ul>

                <div class="button-group">
                    <div class="button-group__left">
                        <a class="btn btn_outline btn_xs" href="{{ Page::indexURL() }}">
                            Kembali ke List
                        </a>
                    </div>

                    <div class="button-group__mobile">
                        <a href="{{ Page::indexURL() }}"
                            class="btn btn_outline btn_icon btn_xs">
                            <span class="icon icon-arrow-left-solid"></span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="card__body">
                {{-- sidebar left --}}
                <div class="sidebar sidebar_within">
                    <div class="sidebar__header">
                        <h3 class="sidebar__title">
                            Proposal {{ ucfirst($currentData['kode_jenis_pendanaan']) }}
                        </h3>

                        <div class="sidebar__action">
                            <button type="button" class="btn btn_icon">
                                <img src="{{ asset('images/icon/icon-layout.svg') }}" alt="">
                            </button>
                        </div>
                    </div>
                    <ul class="sidebar__list">
                        @foreach ($menu['items'] as $item)
                            <div style="margin-top: 10px;">
                                <h5 style="color: #0F6AF5;">{{ $item['label'] }}</h5>
                                @foreach ($item['items'] as $sub)
                                    @php
                                        $curPath = str_replace($menu['parent'] . '-', '', $sub['path']);
                                        $url =
                                            url('litabmas/' . str_replace('-' . $curPath, '', $sub['path'])) .
                                            '/' .
                                            $idProposalPendanaan .
                                            '/' .
                                            $curPath;
                                    @endphp
                                    <li class="sidebar__item">
                                        <a @class(['sidebar__link', 'active' => $curPath == $subResource]) href="{{ $url }}">
                                            <span class="sidebar__link-text">{{ $sub['label'] }}</span>
                                        </a>
                                    </li>
                                @endforeach
                                <br>
                                <hr>
                            </div>
                        @endforeach
                    </ul>
                </div>

                <div class="content" style="width: 75%; margin-left: 30px; margin-right: 30px;">
                    <div style="display: flex; justify-content:space-between">
                        <h2>{{ $title }}</h2>
                    </div>
                    <br>

                    <div class="card card_details-primary">
                        <div class="grid">
                            <x-litabmas::layouts.detail.line :data="$data['utama']['items']" />
                        </div>
                    </div>
                    <br>

                    @if (isset($alert) && !empty($alert))
                        <div class="alert alert_{{ $alert['type'] }}">
                            <div class="alert__content">
                                <p>{{ $alert['message'] }}</p>
                            </div>
                            <span class="icon icon-x-mark-mini" data-dismiss="alert"></span>
                        </div>
                        <br>
                    @endif

                    @include('litabmas::livewire.penilaian-reviewer.partials.' . $urlInfo['id'])
                </div>

            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script type="text/javascript" src="{{ asset('js/validation.js') }}"></script>
    <script>
        const sidebarWithin = document.querySelector('.sidebar.sidebar_within');
        const mainContent2 = document.querySelector('.content');

        document.querySelector('.sidebar__action button').onclick = function() {
            sidebarWithin.classList.toggle('sidebar_within-collapsed');
            mainContent2.classList.toggle('content_large');

        }
    </script>

    @script
    <script>
        Livewire.on('show-form-modal', (event) => {
            document.getElementById(event[0].id).classList.add("is-visible");

            // remove checked checkbox
            const checkboxes = document.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach((checkbox) => {
                checkbox.checked = false;
            });

            // check checked checkbox
            for (let i = 0; i < event[0].fields.length; i++) {
                if (event[0].fields[i].control == 'checkbox') {
                    for (let j = 0; j < event[0].fields[i].value.length; j++) {
                        let idCheckbox = event[0].fields[i].value[j];
                        let checkbox = document.querySelector(`input[type="checkbox"][value="${idCheckbox}"]`);
                        if (checkbox) {
                            checkbox.checked = true;
                        }
                    }
                }
            }

            // reset file input
            const files = document.querySelectorAll('input[type="file"]');
            files.forEach((file) => {
                file.value = '';
                file.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });

        Livewire.on('hide-form-modal', (event) => {
            document.getElementById(event[0].id).classList.remove("is-visible");
        });
    </script>
    @endscript
@endpush

@pushonce('head')
    @vite('resources/scss/layouts/_detail.scss')
    <style>
        .sidebar.sidebar_right {
            left: unset;
            right: 0;
            /* width: 350px; */
            max-width: unset;
            position: sticky;
            border-right: none;
            border-left: 1px solid #E3E8EF;
            /* padding: 24px 24px 16px 40px; */
        }

        .card_details-custom {
            padding: 24px;
            box-shadow: none;
            border: 1px solid #E3E8EF;
        }
    </style>

    <style>
        @media only screen and (max-width: 79.937rem) {
            .sidebar+.main {
                padding-left: 0 !important;
            }
        }

        @media (max-width: 768px) {
            .sidebar.sidebar_right {
                display: none;
            }

            .sidebar.sidebar_right+.main {
                padding-right: 0;
            }
        }

        /* handle main sidebar icon agar full grow */
        aside.sidebar.sidebar_scroll .sidebar__group:last-child {
            flex-grow: 1;
        }

        .sidebar.sidebar_right {
            left: unset;
            right: 0;
            width: 264px;
            max-width: unset;
            border-right: none;
            padding: 24px 16px;

        }

        .sidebar.sidebar_right .sidebar__title {
            font-size: 1rem;
            font-weight: 600;
            line-height: 28px;
            padding-bottom: 12px;
        }

        .sidebar.sidebar_right+.main {
            padding-right: 264px;
        }

        .stepper {
            display: flex;
            flex-direction: column;
        }

        .stepper .stepper__item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 1rem;
        }

        .stepper .stepper__item.stepper_agenda {
            display: grid;
            /* grid-template-rows: [text-row] auto [line-row] 20px; */
            grid-template-columns: [counter-column] 20px [text-column] auto;
            column-gap: 1rem;
            row-gap: 0.5rem;
        }

        .stepper .stepper__item .stepper__item-order {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            padding: 4px;
            border-radius: 50%;
            border: 1px solid #9AA4B2;
        }

        .stepper .stepper__item .stepper__item-order-number {
            font-size: 12px;
            font-style: normal;
            font-weight: 600;
            line-height: 18px;
            color: #9AA4B2;
        }

        .stepper .stepper__item.stepper_complete .stepper__item-order {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            padding: 4px;
            border-radius: 50%;
            border: 1px solid rgba(255, 255, 255, 0.30);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.00) 0%, rgba(255, 255, 255, 0.00) 100%), #0F6AF5;
            box-shadow: 0px 0px 0px 1px rgba(0, 84, 211, 0.76), 0px 1px 2px 0px rgba(13, 43, 89, 0.40), 0px 0px 0px 3px rgba(48, 130, 255, 0.16);
        }

        .stepper .stepper__item.stepper_complete .stepper__item-order-number {
            color: #fff;
        }

        .stepper .stepper__item.stepper_active .stepper__item-order {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            padding: 4px;
            border-radius: 50%;
            border: 1px solid #0F6AF5;
            background: #fff;
            box-shadow: 0px 0px 0px 3px rgba(48, 130, 255, 0.16);
        }

        .stepper .stepper__item.stepper_active .stepper__item-order-number {
            color: #0F6AF5;
        }

        .stepper .stepper__item .stepper__item-order::after {
            content: '';
            position: absolute;
            display: flex;
            align-items: center;
            width: 2px;
            min-height: 32px;
            height: 100%;
            top: 32px;
            background: #E3E8EF;
            border-radius: 2px;
        }

        .stepper .stepper__item.stepper_agenda .stepper__item-order::after {
            content: '';
            position: absolute;
            display: flex;
            align-items: center;
            width: 2px;
            min-height: 32px;
            height: 100%;
            top: 32px;
            background: #E3E8EF;
            border-radius: 2px;
        }

        .stepper .stepper__item.stepper_agenda .stepper__item-line {
            display: flex;
            justify-content: center;
            width: 2px;
            height: 100%;
            background: #E3E8EF;
            margin-left: 11px;
            margin-top: 50px;
        }

        .stepper .stepper__item.stepper_complete .stepper__item-order::after,
        .stepper .stepper__item.stepper_complete.stepper_agenda .stepper__item-line {
            background: #0F6AF5;
        }

        .stepper .stepper__item:last-child .stepper__item-order::after {
            display: none;
        }

        .stepper .stepper__item .stepper__item-title {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .stepper .stepper__item .stepper__item-title .stepper__item-name {
            font-size: 0.775rem;
            font-weight: 600;
            line-height: 1.25rem;
            color: #202939;
        }

        .stepper .stepper__item .stepper__item-title .stepper__item-date {
            font-size: 0.75rem;
            font-weight: 400;
            line-height: 1.125rem;
            color: #697586;
        }

        .stepper .stepper__item.stepper_agenda .stepper__item-content {
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 8px;
            border-radius: 8px;
            border: 1px solid #E3E8EF;
            width: 196px;
        }

        .stepper .stepper__item.stepper_agenda .stepper__item-content .stepper__item-agenda {
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 4px;
        }

        .stepper .stepper__item.stepper_agenda .stepper__item-content .stepper__item-agenda .stepper__item-agenda-title {
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1.125rem;
            color: #202939;
            word-break: break-word;
        }

        .stepper .stepper__item.stepper_agenda .stepper__item-content .stepper__item-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            padding: 4px;
            border-radius: 50%;
            background: #F1F6FE;
            color: #0F6AF5;
            font-size: 1rem;
        }

        .stepper .stepper__item.stepper_agenda .stepper__item-content .stepper__item-agenda-detail {
            display: flex;
            flex-direction: column;
        }

        .stepper .stepper__item.stepper_agenda .stepper__item-content .stepper__item-agenda-detail .stepper__item-agenda-detail-title {
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1.125rem;
            color: #202939;
        }

        .stepper .stepper__item.stepper_agenda .stepper__item-content .stepper__item-agenda-detail .stepper__item-agenda-detail-subtitle {
            font-size: 0.75rem;
            font-weight: 400;
            line-height: 1.125rem;
            color: #9AA4B2;
        }

        .agenda-link {
            color: var(--qn-primary);
            word-break: break-word;
        }

        .full-page-loader {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(121, 121, 121, 0.8);
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
        }
    </style>
@endpushonce
</div>
