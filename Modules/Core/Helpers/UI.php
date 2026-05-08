<?php

namespace Modules\Core\Helpers;

class UI {

    /**
     * create element
     */
    public static function createElement($tag, $arrattr = [])
    {

        $attr = '';
        $html = false;
        if (!empty($arrattr)) {
            foreach ($arrattr as $k => $v) {
                if (!empty($attr))
                    $attr .= ' ';

                if ($k == 'html')
                    $html = $v;
                else if (isset($v)) {
                    if ($k == 'add')
                        $attr .= $v;
                    else if (isset($v))
                        $attr .= $k . (strlen($v) == 0 ? '' : '="' . $v . '"');
                }
            }
        }

        $classParent = $classGroup = null;
        if (!empty($arrattr['type']) && $arrattr['type'] == 'checkbox') {
            $classParent = "d-flex";
            $classGroup = "m-auto";
        } else if ($tag == 'select') {
            $classParent = "w-250";
        }

        $element = '<div class="form-control '.$classParent.'">';
            $element .= '<div class="form-control__group '.$classGroup.'">';
                if ($arrattr['label'])
                    $element .= $arrattr['label'] ;

                if (!empty($arrattr['type']) && $arrattr['type'] == 'checkbox')
                    $element .= '<input type="hidden" name="'.$arrattr['name'].'" value="0">';

                $element .= '<' . $tag . (empty($attr) ? '' : ' ' . $attr) . ($html === false ? ' />' : '>' . $html . '</' . $tag . '>');
            $element .= '</div>';
        $element .= '</div>';


        return $element;
    }

    /**
     * create form input text
     */
    public static function createInputText($column, $value = null,  $isEdit = false, $customAttr = [])
    {
        if ($isEdit) {
            $attr = [];
            $attr['name'] = $column['nama'];
            $attr['type'] = 'text';
            $attr['class'] = 'form-control__input';
            $attr['label'] = $column['label'];
            $attr['value'] = $value ?? '';

            $html = self::createElement('input', $attr);
        } else {
            $label = $column['label'] ?? '';
            $html = $label . $value ?? '-';
        }

        return $html;
    }

    /**
     * create form input number
     */
    public static function createInputNumber($column, $value, $isEdit = false, $class = null, $customAttr = [])
    {
        if ($isEdit) {
            $attr = [];
            $attr['name'] = $column['nama'];
            $attr['type'] = 'number';
            $attr['step'] = '0.01';
            $attr['class'] = 'form-control__input txt-right';
            $attr['label'] = $column['label'];
            $attr['value'] = $value;

            foreach ($customAttr as $key => $val) {
                $attr[$key] = $val;
            }

            $html = self::createElement('input', $attr);
        } else {
            $label = $column['label'] ?? '';

            $html = $label . $value ?? '-';
        }

        return $html;
    }

    /**
     * create form input decimal
     */
    public static function createInputDecimal($column, $value, $isEdit = false, $customAttr = [])
    {
        if ($isEdit) {
            $attr = [];
            $attr['name'] = $column['nama'];
            $attr['type'] = 'number';
            $attr['step'] = '0.01';
            $attr['class'] = 'form-control__input txt-right';
            $attr['label'] = $column['label'];
            $attr['value'] = $value;

            foreach ($customAttr as $key => $val) {
                $attr[$key] = $val;
            }

            $html = self::createElement('input', $attr);
        } else {
            $label = $column['label'] ?? '';

            $html = $label . $value ?? '-';
        }

        return $html;
    }

    /**
     * create form input checkbox
     */
    public static function createInputCheckbox($column, $value, $isEdit = false)
    {
        if ($isEdit) {
            $attr = [];
            $attr['name'] = $column['nama'];
            $attr['type'] = 'checkbox';
            $attr['class'] = 'form-control_checkbox';
            $attr['label'] = $column['label'];
            $attr['value'] = 1;
            if (!empty($value)) {
                $attr['checked'] = 'checked';
            }

            $html = self::createElement('input', $attr);
        } else {
            $html = (!empty($value) ? '√' : '');
        }

        return $html;
    }

    /**
     * create form input date
     */
    public static function createInputDate($column, $isEdit = false){
        if ($isEdit) {
            $attr = [];
            $attr['name'] = $column['nama'];
            $attr['type'] = 'date';
            $attr['class'] = 'form-control__input';
            $attr['label'] = $column['label'];

            $html = self::createElement('input', $attr);
        } else {
            $html = '-';
        }

        return $html;
    }

    /**
     * create form input textarea
     */
    public static function createInputTextarea($column, $value, $isEdit = false)
    {
        if ($isEdit) {
            $attr = [];
            $attr['name'] = $column['nama'];
            $attr['class'] = 'form-control__input textarea';
            $attr['html'] = $value;
            $attr['label'] = $column['label'];

            $html = self::createElement('Textarea', $attr);
        } else {
            $html = $value ?? '-';
        }

        return $html;
    }

