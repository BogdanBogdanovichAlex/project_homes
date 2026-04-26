<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);
?>
<section class="ns-doma" id="doma-items-anchor">
<div class="c-sel--div__CONTAINER">

<div class="doma-intro">
    <div class="doma-intro__left">
        <h2 class="font__HEADING_SECTION_TITLE">Проекты домов</h2>
    </div>
    <div class="doma-intro__right">
        <p class="font__BODY_TEXT_PRIMARY" style="color:var(--text-secondary);">Выбирайте по цене, площади, материалу или готовности. Карточка откроет фотогалерею, планировки и полные характеристики.</p>
    </div>
</div>

<?if($arResult['ITEMS']):?>

<!-- Фильтр -->
<div class="doma-filter" id="doma-filter">
    <div class="doma-filter__row">
        <div class="doma-filter__group">
            <label class="doma-filter__label font__BODY_TEXT_CAPTION">Материал</label>
            <select class="doma-filter__select" id="filter-material">
                <option value="">Все материалы</option>
                <option value="gazobeton">Газобетон</option>
                <option value="derevo">Дерево</option>
            </select>
        </div>
        <div class="doma-filter__group">
            <label class="doma-filter__label font__BODY_TEXT_CAPTION">Цена, ₽</label>
            <div class="doma-filter__range">
                <input class="doma-filter__input" type="number" id="filter-price-min" placeholder="от" min="0">
                <span>—</span>
                <input class="doma-filter__input" type="number" id="filter-price-max" placeholder="до" min="0">
            </div>
        </div>
        <div class="doma-filter__group">
            <label class="doma-filter__label font__BODY_TEXT_CAPTION">Площадь, м²</label>
            <div class="doma-filter__range">
                <input class="doma-filter__input" type="number" id="filter-sq-min" placeholder="от" min="0">
                <span>—</span>
                <input class="doma-filter__input" type="number" id="filter-sq-max" placeholder="до" min="0">
            </div>
        </div>
        <div class="doma-filter__group">
            <label class="doma-filter__label font__BODY_TEXT_CAPTION">Статус</label>
            <div class="doma-filter__status">
                <label class="doma-filter__checkbox font__BODY_TEXT_CAPTION">
                    <input type="checkbox" id="filter-ready"> Готовый дом
                </label>
                <label class="doma-filter__checkbox font__BODY_TEXT_CAPTION">
                    <input type="checkbox" id="filter-inprocess"> Идёт строительство
                </label>
            </div>
        </div>
        <div class="doma-filter__group doma-filter__group--btns">
            <button class="doma-filter__btn font__BUTTONS_BUTTON" id="filter-apply">Найти</button>
            <button class="doma-filter__btn-reset font__BODY_TEXT_CAPTION" id="filter-reset">Сбросить</button>
        </div>
    </div>
</div>

<!-- Сетка больших image-fill карточек -->
<div class="doma-grid" id="doma-items">
<?foreach($arResult["ITEMS"] as $arItem):?>
<?
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
$priceRaw    = isset($props['PRICE']) ? (int)preg_replace('/\D/', '', $props['PRICE']) : 0;
$squareRaw   = isset($props['SQUARE']) ? (float)$props['SQUARE'] : 0;
$isReady     = (!empty($props['READY']) && $props['READY'] == 'Y') ? 'yes' : 'no';
$isInProcess = (!empty($props['IN_PROCESS']) && $props['IN_PROCESS'] == 'Y') ? 'yes' : 'no';
$matXmlId    = isset($props['MATERIAL']['XML_ID']) ? $props['MATERIAL']['XML_ID'] : '';

