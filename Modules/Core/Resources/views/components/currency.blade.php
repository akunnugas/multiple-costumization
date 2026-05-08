@php
    // wire:click untuk clear span
    $wireClick = $attributes->get('wire:click');

    $attributes = Page::buildAttributes(attributes: $attributes, isLivewire: $isLivewire ?? null);
@endphp
<input {{ $attributes->except(['wire:click'])->class(['form-control__input', 'form-control__input_currency']) }}>
@php
    $isUseClear = $attributes->get('data-clear') === 'input' ?? true;
    if ($isUseClear) {
        $param = ['data-clear' => 'input'];
    }
    if (!empty($wireClick)) {
        $param['wire:click'] = $wireClick;
    }

    $attributes = Page::buildAttributes($param ?? null, isLivewire: $isLivewire ?? null);
@endphp
<span {{ $attributes }}></span>

@pushOnce('scripts')
    @if($isLivewire ?? false)
        @script
    @endif
    <script>
        (()=> {
            // Format Currency untuk .form-control__input_currency
            let config = {
                decimalNumber: 0,
                separatorThousand: '.',
                separatorDecimal: ',',
                prefix: '',
                subfix: '',
            }

            @if($isLivewire ?? false) // jika livewire handle saat pertama kali init
                Livewire.hook('element.init', ({ el }) => {
                    if (el.classList.contains('form-control__input_currency')) {
                        formatCurrency(el);
                    }
                });
            @else // jika non-livewire, handle saat pertama kali load
                document.addEventListener('DOMContentLoaded', () => {
                    const inputCurrencies = document.querySelectorAll('.form-control__input_currency');
                    inputCurrencies.forEach((inputCurrency) => {
                        formatCurrency(inputCurrency);
                    });
                });
            @endif

            document.addEventListener('keyup', (e) => {
                const target = e.target;
                const inputCurrency = target.closest('.form-control__input_currency');
                if (inputCurrency) {
                    formatCurrency(inputCurrency);
                }
            });
            document.addEventListener('blur', (e) => {
                const target = e.target;
                const inputCurrency = target.closest('.form-control__input_currency');
                if (inputCurrency) {
                    formatCurrency(inputCurrency, 'blur');
                }
            });

            function formatNumber(n, separatorThousand = config?.separatorThousand) {
                return n.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, separatorThousand);
            }

            function formatCurrency(input, blur) {
                let inputVal = input.value;
                if (inputVal === "") {
                    return;
                }

                let originalLength = inputVal.length;
                let CaretPos = input.selectionStart;
                if (inputVal.indexOf(`${config.separatorDecimal}`) >= 0) {
                    let devimalPos = inputVal.indexOf(`${config.separatorDecimal}`);
                    let leftSide = inputVal.substring(0, devimalPos);
                    let rightSide = inputVal.substring(devimalPos);

                    leftSide = formatNumber(leftSide);
                    rightSide = formatNumber(rightSide, "");

                    // NOT WORKING RN
                    if (blur === "blur") {
                        rightSide += "00"; // for USD or something
                    }

                    rightSide = rightSide.substring(0, config.decimalNumber);
                    inputVal = `${config.prefix}${leftSide}${config.decimalNumber < 1 ? "" : config.separatorDecimal + rightSide}${config.subfix}`;
                } else {
                    inputVal = formatNumber(inputVal);
                    inputVal = config.prefix + inputVal;

                    if (blur === "blur") {
                        inputVal += `${config.separatorDecimal}00`;
                    }
                }

                @if($isLivewire ?? false) // jika livewire maka set value record juga pakai alpine
                    const name = input.getAttribute("name");
                    $wire.record[name] = inputVal; // $wire agar tidak selalu live terproses ketika change (berat)
                @endif

                input.value = inputVal;

                let updatedLength = inputVal.length;
                CaretPos = updatedLength - originalLength + CaretPos;
                input.setSelectionRange(CaretPos, CaretPos);
            }

            // Observer
            const observerFormatCurrency = new MutationObserver(mutationsList => {
                mutationsList.forEach(mutation => {
                    mutation.addedNodes.forEach(node => {
                        if (node.nodeType === 1) {
                            if (node.classList.contains('form-control__input_currency')) {
                                formatCurrency(node);
                            }
                            node.querySelectorAll('.form-control__input_currency').forEach(inputCurrency => {
                                formatCurrency(inputCurrency);
                            });
                        }
                    });
                });
            });
            observerFormatCurrency.observe(document.body, { childList: true, subtree: true });
        })();
    </script>

    @if($isLivewire ?? false)
        @endscript
    @endif
@endPushOnce
