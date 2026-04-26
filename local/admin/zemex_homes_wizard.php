<?php
/**
 * Настройка «Мастера подбора» на странице каталога /doma-i-kottedzhi/.
 * URL: /bitrix/admin/zemex_homes_wizard.php?lang=ru
 * Хранится как JSON в b_option (модуль 'main', ключ 'zemex_homes_wizard').
 */
require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php";
/** @var CMain $APPLICATION */
/** @var CUser $USER */

if (!$USER->IsAdmin()) $APPLICATION->AuthForm("Доступ только администраторам");

$APPLICATION->SetTitle("Мастер подбора дома — настройки");

$OPT_MODULE = 'main';
$OPT_KEY    = 'zemex_homes_wizard';

/* ── Дефолты (повторяют то, что захардкожено сейчас в шаблоне) ── */
$defaults = [
    'trigger' => [
        'icon'     => '🏡',
        'title'    => 'Не знаете, какой дом подойдёт?',
        'subtitle' => 'Подбор за 1 минуту по семье и бюджету — мы найдём 3 проекта под ваш запрос',
        'cta'      => 'Подобрать дом →',
    ],
    'head' => [
        'eyebrow' => 'Подбор за 1 минуту · 3 шага',
        'title'   => 'Не знаете, какой дом подойдёт?',
        'lead'    => 'Расскажите о семье и бюджете — мы подберём 3 проекта, которые подходят именно вам. Не нужно разбираться в квадратах и технологиях.',
    ],
    'step_labels' => ['Семья', 'Бюджет', 'Уточнения'],
    'step1' => [
        'title' => 'Сколько человек будет жить в доме?',
        'subtitle' => 'Стандарты комфорта: ~30 м² на человека плюс общие зоны.',
        'cards' => [
            ['family' => 2, 'icon' => '👤👤',          'label' => '1–2 человека',  'area_min' => 60,  'area_max' => 100, 'bedrooms' => '1'],
            ['family' => 3, 'icon' => '👤👤👶',        'label' => '3 человека',     'area_min' => 90,  'area_max' => 130, 'bedrooms' => '2'],
            ['family' => 4, 'icon' => '👨‍👩‍👧‍👦',     'label' => '4 человека',     'area_min' => 120, 'area_max' => 170, 'bedrooms' => '3'],
            ['family' => 5, 'icon' => '👨‍👩‍👧‍👦+',    'label' => '5 и больше',     'area_min' => 150, 'area_max' => 220, 'bedrooms' => '4+'],
        ],
    ],
    'step2' => [
        'title' => 'Какой у вас бюджет?',
        'subtitle' => 'Можно указать всю сумму или платёж по ипотеке — посчитаем сами.',
        'mortgage_rate' => 6,
        'mortgage_term_months' => 240,
    ],
    'step3' => [
        'title' => 'Несколько уточнений',
        'subtitle' => 'Это поможет выбрать оптимальный материал и технологию строительства.',
        'groups' => [
            'when' => [
                'label' => 'Когда нужен дом?',
                'options' => [
                    ['val' => 'urgent',  'label' => 'Срочно (сезон 2026)'],
                    ['val' => 'quarter', 'label' => 'Через 3–6 месяцев'],
                    ['val' => 'year',    'label' => 'До года'],
                    ['val' => 'future',  'label' => 'Просто смотрю'],
                ],
            ],
            'floors' => [
                'label' => 'Этажность',
                'options' => [
                    ['val' => 'any', 'label' => 'Не важно'],
                    ['val' => '1',   'label' => '1 этаж — без лестниц, проще для пожилых'],
                    ['val' => '2',   'label' => '2 этажа — больше площади на маленьком участке'],
                ],
            ],
            'purpose' => [
                'label' => 'Дом для…',
                'options' => [
                    ['val' => 'permanent', 'label' => 'Постоянного проживания'],
                    ['val' => 'dacha',     'label' => 'Дачи / летнего жилья'],
                ],
            ],
        ],
    ],
    'finish_button' => 'Показать подходящие проекты →',
    'result_cta'    => 'Получить персональную подборку',
];

