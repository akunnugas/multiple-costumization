<tr>
    @php
        $sIndex = 0;
        $isEditRow = request()->has('is_edit') && request()->is_edit;
        $isHeader = $isHeader ?? false;
    @endphp
    @if ($tableInput[0] == '_no_')
        <td>
            {{ $index }}
        </td>
    @endif
    @foreach ($tableInput as $dKey => $sValue)
        @php
            $inc = $tableInput[0] == '_no_' ? 2 : 1;
            $v = $val[$sIndex + $inc] ?? '';
            $isFunction = false;
            $isCurrency = false;
            $isLazyLoad = false;
            $lazyApiUrl = null;

            if (is_array($sValue)) {
                $inputType = explode(':', $sValue[0])[0];

                if ($inputType == 'select') {
                    $inputOptions = [];
                    foreach ($sValue[1] as $opt) {
                        $inputOptions[$opt] = $opt;
                    }
                } elseif ($inputType == 'select_lazy') {
                    $isLazyLoad = true;
                    $lazyApiUrl = $sValue[1];
                    $inputType = 'select';
                } else {
                    $inputValue = $sValue[1];
                }
            } else {
                $arrstr = explode('|', $sValue);
                $inputType = $arrstr[0];
                $inputValue = $arrstr[1] ?? 'text';
                $inputRules = explode(',', $arrstr[2] ?? null);
                $additionalRules = [];

                foreach ($inputRules as $rule) {
                    if ($rule == 'currency') {
                        $isCurrency = true;
                    }

                    if (strpos($rule, 'max_length') !== false) {
                        $additionalRules['max_length'] = str_replace('max_length:', '', $rule);
                    }

                    if (strpos($rule, 'max:') !== false) {
                        $additionalRules['max'] = str_replace('max:', '', $rule);
                    }

                    if (strpos($rule, 'min:') !== false) {
                        $additionalRules['min'] = str_replace('min:', '', $rule);
                    }
                }
            }

            if ($inputType == '_no_' || $inputType == '_action_') {
                continue;
            }

            if ($inputType == '_function_') {
                $isFunction = true;
            }

            $rowId = $dataIndex . '-' . ($sIndex + $inc);
        @endphp

        @if (isset($tableDisabled[$sIndex + $inc]) && in_array($dataIndex, $tableDisabled[$sIndex + $inc]))
            <td class="disabled"></td>
        @else
            @if ($isFunction)
                <td class="txt-right">
                    @php
                        if ($isHeader) {
                            echo '';
                        } else {
                            $prefix = '';
                            if (!empty($dataLabels)) {
                                $prefixLabel = $dataLabels[$sIndex + $inc] ?? '';
                                $prefix = $prefixLabel ? $prefixLabel . ' = ' : '';
                            }

                            $suffix = '';
                            if (!empty($dataUnits)) {
                                $suffixUnit = $dataUnits[$sIndex + $inc] ?? '';
                                $suffix = $suffixUnit ? $suffixUnit : '';
                            }

                            $type = $inputValue[2] ?? null;
                            if (!$isCurrency && !empty($type) && $type == 'currency') {
                                $isCurrency = true;
                            }

                            if ($inputValue[0] == 'SUM') {
                                $indexes = $inputValue[1];
                                $sum = 0;
                                foreach ($val as $vKey => $vVal) {
                                    $ddval = $vVal ?? 0;
                                    if (in_array($vKey, $indexes) && is_numeric($ddval)) {
                                        $sum += $ddval;
                                    }
                                }

                                if ($isCurrency) {
                                    echo $prefix . money($sum, 'IDR', true) . $suffix;
                                } else {
                                    // Sum seharusnya tidak perlu dijadikan desimal
                                    echo $prefix . $sum . $suffix;
                                }
                            } elseif ($inputValue[0] == 'COUNT') {
                                $indexes = $inputValue[1];
                                $count = 0;
                                foreach ($val as $vKey => $vVal) {
                                    if (in_array($vKey, $indexes) && !empty($vVal)) {
                                        $count++;
                                    }
                                }
                                echo $prefix . $count . $suffix;
                            } elseif ($inputValue[0] == 'AVERAGE') {
                                $indexes = $inputValue[1];
                                $sum = 0;
                                $count = 0;
                                foreach ($val as $vKey => $vVal) {
                                    $ddval = $vVal ?? null;
                                    if (in_array($vKey, $indexes) && is_numeric($ddval)) {
                                        $sum += $ddval;
                                        $count++;
                                    }
                                }
                                if ($count == 0) {
                                    $avgValue = '0';
                                } else {
                                    $avgValue = $sum / $count;
                                }

                                if ($isCurrency) {
                                    echo $prefix . money($avgValue, 'IDR', true) . $suffix;
                                } else {
                                    echo $prefix . number_format($avgValue, 2, '.') . $suffix;
                                }
                            }
                        }
                    @endphp
                </td>
            @elseif ($isEditRow)
                <td>
                    @if ($inputType == 'disabled' || $isHeader)
                        @if ($isRawHtml)
                            {!! $v !!}
                        @else
                            {{ $v }}
                        @endif
                        @if (!empty($dataUnits))
                            @php
                                $suffixUnit = $dataUnits[$sIndex + $inc] ?? '';
                                echo $suffixUnit ? $suffixUnit : '';
                            @endphp
                        @endif
                    @else
                        <div class="form-control d-flex">
                            <div class="form-control__group {{ !($inputType == 'disabled' || $isHeader) ? 'm-auto' : '' }}"
                                style="width: 100%; @if ($inputType != 'disabled') justify-content: center; @endif @if ($inputType != 'checkbox') min-width: 150px; @endif">
                                @if (!empty($dataLabels))
                                    @php
                                        $prefix = '';
                                        if (!empty($dataLabels)) {
                                            $prefixLabel = $dataLabels[$sIndex + $inc] ?? '';
                                            $prefix = $prefixLabel ? $prefixLabel . ' = ' : '';
                                        }
                                        echo $prefix;
                                    @endphp
                                @endif
                                @if ($inputType == 'select')
                                    @if ($isLazyLoad)
                                        <select name="{{ $isCreate ? 'i' : 'u' }}_{{ $rowId }}"
                                                class="select-search lazy-select"
                                                data-lazy-api="{{ $lazyApiUrl }}"
                                                data-current-value="{{ $v }}">
                                            <option value="">Pilih...</option>
                                        </select>
                                    @else
                                        <x-core::controls.select name="{{ $isCreate ? 'i' : 'u' }}_{{ $rowId }}"
                                            :options="$inputOptions" purpose="form" :value="$v" style="search" />
                                    @endif
                                @elseif ($inputType == 'textarea')
                                    <textarea style="min-width: 6rem !important" class="form-control__input textarea"
                                        name="{{ $isCreate ? 'i' : 'u' }}_{{ $rowId }}">{{ $v }}</textarea>
                                @elseif ($inputType == 'input')
                                    <input class="form-control__input"
                                        @if ($inputValue == 'number' && !$isCurrency) type="number"
                                            onkeyup="validateNumericInput(this)"
                                            onblur="validateNumberInput(this)"
                                        @elseif ($inputValue == 'number' && $isCurrency)
                                            type="number"
                                            onkeyup="validateNumberInput(this)"
                                            onblur="validateNumberInput(this)"
                                        @elseif ($inputValue == 'decimal')
                                            type="number"
                                            step="0.0000000001"
                                        @else
                                            type="text" @endif
                                        name="{{ $isCreate ? 'i' : 'u' }}_{{ $rowId }}"
                                        @if (isset($additionalRules['max_length']) && in_array($inputValue, ['number', 'decimal'])) onInput="if(this.value.length > {{ $additionalRules['max_length'] }}) this.value = this.value.slice(0, {{ $additionalRules['max_length'] }});" @endif
                                        @if (isset($additionalRules['max']) || (isset($additionalRules['min']) && in_array($inputValue, ['number', 'decimal']))) onInput="enforceMinMaxNumber(this, {{ $additionalRules['min'] ?? 'null' }}, {{ $additionalRules['max'] ?? 'null' }})"
                                            onblur="enforceMinMaxNumber(this, {{ $additionalRules['min'] ?? 'null' }}, {{ $additionalRules['max'] ?? 'null' }})"
                                            max="{{ $additionalRules['max'] ?? null }}"
                                            min="{{ $additionalRules['min'] ?? null }}" @endif
                                        value="{{ $v }}" value="{{ $v }}">
                                @elseif ($inputType == 'checkbox')
                                    @php
                                        // Jika input value ada symbol #
                                        $isSingleCheck = strpos($inputValue, '#') !== false;
                                        if ($isSingleCheck) {
                                            $inputValue = str_replace('#', '', $inputValue);
                                        }
                                    @endphp
                                    <input class="form-control__checkbox check-item"
                                        @if ($isSingleCheck) data-check-single="{{ $inputValue . '_' . $dataIndex }}" onclick='handleCheckSingle(this)' @endif
                                        type="checkbox" name="{{ $isCreate ? 'i' : 'u' }}_{{ $rowId }}"
                                        value="1" {{ $v == '1' ? 'checked' : '' }}>
                                @endif
                            </div>
                        </div>
                    @endif
                </td>
            @else
                <td @if ($inputValue == 'number') class="txt-right" @endif
                    @if ($inputType == 'checkbox') style="text-align: center;" @endif>
                    @php
                        if ($inputType == 'checkbox') {
                            echo $v == '1' ? '√' : '';
                        } elseif ($inputType == 'select' && $isLazyLoad && !empty($v)) {
                            // Check if this is a dosen/biodata lazy-load by checking the API URL
                            $isDosenLazyLoad = str_contains($lazyApiUrl ?? '', 'search-dosen') || str_contains($lazyApiUrl ?? '', 'dosen-option');
                            if ($isDosenLazyLoad) {
                                // For dosen lazy-load selects, use pre-fetched names if available, otherwise fetch individually
                                if (isset($biodataNames) && isset($biodataNames[$v])) {
                                    echo $biodataNames[$v];
                                } else {
                                    echo \Modules\Core\Models\Biodata::getPengisianDataDosenNameById($v) ?? $v;
                                }
                            } else {
                                // For other lazy-loads (unit, etc), just display the value
                                echo $v;
                            }
                        } else {
                            $prefix = '';
                            if (!empty($dataLabels)) {
                                $prefixLabel = $dataLabels[$sIndex + $inc] ?? '';
                                $prefix = $prefixLabel ? $prefixLabel . ' = ' : '';
                            }
                            $suffix = '';
                            if (!empty($dataUnits) && $v != null) {
                                $suffixUnit = $dataUnits[$sIndex + $inc] ?? '';
                                $suffix = $suffixUnit ? $suffixUnit : '';
                            }
                            if (is_numeric($v) && $isCurrency) {
                                echo money($v, 'IDR', true) . $suffix;
                            } else {
                                echo $prefix . $v . $suffix;
                            }
                        }
                    @endphp
                </td>
            @endif
        @endif

        @php
            $sIndex++;

            // Reset input type, value, and options
            $inputType = null;
            $inputValue = null;
            $inputOptions = null;
        @endphp
    @endforeach
</tr>
