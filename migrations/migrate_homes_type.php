<?php
// Одноразовая миграция: создаёт iblock type "homes" и переносит туда iblock 43 (Дома) и 68 (Застройщики)
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

if (!CModule::IncludeModule('iblock')) {
    echo "ERROR: iblock module not loaded\n";
    exit;
}

header('Content-Type: text/plain; charset=utf-8');

// 1. Тип инфоблока homes
$existing = CIBlockType::GetByID('homes')->Fetch();
if (!$existing) {
    $obType = new CIBlockType;
    $arFields = [
        'ID'       => 'homes',
        'SECTIONS' => 'Y',
        'IN_RSS'   => 'N',
        'SORT'     => 400,
        'LANG'     => [
            'ru' => [
                'NAME'         => 'Дома и застройщики',
                'SECTION_NAME' => 'Разделы',
                'ELEMENT_NAME' => 'Элементы',
            ],
            'en' => [
                'NAME'         => 'Houses & Builders',
                'SECTION_NAME' => 'Sections',
                'ELEMENT_NAME' => 'Elements',
            ],
        ],
    ];
    if (!$obType->Add($arFields)) {
        echo "ERROR creating type 'homes': " . $obType->LAST_ERROR . "\n";
        exit;
    }
    echo "Created iblock type 'homes' (sort=400, NAME='Дома и застройщики')\n";
} else {
    echo "Iblock type 'homes' already exists, skipping creation\n";
}

// 2. Перенос инфоблоков
$ib = new CIBlock;
$plan = [
    43 => ['type' => 'homes', 'name' => 'Готовые дома',  'sort' => 100],
    68 => ['type' => 'homes', 'name' => 'Застройщики',   'sort' => 200],
];
foreach ($plan as $id => $upd) {
    $cur = CIBlock::GetByID($id)->Fetch();
    if (!$cur) {
        echo "Iblock $id: not found, skip\n";
        continue;
    }
    $fields = [
        'IBLOCK_TYPE_ID' => $upd['type'],
        'NAME'           => $upd['name'],
        'SORT'           => $upd['sort'],
    ];
    $ok = $ib->Update($id, $fields);
    if ($ok) {
        echo "Iblock $id: moved to '{$upd['type']}', renamed to '{$upd['name']}', sort {$upd['sort']}\n";
    } else {
        echo "Iblock $id: FAILED — " . $ib->LAST_ERROR . "\n";
    }
}

echo "\nDone.\n";
