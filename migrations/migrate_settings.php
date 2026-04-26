<?php
// Миграция: создаёт iblock «Настройки разделов» в типе homes + 3 элемента (catalog/builders/detail)
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
CModule::IncludeModule('iblock');
header('Content-Type: text/plain; charset=utf-8');

$IBLOCK_CODE = 'homes_settings';
$IBLOCK_TYPE = 'homes';

// 1. Создаём инфоблок
$rs = CIBlock::GetList([], ['CHECK_PERMISSIONS' => 'N', 'CODE' => $IBLOCK_CODE, 'IBLOCK_TYPE' => $IBLOCK_TYPE]);
$ib = $rs->Fetch();
if (!$ib) {
    $obIB = new CIBlock;
    $iblockId = $obIB->Add([
        'NAME'           => 'Настройки разделов',
        'CODE'           => $IBLOCK_CODE,
        'IBLOCK_TYPE_ID' => $IBLOCK_TYPE,
        'ACTIVE'         => 'Y',
        'SITE_ID'        => ['s1'],
        'SORT'           => 50, // выше домов и застройщиков
        'API_CODE'       => 'HomesSettings',
        'BIZPROC'        => 'N',
        'WORKFLOW'       => 'N',
        'INDEX_ELEMENT'  => 'N',
        'INDEX_SECTION'  => 'N',
    ]);
    if (!$iblockId) {
        echo "FAIL create iblock: " . $obIB->LAST_ERROR . "\n";
        exit;
    }
    echo "Created iblock id=$iblockId\n";
} else {
    $iblockId = (int)$ib['ID'];
    echo "Iblock exists id=$iblockId\n";
}

// 2. Свойства
$props = [
    'HERO_EYEBROW'   => ['NAME' => 'Hero · надзаголовок (eyebrow)',         'TYPE' => 'S', 'MULTIPLE' => 'N', 'SORT' => 100],
    'HERO_TITLE'     => ['NAME' => 'Hero · заголовок (H1)',                 'TYPE' => 'S', 'MULTIPLE' => 'N', 'SORT' => 110, 'ROW_COUNT' => 2],
    'HERO_LEAD'      => ['NAME' => 'Hero · подзаголовок',                   'TYPE' => 'S', 'MULTIPLE' => 'N', 'SORT' => 120, 'ROW_COUNT' => 3],
    'HERO_BG'        => ['NAME' => 'Hero · фоновая картинка',               'TYPE' => 'F', 'MULTIPLE' => 'N', 'SORT' => 130],
    'TRUST_LINES'    => ['NAME' => 'Trustbar · строки (формат: "жирно | хвост")',           'TYPE' => 'S', 'MULTIPLE' => 'Y', 'SORT' => 200, 'ROW_COUNT' => 1],
    'AUDIT_LINES'    => ['NAME' => 'Аудит-шаги · строки (формат: "Заголовок | Описание")',   'TYPE' => 'S', 'MULTIPLE' => 'Y', 'SORT' => 300, 'ROW_COUNT' => 2],
    'BASE_INCLUDES'  => ['NAME' => 'В комплектацию входит · строки (формат: "код | Заголовок | Подпись"). Коды: walls, roof, windows, insulation, foundation, engineering', 'TYPE' => 'S', 'MULTIPLE' => 'Y', 'SORT' => 400, 'ROW_COUNT' => 1],
    'REVIEWS'        => ['NAME' => 'Отзывы · строки (формат: "Автор | Год · доп | Текст")',  'TYPE' => 'S', 'MULTIPLE' => 'Y', 'SORT' => 500, 'ROW_COUNT' => 3],
    'FAQ_LINES'      => ['NAME' => 'FAQ · строки (формат: "Вопрос | Ответ")',                'TYPE' => 'S', 'MULTIPLE' => 'Y', 'SORT' => 600, 'ROW_COUNT' => 3],
    'CTA_TITLE'      => ['NAME' => 'Нижний CTA-блок · заголовок',          'TYPE' => 'S', 'MULTIPLE' => 'N', 'SORT' => 700, 'ROW_COUNT' => 2],
    'CTA_LEAD'       => ['NAME' => 'Нижний CTA-блок · подзаголовок',       'TYPE' => 'S', 'MULTIPLE' => 'N', 'SORT' => 710, 'ROW_COUNT' => 3],
    'CTA_LINK'       => ['NAME' => 'Нижний CTA-блок · ссылка кнопки',      'TYPE' => 'S', 'MULTIPLE' => 'N', 'SORT' => 720],
    'CTA_BUTTON'     => ['NAME' => 'Нижний CTA-блок · текст кнопки',       'TYPE' => 'S', 'MULTIPLE' => 'N', 'SORT' => 730],
];

$obProp = new CIBlockProperty;
foreach ($props as $code => $cfg) {
    $rs = CIBlockProperty::GetList([], ['IBLOCK_ID' => $iblockId, 'CODE' => $code]);
    if ($rs->Fetch()) {
        echo "Prop $code: exists\n";
        continue;
    }
    $fields = array_merge([
        'IBLOCK_ID'     => $iblockId,
        'CODE'          => $code,
        'IS_REQUIRED'   => 'N',
        'PROPERTY_TYPE' => $cfg['TYPE'],
        'MULTIPLE'      => $cfg['MULTIPLE'],
        'SORT'          => $cfg['SORT'],
        'NAME'          => $cfg['NAME'],
    ], isset($cfg['ROW_COUNT']) ? ['ROW_COUNT' => $cfg['ROW_COUNT']] : []);
    $newId = $obProp->Add($fields);
    echo "Prop $code: " . ($newId ? "OK id=$newId" : "FAIL " . $obProp->LAST_ERROR) . "\n";
}

