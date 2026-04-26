<?php
// Перенос демо-застройщиков из шаблона в инфоблок 68
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
CModule::IncludeModule('iblock');
header('Content-Type: text/plain; charset=utf-8');

$IBLOCK_ID = 68;

$demos = [
    [
        'NAME' => 'Земельный Экспресс Строй',
        'CODE' => 'zemelnyy-ekspress-stroy',
        'PREVIEW_TEXT' => 'Строительство домов из газобетона и клеёного бруса под ключ',
        'IMG' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1200&q=80',
        'PROPS' => [
            'REGION'       => 'Московская область',
            'EXPERIENCE'   => '7',
            'HOUSES_COUNT' => '240',
            'PHONE'        => '+7 (495) 989-10-70',
        ],
        'SORT' => 100,
    ],
    [
        'NAME' => 'Rubkoff Wood',
        'CODE' => 'rubkoff-wood',
        'PREVIEW_TEXT' => 'Строительство и проектирование деревянных домов и бань',
        'IMG' => 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=1200&q=80',
        'PROPS' => [
            'REGION'       => 'Подмосковье',
            'EXPERIENCE'   => '12',
            'HOUSES_COUNT' => '320',
        ],
        'SORT' => 200,
    ],
    [
        'NAME' => 'Green House Stroy',
        'CODE' => 'green-house-stroy',
        'PREVIEW_TEXT' => 'Строительство домов: Барнхаус, Фахверк, в стиле «Хай-Тек»',
        'IMG' => 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?auto=format&fit=crop&w=1200&q=80',
        'PROPS' => [
            'REGION'       => 'Москва и МО',
            'EXPERIENCE'   => '9',
            'HOUSES_COUNT' => '180',
        ],
        'SORT' => 300,
    ],
    [
        'NAME' => 'Brick House',
        'CODE' => 'brick-house',
        'PREVIEW_TEXT' => 'Проектирование и строительство домов из камня и пеноблоков',
        'IMG' => 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=1200&q=80',
        'PROPS' => [
            'REGION'       => 'Тверская область',
            'EXPERIENCE'   => '11',
            'HOUSES_COUNT' => '275',
        ],
        'SORT' => 400,
    ],
    [
        'NAME' => 'Modul Home',
        'CODE' => 'modul-home',
        'PREVIEW_TEXT' => 'Модульные дома с готовыми инженерными системами',
        'IMG' => 'https://images.unsplash.com/photo-1600047509807-ba8f99d2cdde?auto=format&fit=crop&w=1200&q=80',
        'PROPS' => [
            'REGION'       => 'Московская область',
            'EXPERIENCE'   => '6',
            'HOUSES_COUNT' => '120',
        ],
        'SORT' => 500,
    ],
];

function downloadAsBitrixFile($url, $name)
{
    $tmp = tempnam(sys_get_temp_dir(), 'img_');
    $ctx = stream_context_create(['http' => ['timeout' => 20]]);
    $data = @file_get_contents($url, false, $ctx);
    if (!$data) {
        return null;
    }
    file_put_contents($tmp, $data);
    $arFile = CFile::MakeFileArray($tmp);
    if (!$arFile) {
        @unlink($tmp);
        return null;
    }
    $arFile['name'] = $name . '.jpg';
    $arFile['MODULE_ID'] = 'iblock';
    return $arFile;
}

$el = new CIBlockElement;

foreach ($demos as $d) {
    // skip if element with the same code already exists
    $rs = CIBlockElement::GetList([], ['IBLOCK_ID' => $IBLOCK_ID, 'CODE' => $d['CODE']], false, false, ['ID', 'NAME']);
    if ($ex = $rs->Fetch()) {
        echo "SKIP {$d['NAME']} — already exists (ID={$ex['ID']})\n";
        continue;
    }

    $imgFile = downloadAsBitrixFile($d['IMG'], $d['CODE']);

    $fields = [
        'IBLOCK_ID'      => $IBLOCK_ID,
        'NAME'           => $d['NAME'],
        'CODE'           => $d['CODE'],
        'ACTIVE'         => 'Y',
        'SORT'           => $d['SORT'],
        'PREVIEW_TEXT'   => $d['PREVIEW_TEXT'],
        'PREVIEW_TEXT_TYPE' => 'text',
        'PROPERTY_VALUES' => $d['PROPS'],
    ];
    if ($imgFile) {
        $fields['PREVIEW_PICTURE'] = $imgFile;
        $fields['DETAIL_PICTURE']  = $imgFile;
    }

    $newId = $el->Add($fields);
    if ($newId) {
        echo "OK   {$d['NAME']} (ID={$newId}" . ($imgFile ? ", with image" : ", no image") . ")\n";
    } else {
        echo "FAIL {$d['NAME']}: " . $el->LAST_ERROR . "\n";
    }
}

echo "\nDone.\n";
