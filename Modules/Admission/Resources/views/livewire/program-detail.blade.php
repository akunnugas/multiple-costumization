<div>
    <x-admission::breadcrumb :title="$title" :parentNav="$parentNav" />

    <section id="detail-program">
        <div class="container">
            <div class="grid">
                <div class="col-12">
                    <div class="card card-detail-program">
                        <div class="card__header">
                            <h1 class="title">
                                {{ $program['degree_id'] . ' ' . $program['name'] }}
                            </h1>
                        </div>
                        <div class="card__body">
                            <nav class="nav-tab nav-tab_large" aria-label="Tab navigation">
                                <ul class="nav-tab__wrapper">
                                    <a class="nav-tab__item active" href="#about-program">
                                        {{ __('admission::program_detail.about_program') }}
                                    </a>
                                    <a class="nav-tab__item" href="#registration-paths">
                                        {{ __('admission::program_detail.registration_path') }}
                                    </a>
                                    @if(!empty($program['career_prospects']))
                                        <a class="nav-tab__item" href="#career-prospects">
                                            {{ __('admission::program_detail.career_prospect') }}
                                        </a>
                                    @endif
                                    @if(!empty($program['learning_materials']))
                                        <a class="nav-tab__item" href="#learning-materials">
                                            {{ __('admission::program_detail.learning_material') }}
                                        </a>
                                    @endif
                                </ul>
                            </nav>
                            <div class="tab-content">
                                {{-- About Program Start --}}
                                <div class="tab-pane active show" id="about-program">
                                    <h1 class="title">
                                        {{ __('admission::program_detail.about_program') }}
                                    </h1>
                                    <div class="grid">
                                        <div class="col-12 col-md-4">
                                            <div class="list">
                                                <h2 class="title-list">
                                                    {{ __('admission::program_detail.accreditation') }}
                                                </h2>
                                                <p class="description">{{ $program['accreditation'] }}</p>
                                            </div>
                                        </div>
                                        <div class="util_d-none util_d-md-flex col-md-2"></div>
                                        <div class="col-12 col-md-4">
                                            <div class="list">
                                                <h2 class="title-list">Website</h2>
                                                @if(!empty($program['website']))
                                                    <a class="description link" target="_blank" href="{{ $program['website'] }}">
                                                        {{ __('admission::program_detail.visit_website') }}
                                                    </a>
                                                @else
                                                    <p class="description">-</p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            @if(!empty($program['image']))
                                                <img class="program-image" src="<?= $program['image'] ?>" alt="Gambar Program Studi">
                                                <br>
                                            @endif

                                            @if(!empty($program['description']))
                                                <div class="about-right-section">
                                                    <div id="program-description">{!! $program['description'] !!}</div>
                                                    <div id="show-more" class="cta-more" onclick="goShowDetail()">
                                                        {{ __('admission::program_detail.show_more') }}
                                                        <span class="icon icon-chevron-down"></span>
                                                    </div>
                                                    <div id="show-less" class="cta-more" onclick="goHideDetail()" style="display: none;">
                                                        {{ __('admission::program_detail.show_less') }}
                                                        <span class="icon icon-chevron-up"> </span>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                {{-- About Program End --}}

                                {{-- Registration Path Start --}}
                                <div class="line-bold"></div>
                                <div id="registration-paths">
                                    <h1 class="title">{{ __('admission::program_detail.choose_registration_path') }}</h1>
                                    @php
                                        // FIXME: data loopingan dinamis
                                    @endphp
                                    <x-admission::card-registration-path :data="[]" />
                                </div>
                                {{-- Registration Path End --}}

                                @if(!empty($program['career_prospects']))
                                    <div class="line-bold"></div>
                                    <div id="career-prospects">
                                        <h1 class="title">{{ __('admission::program_detail.career_prospect') }}</h1>
                                        <div>
                                            {!! $program['career_prospects'] !!}
                                        </div>
                                    </div>
                                @endif

                                @if(!empty($program['learning_materials']))
                                    <div class="line-bold"></div>
                                    <div id="learning-materials">
                                        <h1 class="title">{{ __('admission::program_detail.learning_material') }}</h1>
                                        <div>
                                            {!! $program['learning_materials'] !!}
                                        </div>
                                        <br>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        <script>
            let programDescription = document.querySelector('#program-description p');
            let showMore = document.getElementById('show-more');
            let showLess = document.getElementById('show-less');

            function goShowDetail() {
                programDescription.style.webkitLineClamp = 'unset';
                showMore.style.display = 'none';
                showLess.style.display = 'flex';
            }

            function goHideDetail() {
                programDescription.style.webkitLineClamp = '3';
                showMore.style.display = 'flex';
                showLess.style.display = 'none';
            }

            // Custom offset for anchor link, karena quantum offsetnya hanya 75
            document.addEventListener("click", (e) => {
                const offset = 100;
                const anchor = e.target.closest('a[href^="#"]');

                // Scroll
                if (anchor) {
                    e.preventDefault();
                    let scrollTarget = anchor?.getAttribute('href') !== "#"
                        ? document.querySelector(anchor?.getAttribute('href'))
                        : false;

                    if (scrollTarget) {
                        let scrollTargetPosition = scrollTarget.getBoundingClientRect().top;
                        let startPosition = window.pageYOffset || document.documentElement.scrollTop;
                        let scrollDistance = scrollTargetPosition - offset + startPosition;

                        window.scrollTo({
                            top: scrollDistance,
                            behavior: 'smooth'
                        });
                    }
                }
            });
        </script>
    @endpush
</div>
