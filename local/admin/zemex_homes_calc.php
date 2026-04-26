<?php
/**
 * Настройка калькулятора домов: материалы (5 категорий × 3 варианта) + доп. опции (N штук).
 * URL: /bitrix/admin/zemex_homes_calc.php?lang=ru
 * Хранится как JSON в b_option (модуль 'main', ключ 'zemex_homes_calc').
 */
require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php";
/** @var CMain $APPLICATION */
/** @var CUser $USER */

if (!$USER->IsAdmin()) $APPLICATION->AuthForm("Доступ только администраторам");

$APPLICATION->SetTitle("Калькулятор домов — настройки");

$OPT_MODULE = 'main';
$OPT_KEY    = 'zemex_homes_calc';

/* ── Дефолты (повторяют то, что захардкожено сейчас в шаблоне) ── */
$defaults = [
    'walls' => [
        'label' => 'Материал стен',
        'options' => [
            ['label' => 'Газобетон D500',  'price' => 0],
            ['label' => 'Кирпич',          'price' => 450000],
            ['label' => 'Клеёный брус',    'price' => 850000],
        ],
    ],
    'roof' => [
        'label' => 'Материал крыши',
        'options' => [
            ['label' => 'Металлочерепица',       'price' => 0],
            ['label' => 'Композитная черепица',  'price' => 180000],
            ['label' => 'Фальцевая кровля',      'price' => 260000],
        ],
    ],
    'windows' => [
        'label' => 'Окна',
        'options' => [
            ['label' => 'Двухкамерный',     'price' => 0],
            ['label' => 'Энергосберегающий','price' => 120000],
            ['label' => 'Тёплый алюминий',  'price' => 280000],
        ],
    ],
    'insulation' => [
        'label' => 'Утепление контура',
        'options' => [
            ['label' => 'Стандарт 100 мм',  'price' => 0],
            ['label' => 'Усиленное 150 мм', 'price' => 90000],
            ['label' => 'Премиум 200 мм',   'price' => 180000],
        ],
    ],
    'foundation' => [
        'label' => 'Фундамент',
        'options' => [
            ['label' => 'Свайно-ростверковый', 'price' => 0],
            ['label' => 'Монолитная плита',    'price' => 250000],
            ['label' => 'Утеплённая лента',    'price' => 400000],
        ],
    ],
    'extras' => [
        ['label' => 'Тёплый пол',     'price' => 220000],
        ['label' => 'Рекуператор',    'price' => 180000],
        ['label' => 'Умный дом',      'price' => 120000],
        ['label' => 'Панорамные окна','price' => 190000],
        ['label' => 'Терраса',        'price' => 350000],
        ['label' => 'Навес / гараж',  'price' => 550000],
        ['label' => 'Сауна',          'price' => 280000],
        ['label' => 'Камин',          'price' => 180000],
    ],
];

$messages = [];
$errors   = [];

/* ── Загрузить текущую конфигурацию ── */
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
        $newConfig = [];
        // Категории
        foreach (['walls','roof','windows','insulation','foundation'] as $cat) {
            $catData = $_POST['cat'][$cat] ?? [];
            $label = trim((string)($catData['label'] ?? ''));
            if ($label === '') $label = $defaults[$cat]['label'];
            $options = [];
            foreach (($catData['options'] ?? []) as $opt) {
                $optLabel = trim((string)($opt['label'] ?? ''));
                $optPrice = (int)str_replace([' ', "\xc2\xa0"], '', (string)($opt['price'] ?? '0'));
                if ($optLabel === '') continue;
                $options[] = ['label' => $optLabel, 'price' => max(0, $optPrice)];
            }
            // Должно быть ровно 3 варианта (или хотя бы 1 — базовый). Если меньше 3 — добавим из дефолтов.
            while (count($options) < 3) {
                $idx = count($options);
                $options[] = $defaults[$cat]['options'][$idx] ?? ['label' => 'Вариант ' . ($idx+1), 'price' => 0];
            }
            $newConfig[$cat] = ['label' => $label, 'options' => array_slice($options, 0, 3)];
        }
        // Extras (динамический список)
        $extras = [];
        foreach (($_POST['extra'] ?? []) as $e) {
            $eLabel = trim((string)($e['label'] ?? ''));
            $ePrice = (int)str_replace([' ', "\xc2\xa0"], '', (string)($e['price'] ?? '0'));
            if ($eLabel === '') continue;
            $extras[] = ['label' => $eLabel, 'price' => max(0, $ePrice)];
        }
        $newConfig['extras'] = $extras;

        COption::SetOptionString($OPT_MODULE, $OPT_KEY, json_encode($newConfig, JSON_UNESCAPED_UNICODE));
        $config = $newConfig;
        $messages[] = "✓ Настройки калькулятора сохранены.";
    }
}

