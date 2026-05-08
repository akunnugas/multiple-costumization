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

<aside class="sidebar sidebar_right">
    <h1 class="sidebar__title">Tahapan Kegiatan</h1>

    <ul class="stepper stepper_vertical">
        @foreach ($timelines as $timeline)
            @php
                $isCompleted = $timeline->completed ?? false;
                $isActive = $timeline->active ?? false;

                $statusStepper = null;
                if ($isActive) {
                    $statusStepper = 'stepper_agenda stepper_active';
                } elseif ($isCompleted) {
                    $statusStepper = 'stepper_complete';
                }

                $formattedDate = '';
                if ($timeline->waktu_mulai) {
                    $formattedDate = Carbon\Carbon::parse($timeline->waktu_mulai)->translatedFormat('j M');
                }

                if ($timeline->waktu_selesai) {
                    if ($timeline->waktu_mulai) {
                        $formattedDate .= ' - ';
                    }

                    $formattedDate .= Carbon\Carbon::parse($timeline->waktu_selesai)->translatedFormat('j M');
                }

                if (
                    Carbon\Carbon::parse($timeline->waktu_mulai)->translatedFormat('Y-m-d') ==
                    Carbon\Carbon::parse($timeline->waktu_selesai)->translatedFormat('Y-m-d')
                ) {
                    $formattedDate = Carbon\Carbon::parse($timeline->waktu_mulai)->translatedFormat('j M Y');
                } else {
                    $formattedDate .= ' ' . Carbon\Carbon::parse($timeline->waktu_selesai)->translatedFormat('Y');
                }
            @endphp
            <li class="stepper__item {{ $statusStepper }}">
                <div class="stepper__item-order">
                    <span class="stepper__item-order-number">{{ $loop->iteration }}.</span>
                </div>
                <div class="stepper__item-title">
                    <h3 class="stepper__item-name">{{ $timeline->nama_agenda }}</h3>
                    <span class="stepper__item-date">{{ $formattedDate }}</span>
                </div>
                @if (!empty($content))
                    <span class="stepper__item-line"></span>

                    <div class="stepper__item-content">
                        <div class="stepper__item-agenda">
                            <div class="stepper__item-icon">
                                <span class="icon icon-calendar-solid"></span>
                            </div>
                            <span class="stepper__item-agenda-title">
                                {{ $contentName }}
                            </span>
                        </div>

                        <div class="stepper__item-agenda">
                            <div class="stepper__item-icon">
                                <span class="icon icon-clock"></span>
                            </div>
                            <div class="stepper__item-agenda-detail">
                                <span class="stepper__item-agenda-detail-title">
                                    {{ $contentDate }}
                                </span>
                                <span class="stepper__item-agenda-detail-subtitle">
                                    {{ $contentTime }}
                                </span>
                            </div>
                        </div>

                        <div class="stepper__item-agenda">
                            <div class="stepper__item-icon">
                                <span class="icon icon-{{ $contentPlaceIcon }}"></span>
                            </div>
                            <div class="stepper__item-agenda-detail">
                                <span class="stepper__item-agenda-detail-title">
                                    {{ $contentPlaceType }}
                                </span>
                                <span class="stepper__item-agenda-detail-subtitle">
                                    @if ($content['tipe_kegiatan'] === \Modules\Litabmas\Models\PengajuanPendanaanJadwalPresentasi::TIPE_KEGIATAN_OFFLINE)
                                        {{ $contentPlace }}
                                    @else
                                        <div class="long-link">
                                            <a href="{{ $contentPlace }}" target="_blank" rel="noopener"
                                                class="agenda-link">
                                                {{ $contentPlace }}
                                            </a>
                                        </div>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                @endif
            </li>
        @endforeach
    </ul>
</aside>