// 3. Элементы с дефолтными значениями (равны текущим хардкодам)
$elements = [
    'catalog' => [
        'NAME' => 'Каталог домов · /doma-i-kottedzhi/',
        'SORT' => 100,
        'PROPS' => [
            'HERO_EYEBROW' => 'Проекты домов для постройки',
            'HERO_TITLE'   => 'Дом вашей мечты — за 90 дней',
            'HERO_LEAD'    => 'Готовые и строящиеся проекты от проверенных застройщиков. Фиксированная цена, прозрачные сроки и планировки на любой бюджет.',
            'TRUST_LINES'  => [
                'Фикс. цена | в договоре',
                'Ипотека от 6% | партнёрские ставки',
                '5 лет гарантии | на конструктив',
                'Сроки в договоре | от 90 дней',
            ],
        ],
    ],
    'builders' => [
        'NAME' => 'Каталог застройщиков · /zastrojshhiki/',
        'SORT' => 200,
        'PROPS' => [
            'HERO_EYEBROW' => 'Партнёрские компании',
            'HERO_TITLE'   => 'Застройщики',
            'HERO_LEAD'    => 'Работаем только с проверенными командами. Каждая прошла аудит: проверка финустойчивости, качества объектов и репутации. Выбирайте партнёра под проект.',
            'AUDIT_LINES'  => [
                'Финансовая устойчивость | Запрашиваем выписки, проверяем долги и судебные разбирательства за 3 года.',
                'Лицензии и допуски | Проверяем наличие СРО, разрешений на строительство, страхование ответственности.',
                'Качество объектов | Выезжаем на 2–3 готовых объекта, общаемся с владельцами, делаем технический осмотр.',
                'Репутация и сделки | Анализируем отзывы, проверяем количество завершённых сделок и сроки.',
            ],
            'CTA_TITLE'    => 'Строите дома? Разместите проекты у нас',
            'CTA_LEAD'     => 'Работа по договору-оферте, аудит за 5 рабочих дней, доступ к клиентам и инструменты для ведения сделок.',
            'CTA_BUTTON'   => 'Оставить заявку →',
            'CTA_LINK'     => '#partner-form',
        ],
    ],
    'detail' => [
        'NAME' => 'Детальная страница дома',
        'SORT' => 300,
        'PROPS' => [
            'BASE_INCLUDES' => [
                'walls | Газобетон D500 | стены 400 мм',
                'roof | Металлочерепица | гарантия 20 лет',
                'windows | Двухкамерные окна | энергосбережение',
                'insulation | Утепление 100 мм | по СНиП',
                'foundation | Свайно-ростверковый | фундамент',
                'engineering | Базовая инженерка | разводка под ключ',
            ],
            'REVIEWS' => [
                'Анна М. | дом 96 м², 2024 | «Строили дом 96 м² за 4 месяца. Сроки выдержали, качество отличное. Отдельное спасибо прорабу за постоянную связь и отчёты по фото.»',
                'Дмитрий К. | дом 128 м², 2023 | «Самое ценное — никаких внезапных доплат. Всё по смете. Ребята профи, рекомендуем друзьям. Уже 2 года живём — ни одной претензии к качеству.»',
                'Елена и Сергей | семейная ипотека, 2024 | «Помогли и с проектом, и с ипотекой — семейная под 6%. Въехали через 3,5 месяца после подписания. Дом тёплый, всё продумано до мелочей.»',
            ],
            'FAQ_LINES' => [
                'Можно ли вносить изменения в проект? | Да. На этапе договора фиксируем планировку и материалы. Незначительные правки (расположение перегородок, окон) — без доплат. Существенные — пересчитываем смету.',
                'Что входит в стоимость? | Проект, материалы, работы по возведению коробки и кровли, базовая инженерка (разводка водопровода, канализации, электрики). Чистовая отделка и фасад — опционально.',
                'Какие гарантии? | Гарантия на конструктив 5 лет, на инженерные системы 2 года, на отделочные работы 1 год. Договор закрепляет фиксированную цену и сроки.',
                'Как идёт оплата? | Поэтапная: аванс 20%, после фундамента 30%, после коробки 30%, остаток после сдачи. Цена в договоре не меняется, даже если материалы подорожают.',
            ],
        ],
    ],
];

foreach ($elements as $code => $data) {
    $rs = CIBlockElement::GetList([], ['IBLOCK_ID' => $iblockId, 'CODE' => $code], false, false, ['ID']);
    if ($ex = $rs->Fetch()) {
        echo "Element $code: exists (ID={$ex['ID']})\n";
        continue;
    }
    $el = new CIBlockElement;
    $newId = $el->Add([
        'IBLOCK_ID' => $iblockId,
        'NAME'      => $data['NAME'],
        'CODE'      => $code,
        'ACTIVE'    => 'Y',
        'SORT'      => $data['SORT'],
        'PROPERTY_VALUES' => $data['PROPS'],
    ]);
    echo "Element $code: " . ($newId ? "OK ID=$newId" : "FAIL " . $el->LAST_ERROR) . "\n";
}

echo "\nDone.\n";