require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php";

function fmt_price($n) { return number_format((int)$n, 0, ',', ' '); }
?>

<style>
.zhc { max-width: 1200px; margin: 0 auto; padding: 12px 18px; font-size: 13px; }
.zhc h2 { font-size: 18px; margin: 24px 0 8px; color: #1a3a1f; }
.zhc .zhc__hint { color: #5b6473; font-size: 12px; margin: 0 0 18px; line-height: 1.5; }
.zhc__msg { margin: 12px 0; padding: 10px 14px; border-radius: 4px; font-size: 13px; }
.zhc__msg--ok { background: #eaf6ea; border-left: 3px solid #27ae60; color: #1a3a1f; }
.zhc__msg--err{ background: #fdecec; border-left: 3px solid #d62b2b; color: #5a0e0e; }
.zhc__cat { background: #fff; border: 1px solid #e1e4ea; border-radius: 6px; padding: 16px 18px; margin: 14px 0; }
.zhc__cat-head { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
.zhc__cat-head label { font-weight: 600; flex: 0 0 110px; }
.zhc__cat-head input { flex: 1; padding: 6px 10px; border: 1px solid #c8cdd4; border-radius: 4px; font-size: 14px; font-weight: 600; }
.zhc__opts { display: grid; grid-template-columns: 90px 1fr 160px; gap: 8px 12px; align-items: center; }
.zhc__opts > div { padding: 4px 0; }
.zhc__opts .zhc__opt-head { font-size: 11px; color: #8b94a3; text-transform: uppercase; letter-spacing: .04em; padding: 0; }
.zhc__opts input { width: 100%; padding: 6px 10px; border: 1px solid #c8cdd4; border-radius: 4px; font-size: 13px; }
.zhc__opts .zhc__price-wrap { display: flex; align-items: center; gap: 6px; }
.zhc__opts .zhc__price-wrap span { color: #5b6473; }
.zhc__base-tag { background: #e9f8ee; color: #1a3a1f; padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 600; text-align: center; }
.zhc__upgrade-tag { color: #5b6473; font-size: 12px; text-align: center; }
.zhc__extras { background: #fff; border: 1px solid #e1e4ea; border-radius: 6px; padding: 16px 18px; margin: 14px 0; }
.zhc__extras-grid { display: grid; grid-template-columns: 1fr 160px 30px; gap: 8px 12px; }
.zhc__extras-grid .zhc__head { font-size: 11px; color: #8b94a3; text-transform: uppercase; letter-spacing: .04em; }
.zhc__extras-grid input { width: 100%; padding: 6px 10px; border: 1px solid #c8cdd4; border-radius: 4px; font-size: 13px; }
.zhc__del { background: #fdecec; border: 0; color: #d62b2b; cursor: pointer; border-radius: 4px; font-weight: 700; }
.zhc__del:hover { background: #f8c4c4; }
.zhc__add { background: #fff; border: 1px dashed #00bf3f; color: #1a3a1f; padding: 8px 14px; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 600; margin-top: 10px; }
.zhc__add:hover { background: #f0fbf3; }
.zhc__buttons { margin-top: 20px; display: flex; gap: 10px; }
.zhc__buttons button { padding: 10px 20px; border-radius: 4px; border: 0; cursor: pointer; font-size: 14px; font-weight: 600; }
.zhc__save  { background: #27ae60; color: #fff; }
.zhc__save:hover { background: #1e8c4d; }
.zhc__reset { background: #f0f1f3; color: #5b6473; }
.zhc__reset:hover { background: #e0e1e3; }
</style>

<div class="zhc">
    <p class="zhc__hint">
        Управление калькулятором стоимости на детальной странице дома. Цены задаются в рублях, наценка прибавляется к базовой стоимости проекта.<br>
        В каждой категории первый вариант — базовый (включён в цену), второй и третий — апгрейды с доплатой.
    </p>

    <?php foreach ($messages as $m): ?>
        <div class="zhc__msg zhc__msg--ok"><?= htmlspecialchars($m) ?></div>
    <?php endforeach; ?>
    <?php foreach ($errors as $e): ?>
        <div class="zhc__msg zhc__msg--err"><?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>

    <form method="POST" action="<?= POST_FORM_ACTION_URI ?>" enctype="multipart/form-data">
        <?= bitrix_sessid_post() ?>

        <?php foreach (['walls' => '🧱 Стены', 'roof' => '🏠 Крыша', 'windows' => '🪟 Окна', 'insulation' => '🧊 Утепление', 'foundation' => '🏗 Фундамент'] as $cat => $catTitle): ?>
            <?php $cfg = $config[$cat] ?? $defaults[$cat]; ?>
            <div class="zhc__cat">
                <div class="zhc__cat-head">
                    <span style="font-size:18px;"><?= mb_substr($catTitle, 0, 2) ?></span>
                    <label for="cat_<?= $cat ?>_label">Заголовок</label>
                    <input type="text" id="cat_<?= $cat ?>_label" name="cat[<?= $cat ?>][label]" value="<?= htmlspecialchars($cfg['label']) ?>">
                </div>
                <div class="zhc__opts">
                    <div class="zhc__opt-head">Статус</div>
                    <div class="zhc__opt-head">Название варианта</div>
                    <div class="zhc__opt-head">Цена ₽ (наценка)</div>
                    <?php for ($i = 0; $i < 3; $i++):
                        $opt = $cfg['options'][$i] ?? ['label' => '', 'price' => 0];
                    ?>
                        <div><?php if ($i === 0): ?><span class="zhc__base-tag">в базе</span><?php else: ?><span class="zhc__upgrade-tag">апгрейд</span><?php endif; ?></div>
                        <div><input type="text" name="cat[<?= $cat ?>][options][<?= $i ?>][label]" value="<?= htmlspecialchars($opt['label']) ?>"></div>
                        <div class="zhc__price-wrap">
                            <input type="text" name="cat[<?= $cat ?>][options][<?= $i ?>][price]" value="<?= $i === 0 ? '0' : fmt_price($opt['price']) ?>" <?= $i === 0 ? 'readonly style="background:#f0f1f3"' : '' ?>>
                            <span>₽</span>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="zhc__extras">
            <h2 style="margin-top:0">➕ Дополнительные опции</h2>
            <p class="zhc__hint">Чекбоксы под калькулятором: тёплый пол, рекуператор, терраса и т.д. Можно добавлять и удалять.</p>
            <div class="zhc__extras-grid">
                <div class="zhc__head">Название</div>
                <div class="zhc__head">Цена ₽</div>
                <div class="zhc__head"></div>
                <div id="zhc-extras-rows" style="display:contents">
                    <?php foreach ($config['extras'] ?? [] as $i => $e): ?>
                        <input type="text" name="extra[<?= $i ?>][label]" value="<?= htmlspecialchars($e['label']) ?>">
                        <input type="text" name="extra[<?= $i ?>][price]" value="<?= fmt_price($e['price']) ?>">
                        <button type="button" class="zhc__del" onclick="this.previousElementSibling.previousElementSibling.value='';this.previousElementSibling.value='';this.parentNode.removeChild(this.previousElementSibling.previousElementSibling);this.parentNode.removeChild(this.previousElementSibling);this.parentNode.removeChild(this);" title="Удалить">✕</button>
                    <?php endforeach; ?>
                </div>
            </div>
            <button type="button" class="zhc__add" onclick="(function(){
                var grid = document.querySelector('.zhc__extras-grid');
                var rows = grid.querySelectorAll('input[name^=&quot;extra[&quot;]');
                var idx = rows.length / 2;
                var l = document.createElement('input'); l.type='text'; l.name='extra['+idx+'][label]'; l.placeholder='Название опции';
                var p = document.createElement('input'); p.type='text'; p.name='extra['+idx+'][price]'; p.placeholder='Цена ₽';
                var b = document.createElement('button'); b.type='button'; b.className='zhc__del'; b.textContent='✕'; b.title='Удалить';
                b.onclick = function(){ this.previousElementSibling.previousElementSibling.remove(); this.previousElementSibling.remove(); this.remove(); };
                grid.appendChild(l); grid.appendChild(p); grid.appendChild(b);
                l.focus();
            })()">+ Добавить опцию</button>
        </div>

        <div class="zhc__buttons">
            <button type="submit" class="zhc__save">💾 Сохранить</button>
            <button type="submit" name="reset" value="1" class="zhc__reset" onclick="return confirm('Сбросить все настройки калькулятора к значениям по умолчанию?')">↺ Сбросить</button>
        </div>
    </form>

    <p class="zhc__hint" style="margin-top:30px">
        💡 После сохранения изменения сразу применяются ко всем проектам в каталоге <a href="/doma-i-kottedzhi/" target="_blank">/doma-i-kottedzhi/</a>.
    </p>
</div>

<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php"; ?>
