<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);

use Bitrix\Main\Page\Asset;
Asset::getInstance()->addCss(SITE_TEMPLATE_PATH."/css/zx-design.css");

$items = $arResult['ITEMS'];
$total = is_array($items) ? count($items) : 0;

$cards = [];
$pricesAll = [];
foreach($items as $arItem){
    $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
    $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));

    $props = [];
    foreach($arItem['PROPERTIES'] as $prop){
        if($prop['CODE'] == 'MATERIAL') {
            $props[$prop['CODE']] = ['XML_ID' => $prop['VALUE_XML_ID'], 'VALUE' => $prop['VALUE']];
        } elseif(!empty($prop['VALUE'])) {
            $props[$prop['CODE']] = $prop['VALUE'];
        }
    }
    $priceRaw  = isset($props['PRICE']) ? (int)preg_replace('/\D/','', $props['PRICE']) : 0;
    $squareRaw = isset($props['SQUARE']) ? (float)$props['SQUARE'] : 0;
    $isReady     = (!empty($props['READY']) && $props['READY'] == 'Y') ? 'yes' : 'no';
    $isInProcess = (!empty($props['IN_PROCESS']) && $props['IN_PROCESS'] == 'Y') ? 'yes' : 'no';
    $matXmlId  = isset($props['MATERIAL']['XML_ID']) ? $props['MATERIAL']['XML_ID'] : '';
    $matName   = isset($props['MATERIAL']['VALUE']) ? $props['MATERIAL']['VALUE'] : '';
    $hasSecond = !empty($props['SECOND_FLOOR_IMG']) || !empty($props['SECOND_FLOOR_DESC']);
    $floorsNum = $hasSecond ? 2 : 1;
    $imgSrc = '';
    if(!empty($arItem['PREVIEW_PICTURE']['SRC'])) $imgSrc = $arItem['PREVIEW_PICTURE']['SRC'];
    elseif(!empty($arItem['DETAIL_PICTURE']['SRC'])) $imgSrc = $arItem['DETAIL_PICTURE']['SRC'];

    if($priceRaw > 0) $pricesAll[] = $priceRaw;

    $cards[] = [
        'id' => $arItem['ID'], 'editArea' => $this->GetEditAreaId($arItem['ID']),
        'name' => $arItem['NAME'], 'url' => $arItem['DETAIL_PAGE_URL'],
        'preview' => $arItem['PREVIEW_TEXT'] ?? '', 'img' => $imgSrc,
        'price' => isset($props['PRICE']) ? $props['PRICE'] : '', 'priceRaw' => $priceRaw,
        'square' => isset($props['SQUARE']) ? $props['SQUARE'] : '', 'squareRaw' => $squareRaw,
        'floors' => $floorsNum,
        'material' => $matName, 'matXml' => $matXmlId,
        'ready' => $isReady, 'inprocess' => $isInProcess,
    ];
}

$priceMin = !empty($pricesAll) ? min($pricesAll) : 0;
$priceMax = !empty($pricesAll) ? max($pricesAll) : 0;

$matOptions = [];
foreach($cards as $c){
    if(!empty($c['matXml']) && !isset($matOptions[$c['matXml']])){
        $matOptions[$c['matXml']] = $c['material'];
    }
}

$floorOptions = [];
foreach($cards as $c){
    if(!empty($c['floors'])) $floorOptions[(string)$c['floors']] = true;
}
ksort($floorOptions);

$hasMultiFloors = count($floorOptions) > 1;

