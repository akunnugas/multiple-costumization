@php
    $mapValues = [];
    $mapFunction = [];
    foreach ($aData as $dKey => $val) {
        $sIndex = 1;
        foreach ($tableInput as $sValue) {
            $v = $val[$sIndex] ?? '';
            $isFunction = false;
            if (is_array($sValue)) {
                $inputType = explode(':', $sValue[0])[0];

                if ($inputType == 'select') {
                    $inputOptions = [];
                    foreach ($sValue[1] as $opt) {
                        $inputOptions[$opt] = $opt;
                    }
                } else {
                    $inputValue = $sValue[1];
                }
            } else {
                $arrstr = explode('|', $sValue);
                $inputType = $arrstr[0];
                $inputValue = $arrstr[1] ?? 'text';
            }

            if ($inputType == '_function_') {
                $isFunction = true;
                $mapFunction[$sIndex] = true;
            }

            if ($isFunction) {
                if ($inputValue[0] == 'SUM') {
                    $indexes = $inputValue[1];
                    $sum = 0;
                    foreach ($val as $vKey => $vVal) {
                        $ddval = (float) ($vVal ?? 0);
                        if (in_array($vKey, $indexes) && is_numeric($ddval)) {
                            $sum += $ddval;
                        }
                    }
                    $isValidRow = true;
                    if (isset($col['condition'])) {
                        foreach ($col['condition'] as $columnIndex => $columnValue) {
                            if ($val[$columnIndex] != $columnValue) {
                                $isValidRow = false;
                                break;
                            }
                        }
                    }
                    if ($isValidRow) {
                        $mapValues[$dKey + 1][$sIndex] = $sum;

                        if($inputValue[2] !== null){
                            $divides = $inputValue[2];

                            $mapValues[$dKey + 1][$sIndex] = number_format($mapValues[$dKey + 1][$sIndex] / $divides, 2);
                        }
                    }
                } elseif ($inputValue[0] == 'COUNT') {
                    $indexes = $inputValue[1];
                    $count = 0;
                    foreach ($val as $vKey => $vVal) {
                        if (in_array($vKey, $indexes) && !empty($vVal)) {
                            $count++;
                        }
                    }
                    $isValidRow = true;
                    if (isset($col['condition'])) {
                        foreach ($col['condition'] as $columnIndex => $columnValue) {
                            if ($val[$columnIndex] != $columnValue) {
                                $isValidRow = false;
                                break;
                            }
                        }
                    }
                    if ($isValidRow) {
                        $mapValues[$dKey + 1][$sIndex] = $count;
                    }
                } elseif ($inputValue[0] == 'AVERAGE') {
                    $indexes = $inputValue[1];
                    $sum = 0;
                    $count = 0;
                    foreach ($val as $vKey => $vVal) {
                        $ddval = (float) ($vVal ?? 0);
                        if (in_array($vKey, $indexes) && is_numeric($ddval)) {
                            $sum += $ddval;
                            $count++;
                        }
                    }
                    $isValidRow = true;
                    if (isset($col['condition'])) {
                        foreach ($col['condition'] as $columnIndex => $columnValue) {
                            if ($val[$columnIndex] != $columnValue) {
                                $isValidRow = false;
                                break;
                            }
                        }
                    }
                    if ($isValidRow) {
                        if ($count == 0) {
                            $mapValues[$dKey + 1][$sIndex] = 0;
                        } else {
                            $mapValues[$dKey + 1][$sIndex] = number_format($sum / $count, 2);

                            if($inputValue[2] !== null){
                                $divides = $inputValue[2];

                                $mapValues[$dKey + 1][$sIndex] = number_format($mapValues[$dKey + 1][$sIndex] / $divides, 2);
                            }
                        }
                    }
                }
            }

            $sIndex++;

            // Reset input type, value, and options
            $inputType = null;
            $inputValue = null;
            $inputOptions = null;
        }
    }

    $colspan = $col['colspan'] ?? 1;
@endphp

