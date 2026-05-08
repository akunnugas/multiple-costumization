@php
    // fetch data untuk lazy load
    $biodataIds = [];
    foreach ($data as $val) {
        $sIndex = 1;
        foreach ($tableInput as $sValue) {
            if (is_array($sValue)) {
                $inputType = explode(':', $sValue[0])[0];
                if ($inputType == 'select_lazy') {
                    $apiUrl = $sValue[1] ?? '';
                    // Only collect IDs if this is a dosen/biodata lazy-load
                    if (str_contains($apiUrl, 'search-dosen') || str_contains($apiUrl, 'dosen-option')) {
                        $v = $val[$sIndex] ?? '';
                        if (!empty($v) && is_numeric($v)) {
                            $biodataIds[] = $v;
                        }
                    }
                }
            }
            $sIndex++;
        }
    }
    $biodataNames = !empty($biodataIds) ? \Modules\Core\Models\Biodata::getPengisianDataDosenNamesByIds($biodataIds) : [];
@endphp
@foreach ($data as $dKey => $val)
    <tr>
        @php
            $sIndex = 1;
            $dataIndex = isset($col) ? $col['data_index'] : 0;
            $isEditRow = request()->has('id_record');
            if ($isEditRow) {
                [$x, $y] = explode('-', request()->input('id_record'));

                if ($dataIndex == $x && $dKey + 1 == $y) {
                    $isEditRow = true;
                } else {
                    $isEditRow = false;
                }
            }
        @endphp
        @foreach ($tableInput as $sValue)
            @php
                $v = $val[$sIndex] ?? '';
                $isCurrency = false;
                $isLazyLoad = false;
                $lazyApiUrl = null;

                if (is_array($sValue)) {
                    $inputType = explode(':', $sValue[0])[0];

                    if ($inputType == 'select') {
                        $inputOptions = [];
                        foreach ($sValue[1] as $optKey => $opt) {
                            $inputOptions[$optKey] = $opt;
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

                        if (strpos($rule, 'suffix:') !== false) {
                            $additionalRules['suffix'] = str_replace('suffix:', '', $rule);
                        }
                    }
                }

                $rowId = $dataIndex . '-' . ($dKey + 1) . '-' . $sIndex;
            @endphp
            @if ($inputType == '_no_')
                <td>
                    @if (!$isCreate)
                        {{ $dKey + 1 }}
                    @endif
                </td>
            @elseif ($inputType == '_function_')
                <td class="txt-right">
                    @php
                        if ($inputValue[0] == 'SUM') {
                            $indexes = $inputValue[1];
                            $sum = 0;
                            foreach ($val as $vKey => $vVal) {
                                $ddval = $vVal ?? 0;
                                if (in_array($vKey, $indexes) && is_numeric($ddval)) {
                                    $sum += $ddval;
                                }
                            }

                            if ($inputValue[2] !== null && !empty($sum)) {
                                $divides = $inputValue[2];

                                $sum = $sum / $divides;
                            }

                            if ($isCurrency) {
                                echo money($sum, 'IDR', true);
                            } else {
                                // Sum seharusnya tidak perlu dijadikan desimal
                                echo $sum;
                            }
                        } elseif ($inputValue[0] == 'COUNT') {
                            $indexes = $inputValue[1];
                            $count = 0;
                            foreach ($val as $vKey => $vVal) {
                                if (in_array($vKey, $indexes) && !empty($vVal)) {
                                    $count++;
                                }
                            }
                            echo $count;
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
                                $avgValue = 0;
                            } else {
                                $avgValue = $sum / $count;

                                if ($inputValue[2] !== null) {
                                    $divides = $inputValue[2];

                                    $avgValue = $avgValue / $divides;
                                }
                            }

                            if ($isCurrency) {
                                echo money($avgValue, 'IDR', true);
                            } else {
                                echo number_format($avgValue, 2, '.');
                            }
                        }
                    @endphp
                </td>
            @elseif ($inputType == '_action_')
                @if ($isAction)
                    @if ($isCreate)
                        <td class="cell-action">
                            <div class="dropdown-group">
                                <button type="submit" class="btn btn_outline btn_xs" data-type="saveip" data-act="i"
                                    data-id="{{ $dataIndex }}">
                                    <span class="icon icon-check-solid"></span>
                                </button>
                            </div>
                        </td>
                    @else
                        <td class="cell-action">
                            <div class="dropdown-group">
                                @if (!$isEditRow)
                                    <button type="button" class="btn btn_outline btn_xs" data-type="editip"
                                        data-id="{{ $dataIndex . '-' . ($dKey + 1) }}">
                                        <span class="icon icon-pencil-solid"></span>
                                    </button>
                                    <button type="button" class="btn btn_outline btn_xs" data-type="deleteip"
                                        data-id="{{ $dataIndex . '-' . ($dKey + 1) }}">
                                        <span class="icon icon-trash-solid"></span>
                                    </button>
                                @else
                                    <button type="submit" class="btn btn_outline btn_xs" data-type="saveip"
                                        data-act="u" data-id="{{ $dataIndex . '-' . ($dKey + 1) }}">
                                        <span class="icon icon-check-solid"></span>
                                    </button>
                                    <button type="button" class="btn btn_outline btn_xs" data-type="cancelip">
                                        <span class="icon icon-x-mark-solid"></span>
                                    </button>
                                @endif
                            </div>
                        </td>
                    @endif
                @endif
            @elseif (($isEditRow && ($dataIndex == $x && $dKey + 1 == $y)) || $isCreate)
                <td>
                    <div class="form-control d-flex">
                        <div class="form-control__group m-auto" style="width: 100%; justify-content: center; @if ($inputType != 'checkbox') min-width: 150px; @endif">
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
                                    onblur="validateNumericInput(this)"
                                @elseif ($inputValue == 'number' && $isCurrency)
                                    type="number"
                                    onkeyup="validateNumberInput(this)"
                                    onblur="validateNumberInput(this)"
                                @elseif ($inputValue == 'decimal')
                                    type="text"
                                    step="0.0000000001"
                                    inputmode="decimal"
                                    oninput="validateDecimalInput(this)"
                                    onblur="validateDecimalInput(this)"
                                @elseif ($inputValue == 'negative_decimal')
                                    type="text"
                                    step="0.0000000001"
                                    inputmode="decimal"
                                    oninput="validateNegativeDecimalInput(this)"
                                    onblur="validateNegativeDecimalInput(this)"
                                @else
                                    type="text" @endif
                                    name="{{ $isCreate ? 'i' : 'u' }}_{{ $rowId }}"
                                    @if (!empty($additionalRules['max_length']) && in_array($inputValue, ['number', 'decimal'])) onInput="if(this.value.length > {{ $additionalRules['max_length'] }}) this.value = this.value.slice(0, {{ $additionalRules['max_length'] }});" @endif
                                    @if (isset($additionalRules['max']) || (isset($additionalRules['min']) && in_array($inputValue, ['number', 'decimal']))) onInput="enforceMinMaxNumber(this, {{ $additionalRules['min'] ?? 'null' }}, {{ $additionalRules['max'] ?? 'null' }})"
                                        onblur="enforceMinMaxNumber(this, {{ $additionalRules['min'] ?? 'null' }}, {{ $additionalRules['max'] ?? 'null' }})"
                                        max="{{ $additionalRules['max'] ?? null }}"
                                        min="{{ $additionalRules['min'] ?? null }}" @endif
                                    value="{{ $v }}">
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
                </td>
            @else
                <td @if (isset($inputValue) && $inputValue == 'number') class="txt-right" @endif
                    @if ($inputType == 'checkbox') style="text-align: center;" @endif>
                    @php
                        if ($inputType == 'checkbox') {
                            echo $v == '1' ? '√' : '';
                        } elseif ($inputType == 'select') {
                            // Show data
                            if ($isLazyLoad && !empty($v)) {
                                // Check if this is a dosen/biodata lazy-load by checking the API URL
                                $isDosenLazyLoad = str_contains($lazyApiUrl ?? '', 'search-dosen') || str_contains($lazyApiUrl ?? '', 'dosen-option');
                                if ($isDosenLazyLoad) {
                                    // For dosen lazy-load selects, use pre-fetched names to avoid N+1
                                    echo $biodataNames[$v] ?? $v;
                                } else {
                                    // For other lazy-loads (unit, etc), just display the value
                                    echo $v;
                                }
                            } elseif (isset($inputOptions)) {
                                // untuk data baru
                                if (isset($inputOptions[$v])) {
                                    echo $inputOptions[$v];
                                }

                                // NOTES: HANDLING KHUSUS UNTUK DATA LAMA, KARENA BERISKO MENGGUNAKAN MIGRASI (7 Januari 2026)
                                else {
                                    $foundKey = array_search($v, $inputOptions);
                                    if ($foundKey !== false) {
                                        echo $inputOptions[$foundKey];
                                    } else {
                                        echo $v;
                                    }
                                }
                            } else {
                                echo $v;
                            }
                        } else {
                            if (is_numeric($v) && $isCurrency) {
                                echo money($v, 'IDR');
                            } else {
                                echo $v;
                            }

                            if(!empty($v) && isset($additionalRules['suffix'])) {
                                echo $additionalRules['suffix'];
                            }
                        }
                    @endphp
                </td>
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
@endforeach