function zx_price_short($v){
    if(!$v) return 'по запросу';
    if($v >= 1000000) {
        $m = $v / 1000000;
        $s = number_format($m, ($m < 10 ? 2 : 1), ',', '');
        $s = rtrim(rtrim($s, '0'), ',');
        return $s.' млн ₽';
    }
    return number_format($v, 0, ',', ' ').' ₽';
}
?>
<div class="zx-scope" id="doma-items">
  <?if(!empty($cards)):?>
  <div class="c-sel--div__CONTAINER zx-doma-wrap">

    <div class="zx-trustbar zx-trustbar--inline zx-trustbar--top">
      <div class="zx-trustbar__item"><span class="zx-trustbar__icon">✓</span><span><b>Фикс. цена</b> в договоре</span></div>
      <div class="zx-trustbar__item"><span class="zx-trustbar__icon">✓</span><span><b>Ипотека от 6%</b> · партнёрские ставки</span></div>
      <div class="zx-trustbar__item"><span class="zx-trustbar__icon">✓</span><span><b>5 лет гарантии</b> на конструктив</span></div>
      <div class="zx-trustbar__item"><span class="zx-trustbar__icon">✓</span><span><b>Сроки в договоре</b> · от 90 дней</span></div>
    </div>

    <form name="zxFilterForm" action="javascript:void(0)" class="smartfilter zx-doma-filter" onsubmit="return false;">
      <div class="zx-doma-filter__row">

        <fieldset class="c-sel--fieldset__ROAD bx-filter-parameters-box bx-filter-select-container zx-field">
          <span class="bx-filter-container-modef"></span>
          <legend class="font__BODY_TEXT_CAPTION">Материал</legend>
          <div class="bx-filter-select-block">
            <div class="c-sel--button__ROAD font__BODY_TEXT_PRIMARY bx-filter-select-text" data-role="currentOption">
              <span>Не важно</span>
              <img class="c-sel--img__ROAD" src="/local/templates/zemexx_redisign/images/c-sel-arr-bott.svg" alt="стрелка вниз">
            </div>
            <div class="bx-filter-select-popup c-sel--div__ROAD c-sel--div__ROAD4" data-role="dropdownContent">
              <label class="bx-filter-param-label c-sel--label__ROAD font__BODY_TEXT_PRIMARY __c-sel--label__ROAD__CHECKED" data-zx-key="material" data-zx-val="">Не важно</label>
              <?foreach($matOptions as $xml => $name):?>
                <label class="bx-filter-param-label c-sel--label__ROAD font__BODY_TEXT_PRIMARY" data-zx-key="material" data-zx-val="<?=htmlspecialchars($xml)?>"><?=htmlspecialchars($name)?></label>
              <?endforeach?>
            </div>
          </div>
        </fieldset>

        <?if($hasMultiFloors):?>
        <div class="zx-field zx-field--floors">
          <span class="zx-field__legend">Этажность</span>
          <div class="zx-floors-chips" role="group" aria-label="Этажность">
            <button type="button" class="zx-floor-chip" data-zx-floor="1">1</button>
            <button type="button" class="zx-floor-chip" data-zx-floor="2">2</button>
          </div>
        </div>
        <?endif?>

        <label class="c-sel--label__RANGE font__BODY_TEXT_CAPTION bx-filter-parameters-box zx-field zx-field--range">
          <span class="bx-filter-container-modef"></span>
          Цена, ₽
          <div class="zx-range-box">
            <span class="zx-range-val">от <b class="c-sel--span__RANGE min" data-from="<?=$priceMin?>"><?=number_format($priceMin,0,',',' ')?></b></span>
            <span class="zx-range-dash">—</span>
            <span class="zx-range-val">до <b class="c-sel--span__RANGE max" data-to="<?=$priceMax?>"><?=number_format($priceMax,0,',',' ')?></b></span>
          </div>
          <div class="zx-range-sliders">
            <input class="c-sel--input__RANGE min" type="range" name="zxPriceMinR" min="0" max="100" value="0">
            <input class="c-sel--input__RANGE max" type="range" name="zxPriceMaxR" min="0" max="100" value="100">
          </div>
          <input class="min-price price-sotka" type="hidden" name="zxPriceMin" id="zxPriceMin" value="">
          <input class="max-price price-sotka" type="hidden" name="zxPriceMax" id="zxPriceMax" value="">
        </label>

        <fieldset class="c-sel--fieldset__ROAD2 bx-filter-parameters-box zx-field">
          <span class="bx-filter-container-modef"></span>
          <legend class="font__BODY_TEXT_CAPTION">Площадь, м²</legend>
          <button class="c-sel--button__ROAD2 font__BODY_TEXT_PRIMARY" type="button">
            <span>Не важно</span>
            <img class="c-sel--img__ROAD2" src="/local/templates/zemexx_redisign/images/c-sel-arr-bott.svg" alt="стрелка вниз">
          </button>
          <div class="c-sel--div__ROAD2">
            <label class="c-sel--label__ROAD2 __c-sel--label__ROAD__CHECKED2 font__BODY_TEXT_PRIMARY">
              Не важно<input type="radio" name="zxArea" value="" checked>
            </label>
            <label class="c-sel--label__ROAD2 font__BODY_TEXT_PRIMARY">
              До 100<input type="radio" name="zxArea" value="0-100">
            </label>
            <label class="c-sel--label__ROAD2 font__BODY_TEXT_PRIMARY">
              100—150<input type="radio" name="zxArea" value="100-150">
            </label>
            <label class="c-sel--label__ROAD2 font__BODY_TEXT_PRIMARY">
              150—200<input type="radio" name="zxArea" value="150-200">
            </label>
            <label class="c-sel--label__ROAD2 font__BODY_TEXT_PRIMARY">
              От 200<input type="radio" name="zxArea" value="200-99999">
            </label>
          </div>
        </fieldset>

      </div>

      <div class="zx-doma-filter__chips">
        <label class="zx-preset-chip" data-zx-quick="ready">
          <input type="checkbox" value="Y"><span>Готовые сейчас</span>
        </label>
        <label class="zx-preset-chip" data-zx-quick="inprocess">
          <input type="checkbox" value="Y"><span>Идёт строительство</span>
        </label>
        <label class="zx-preset-chip" data-zx-quick="floor1">
          <input type="checkbox" value="Y"><span>Одноэтажные</span>
        </label>
        <label class="zx-preset-chip" data-zx-quick="floor2">
          <input type="checkbox" value="Y"><span>Двухэтажные</span>
        </label>
        <label class="zx-preset-chip" data-zx-quick="price5">
          <input type="checkbox" value="Y"><span>До 5 млн ₽</span>
        </label>
        <label class="zx-preset-chip" data-zx-quick="price8">
          <input type="checkbox" value="Y"><span>До 8 млн ₽</span>
        </label>
        <button type="button" class="zx-doma-filter__reset" id="zxResetBottom">× Сбросить</button>
      </div>
    </form>

    <div class="zx-doma-toolbar">
      <div class="zx-doma-toolbar__result font__BODY_TEXT_PRIMARY">
        Подобрано <b id="zxCountTop"><?=$total?></b>
        <span id="zxCountTopWord">проектов</span>
        <span id="zxCount" style="display:none"><?=$total?></span>
        <span id="zxCountWord" style="display:none">проектов</span>
      </div>
      <label class="zx-doma-toolbar__sort font__BODY_TEXT_CAPTION">
        Сортировка
        <select class="zx-filter-select" id="zxSort">
          <option value="price-asc">Цена: по возрастанию</option>
          <option value="price-desc">Цена: по убыванию</option>
          <option value="area-asc">Площадь: меньше</option>
          <option value="area-desc">Площадь: больше</option>
        </select>
      </label>
    </div>

    <div class="zx-cat-grid" id="zxItems">
      <?foreach($cards as $c):
        $statusLabel = $c['ready']==='yes' ? 'Готовый дом' : ($c['inprocess']==='yes' ? 'Идёт строительство' : 'Проект');
        $statusClass = $c['ready']==='yes' ? 'zx-chip--ready' : 'zx-chip--progress';
      ?>
      <a class="zx-proj-card zx-hover-lift" id="<?=$c['editArea']?>" href="<?=$c['url']?>"
         data-material="<?=htmlspecialchars($c['matXml'])?>"
         data-price="<?=$c['priceRaw']?>"
         data-square="<?=$c['squareRaw']?>"
         data-floors="<?=htmlspecialchars((string)$c['floors'])?>"
         data-ready="<?=$c['ready']?>"
         data-inprocess="<?=$c['inprocess']?>">
        <div class="zx-img-ph zx-proj-card__img">
          <?if($c['img']):?><img src="<?=$c['img']?>" alt="<?=htmlspecialchars($c['name'])?>" loading="lazy"><?endif?>
          <div class="zx-proj-card__badges">
            <span class="zx-chip <?=$statusClass?>"><span class="zx-chip__dot"></span><?=$statusLabel?></span>
            <?if($c['material']):?><span class="zx-chip"><?=htmlspecialchars($c['material'])?></span><?endif?>
          </div>
        </div>
        <div class="zx-proj-card__body">
          <div class="zx-proj-card__head">
            <h3 class="zx-proj-card__title font__HEADING_CARD_TITLE"><?=htmlspecialchars($c['name'])?></h3>
            <div class="zx-proj-card__price-wrap">
              <div class="zx-proj-card__price zx-mono"><?=zx_price_short($c['priceRaw'])?></div>
              <?php $monthly = $c['priceRaw'] ? round($c['priceRaw'] * 0.0072 / 1000) * 1000 : 0; ?>
              <?if($monthly):?>
                <div class="zx-proj-card__mortgage">~<?=number_format($monthly,0,',',' ')?> ₽/мес</div>
              <?endif?>
            </div>
          </div>
          <?if($c['preview']):?>
            <div class="zx-proj-card__sub"><?=strip_tags($c['preview'])?></div>
          <?endif?>
          <div class="zx-proj-card__specs">
            <?if($c['square']):?><span><b><?=$c['square']?></b> м²</span><?endif?>
            <?if($c['floors']):?><span>·</span><span><b><?=$c['floors']?></b> эт.</span><?endif?>
          </div>
        </div>
      </a>
      <?endforeach?>
    </div>

    <div class="zx-cat-empty font__BODY_TEXT_PRIMARY" id="zxEmpty" style="display:none;">По выбранным параметрам ничего не найдено</div>

  </div>
  <?else:?>
  <div class="c-sel--div__CONTAINER"><div class="zx-cat-empty font__BODY_TEXT_PRIMARY">Каталог пополняется.</div></div>
  <?endif?>