<tr>
    <td colspan="{{ $colspan }}">
        <b>{{ $col['label'] }}</b>
    </td>
    @foreach ($tableInput as $key => $value)
        @php
            if ($key < $colspan) {
                continue;
            }

            $index = $key + 1;
        @endphp
        @if (in_array($index, $col['indexes']))
            <td class="txt-right"><b>
                    @php
                        $prefixLabel = $col['indexes_label'][$index] ?? '';
                        $prefix = $prefixLabel ? $prefixLabel . ' = ' : '';
                        $isCurrency = ($col['type'] ?? null) === 'currency';

                        if (!empty($aData)) {
                            if (isset($col['indexes_function'][$index])) {
                                $function = $col['indexes_function'][$index]['function'] ?? 'SUM';
                                if($function == 'AVERAGE_Y'){
                                    $total = [];
                                    $count = 0;
                                    $prevRow = null;
                                    foreach ($col['indexes_function'][$index]['cell'] as $cor) {
                                        if(!isset($total[$cor[1]])){
                                            $total[$cor[1]] = (float)($aData[$cor[0] - 1][$cor[1]] ?? 0);
                                        }else{
                                            $total[$cor[1]] = $total[$cor[1]] + (float)($aData[$cor[0] - 1][$cor[1]] ?? 0);
                                        }
                                    }
                                    $count = count($total);
                                    $total = array_sum($total);

                                    if ($total > 0 && $count > 0) {
                                        echo $prefix . number_format($total / $count, 2);
                                    } else {
                                        echo $prefix . '0';
                                    }
                                    continue;
                                } else {
                                $sum = 0;
                                foreach ($col['indexes_function'][$index]['cell'] as $cor) {
                                    $isStatic = $col['indexes_function'][$index]['is_static'] ?? true;

                                    //  Jika mendefinisikan cell static
                                    if ($isStatic) {
                                        $sum += $aData[$cor[0] - 1][$cor[1]] ?? 0;
                                    } else {
                                        // $sum += $mapValues[$cor[0]][$cor[1]] ?? 0;
                                        foreach ($cor as $rowValue) {
                                            foreach ($aData as $value) {
                                                $sum += (float) ($value[$rowValue] ?? 0);
                                            }
                                        }
                                    }
                                }

                                if ($isCurrency) {
                                    echo $prefix . money($sum, 'IDR', true);
                                } else {
                                    // Sum seharusnya tidak perlu dijadikan desimal
                                    echo $prefix . $sum;
                                }
                                }
                            } else {
                                $function = is_array($col['function']) ? $col['function'][$index] : $col['function'];
                                if (isset($mapFunction[$index])) {
                                    $total = 0;
                                    foreach ($mapValues as $mapValue) {
                                        foreach ($mapValue as $zIndex => $zValue) {
                                            if ($zIndex == $index) {
                                                $total += (float) $zValue ?? 0;
                                            }
                                        }
                                    }
                                    if ($function == 'COUNT') {
                                        echo $prefix . count($mapValues);
                                    } elseif ($function == 'SUM') {
                                        if ($isCurrency) {
                                            echo $prefix . money($sum, 'IDR', true);
                                        } else {
                                            // Sum seharusnya tidak perlu dijadikan desimal
                                            echo $prefix . $total;
                                        }
                                    } elseif ($function == 'AVERAGE') {
                                        if ($total > 0) {
                                            echo $prefix . number_format($total / count($mapValues), 2);
                                        } else {
                                            echo $prefix . '0';
                                        }
                                    }
                                } else {
                                    if ($function == 'COUNT') {
                                        $count = 0;
                                        foreach ($aData as $val) {
                                            $refIndex = $index;
                                            if(isset($col['references']) && is_array($col['references'])) {
                                                $refIndex = $col['references'][$index] ?? $refIndex;
                                            }
                                            if (!empty($val[$refIndex])) {
                                                $count++;
                                            }
                                        }
                                        echo $prefix . $count;
                                    } elseif ($function == 'SUM') {
                                        $sum = 0;
                                        foreach ($aData as $val) {
                                            if (is_numeric($val[$index] ?? 0)) {
                                                $sum += (float) ($val[$index] ?? 0);
                                            }
                                        }

                                        if ($isCurrency) {
                                            echo $prefix . money($sum, 'IDR', true);
                                        } else {
                                            // Sum seharusnya tidak perlu dijadikan desimal
                                            echo $prefix . $sum;
                                        }
                                    } elseif ($function == 'AVERAGE') {
                                        $sum = 0;
                                        foreach ($aData as $val) {
                                            if (is_numeric($val[$index] ?? 0)) {
                                                $sum += (float) ($val[$index] ?? 0);
                                            }
                                        }
                                        if ($sum > 0) {
                                            echo $prefix . number_format($sum / count($aData), 2);
                                        } else {
                                            echo $prefix . '0';
                                        }
                                    } elseif ($function == 'CUSTOM_VALUE') {
                                        echo $prefix . $col['values'][$index];
                                    }
                                }
                            }
                        } else {
                            echo $prefix . '0';
                        }
                    @endphp
                </b>
            </td>
        @else
            @if ($value == '_action_')
                @if ($isAction)
                    <td class="cell-action"></td>
                @endif
            @else
                <td></td>
            @endif
        @endif
    @endforeach
</tr>
