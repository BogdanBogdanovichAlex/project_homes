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

    // Собираем галерею: DETAIL_PICTURE + PREVIEW_PICTURE + GALLERY_TOP + GALLERY_BOTTOM (до 10 фото, без дубликатов)
    $gallerySrcs = [];
    foreach([
        $arItem['DETAIL_PICTURE']['SRC'] ?? null,
        $arItem['PREVIEW_PICTURE']['SRC'] ?? null,
    ] as $src){
        if($src && !in_array($src, $gallerySrcs, true)) $gallerySrcs[] = $src;
    }
    foreach(['GALLERY_TOP','GALLERY_BOTTOM'] as $galCode){
        if(!empty($props[$galCode])){
            $fids = is_array($props[$galCode]) ? $props[$galCode] : [$props[$galCode]];
            foreach($fids as $fid){
                if(!$fid) continue;
                $f = CFile::GetFileArray($fid);
                if(!empty($f['SRC']) && !in_array($f['SRC'], $gallerySrcs, true)){
                    $gallerySrcs[] = $f['SRC'];
                    if(count($gallerySrcs) >= 10) break 2;
                }
            }
        }
    }

    if($priceRaw > 0) $pricesAll[] = $priceRaw;

    $cards[] = [
        'id' => $arItem['ID'], 'editArea' => $this->GetEditAreaId($arItem['ID']),
        'name' => $arItem['NAME'], 'url' => $arItem['DETAIL_PAGE_URL'],
        'preview' => $arItem['PREVIEW_TEXT'] ?? '', 'img' => $imgSrc, 'gallery' => $gallerySrcs,
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

    <?
    $catalogCfg = function_exists('zemex_get_homes_settings') ? zemex_get_homes_settings('catalog') : [];
    $trustLines = !empty($catalogCfg['TRUST_LINES']) && is_array($catalogCfg['TRUST_LINES']) ? $catalogCfg['TRUST_LINES'] : [
        'Фикс. цена | в договоре',
        'Ипотека от 6% | партнёрские ставки',
        '5 лет гарантии | на конструктив',
        'Сроки в договоре | от 90 дней',
    ];
    ?>
    <div class="zx-trustbar zx-trustbar--inline zx-trustbar--top">
      <?foreach($trustLines as $line):
        $parts = function_exists('zemex_split_pipe') ? zemex_split_pipe($line, 2) : array_map('trim', explode('|', $line, 2));
        $bold = $parts[0] ?? '';
        $tail = $parts[1] ?? '';
      ?>
      <div class="zx-trustbar__item"><span class="zx-trustbar__icon">✓</span><span><b><?=htmlspecialchars($bold)?></b><?=$tail ? ' '.htmlspecialchars($tail) : ''?></span></div>
      <?endforeach?>
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
        <div class="zx-img-ph zx-proj-card__img<?= count($c['gallery']) > 1 ? ' zx-proj-card__img--gallery' : '' ?>">
          <?if(!empty($c['gallery'])):?>
            <div class="zx-proj-card__slides js-proj-slides">
              <?foreach($c['gallery'] as $gi => $gsrc):?>
                <img src="<?=$gsrc?>" alt="<?=htmlspecialchars($c['name'])?>" loading="<?= $gi === 0 ? 'lazy' : 'lazy' ?>" class="zx-proj-card__slide<?= $gi === 0 ? ' is-active' : '' ?>" data-idx="<?=$gi?>">
              <?endforeach?>
            </div>
            <?if(count($c['gallery']) > 1):?>
              <button type="button" class="zx-proj-card__slide-nav zx-proj-card__slide-nav--prev js-proj-prev" aria-label="Предыдущее фото" onclick="event.preventDefault();event.stopPropagation();">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M9 11L5 7l4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
              </button>
              <button type="button" class="zx-proj-card__slide-nav zx-proj-card__slide-nav--next js-proj-next" aria-label="Следующее фото" onclick="event.preventDefault();event.stopPropagation();">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M5 3l4 4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
              </button>
              <div class="zx-proj-card__slide-dots js-proj-dots">
                <?foreach($c['gallery'] as $gi => $gsrc):?>
                  <button type="button" class="zx-proj-card__slide-dot<?= $gi === 0 ? ' is-active' : '' ?>" data-idx="<?=$gi?>" aria-label="Фото <?=$gi+1?>" onclick="event.preventDefault();event.stopPropagation();"></button>
                <?endforeach?>
              </div>
            <?endif?>
          <?endif?>
          <div class="zx-proj-card__badges">
            <span class="zx-chip <?=$statusClass?>"><span class="zx-chip__dot"></span><?=$statusLabel?></span>
          </div>
          <button type="button" class="zx-proj-card__fav" aria-label="В избранное" onclick="event.preventDefault();event.stopPropagation();this.classList.toggle('is-active');">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
          </button>
        </div>
        <div class="zx-proj-card__body">
          <h3 class="zx-proj-card__title font__HEADING_CARD_TITLE"><?=htmlspecialchars($c['name'])?></h3>
          <?if(!empty($c['preview'])):?>
            <p class="zx-proj-card__sub"><?=strip_tags($c['preview'])?></p>
          <?endif?>
          <div class="zx-proj-card__meta">
            <?if($c['square']):?>
              <span class="zx-proj-card__meta-item">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3h18v18H3z"/><path d="M3 9h3M3 15h3M9 3v3M15 3v3"/></svg>
                <span><?=$c['square']?> м²</span>
              </span>
            <?endif?>
            <?if($c['floors']):?>
              <span class="zx-proj-card__meta-item">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21V10l9-6 9 6v11"/><path d="M3 14h18"/></svg>
                <span><?=$c['floors']?> эт.</span>
              </span>
            <?endif?>
            <?if($c['material']):?>
              <span class="zx-proj-card__meta-item">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18v4H3zM3 14h18v4H3z"/><path d="M8 6v4M14 6v4M6 14v4M12 14v4M18 14v4"/></svg>
                <span><?=htmlspecialchars($c['material'])?></span>
              </span>
            <?endif?>
          </div>
          <div class="zx-proj-card__price-row">
            <div class="zx-proj-card__price zx-mono"><?=$c['priceRaw'] ? 'от '.zx_price_short($c['priceRaw']) : zx_price_short($c['priceRaw'])?></div>
            <?php $monthly = $c['priceRaw'] ? round($c['priceRaw'] * 0.0072 / 1000) * 1000 : 0; ?>
            <?if($monthly):?>
              <div class="zx-proj-card__mortgage" title="Расчётный ежемесячный платёж по ипотеке, ставка от 6%, первый взнос 20%, срок 30 лет">
                <span class="zx-proj-card__mortgage-label">в ипотеку</span>
                от <?=number_format($monthly,0,',',' ')?> ₽/мес
              </div>
            <?endif?>
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
.zx-doma-wrap { padding-top: 24px; padding-bottom: 32px; }
@media(min-width:992px){ .zx-doma-wrap { padding-top: 32px; padding-bottom: 40px; } }

/* Trustbar — top: мини-USP-панель, «лёжит» на нижней кромке hero */
.zx-trustbar--top {
  position: relative;
  z-index: 3;
  margin: -80px 0 40px;
  padding: 18px 28px;
  background: #ffffff;
  border: 1px solid #E6EAF0;
  border-radius: 20px;
  box-shadow: 0 14px 34px rgba(7, 23, 53, .12);
  display: flex;
  flex-wrap: wrap;
  align-items: stretch;
  gap: 0;
}
.zx-trustbar--top .zx-trustbar__item {
  flex: 1 1 0;
  min-width: 220px;
  display: inline-flex;
  align-items: center;
  gap: 12px;
  padding: 6px 24px;
  border-left: 1px solid #EDF1F5;
  font-size: 15px;
  line-height: 1.4;
  color: var(--text-secondary,#6F737A);
}
.zx-trustbar--top .zx-trustbar__item:first-child { border-left: 0; padding-left: 0; }
.zx-trustbar--top .zx-trustbar__item:last-child  { padding-right: 0; }
.zx-trustbar--top .zx-trustbar__item b { color: var(--text-primary,#11181C); font-weight: 600; }
.zx-trustbar--top .zx-trustbar__icon {
  display: inline-flex;
  width: 24px; height: 24px;
  align-items: center; justify-content: center;
  color: #fff;
  background: #00BF3F;
  border-radius: 50%;
  font-size: 13px;
  font-weight: 700;
  flex-shrink: 0;
  box-shadow: 0 2px 6px rgba(0, 191, 63, .22);
}
@media (max-width: 991px) {
  .zx-trustbar--top .zx-trustbar__item {
    flex: 1 1 calc(50% - 1px);
    border-left: 0;
    padding: 6px 0;
  }
}

/* Filter card */
.zx-doma-filter {
  margin: 0 0 16px;
  padding: 20px 22px;
  background: #fff;
  border: 1px solid #ECECEC;
  border-radius: 16px;
  box-shadow: 0 1px 2px rgba(16,24,40,.04);
}

.zx-doma-filter__row {
  display: grid;
  grid-template-columns: 1fr;
  gap: 14px 18px;
  align-items: start;
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
  font-family: 'PT Sans', sans-serif;
  font-size: 14px;
  line-height: 1.2;
  color: #6F737A;
  letter-spacing: .01em;
  font-weight: 400;
}
.zx-doma-filter .c-sel--label__RANGE { font-family: 'PT Sans', sans-serif; font-size: 14px; color: #6F737A; font-weight: 400; line-height: 1.2; letter-spacing: .01em; }
.zx-doma-filter .c-sel--label__RANGE .zx-range-box,
.zx-doma-filter .c-sel--label__RANGE .zx-range-sliders { margin-top: 8px; }

/* Dropdown buttons (Material, Area) */
.zx-doma-filter .c-sel--button__ROAD,
.zx-doma-filter .c-sel--button__ROAD2 {
  width: 100%;
  padding: 0 14px;
  height: 46px;
  box-sizing: border-box;
  border: 1px solid #E5E7EB;
  border-radius: 12px;
  background: #fff;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 8px;
  color: var(--text-primary,#11181C);
  font-family: 'Montserrat', sans-serif;
  font-size: 15px;
  font-weight: 400;
  cursor: pointer;
  transition: border-color .15s;
}
.zx-doma-filter .c-sel--button__ROAD span,
.zx-doma-filter .c-sel--button__ROAD2 span,
.zx-doma-filter .zx-range-box,
.zx-doma-filter .zx-range-val,
.zx-doma-filter .zx-range-val b {
  font-family: 'Montserrat', sans-serif;
}
.zx-doma-filter .c-sel--button__ROAD:hover,
.zx-doma-filter .c-sel--button__ROAD2:hover { border-color: #C7CCD4; }
.zx-doma-filter .c-sel--img__ROAD,
.zx-doma-filter .c-sel--img__ROAD2 { width: 14px; height: 14px; opacity: .6; }

/* Floors toggle chips */
.zx-doma-filter .zx-field--floors { }
.zx-doma-filter .zx-floors-chips { display: flex; gap: 8px; }
.zx-doma-filter button.zx-floor-chip {
  flex: 1 1 auto;
  min-width: 56px;
  height: 46px;
  padding: 0 16px;
  border: 1.5px solid #CDD2DB !important;
  border-radius: 12px !important;
  background: #fff !important;
  font-size: 15px;
  font-weight: 500;
  color: var(--text-primary,#11181C);
  cursor: pointer;
  transition: all .15s;
  font-family: inherit;
  appearance: none;
  -webkit-appearance: none;
  box-sizing: border-box;
}
.zx-doma-filter button.zx-floor-chip:hover {
  border-color: #00BF3F !important;
  color: #00BF3F;
}
.zx-doma-filter button.zx-floor-chip.is-active {
  background: #00BF3F !important;
  border-color: #00BF3F !important;
  color: #fff;
  font-weight: 600;
  box-shadow: 0 2px 6px rgba(0,191,63,.25);
}

/* Price range */
.zx-field--range { }
.zx-range-box {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  height: 46px;
  padding: 0 14px;
  border: 1px solid #E5E7EB;
  border-radius: 12px;
  background: #fff;
  font-size: 15px;
  color: #8A8F99;
  white-space: nowrap;
  transition: border-color .15s;
}
.zx-range-box:hover { border-color: #C7CCD4; }
.zx-range-val { display: inline-flex; align-items: baseline; gap: 6px; min-width: 0; overflow: hidden; text-overflow: ellipsis; }
.zx-range-val b { color: var(--text-primary,#11181C); font-weight: 500; font-feature-settings: "tnum"; }
.zx-range-dash { color: #C7CCD4; flex-shrink: 0; }

.zx-range-sliders { position: relative; height: 18px; margin-top: 8px; padding: 0 6px; }
.zx-range-sliders .c-sel--input__RANGE {
  position: absolute;
  left: 6px; right: 6px;
  width: calc(100% - 12px);
  margin: 0;
  top: 8px;
  bottom: auto;
  height: 2px;
}
.zx-range-sliders .c-sel--input__RANGE.max {
  top: 8px;
  background: transparent;
}

/* Area dropdown popup — keep within card */
.zx-doma-filter .c-sel--fieldset__ROAD2 { position: relative; }
.zx-doma-filter .c-sel--div__ROAD2 { width: 100%; left: 0; right: 0; top: calc(100% + 4px); }

/* Preset chips */
.zx-doma-filter__chips {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px dashed #EFEFEF;
  align-items: center;
}
.zx-preset-chip {
  position: relative;
  display: inline-flex;
  align-items: center;
  height: 32px;
  padding: 0 14px;
  border: 1px solid #E5E7EB;
  border-radius: 999px;
  background: #fff;
  font-size: 13px;
  color: var(--text-primary,#11181C);
  cursor: pointer;
  user-select: none;
  transition: background .15s, border-color .15s, color .15s;
}
.zx-preset-chip input { position: absolute; opacity: 0; pointer-events: none; }
.zx-preset-chip:hover { border-color: #C7CCD4; }
.zx-preset-chip:has(input:checked) { background: #00BF3F; border-color: #00BF3F; color: #fff; }

.zx-doma-filter__reset {
  margin-left: auto;
  border: 0;
  background: transparent;
  color: #8A8F99;
  font-size: 13px;
  cursor: pointer;
  padding: 6px 10px;
  border-radius: 8px;
}
.zx-doma-filter__reset:hover { color: var(--text-primary,#11181C); background: #F4F6F9; }

/* Toolbar */
.zx-doma-toolbar { display:flex; align-items:center; justify-content:space-between; gap:16px; margin: 20px 0 16px; flex-wrap:wrap; }
.zx-doma-toolbar__result { font-size: 14px; color: var(--text-secondary,#6F737A); }
.zx-doma-toolbar__result b { color: var(--text-primary, #11181C); font-weight: 600; }
.zx-doma-toolbar__sort { display:flex; align-items:center; gap:8px; color: #8A8F99; font-size: 13px; }
.zx-doma-toolbar__sort .zx-filter-select { border: 1px solid #E5E7EB; background:#fff; border-radius: 10px; padding: 7px 10px; font-size: 13px; color: var(--text-primary,#11181C); cursor:pointer; }

@media (max-width: 640px){
  .zx-doma-filter { padding: 16px; }
  .zx-doma-toolbar { flex-direction: column; align-items: stretch; }
  .zx-doma-toolbar__sort { justify-content: space-between; }
  .zx-doma-filter__chips { padding-top: 12px; margin-top: 12px; }
  .zx-doma-filter__reset { margin-left: 0; }
  .zx-trustbar--top { margin: -32px 0 24px; padding: 14px 16px; border-radius: 16px; }
  .zx-trustbar--top .zx-trustbar__item { font-size: 14px; min-width: 0; flex: 1 1 100%; padding: 4px 0; }
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
      var parentLbl = inp.closest('.c-sel--label__ROAD2');
      // Обновляем класс «выбранного» элемента
      section.querySelectorAll('.c-sel--label__ROAD2').forEach(function(l){ l.classList.remove('__c-sel--label__ROAD__CHECKED2'); });
      if(parentLbl) parentLbl.classList.add('__c-sel--label__ROAD__CHECKED2');
      // Обновляем текст кнопки селекта
      var btnSpan = section.querySelector('.c-sel--button__ROAD2 span');
      if(btnSpan && parentLbl){
        btnSpan.textContent = (parentLbl.textContent || '').trim() || 'Не важно';
      }
      // Закрываем дропдаун (убираем класс открытия у родительского fieldset)
      var fs = inp.closest('.c-sel--fieldset__ROAD2');
      if(fs) fs.classList.remove('is-open','opened','__c-sel--fieldset__ROAD2__OPEN');
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

<style>
/* Мини-галерея внутри карточки проекта */
.zx-proj-card__img--gallery { position: relative; }
.zx-proj-card__slides {
  position: absolute; inset: 0;
  display: block;
}
.zx-proj-card__slide {
  position: absolute; inset: 0;
  width: 100%; height: 100%;
  object-fit: cover;
  opacity: 0;
  transition: opacity .25s ease;
  pointer-events: none;
}
.zx-proj-card__slide.is-active { opacity: 1; }

/* Навигация */
.zx-proj-card__slide-nav {
  position: absolute; top: 50%; transform: translateY(-50%);
  width: 40px; height: 40px;
  display: flex; align-items: center; justify-content: center;
  background: #fff;
  color: #11181C;
  border: none;
  border-radius: 50%;
  cursor: pointer;
  opacity: .95;
  transition: opacity .2s, background .2s, transform .2s, box-shadow .2s;
  z-index: 3;
  box-shadow: 0 4px 14px rgba(7,23,53,.22);
  padding: 0;
}
.zx-proj-card__slide-nav svg { width: 18px; height: 18px; }
.zx-proj-card__slide-nav--prev { left: 12px; }
.zx-proj-card__slide-nav--next { right: 12px; }
.zx-proj-card__slide-nav:hover {
  background: #00BF3F; color: #fff; opacity: 1;
  transform: translateY(-50%) scale(1.06);
  box-shadow: 0 6px 18px rgba(0,191,63,.35);
}
.zx-proj-card__slide-nav:active { transform: translateY(-50%) scale(.95); }
.zx-proj-card__slide-nav[disabled] { opacity: 0; pointer-events: none; }

/* Точки */
.zx-proj-card__slide-dots {
  position: absolute;
  bottom: 12px; left: 50%;
  transform: translateX(-50%);
  display: flex; gap: 6px;
  z-index: 3;
  padding: 6px 10px;
  background: rgba(7, 23, 53, .28);
  border-radius: 999px;
  backdrop-filter: blur(6px);
}
.zx-proj-card__slide-dot {
  width: 6px; height: 6px;
  border-radius: 50%;
  background: rgba(255,255,255,.55);
  border: 0;
  padding: 0;
  cursor: pointer;
  transition: background .2s, width .2s;
}
.zx-proj-card__slide-dot.is-active { background: #fff; width: 18px; border-radius: 999px; }
</style>

<script>
(function(){
  function initCard(card){
    // Убираем битые слайды и лишние точки (после всех ошибок загрузки)
    var slidesAll = Array.prototype.slice.call(card.querySelectorAll('.zx-proj-card__slide'));
    var dotsWrap = card.querySelector('.js-proj-dots');
    var dotsAll = dotsWrap ? Array.prototype.slice.call(dotsWrap.querySelectorAll('.zx-proj-card__slide-dot')) : [];

    var prev = card.querySelector('.js-proj-prev');
    var next = card.querySelector('.js-proj-next');

    function currentList(){ return slidesAll.filter(function(s){ return !s.dataset.broken; }); }
    function currentDots(){ return dotsAll.filter(function(d, k){ return slidesAll[k] && !slidesAll[k].dataset.broken; }); }

    function update(){
      var slides = currentList();
      var dots = currentDots();
      dotsAll.forEach(function(d, k){ d.style.display = (slidesAll[k] && slidesAll[k].dataset.broken) ? 'none' : ''; });
      if(slides.length <= 1){
        if(prev) prev.style.display = 'none';
        if(next) next.style.display = 'none';
        if(dotsWrap) dotsWrap.style.display = 'none';
        card.classList.remove('zx-proj-card__img--gallery');
      } else {
        card.classList.add('zx-proj-card__img--gallery');
      }
      // Убедимся, что активный слайд — первый неломанный
      var active = slides.filter(function(s){ return s.classList.contains('is-active'); })[0];
      if(!active && slides.length){
        slidesAll.forEach(function(s){ s.classList.remove('is-active'); });
        slides[0].classList.add('is-active');
        var firstIdx = slidesAll.indexOf(slides[0]);
        dotsAll.forEach(function(d, k){ d.classList.toggle('is-active', k === firstIdx); });
      }
    }

    // Слушаем ошибки загрузки изображений
    slidesAll.forEach(function(s){
      // Форсим eager-загрузку, чтобы сразу знать о битых файлах
      s.loading = 'eager';
      if(s.complete && s.naturalWidth === 0){ s.dataset.broken = '1'; s.style.display = 'none'; }
      s.addEventListener('error', function(){ s.dataset.broken = '1'; s.style.display = 'none'; update(); });
      s.addEventListener('load', function(){ update(); });
    });
    update();

    function go(dir){
      var slides = currentList();
      if(slides.length < 2) return;
      var currentIdx = slides.findIndex(function(s){ return s.classList.contains('is-active'); });
      if(currentIdx < 0) currentIdx = 0;
      var nextIdx = (currentIdx + dir + slides.length) % slides.length;
      slidesAll.forEach(function(s){ s.classList.remove('is-active'); });
      slides[nextIdx].classList.add('is-active');
      var targetSlideAllIdx = slidesAll.indexOf(slides[nextIdx]);
      dotsAll.forEach(function(d, k){ d.classList.toggle('is-active', k === targetSlideAllIdx); });
    }

    if(prev) prev.addEventListener('click', function(e){ e.preventDefault(); e.stopPropagation(); go(-1); });
    if(next) next.addEventListener('click', function(e){ e.preventDefault(); e.stopPropagation(); go(1); });
    dotsAll.forEach(function(dot){
      dot.addEventListener('click', function(e){
        e.preventDefault(); e.stopPropagation();
        var target = parseInt(dot.dataset.idx, 10) || 0;
        if(slidesAll[target] && !slidesAll[target].dataset.broken){
          slidesAll.forEach(function(s){ s.classList.remove('is-active'); });
          slidesAll[target].classList.add('is-active');
          dotsAll.forEach(function(d, k){ d.classList.toggle('is-active', k === target); });
        }
      });
    });

    // Свайп
    var startX = null;
    card.addEventListener('touchstart', function(e){ startX = e.touches[0].clientX; }, {passive: true});
    card.addEventListener('touchend', function(e){
      if(startX === null) return;
      var dx = e.changedTouches[0].clientX - startX;
      if(Math.abs(dx) > 40) { go(dx < 0 ? 1 : -1); }
      startX = null;
    });
  }
  document.querySelectorAll('.zx-proj-card__img--gallery').forEach(initCard);
})();
</script>