</div>

<style>
/* Doma catalog filter — aligned with site design tokens (Montserrat + PT Sans,
   --text-primary / --text-secondary / --text-accent / --bg-*, radii 14/20/28). */
.zx-doma-wrap { padding-top: 0; padding-bottom: 48px; }

/* Trustbar — top */
.zx-trustbar--top {
  margin: 20px 0;
  padding: 16px 22px;
  background: var(--bg-quaternary, #e5f9ec);
  border: 1px solid #cdebd4;
  border-radius: 20px;
  display: flex;
  flex-wrap: wrap;
  gap: 10px 32px;
}
.zx-trustbar--top .zx-trustbar__item {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  font-family: 'PT Sans', sans-serif;
  font-size: 14px;
  color: var(--text-secondary, #6f737a);
}
.zx-trustbar--top .zx-trustbar__item b {
  color: var(--text-primary, #030813);
  font-weight: 600;
}
.zx-trustbar--top .zx-trustbar__icon {
  display: inline-flex;
  width: 20px; height: 20px;
  align-items: center; justify-content: center;
  color: #fff;
  background: var(--text-accent, #00bf3f);
  border-radius: 50%;
  font-size: 12px;
  font-weight: 700;
  flex-shrink: 0;
}

/* Filter card */
.zx-doma-filter {
  margin: 0 0 20px;
  padding: 28px;
  background: #fff;
  border: 1px solid var(--border-primary-strong, #d8e2eb);
  border-radius: 28px;
  box-shadow: 0 2px 10px rgba(7, 23, 53, .05);
  font-family: 'Montserrat', sans-serif;
}

.zx-doma-filter__row {
  display: grid;
  grid-template-columns: 1fr;
  gap: 16px 20px;
  align-items: end;
}
@media (min-width: 720px){
  .zx-doma-filter__row { grid-template-columns: repeat(2, minmax(0,1fr)); }
}
@media (min-width: 1080px){
  .zx-doma-filter__row { grid-template-columns: minmax(180px,1.1fr) minmax(140px,0.7fr) minmax(280px,1.8fr) minmax(180px,1.1fr); }
}

.zx-doma-filter .zx-field { min-width: 0; margin: 0; padding: 0; border: 0; display: flex; flex-direction: column; }
.zx-doma-filter .zx-field > legend,
.zx-doma-filter .zx-field__legend {
  display: block;
  padding: 0;
  margin: 0 0 8px;
  font-size: 13px;
  font-weight: 500;
  line-height: 1;
  color: var(--text-secondary, #6f737a);
  letter-spacing: .01em;
}

/* Dropdown buttons (Material, Area) */
.zx-doma-filter .c-sel--button__ROAD,
.zx-doma-filter .c-sel--button__ROAD2 {
  width: 100%;
  padding: 0 16px;
  height: 48px;
  box-sizing: border-box;
  border: 1px solid var(--border-primary-strong, #d8e2eb);
  border-radius: 14px;
  background: #fff;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 8px;
  color: var(--text-primary, #030813);
  font-family: inherit;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: border-color .15s;
}
.zx-doma-filter .c-sel--button__ROAD:hover,
.zx-doma-filter .c-sel--button__ROAD2:hover { border-color: var(--text-primary, #030813); }
.zx-doma-filter .c-sel--img__ROAD,
.zx-doma-filter .c-sel--img__ROAD2 { width: 14px; height: 14px; opacity: .6; }

/* Floors toggle chips */
.zx-floors-chips { display: flex; gap: 8px; }
.zx-floor-chip {
  flex: 1 1 auto;
  min-width: 56px;
  height: 48px;
  padding: 0 16px;
  border: 1px solid var(--border-primary-strong, #d8e2eb);
  border-radius: 14px;
  background: #fff;
  font-family: inherit;
  font-size: 14px;
  font-weight: 500;
  color: var(--text-primary, #030813);
  cursor: pointer;
  transition: all .15s;
}
.zx-floor-chip:hover { border-color: var(--text-primary, #030813); }
.zx-floor-chip.is-active {
  background: var(--bg-accent, #00bf3f);
  border-color: var(--bg-accent, #00bf3f);
  color: #fff;
}

/* Price range */
.zx-range-box {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  height: 48px;
  padding: 0 16px;
  border: 1px solid var(--border-primary-strong, #d8e2eb);
  border-radius: 14px;
  background: #fff;
  font-family: inherit;
  font-size: 14px;
  color: var(--text-secondary, #6f737a);
  white-space: nowrap;
  transition: border-color .15s;
}
.zx-range-box:hover { border-color: var(--text-primary, #030813); }
.zx-range-val { display: inline-flex; align-items: baseline; gap: 6px; min-width: 0; overflow: hidden; text-overflow: ellipsis; }
.zx-range-val b {
  color: var(--text-primary, #030813);
  font-weight: 600;
  font-feature-settings: "tnum";
}
.zx-range-dash { color: var(--text-tertiary, #a3a5aa); flex-shrink: 0; }

.zx-range-sliders { position: relative; height: 18px; margin-top: 10px; padding: 0 6px; }
.zx-range-sliders .c-sel--input__RANGE {
  position: absolute;
  left: 6px; right: 6px;
  width: calc(100% - 12px);
  margin: 0;
  top: 8px;
  height: 2px;
}
.zx-range-sliders .c-sel--input__RANGE.max { background: transparent; }

/* Area dropdown popup — keep within card */
.zx-doma-filter .c-sel--fieldset__ROAD2 { position: relative; }
.zx-doma-filter .c-sel--div__ROAD2 { width: 100%; left: 0; right: 0; top: calc(100% + 6px); }

/* Preset chips */
.zx-doma-filter__chips {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid var(--border-primary-weak, #e6eaf0);
  align-items: center;
}
.zx-preset-chip {
  position: relative;
  display: inline-flex;
  align-items: center;
  height: 36px;
  padding: 0 16px;
  border: 1px solid var(--border-primary-strong, #d8e2eb);
  border-radius: 999px;
  background: #fff;
  font-family: inherit;
  font-size: 13px;
  font-weight: 500;
  color: var(--text-primary, #030813);
  cursor: pointer;
  user-select: none;
  transition: background .15s, border-color .15s, color .15s;
}
.zx-preset-chip input { position: absolute; opacity: 0; pointer-events: none; }
.zx-preset-chip:hover { border-color: var(--text-primary, #030813); }
.zx-preset-chip:has(input:checked) {
  background: var(--bg-quaternary, #e5f9ec);
  border-color: var(--text-accent, #00bf3f);
  color: var(--text-accent, #00bf3f);
}

.zx-doma-filter__reset {
  margin-left: auto;
  border: 0;
  background: transparent;
  color: var(--text-secondary, #6f737a);
  font-family: inherit;
  font-size: 13px;
  cursor: pointer;
  padding: 8px 12px;
  border-radius: 10px;
  transition: background .15s, color .15s;
}
.zx-doma-filter__reset:hover {
  color: var(--text-primary, #030813);
  background: var(--bg-secondary, #f4f6f9);
}

/* Toolbar */
.zx-doma-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin: 8px 0 20px;
  flex-wrap: wrap;
}
.zx-doma-toolbar__result {
  font-family: 'PT Sans', sans-serif;
  font-size: 14px;
  color: var(--text-secondary, #6f737a);
}
.zx-doma-toolbar__result b {
  color: var(--text-primary, #030813);
  font-weight: 600;
}
.zx-doma-toolbar__sort {
  display: flex;
  align-items: center;
  gap: 10px;
  font-family: 'PT Sans', sans-serif;
  font-size: 13px;
  color: var(--text-secondary, #6f737a);
}
.zx-doma-toolbar__sort .zx-filter-select {
  border: 1px solid var(--border-primary-strong, #d8e2eb);
  background: #fff;
  border-radius: 12px;
  padding: 9px 12px;
  font-family: inherit;
  font-size: 13px;
  color: var(--text-primary, #030813);
  cursor: pointer;
  transition: border-color .15s;
}
.zx-doma-toolbar__sort .zx-filter-select:hover { border-color: var(--text-primary, #030813); }

@media (max-width: 640px){
  .zx-doma-filter { padding: 20px; border-radius: 20px; }
  .zx-doma-toolbar { flex-direction: column; align-items: stretch; }
  .zx-doma-toolbar__sort { justify-content: space-between; }
  .zx-doma-filter__chips { padding-top: 16px; margin-top: 16px; }
  .zx-doma-filter__reset { margin-left: 0; }
  .zx-trustbar--top { padding: 14px 16px; gap: 8px 20px; border-radius: 16px; }
  .zx-trustbar--top .zx-trustbar__item { font-size: 13px; }
}
</style>

<script>
(function(){
  var section = document.querySelector('form.zx-doma-filter');
  if(!section) return;

  var state = {
    material: '',
    floors: '',
    area: '',
    priceMin: null,
    priceMax: null,
    quick: {},
    sort: 'price-asc'
  };

  var items = Array.prototype.slice.call(document.querySelectorAll('#zxItems .zx-proj-card'));
  var container = document.getElementById('zxItems');
  var countEl = document.getElementById('zxCount');
  var countWord = document.getElementById('zxCountWord');
  var countTop = document.getElementById('zxCountTop');
  var countTopWord = document.getElementById('zxCountTopWord');
  var emptyEl = document.getElementById('zxEmpty');

  var priceMinHidden = document.getElementById('zxPriceMin');
  var priceMaxHidden = document.getElementById('zxPriceMax');

  function declProjects(n){
    var n10 = n % 10, n100 = n % 100;
    if (n10 === 1 && n100 !== 11) return 'проект';
    if (n10 >= 2 && n10 <= 4 && (n100 < 10 || n100 >= 20)) return 'проекта';
    return 'проектов';
  }

  function matchFilters(el){
    var price = parseFloat(el.dataset.price)||0;
    var area  = parseFloat(el.dataset.square)||0;
    var floors = String(el.dataset.floors||'');
    var mat = el.dataset.material||'';
    var ready = el.dataset.ready === 'yes';
    var inprocess = el.dataset.inprocess === 'yes';

    if(state.material && mat !== state.material) return false;
    if(state.floors && floors !== state.floors) return false;
    if(state.area){
      var parts = state.area.split('-');
      var aMin = parseFloat(parts[0])||0, aMax = parseFloat(parts[1])||99999;
      if(area < aMin || area > aMax) return false;
    }
    if(state.priceMin !== null && price < state.priceMin) return false;
    if(state.priceMax !== null && price > state.priceMax) return false;

    if(state.quick.ready && !ready) return false;
    if(state.quick.inprocess && !inprocess) return false;
    if(state.quick.floor1 && floors !== '1') return false;
    if(state.quick.floor2 && floors !== '2') return false;
    if(state.quick.price5 && (price===0 || price > 5000000)) return false;
    if(state.quick.price8 && (price===0 || price > 8000000)) return false;
    return true;
  }

  function render(){
    var list = items.slice().filter(matchFilters);
    list.sort(function(a,b){
      var ap = parseFloat(a.dataset.price)||0, bp = parseFloat(b.dataset.price)||0;
      var aa = parseFloat(a.dataset.square)||0, ba = parseFloat(b.dataset.square)||0;
      switch(state.sort){
        case 'price-asc': return (ap||Infinity) - (bp||Infinity);
        case 'price-desc': return bp - ap;
        case 'area-asc': return aa - ba;
        case 'area-desc': return ba - aa;
      }
      return 0;
    });
    items.forEach(function(el){ el.style.display = 'none'; });
    list.forEach(function(el){ el.style.display = ''; container.appendChild(el); });
    var n = list.length;
    if(countEl) countEl.textContent = n;
    if(countTop) countTop.textContent = n;
    var w = declProjects(n);
    if(countWord) countWord.textContent = w;
    if(countTopWord) countTopWord.textContent = w;
    if(emptyEl) emptyEl.style.display = n ? 'none' : '';
  }

  // Material dropdown (c-sel--label__ROAD)
  section.querySelectorAll('.c-sel--label__ROAD').forEach(function(lbl){
    lbl.addEventListener('click', function(){
      if(lbl.dataset.zxKey === 'material'){
        state.material = lbl.dataset.zxVal || '';
        render();
      }
    });
  });

  // Floors toggle chips — clicking same chip deselects
  section.querySelectorAll('.zx-floor-chip').forEach(function(btn){
    btn.addEventListener('click', function(){
      var val = btn.dataset.zxFloor;
      if(state.floors === val){
        state.floors = '';
        btn.classList.remove('is-active');
      } else {
        state.floors = val;
        section.querySelectorAll('.zx-floor-chip').forEach(function(b){ b.classList.remove('is-active'); });
        btn.classList.add('is-active');
      }
      render();
    });
  });

  // Area radios inside c-sel--div__ROAD2
  section.querySelectorAll('input[name="zxArea"]').forEach(function(inp){
    inp.addEventListener('change', function(){
      state.area = inp.value;
      render();
    });
  });

  // Price range — main.js writes final values to hidden inputs .min-price.price-sotka / .max-price.price-sotka
  function fmtNum(n){
    n = Math.round(n);
    return String(n).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
  }
  function readPriceRange(){
    var minV = priceMinHidden && priceMinHidden.value !== '' ? parseFloat(priceMinHidden.value) : null;
    var maxV = priceMaxHidden && priceMaxHidden.value !== '' ? parseFloat(priceMaxHidden.value) : null;
    state.priceMin = (minV !== null && !isNaN(minV)) ? minV : null;
    state.priceMax = (maxV !== null && !isNaN(maxV)) ? maxV : null;
    var spanMin = section.querySelector('.c-sel--span__RANGE.min');
    var spanMax = section.querySelector('.c-sel--span__RANGE.max');
    if(spanMin && state.priceMin !== null) spanMin.textContent = fmtNum(state.priceMin);
    if(spanMax && state.priceMax !== null) spanMax.textContent = fmtNum(state.priceMax);
    render();
  }
  if(priceMinHidden) priceMinHidden.addEventListener('change', readPriceRange);
  if(priceMaxHidden) priceMaxHidden.addEventListener('change', readPriceRange);

  // Quick preset chips
  section.querySelectorAll('.zx-preset-chip[data-zx-quick]').forEach(function(lbl){
    var input = lbl.querySelector('input[type="checkbox"]');
    if(!input) return;
    input.addEventListener('change', function(){
      var key = lbl.dataset.zxQuick;
      state.quick[key] = input.checked;
      render();
    });
  });

  // Sort
  var sortEl = document.getElementById('zxSort');
  if(sortEl) sortEl.addEventListener('change', function(){ state.sort = this.value; render(); });

  // Reset
  function resetFilter(){
    state = { material:'', floors:'', area:'', priceMin:null, priceMax:null, quick:{}, sort: state.sort };
    // Clear UI
    section.querySelectorAll('.c-sel--label__ROAD').forEach(function(l){ l.classList.remove('__c-sel--label__ROAD__CHECKED'); });
    var matDefault = section.querySelector('.c-sel--label__ROAD[data-zx-val=""]');
    if(matDefault) matDefault.classList.add('__c-sel--label__ROAD__CHECKED');
    var matBtn = section.querySelector('.c-sel--button__ROAD span'); if(matBtn) matBtn.textContent = 'Не важно';

    section.querySelectorAll('.zx-floor-chip').forEach(function(b){ b.classList.remove('is-active'); });

    section.querySelectorAll('input[name="zxArea"]').forEach(function(i){ i.checked = (i.value === ''); });
    section.querySelectorAll('.c-sel--label__ROAD2').forEach(function(l){ l.classList.remove('__c-sel--label__ROAD__CHECKED2'); });
    var areaDefault = section.querySelector('.c-sel--label__ROAD2');
    if(areaDefault) areaDefault.classList.add('__c-sel--label__ROAD__CHECKED2');
    var areaBtn = section.querySelector('.c-sel--button__ROAD2 span'); if(areaBtn) areaBtn.textContent = 'Не важно';

    var rMin = section.querySelector('.c-sel--input__RANGE.min');
    var rMax = section.querySelector('.c-sel--input__RANGE.max');
    var sMin = section.querySelector('.c-sel--span__RANGE.min');
    var sMax = section.querySelector('.c-sel--span__RANGE.max');
    if(rMin) rMin.value = 0;
    if(rMax) rMax.value = 100;
    if(sMin) sMin.textContent = sMin.getAttribute('data-from');
    if(sMax) sMax.textContent = sMax.getAttribute('data-to');
    if(priceMinHidden) priceMinHidden.value = '';
    if(priceMaxHidden) priceMaxHidden.value = '';

    section.querySelectorAll('.zx-preset-chip input[type="checkbox"]').forEach(function(i){ i.checked = false; });

    render();
  }
  var resetBottom = document.getElementById('zxResetBottom');
  if(resetBottom) resetBottom.addEventListener('click', function(e){ e.preventDefault(); resetFilter(); });

  render();
})();
</script>
