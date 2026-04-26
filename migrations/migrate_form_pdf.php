<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
CModule::IncludeModule('iblock');
CModule::IncludeModule('form');
header('Content-Type: text/plain; charset=utf-8');

/* ─── 1. Добавляем свойство PDF_PLAN в iblock 43 ─── */
$IBLOCK_ID = 43;
$rs = CIBlockProperty::GetList([], ['IBLOCK_ID' => $IBLOCK_ID, 'CODE' => 'PDF_PLAN']);
if ($rs->Fetch()) {
    echo "Property PDF_PLAN: exists\n";
} else {
    $obProp = new CIBlockProperty;
    $newId = $obProp->Add([
        'IBLOCK_ID'     => $IBLOCK_ID,
        'CODE'          => 'PDF_PLAN',
        'NAME'          => 'PDF планировки (файл для скачивания)',
        'PROPERTY_TYPE' => 'F',
        'MULTIPLE'      => 'N',
        'IS_REQUIRED'   => 'N',
        'SORT'          => 350,
        'FILE_TYPE'     => 'pdf,jpg,jpeg,png',
    ]);
    echo "Property PDF_PLAN: " . ($newId ? "OK id=$newId" : "FAIL " . $obProp->LAST_ERROR) . "\n";
}

/* ─── 2. Расширяем форму 7 (FORM_CONSULT) — поля SCOPE, EMAIL, MESSAGE, PROJECT ─── */
$FORM_ID = 7;
$wantFields = [
    'SCOPE'   => ['title' => 'Контекст заявки (о чём)',         'type' => 'text', 'sort' => 100],
    'EMAIL'   => ['title' => 'Email',                            'type' => 'email','sort' => 200],
    'PROJECT' => ['title' => 'Проект / Объект',                   'type' => 'text', 'sort' => 300],
    'MESSAGE' => ['title' => 'Сообщение',                         'type' => 'textarea','sort' => 400],
];
$existing = [];
$rsF = CFormField::GetList($FORM_ID, 'ALL', $by='s_sort', $order='asc', [], $isFiltered);
while ($f = $rsF->Fetch()) $existing[$f['SID']] = $f['ID'];
echo "\nExisting form-7 fields: " . implode(', ', array_keys($existing)) . "\n";

foreach ($wantFields as $sid => $cfg) {
    if (isset($existing[$sid])) {
        echo "Field $sid: exists (ID={$existing[$sid]})\n";
        continue;
    }
    $arFields = [
        'FORM_ID'      => $FORM_ID,
        'SID'          => $sid,
        'TITLE'        => $cfg['title'],
        'TITLE_TYPE'   => 'text',
        'C_SORT'       => $cfg['sort'],
        'ACTIVE'       => 'Y',
        'REQUIRED'     => 'N',
        'IN_FILTER'    => 'N',
        'IN_RESULTS_TABLE' => 'Y',
        'FIELD_TYPE'   => '',
        'FILTER_TITLE' => $cfg['title'],
        'RESULTS_TABLE_TITLE' => $cfg['title'],
        'arANSWER'     => [
            [
                'MESSAGE'     => $cfg['title'],
                'C_SORT'      => 100,
                'ACTIVE'      => 'Y',
                'FIELD_TYPE'  => $cfg['type'],
                'FIELD_WIDTH' => 30,
                'FIELD_HEIGHT'=> $cfg['type'] === 'textarea' ? 4 : 1,
                'FIELD_PARAM' => $cfg['type'] === 'textarea' ? '' : 'placeholder=""',
                'VALUE'       => '',
            ],
        ],
    ];
    $newId = CFormField::Set($arFields);
    echo "Field $sid: " . ($newId ? "OK id=$newId" : "FAIL — " . (function_exists('strLastError') ? strLastError() : 'unknown')) . "\n";
}

echo "\nDone.\n";
