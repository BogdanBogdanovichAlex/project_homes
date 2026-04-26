<?php
/**
 * Доступ к настройкам разделов «Дома и застройщики» (iblock 69 — homes_settings).
 *
 * Использование в шаблонах:
 *   $cfg = zemex_get_homes_settings('catalog');   // 'catalog' | 'builders' | 'detail'
 *   $cfg['HERO_TITLE']     // строка
 *   $cfg['TRUST_LINES']    // массив строк
 *   $cfg['HERO_BG_SRC']    // путь к картинке (если есть HERO_BG)
 *
 * Если инфоблок недоступен или элемент не найден — возвращается пустой массив,
 * шаблон может использовать собственные дефолты.
 */

if (!function_exists('zemex_get_homes_settings')) {
    function zemex_get_homes_settings(string $code): array
    {
        static $cache = [];
        if (isset($cache[$code])) return $cache[$code];

        if (!CModule::IncludeModule('iblock')) {
            return $cache[$code] = [];
        }

        $rs = CIBlockElement::GetList(
            [],
            ['IBLOCK_CODE' => 'homes_settings', 'CODE' => $code, 'ACTIVE' => 'Y'],
            false,
            ['nTopCount' => 1],
            ['ID', 'IBLOCK_ID', 'NAME']
        );
        $element = $rs->GetNextElement();
        if (!$element) {
            return $cache[$code] = [];
        }
        $fields = $element->GetFields();
        $properties = $element->GetProperties();

        $out = ['ID' => $fields['ID'], 'NAME' => $fields['NAME']];
        foreach ($properties as $pCode => $p) {
            $val = $p['VALUE'];
            if (is_array($val)) {
                // multiple-value
                $val = array_values(array_filter(array_map(function ($v) {
                    return is_array($v) ? ($v['TEXT'] ?? '') : trim((string)$v);
                }, $val), 'strlen'));
            } else {
                $val = is_array($val) ? '' : trim((string)$val);
            }
            $out[$pCode] = $val;
            if ($p['PROPERTY_TYPE'] === 'F' && !empty($p['VALUE'])) {
                $f = CFile::GetFileArray($p['VALUE']);
                if ($f && !empty($f['SRC'])) $out[$pCode . '_SRC'] = $f['SRC'];
            }
        }

        return $cache[$code] = $out;
    }
}

if (!function_exists('zemex_split_pipe')) {
    /**
     * Разбивает строку формата "A | B | C" на массив. Пробелы вокруг разделителя обрезаются.
     */
    function zemex_split_pipe(string $line, int $limit = -1): array
    {
        $parts = $limit > 0 ? explode('|', $line, $limit) : explode('|', $line);
        return array_map('trim', $parts);
    }
}

if (!function_exists('zemex_homes_icon_for')) {
    /**
     * Иконка-эмодзи для кода категории «В комплектацию входит».
     */
    function zemex_homes_icon_for(string $code): string
    {
        $map = [
            'walls'       => '🧱',
            'roof'        => '🏠',
            'windows'     => '🪟',
            'insulation'  => '🧊',
            'foundation'  => '🏗',
            'engineering' => '🔌',
        ];
        return $map[strtolower(trim($code))] ?? '✓';
    }
}