    /**
     * create form input select
     */
    public static function createSelect($column, $value, $isEdit = false, $customAttr = [])
    {
        if ($isEdit) {
            // if has option

            $options =  call_user_func('Modules\SPMI\Helpers\ComboList::' . $column['option_dropdown']);

            $attr = [];
            $attr['name'] = $column['nama'];
            $attr['class'] = 'select-search choices__input';
            $attr['label'] = $column['label'];
            $attr['html'] = self::createOption($options, $value);
            $attr['value'] = $value;

            foreach ($customAttr as $key => $val) {
                $attr[$key] = $val;
            }

            $html = self::createElement('select', $attr);
        } else {
            $label = $column['label'] ?? '';

            $html = $label . $value ?? '-';
        }

        return $html;
    }

    /**
     * create form option
     */
    public static function createOption($options, $value)
    {
        $html = '';
        foreach ($options as $key => $val) {
            $selected = '';
            if ($key == $value) {
                $selected = 'selected';
            }
            $html .= '<option value="' . $key . '" ' . $selected . '>' . $val . '</option>';
        }

        return $html;
    }

    /**
     * Create Tree View
     */
    public static function createTreeView($data, $active = null)
    {
        // create tree view with recursive
        $html = '';
        foreach ($data as $item) {
            $attr = [];
            $padding = $item['info_level'] * 10;
            $attr[] = 'style="padding-left: '.$padding.'px"';
            $href = $item['apakah_parent'] ? '#' : ($item['link'] ?? "#");

            $item['nama'] ??= $item['nama_indikator_laporan_kinerja'] ?? $item['nama_indikator_evaluasi_diri'] ?? null;
            if ($item['apakah_parent']) {
                $isParent = false;
                foreach ($item['children'] as $child) {
                    if (!empty($active) && $child['info_left'] <= $active['info_left'] && $child['info_right'] >= $active['info_right']) {
                        $isParent = true;
                        break;
                    }
                }

                $html .= '<li>';
                if (!empty($item['children'])) {
                    $attr[] = 'class="link-sidebar-tree'.($isParent ? " open" : "").'" ';
                    $attr[] = 'data-id="list-' . $item['id'].'"';
                }
                $html .= '<a href="'.$href.'" '.implode('', $attr).'><b>';
                    $html .= '<div class="indicator-vector"></div>';
                    $html .= '<div class="title">' . $item['nama'] . '</div>';
                $html .= '</b></a>';
                if (!empty($item['children'])) {
                    $html .= '<ul id="list-' . $item['id'] . '" class="'.($isParent ? " open" : "").'">';
                        $html .= self::createTreeView($item['children'], $active);
                    $html .= '</ul>';
                }
                $html .= '</li>';
            } else {
                // set parent to open if active
                if (!empty($active) && $item['id'] == $active['id']) {
                    $attr[] = 'class="active"';
                }
                $html .= '<li>';
                    $html .= '<a href="'.$href.'" '.implode('', $attr).'>';
                        $html .= '<div class="indicator-vector"></div>';
                        $html .= '<div class="title">' . $item['nama'] . '</div>';
                    $html .= '</a>';
                $html .= '</li>';
            }
        }

        return $html;
    }

    // /**
    //  * Get Tree Parent
    //  * (untuk mengetahui parent mana saja yang harus di open)
    //  *
    //  * @param array $data
    //  * @param int $active
    //  *
    //  * @return array
    //  */
    // public static function getTreeParent($data, $active)
    // {
    //     $dataParent = [];
    //     foreach ($data as $k => $item) {
    //         $parent = [];
    //         $isFound = false;
    //         if (isset($item['children']) && !empty($item['children'])) {
    //             $parent[$item['id']] = [
    //                 'id' => $item['id'],
    //                 'depth' => $item['depth']
    //             ];

    //             list($dataParent, $isFound) = self::searchChild($item['children'], $active, $parent);
    //             if ($isFound){
    //                 break;
    //             }
    //         } else {
    //             if ($item['id'] == $active) {
    //                 $isFound = true;
    //                 $dataParent[$item['id']] = [
    //                     'id' => $item['id'],
    //                     'depth' => $item['depth']
    //                 ];
    //                 break;
    //             }
    //         }
    //     }

    //     return $dataParent;
    // }

    // /**
    //  * Search Recursive Child
    //  * (untuk mapping parent yang harus di open)
    //  */
    // public static function searchChild($data, $active, $dataParent = []) {
    //     $isFound = false;
    //     foreach ($data as $k => $item) {
    //         if (isset($item['children']) && !empty($item['children'])) {
    //             $dataParent[$item['id']] = [
    //                 'id' => $item['id'],
    //                 'depth' => $item['depth']
    //             ];

    //             list($dataParent, $isFound) = self::searchChild($item['children'], $active, $dataParent);
    //             if ($isFound){
    //                 break;
    //             } else {
    //                 // delete parent if not found
    //                 unset($dataParent[$item['id']]);
    //             }
    //         } else {
    //             if ($item['id'] == $active) {
    //                 $isFound = true;
    //                 $dataParent[$item['id']] = [
    //                     'id' => $item['id'],
    //                     'depth' => $item['depth']
    //                 ];
    //                 break;
    //             }
    //         }
    //     }

    //     return [$dataParent, $isFound];
    // }

}