$messages = [];

/* ── Загрузить конфиг ── */
$raw = COption::GetOptionString($OPT_MODULE, $OPT_KEY, '');
$config = $raw ? json_decode($raw, true) : null;
if (!is_array($config)) $config = $defaults;

/* ── POST ── */
if ($_SERVER["REQUEST_METHOD"] === "POST" && check_bitrix_sessid()) {
    if (!empty($_POST['reset'])) {
        COption::RemoveOption($OPT_MODULE, $OPT_KEY);
        $config = $defaults;
        $messages[] = "Сброшено к значениям по умолчанию.";
    } else {
        $newCfg = [];
        $newCfg['trigger'] = [
            'icon'     => trim((string)($_POST['trigger']['icon']     ?? '')),
            'title'    => trim((string)($_POST['trigger']['title']    ?? '')),
            'subtitle' => trim((string)($_POST['trigger']['subtitle'] ?? '')),
            'cta'      => trim((string)($_POST['trigger']['cta']      ?? '')),
        ];
        $newCfg['head'] = [
            'eyebrow' => trim((string)($_POST['head']['eyebrow'] ?? '')),
            'title'   => trim((string)($_POST['head']['title']   ?? '')),
            'lead'    => trim((string)($_POST['head']['lead']    ?? '')),
        ];
        $newCfg['step_labels'] = [
            trim((string)($_POST['step_labels'][0] ?? 'Семья')),
            trim((string)($_POST['step_labels'][1] ?? 'Бюджет')),
            trim((string)($_POST['step_labels'][2] ?? 'Уточнения')),
        ];
        $newCfg['step1'] = [
            'title'    => trim((string)($_POST['step1']['title']    ?? '')),
            'subtitle' => trim((string)($_POST['step1']['subtitle'] ?? '')),
            'cards'    => [],
        ];
        foreach (($_POST['step1']['cards'] ?? []) as $card) {
            $label = trim((string)($card['label'] ?? ''));
            if ($label === '') continue;
            $newCfg['step1']['cards'][] = [
                'family'   => (int)($card['family'] ?? 0),
                'icon'     => trim((string)($card['icon'] ?? '')),
                'label'    => $label,
                'area_min' => (int)$card['area_min'],
                'area_max' => (int)$card['area_max'],
                'bedrooms' => trim((string)($card['bedrooms'] ?? '')),
            ];
        }
        $newCfg['step2'] = [
            'title'    => trim((string)($_POST['step2']['title']    ?? '')),
            'subtitle' => trim((string)($_POST['step2']['subtitle'] ?? '')),
            'mortgage_rate'        => (float)str_replace(',', '.', (string)($_POST['step2']['mortgage_rate'] ?? 6)),
            'mortgage_term_months' => (int)($_POST['step2']['mortgage_term_months'] ?? 240),
        ];
        $newCfg['step3'] = [
            'title'    => trim((string)($_POST['step3']['title']    ?? '')),
            'subtitle' => trim((string)($_POST['step3']['subtitle'] ?? '')),
            'groups'   => [],
        ];
        foreach (['when','floors','purpose'] as $gKey) {
            $g = $_POST['step3']['groups'][$gKey] ?? [];
            $opts = [];
            foreach (($g['options'] ?? []) as $o) {
                $val = trim((string)($o['val'] ?? ''));
                $lbl = trim((string)($o['label'] ?? ''));
                if ($val === '' || $lbl === '') continue;
                $opts[] = ['val' => $val, 'label' => $lbl];
            }
            $newCfg['step3']['groups'][$gKey] = [
                'label'   => trim((string)($g['label'] ?? '')),
                'options' => $opts,
            ];
        }
        $newCfg['finish_button'] = trim((string)($_POST['finish_button'] ?? ''));
        $newCfg['result_cta']    = trim((string)($_POST['result_cta']    ?? ''));

        COption::SetOptionString($OPT_MODULE, $OPT_KEY, json_encode($newCfg, JSON_UNESCAPED_UNICODE));
        $config = $newCfg;
        $messages[] = "✓ Настройки мастера подбора сохранены.";
    }
}

