<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
CModule::IncludeModule('form');
header('Content-Type: text/plain; charset=utf-8');

$FORM_ID = 7;

// Желаемая конфигурация по SID
$want = [
    'SCOPE'   => ['type' => 'hidden',   'param' => ''],
    'PROJECT' => ['type' => 'hidden',   'param' => ''],
    'MESSAGE' => ['type' => 'textarea', 'param' => 'placeholder="Ваш вопрос или комментарий" rows="4"', 'height' => 4],
    'EMAIL'   => ['type' => 'email',    'param' => 'placeholder="your@email.com"'],
];

// Найдём ID полей и их ответов
$rsF = CFormField::GetList($FORM_ID, 'ALL', $by='s_sort', $order='asc', [], $isFiltered);
$fieldsBySid = [];
while ($f = $rsF->Fetch()) $fieldsBySid[$f['SID']] = (int)$f['ID'];

global $DB;
foreach ($want as $sid => $cfg) {
    if (!isset($fieldsBySid[$sid])) {
        echo "$sid: field not found\n";
        continue;
    }
    $qid = $fieldsBySid[$sid];

    // У текстовых вопросов один answer на FIELD_TYPE = type
    $rsA = CFormAnswer::GetList($qid, $by='s_sort', $order='asc', [], $isFiltered);
    $aid = 0;
    while ($a = $rsA->Fetch()) { $aid = (int)$a['ID']; break; }
    if (!$aid) {
        echo "$sid: answer not found for QID=$qid\n";
        continue;
    }

    $upd = [
        'FIELD_TYPE'  => $cfg['type'],
        'FIELD_PARAM' => $cfg['param'],
    ];
    if (isset($cfg['height'])) $upd['FIELD_HEIGHT'] = $cfg['height'];

    $sets = [];
    foreach ($upd as $k => $v) {
        $sets[] = "`$k` = '" . $DB->ForSql($v) . "'";
    }
    $DB->Query("UPDATE b_form_answer SET " . implode(', ', $sets) . " WHERE ID = " . $aid);
    echo "$sid (QID=$qid, AID=$aid): type={$cfg['type']}, param='" . $cfg['param'] . "'\n";
}

// Сброс кэша форм
\Bitrix\Main\Application::getInstance()->getCache()->cleanDir('/form');
echo "\nDone.\n";