$imgSrc = '';
if(!empty($arItem['PREVIEW_PICTURE']['SRC'])) $imgSrc = $arItem['PREVIEW_PICTURE']['SRC'];
elseif(!empty($arItem['DETAIL_PICTURE']['SRC'])) $imgSrc = $arItem['DETAIL_PICTURE']['SRC'];
?>
<a class="doma-card" id="<?=$this->GetEditAreaId($arItem['ID']);?>" href="<?=$arItem['DETAIL_PAGE_URL']?>"
     data-material="<?=$matXmlId?>"
     data-price="<?=$priceRaw?>"
     data-square="<?=$squareRaw?>"
     data-ready="<?=$isReady?>"
     data-inprocess="<?=$isInProcess?>"
     <?if($imgSrc):?>style="background-image:linear-gradient(180deg,rgba(3,8,19,0.15) 0%,rgba(3,8,19,0.75) 100%),url('<?=$imgSrc?>');"<?endif?>>

    <div class="doma-card__badges">
        <?if($isReady === 'yes'):?>
            <span class="doma-card__badge doma-card__badge--promo font__BODY_TEXT_CAPTION">Готовый дом</span>
        <?endif?>
        <?if($isInProcess === 'yes'):?>
            <span class="doma-card__badge font__BODY_TEXT_CAPTION">Идёт строительство</span>
        <?endif?>
        <?if(!empty($props['MATERIAL']['VALUE'])):?>
            <span class="doma-card__badge font__BODY_TEXT_CAPTION"><?=$props['MATERIAL']['VALUE']?></span>
        <?endif?>
    </div>

    <div class="doma-card__top">
        <h3 class="doma-card__name font__HEADING_BLOCK_TITLE"><?=$arItem['NAME']?>
            <span class="doma-card__arrow">→</span>
        </h3>
    </div>

    <div class="doma-card__bottom">
        <div class="doma-card__props">
            <?if(!empty($props['SQUARE'])):?>
            <div class="doma-card__prop">
                <div class="doma-card__prop-label font__BODY_TEXT_CAPTION">Площадь</div>
                <div class="doma-card__prop-value font__HEADING_CARD_TITLE"><?=$props['SQUARE']?> <span>м²</span></div>
            </div>
            <?endif?>
            <?if(!empty($props['FLOORS'])):?>
            <div class="doma-card__prop">
                <div class="doma-card__prop-label font__BODY_TEXT_CAPTION">Этажность</div>
                <div class="doma-card__prop-value font__HEADING_CARD_TITLE"><?=$props['FLOORS']?></div>
            </div>
            <?endif?>
            <?if(!empty($props['BEDROOMS'])):?>
            <div class="doma-card__prop">
                <div class="doma-card__prop-label font__BODY_TEXT_CAPTION">Спальни</div>
                <div class="doma-card__prop-value font__HEADING_CARD_TITLE"><?=$props['BEDROOMS']?></div>
            </div>
            <?endif?>
            <div class="doma-card__prop doma-card__prop--price">
                <div class="doma-card__prop-label font__BODY_TEXT_CAPTION">Цена</div>
                <div class="doma-card__prop-value font__HEADING_CARD_TITLE"><?=$props['PRICE'] ? $props['PRICE'].' ₽' : 'по запросу'?></div>
            </div>
        </div>
    </div>
</a>
<?endforeach?>
</div>

<div class="doma-no-results" id="doma-no-results" style="display:none;">
    По выбранным параметрам проекты не найдены. Попробуйте изменить фильтры.
</div>

<?endif?>
</div>
</section>

<script>
(function() {
    function applyFilter() {
        var material  = document.getElementById('filter-material').value;
        var priceMin  = parseInt(document.getElementById('filter-price-min').value) || 0;
        var priceMax  = parseInt(document.getElementById('filter-price-max').value) || Infinity;
        var sqMin     = parseFloat(document.getElementById('filter-sq-min').value) || 0;
        var sqMax     = parseFloat(document.getElementById('filter-sq-max').value) || Infinity;
        var onlyReady = document.getElementById('filter-ready').checked;
        var onlyInProc= document.getElementById('filter-inprocess').checked;
        var items = document.querySelectorAll('#doma-items .doma-card');
        var visible = 0;
        items.forEach(function(item) {
            var mat   = item.dataset.material || '';
            var price = parseInt(item.dataset.price) || 0;
            var sq    = parseFloat(item.dataset.square) || 0;
            var ready = item.dataset.ready === 'yes';
            var inp   = item.dataset.inprocess === 'yes';
            var show  = true;
            if(material && mat !== material) show = false;
            if(priceMin && price && price < priceMin) show = false;
            if(priceMax !== Infinity && price && price > priceMax) show = false;
            if(sqMin && sq && sq < sqMin) show = false;
            if(sqMax !== Infinity && sq && sq > sqMax) show = false;
            if(onlyReady && !ready) show = false;
            if(onlyInProc && !inp) show = false;
            item.style.display = show ? '' : 'none';
            if(show) visible++;
        });
        var noRes = document.getElementById('doma-no-results');
        if(noRes) noRes.style.display = visible === 0 ? 'block' : 'none';
    }
    function resetFilter() {
        ['filter-material','filter-price-min','filter-price-max','filter-sq-min','filter-sq-max'].forEach(function(id){
            var el = document.getElementById(id); if(el) el.value = '';
        });
        ['filter-ready','filter-inprocess'].forEach(function(id){
            var el = document.getElementById(id); if(el) el.checked = false;
        });
        applyFilter();
    }
    document.addEventListener('DOMContentLoaded', function() {
        var btnA = document.getElementById('filter-apply');
        var btnR = document.getElementById('filter-reset');
        if(btnA) btnA.addEventListener('click', applyFilter);
        if(btnR) btnR.addEventListener('click', resetFilter);
    });
})();
</script>
