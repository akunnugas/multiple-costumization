<div>
    @pushonce('head')
        <style>
            input, .choices .choices__item {
                text-transform: uppercase;
            }
        </style>
    @endpushonce

    {{-- Breadcrumb --}}
    <x-admission::breadcrumb :$title :$parentNav />

    <section class="util_margin-top-fix" id="registration-form">
        <div class="container">
            <div class="grid">
                <div class="col-12">
                    @if (!empty($alert))
                        <x-core::layouts.html.alert :data="$alert" style="margin-bottom:1rem" />
                    @endif
                    <div class="registration-form-card">
                        <div class="registration-form-card-header util_p-20">
                            <h1>{{ $title }}</h1>
                            <p>{{ $subtitle }}</p>
                        </div>
                        <div class="steps-desktop">
                            <div class="steps" style="width: {{ $stepPercentage }}%;"></div>
                        </div>
                        <div class="choosed-path util_p-20">
                            <h4 class="util_mb-10">Kamu Memilih Jalur Pendaftaran</h4>
                            <div class="choosed-path-item">
                                <div class="image">
                                    <svg width="32" height="32" viewBox="0 0 32 32" fill="none"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M16.9036 26.6094C17.2699 26.6946 17.3034 27.1744 16.9466 27.2934L14.84 27.9867C9.54663 29.6934 6.75997 28.2667 5.03997 22.9734L3.3333 17.7067C1.62663 12.4134 3.03997 9.61335 8.3333 7.90668L9.37063 7.56315C9.77349 7.42974 10.1612 7.83212 10.0388 8.23846C9.92673 8.61042 9.82162 9.0065 9.71997 9.42668L8.4133 15.0134C6.94663 21.2934 9.0933 24.76 15.3733 26.2534L16.9036 26.6094Z"
                                            fill="#2B45A2" fill-opacity="0.6"></path>
                                        <path
                                            d="M22.8933 4.28005L20.6666 3.76005C16.2133 2.70672 13.56 3.57338 12 6.80005C11.6 7.61338 11.28 8.60005 11.0133 9.73339L9.70662 15.3201C8.39995 20.8934 10.12 23.6401 15.68 24.9601L17.92 25.4934C18.6933 25.6801 19.4133 25.8001 20.08 25.8534C24.24 26.2534 26.4533 24.3067 27.5733 19.4934L28.88 13.9201C30.1866 8.34672 28.48 5.58672 22.8933 4.28005ZM20.3866 17.7734C20.2666 18.2267 19.8666 18.5201 19.4133 18.5201C19.3333 18.5201 19.2533 18.5067 19.16 18.4934L15.28 17.5067C14.7466 17.3734 14.4266 16.8267 14.56 16.2934C14.6933 15.7601 15.24 15.4401 15.7733 15.5734L19.6533 16.5601C20.2 16.6934 20.52 17.2401 20.3866 17.7734ZM24.2933 13.2667C24.1733 13.7201 23.7733 14.0134 23.32 14.0134C23.24 14.0134 23.16 14.0001 23.0666 13.9867L16.6 12.3467C16.0666 12.2134 15.7466 11.6667 15.88 11.1334C16.0133 10.6001 16.56 10.2801 17.0933 10.4134L23.56 12.0534C24.1066 12.1734 24.4266 12.7201 24.2933 13.2667Z"
                                            fill="#2B45A2"></path>
                                    </svg>
                                </div>
                                <div class="content">
                                    <div>
                                        <h4>
                                            {{ $period['rp_name'] . ' - ' . $period['registration_path_name'] . ' ' . $period['batch_name'] }}
                                        </h4>
                                        <p>
                                            {{ $period['lecture_system_name'] }}
                                        </p>
                                    </div>
                                    <a href="{{ route('admission.registration-path') }}">Pindah Jalur</a>
                                </div>
                            </div>
                        </div>
                        <div class="line-bold util_d-block"></div>

                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </section>

    @pushonce('scriptsModule')
        <script>
            // [Start] init variable
            const emptyCityValue = 'Pilih {{ __('admission::registration.province_id') }} terlebih dahulu';
            const emptySchoolValue = 'Pilih {{ __('admission::registration.city_id') }} terlebih dahulu';
            // [End] init variable

            // Hanya interaksi DOM
            function initChoices(el, customOptions) {
                // cek valid el jika tidak ada maka return
                if (!el) {
                    return;
                }

                // handle jika sudah diinit choiches
                if (el.classList.contains('choices__input')) {
                    return;
                }

                // Opsi default
                const defaultOptions = {
                    allowHTML: false,
                    shouldSort: false,
                    searchEnabled: true,
                };

                // Gabungkan opsi default dengan opsi kustom
                const options = { ...defaultOptions, ...customOptions };

                return new Choices(el, options);
            }

            function disabledChoices(choices, emptyValue = null) {
                if (!choices) {
                    return;
                }

                if (emptyValue) {
                    choices.setValue([emptyValue]);
                }
                choices.disable();
            }
        </script>
    @endpushonce

    @pushonce('scripts')
        <script>
            // Hook choices ketika livewire init/updating
            function customHookChoices(el) {
                // handle jika sudah diinit choiches
                if (el.classList.contains('choices__input')) {
                    return;
                }

                // default choices
                if (el.classList.contains("select-default")) {
                    if (el.tagName == "SELECT") {
                        return initChoices(el);
                    }
                }

                // custom choices
                if (el.name == 'province_id' || el.name == 'city_id' || el.name == 'school_id') {
                    return initChoices(el);
                }
            }

            // Hanya interaksi DOM
            const setSubmitButton = () => { // method dari quantum langsung
                document.querySelectorAll("#next-step-registration").forEach((buttonSubmit) => {
                    let form = buttonSubmit.closest("form");
                    if (form) {
                        let status = false;
                        form.querySelectorAll("input:required, select:required, textarea:required").forEach((input) => {
                            if (input.value == "") {
                                status = true;
                            }
                        });
                        if (status && document.querySelector(".form-nav")) {
                            buttonSubmit.parentNode.dataset.tooltip = "Pastikan semua inputan mandatori telah diisi";
                            buttonSubmit.parentNode.dataset.placement = "left";

                            if (buttonSubmit.closest('.form-table_sticky-bottom')) {
                                buttonSubmit.parentNode.dataset.placement = "top";
                            }
                        } else {
                            buttonSubmit.parentNode.removeAttribute("data-tooltip");
                        }
                        buttonSubmit.disabled = status;
                    }
                });
            }

            // [Start] event listener
            document.addEventListener("DOMContentLoaded", () => {
                setSubmitButton();
            });
            document.addEventListener("change", () => {
                setSubmitButton();
            });
            document.addEventListener("input", () => {
                setSubmitButton();
            });
            // [End] event listener

            // Hanya interaksi DOM
            const disableNextStep = (el) => {
                if (el.id === 'is_confirmed') {
                    let btnNextStep = document.querySelector('#btn-next-step');
                    btnNextStep.setAttribute('disabled', 'disabled');
                    el.addEventListener('click', (e) => {
                        if (e.target.checked) {
                            btnNextStep.removeAttribute('disabled');
                        } else {
                            btnNextStep.setAttribute('disabled', 'disabled');
                        }
                    });
                }
            }

            // Livewire
            document.addEventListener('livewire:init', () => {
                Livewire.hook('element.init', ({el}) => {
                    disableNextStep(el, true);
                    customHookChoices(el, true);
                });
                Livewire.hook('morph.updated', ({el}) => {
                    disableNextStep(el);
                    customHookChoices(el);
                });
            });
        </script>
    @endpushonce
</div>