require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php";

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>

<style>
.zhw { max-width: 1100px; margin: 0 auto; padding: 12px 18px; font-size: 13px; }
.zhw__hint { color: #5b6473; font-size: 12px; margin: 0 0 18px; line-height: 1.5; }
.zhw__msg { margin: 12px 0; padding: 10px 14px; border-radius: 4px; }
.zhw__msg--ok { background: #eaf6ea; border-left: 3px solid #27ae60; color: #1a3a1f; }
.zhw__sec { background: #fff; border: 1px solid #e1e4ea; border-radius: 6px; padding: 16px 18px; margin: 14px 0; }
.zhw__sec h2 { font-size: 16px; margin: 0 0 12px; color: #1a3a1f; }
.zhw__row { display: grid; grid-template-columns: 180px 1fr; gap: 8px 14px; align-items: center; margin-bottom: 10px; }
.zhw__row > label { color: #5b6473; font-weight: 600; }
.zhw__row input, .zhw__row textarea { padding: 7px 10px; border: 1px solid #c8cdd4; border-radius: 4px; font-size: 13px; width: 100%; box-sizing: border-box; font-family: inherit; }
.zhw__row textarea { resize: vertical; min-height: 60px; }
.zhw__cards { display: grid; grid-template-columns: 60px 80px 1fr 90px 90px 80px; gap: 8px 10px; align-items: center; margin-top: 8px; }
.zhw__cards .h { font-size: 11px; color: #8b94a3; text-transform: uppercase; letter-spacing: .04em; padding: 4px 0 0; }
.zhw__cards input { padding: 6px 8px; border: 1px solid #c8cdd4; border-radius: 4px; font-size: 13px; width: 100%; box-sizing: border-box; }
.zhw__opts { display: grid; grid-template-columns: 120px 1fr 30px; gap: 6px 10px; }
.zhw__opts .h { font-size: 11px; color: #8b94a3; text-transform: uppercase; letter-spacing: .04em; padding: 4px 0 0; }
.zhw__opts input { padding: 6px 8px; border: 1px solid #c8cdd4; border-radius: 4px; font-size: 13px; width: 100%; box-sizing: border-box; }
.zhw__del { background: #fdecec; border: 0; color: #d62b2b; cursor: pointer; border-radius: 4px; font-weight: 700; }
.zhw__del:hover { background: #f8c4c4; }
.zhw__add { background: #fff; border: 1px dashed #00bf3f; color: #1a3a1f; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 12.5px; font-weight: 600; margin-top: 8px; }
.zhw__buttons { margin-top: 20px; display: flex; gap: 10px; }
.zhw__buttons button { padding: 10px 20px; border-radius: 4px; border: 0; cursor: pointer; font-size: 14px; font-weight: 600; }
.zhw__save  { background: #27ae60; color: #fff; }
.zhw__reset { background: #f0f1f3; color: #5b6473; }
</style>

<div class="zhw">
    <p class="zhw__hint">
        Управление блоком «Мастер подбора дома» на странице <a href="/doma-i-kottedzhi/" target="_blank">/doma-i-kottedzhi/</a>.
        Меняйте тексты, варианты ответов и параметры карточек семьи. Изменения применяются сразу после сохранения.
    </p>

    <?php foreach ($messages as $m): ?>
        <div class="zhw__msg zhw__msg--ok"><?= h($m) ?></div>
    <?php endforeach; ?>

    <form method="POST" action="<?= POST_FORM_ACTION_URI ?>">
        <?= bitrix_sessid_post() ?>

        <div class="zhw__sec">
            <h2>🪟 Свёрнутый триггер-баннер</h2>
            <div class="zhw__hint">Что видит пользователь до раскрытия мастера.</div>
            <div class="zhw__row"><label>Иконка</label><input type="text" name="trigger[icon]" value="<?= h($config['trigger']['icon']) ?>"></div>
            <div class="zhw__row"><label>Заголовок</label><input type="text" name="trigger[title]" value="<?= h($config['trigger']['title']) ?>"></div>
            <div class="zhw__row"><label>Подпись</label><input type="text" name="trigger[subtitle]" value="<?= h($config['trigger']['subtitle']) ?>"></div>
            <div class="zhw__row"><label>Текст кнопки</label><input type="text" name="trigger[cta]" value="<?= h($config['trigger']['cta']) ?>"></div>
        </div>

        <div class="zhw__sec">
            <h2>🎯 Шапка мастера (после раскрытия)</h2>
            <div class="zhw__row"><label>Надзаголовок</label><input type="text" name="head[eyebrow]" value="<?= h($config['head']['eyebrow']) ?>"></div>
            <div class="zhw__row"><label>Заголовок (H2)</label><input type="text" name="head[title]" value="<?= h($config['head']['title']) ?>"></div>
            <div class="zhw__row"><label>Подзаголовок</label><textarea name="head[lead]"><?= h($config['head']['lead']) ?></textarea></div>
            <div class="zhw__row">
                <label>Подписи шагов</label>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px">
                    <input type="text" name="step_labels[0]" value="<?= h($config['step_labels'][0] ?? 'Семья') ?>" placeholder="Шаг 1">
                    <input type="text" name="step_labels[1]" value="<?= h($config['step_labels'][1] ?? 'Бюджет') ?>" placeholder="Шаг 2">
                    <input type="text" name="step_labels[2]" value="<?= h($config['step_labels'][2] ?? 'Уточнения') ?>" placeholder="Шаг 3">
                </div>
            </div>
        </div>

        <div class="zhw__sec">
            <h2>👨‍👩‍👧 Шаг 1 · Семья</h2>
            <div class="zhw__row"><label>Заголовок шага</label><input type="text" name="step1[title]" value="<?= h($config['step1']['title']) ?>"></div>
            <div class="zhw__row"><label>Подсказка</label><input type="text" name="step1[subtitle]" value="<?= h($config['step1']['subtitle']) ?>"></div>
            <div class="zhw__hint" style="margin-top:8px">Карточки выбора. <b>Площадь min/max</b> используется для подбора подходящих проектов из каталога.</div>
            <div class="zhw__cards">
                <div class="h">Семья (число)</div>
                <div class="h">Иконка</div>
                <div class="h">Название</div>
                <div class="h">Площадь min</div>
                <div class="h">Площадь max</div>
                <div class="h">Спальни</div>
                <?php foreach ($config['step1']['cards'] as $i => $card): ?>
                    <input type="number" name="step1[cards][<?=$i?>][family]" value="<?= h($card['family']) ?>">
                    <input type="text"   name="step1[cards][<?=$i?>][icon]" value="<?= h($card['icon']) ?>">
                    <input type="text"   name="step1[cards][<?=$i?>][label]" value="<?= h($card['label']) ?>">
                    <input type="number" name="step1[cards][<?=$i?>][area_min]" value="<?= h($card['area_min']) ?>">
                    <input type="number" name="step1[cards][<?=$i?>][area_max]" value="<?= h($card['area_max']) ?>">
                    <input type="text"   name="step1[cards][<?=$i?>][bedrooms]" value="<?= h($card['bedrooms']) ?>">
                <?php endforeach; ?>
            </div>
        </div>

        <div class="zhw__sec">
            <h2>💰 Шаг 2 · Бюджет</h2>
            <div class="zhw__row"><label>Заголовок шага</label><input type="text" name="step2[title]" value="<?= h($config['step2']['title']) ?>"></div>
            <div class="zhw__row"><label>Подсказка</label><input type="text" name="step2[subtitle]" value="<?= h($config['step2']['subtitle']) ?>"></div>
            <div class="zhw__row"><label>Ставка ипотеки, %</label><input type="text" name="step2[mortgage_rate]" value="<?= h($config['step2']['mortgage_rate']) ?>" style="max-width:120px"></div>
            <div class="zhw__row"><label>Срок ипотеки, мес</label><input type="number" name="step2[mortgage_term_months]" value="<?= h($config['step2']['mortgage_term_months']) ?>" style="max-width:120px"></div>
            <div class="zhw__hint">Диапазоны слайдеров (от 2 млн до 20 млн ₽ и т.п.) — задаются в коде шаблона.</div>
        </div>

        <div class="zhw__sec">
            <h2>🛠 Шаг 3 · Уточнения</h2>
            <div class="zhw__row"><label>Заголовок шага</label><input type="text" name="step3[title]" value="<?= h($config['step3']['title']) ?>"></div>
            <div class="zhw__row"><label>Подсказка</label><input type="text" name="step3[subtitle]" value="<?= h($config['step3']['subtitle']) ?>"></div>
            <?php foreach (['when' => 'Группа 1: Когда нужен дом?', 'floors' => 'Группа 2: Этажность', 'purpose' => 'Группа 3: Дом для…'] as $gKey => $gTitle):
                $g = $config['step3']['groups'][$gKey] ?? ['label' => '', 'options' => []];
            ?>
                <div style="margin-top:14px;border-top:1px solid #f1f3f6;padding-top:10px">
                    <div class="zhw__row"><label><?= h($gTitle) ?></label><input type="text" name="step3[groups][<?=$gKey?>][label]" value="<?= h($g['label']) ?>" placeholder="Заголовок группы"></div>
                    <div class="zhw__opts" id="opts-<?=$gKey?>">
                        <div class="h">Код (val)</div>
                        <div class="h">Текст варианта</div>
                        <div class="h"></div>
                        <?php foreach ($g['options'] as $i => $o): ?>
                            <input type="text" name="step3[groups][<?=$gKey?>][options][<?=$i?>][val]" value="<?= h($o['val']) ?>">
                            <input type="text" name="step3[groups][<?=$gKey?>][options][<?=$i?>][label]" value="<?= h($o['label']) ?>">
                            <button type="button" class="zhw__del" onclick="this.previousElementSibling.previousElementSibling.remove();this.previousElementSibling.remove();this.remove();" title="Удалить">✕</button>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="zhw__add" onclick="zhwAddOpt('<?=$gKey?>')">+ Добавить вариант</button>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="zhw__sec">
            <h2>🎯 Кнопки действий</h2>
            <div class="zhw__row"><label>Кнопка финиша (шаг 3)</label><input type="text" name="finish_button" value="<?= h($config['finish_button']) ?>"></div>
            <div class="zhw__row"><label>Кнопка после результата</label><input type="text" name="result_cta" value="<?= h($config['result_cta']) ?>"></div>
        </div>

        <div class="zhw__buttons">
            <button type="submit" class="zhw__save">💾 Сохранить</button>
            <button type="submit" name="reset" value="1" class="zhw__reset" onclick="return confirm('Сбросить все настройки мастера к значениям по умолчанию?')">↺ Сбросить</button>
        </div>
    </form>
</div>

<script>
function zhwAddOpt(gKey){
    var grid = document.getElementById('opts-' + gKey);
    var rows = grid.querySelectorAll('input[name*="][val]"]');
    var idx = rows.length;
    var v = document.createElement('input'); v.type='text'; v.name='step3[groups]['+gKey+'][options]['+idx+'][val]'; v.placeholder='код, например any';
    var l = document.createElement('input'); l.type='text'; l.name='step3[groups]['+gKey+'][options]['+idx+'][label]'; l.placeholder='Текст варианта';
    var b = document.createElement('button'); b.type='button'; b.className='zhw__del'; b.textContent='✕';
    b.onclick=function(){ this.previousElementSibling.previousElementSibling.remove(); this.previousElementSibling.remove(); this.remove(); };
    grid.appendChild(v); grid.appendChild(l); grid.appendChild(b);
    v.focus();
}
</script>

<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php"; ?>
