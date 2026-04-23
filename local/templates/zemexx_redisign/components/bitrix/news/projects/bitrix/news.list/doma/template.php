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
  <section class="c-sel zx-doma-filter">
    <div class="c-sel--div__CONTAINER">
      <div class="c-sel__BACK"></div>
      <form name="zxFilterForm" action="javascript:void(0)" class="smartfilter c-sel--form__FILTER" onsubmit="return false;">
        <div class="c-sel--div__FILTER_TOP">
          <h3 class="font__HEADING_SECTION_TITLE">Подбор проекта</h3>
          <label class="c-sel--label__ADD font__BODY_TEXT_PRIMARY" id="zxResetTop">
            Сбросить
            <input class="c-sel--input__RESET" type="reset" style="display:none;">
          </label>
        </div>

        <div class="c-sel--div__ZERO">
          <div class="c-sel--div__FIRST">

            <fieldset class="c-sel--fieldset__ROAD bx-filter-parameters-box bx-filter-select-container">
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
            <fieldset class="c-sel--fieldset__DIST bx-filter-parameters-box">
              <span class="bx-filter-container-modef"></span>
              <legend class="font__BODY_TEXT_CAPTION">Этажность</legend>
              <label class="c-sel--label__DIST font__BODY_TEXT_PRIMARY">
                1<input type="radio" name="zxFloors" value="1">
              </label>
              <label class="c-sel--label__DIST font__BODY_TEXT_PRIMARY">
                2<input type="radio" name="zxFloors" value="2">
              </label>
              <input class="c-sel--input__DIST_HIDDEN" style="display:none;" type="radio" name="zxFloors" checked>
              <input class="min-price from-mkad" type="hidden" name="zxFloorsMin" id="zxFloorsMin" value="">
              <input class="max-price from-mkad" type="hidden" name="zxFloorsMax" id="zxFloorsMax" value="">
            </fieldset>
            <?endif?>

          </div>

          <div class="c-sel--div__SECOND">

            <label class="c-sel--label__RANGE font__BODY_TEXT_CAPTION bx-filter-parameters-box">
              <span class="bx-filter-container-modef"></span>
              Цена, ₽
              <div class="c-sel--p__RANGE font__BODY_TEXT_PRIMARY">
                <p>от <span class="c-sel--span__RANGE min" data-from="<?=$priceMin?>"><?=number_format($priceMin,0,',',' ')?></span></p>
                <span>&#8212;</span>
                <p>до <span class="c-sel--span__RANGE max" data-to="<?=$priceMax?>"><?=number_format($priceMax,0,',',' ')?></span></p>
              </div>
              <input class="c-sel--input__RANGE min" type="range" name="zxPriceMinR" min="0" max="100" value="0">
              <input class="c-sel--input__RANGE max" type="range" name="zxPriceMaxR" min="0" max="100" value="100">
              <input class="min-price price-sotka" type="hidden" name="zxPriceMin" id="zxPriceMin" value="">
              <input class="max-price price-sotka" type="hidden" name="zxPriceMax" id="zxPriceMax" value="">
            </label>

            <fieldset class="c-sel--fieldset__ROAD2 bx-filter-parameters-box">
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
        </div>

        <fieldset class="c-sel--fieldset__THIRD bx-filter-parameters-box bx-active">
          <span class="bx-filter-container-modef"></span>
          <legend class="c-sel--legend__THIRD font__BODY_TEXT_CAPTION">Дополнительно</legend>
          <label class="c-sel--label__THIRD font__BODY_TEXT_PRIMARY bx-filter-param-label" data-zx-quick="ready">
            Готовые сейчас<input type="checkbox" value="Y">
          </label>
          <label class="c-sel--label__THIRD font__BODY_TEXT_PRIMARY bx-filter-param-label" data-zx-quick="inprocess">
            Идёт строительство<input type="checkbox" value="Y">
          </label>
          <label class="c-sel--label__THIRD font__BODY_TEXT_PRIMARY bx-filter-param-label" data-zx-quick="floor1">
            Одноэтажные<input type="checkbox" value="Y">
          </label>
          <label class="c-sel--label__THIRD font__BODY_TEXT_PRIMARY bx-filter-param-label" data-zx-quick="floor2">
            Двухэтажные<input type="checkbox" value="Y">
          </label>
          <label class="c-sel--label__THIRD font__BODY_TEXT_PRIMARY bx-filter-param-label" data-zx-quick="price5">
            До 5 млн ₽<input type="checkbox" value="Y">
          </label>
          <label class="c-sel--label__THIRD font__BODY_TEXT_PRIMARY bx-filter-param-label" data-zx-quick="price8">
            До 8 млн ₽<input type="checkbox" value="Y">
          </label>
        </fieldset>

        <div class="c-sel--div__ADD">
          <label class="c-sel--label__ADD font__BODY_TEXT_PRIMARY" id="zxResetBottom">
            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M0 16C0 7.16344 7.16344 0 16 0C24.8366 0 32 7.16344 32 16C32 24.8366 24.8366 32 16 32C7.16344 32 0 24.8366 0 16Z" fill="#F4F6F9"/>
              <path class="c-sel--path" fill-rule="evenodd" clip-rule="evenodd" d="M11.3008 11.3003C10.9831 11.618 10.9831 12.133 11.3008 12.4507L14.8499 15.9999L11.3008 19.549C10.9831 19.8667 10.9831 20.3818 11.3008 20.6995C11.6185 21.0172 12.1335 21.0172 12.4512 20.6995L16.0004 17.1503L19.5495 20.6995C19.8672 21.0172 20.3823 21.0172 20.7 20.6995C21.0177 20.3818 21.0177 19.8667 20.7 19.549L17.1508 15.9999L20.7 12.4507C21.0177 12.133 21.0177 11.618 20.7 11.3003C20.3823 10.9826 19.8672 10.9826 19.5495 11.3003L16.0004 14.8494L12.4512 11.3003C12.1335 10.9826 11.6185 10.9826 11.3008 11.3003Z" fill="#5A6C7C"/>
            </svg>
            Очистить <span>фильтр</span>
          </label>
        </div>
        <div class="c-sel--div__ADD2">
          <button class="c-sel--button__CLOSE2 font__BUTTONS_BUTTON" type="button" id="zxShowBtn">
            Показать <span id="zxCount"><?=$total?></span> <span id="zxCountWord">проектов</span>
          </button>
        </div>

      </form>
    </div>
  </section>

  <div class="zx-trustbar">
    <div class="c-sel--div__CONTAINER">
      <div class="zx-trustbar__row">
        <div class="zx-trustbar__item"><span class="zx-trustbar__icon">✓</span><span><b>Фикс. цена</b> в договоре</span></div>
        <div class="zx-trustbar__item"><span class="zx-trustbar__icon">✓</span><span><b>Ипотека от 6%</b> · партнёрские ставки</span></div>
        <div class="zx-trustbar__item"><span class="zx-trustbar__icon">✓</span><span><b>5 лет гарантии</b> на конструктив</span></div>
        <div class="zx-trustbar__item"><span class="zx-trustbar__icon">✓</span><span><b>Сроки в договоре</b> · от 90 дней</span></div>
      </div>
    </div>
  </div>

  <div class="c-sel--div__CONTAINER">
    <div class="zx-doma-toolbar">
      <div class="zx-doma-toolbar__result font__BODY_TEXT_PRIMARY">
        Подобрано <b id="zxCountTop"><?=$total?></b>
        <span id="zxCountTopWord">проектов</span>
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
.zx-doma-filter { margin-bottom: 24px; }
.zx-doma-filter .c-sel--form__FILTER { position: relative; }
.zx-doma-toolbar { display:flex; align-items:center; justify-content:space-between; gap:16px; margin: 12px 0 16px; flex-wrap:wrap; }
.zx-doma-toolbar__result b { color: var(--text-primary, #11181C); }
.zx-doma-toolbar__sort { display:flex; align-items:center; gap:8px; color: var(--text-secondary,#6F737A); }
.zx-doma-toolbar__sort .zx-filter-select { border: 1px solid var(--border-primary,#E5E7EB); background:#fff; border-radius: 10px; padding: 8px 10px; font-size: 14px; color: var(--text-primary,#11181C); cursor:pointer; }
@media (max-width: 640px){
  .zx-doma-toolbar { flex-direction: column; align-items: stretch; }
  .zx-doma-toolbar__sort { justify-content: space-between; }
}
</style>

<script>
(function(){
  var section = document.querySelector('.zx-doma-filter');
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
  var floorsMaxHidden = document.getElementById('zxFloorsMax');

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

  // Floors chips (c-sel--label__DIST) — main.js writes to floorsMaxHidden on change
  if(floorsMaxHidden){
    floorsMaxHidden.addEventListener('change', function(){
      state.floors = floorsMaxHidden.value || '';
      render();
    });
  }

  // Area radios inside c-sel--div__ROAD2
  section.querySelectorAll('input[name="zxArea"]').forEach(function(inp){
    inp.addEventListener('change', function(){
      state.area = inp.value;
      render();
    });
  });

  // Price range — main.js writes final values to hidden inputs .min-price.price-sotka / .max-price.price-sotka
  function readPriceRange(){
    var minV = priceMinHidden && priceMinHidden.value !== '' ? parseFloat(priceMinHidden.value) : null;
    var maxV = priceMaxHidden && priceMaxHidden.value !== '' ? parseFloat(priceMaxHidden.value) : null;
    state.priceMin = (minV !== null && !isNaN(minV)) ? minV : null;
    state.priceMax = (maxV !== null && !isNaN(maxV)) ? maxV : null;
    render();
  }
  if(priceMinHidden) priceMinHidden.addEventListener('change', readPriceRange);
  if(priceMaxHidden) priceMaxHidden.addEventListener('change', readPriceRange);

  // Quick checkbox filters (c-sel--label__THIRD)
  section.querySelectorAll('.c-sel--label__THIRD[data-zx-quick]').forEach(function(lbl){
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

    section.querySelectorAll('.c-sel--label__DIST').forEach(function(l){ l.classList.remove('__c-sel--label__DIST__CHECKED'); });
    section.querySelectorAll('input[name="zxFloors"]').forEach(function(i){ i.checked = false; });
    var hiddenFloors = section.querySelector('.c-sel--input__DIST_HIDDEN'); if(hiddenFloors) hiddenFloors.checked = true;
    if(floorsMaxHidden) floorsMaxHidden.value = '';

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

    section.querySelectorAll('.c-sel--label__THIRD').forEach(function(l){
      l.classList.remove('__c-sel--label__THIRD__CHECKED');
      var i = l.querySelector('input'); if(i) i.checked = false;
    });

    render();
  }
  var resetTop = document.getElementById('zxResetTop');
  var resetBottom = document.getElementById('zxResetBottom');
  if(resetTop) resetTop.addEventListener('click', function(e){ e.preventDefault(); resetFilter(); });
  if(resetBottom) resetBottom.addEventListener('click', function(e){ e.preventDefault(); resetFilter(); });

  // Show button — scroll to results
  var showBtn = document.getElementById('zxShowBtn');
  if(showBtn){
    showBtn.addEventListener('click', function(){
      var grid = document.getElementById('zxItems');
      if(grid) grid.scrollIntoView({behavior:'smooth', block:'start'});
    });
  }

  render();
})();
</script>
