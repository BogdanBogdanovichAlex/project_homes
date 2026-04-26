<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);

function zx_sim_price_short($v){
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
<div class="zx-similar-grid">
<?foreach($arResult["ITEMS"] as $arItem):
    $props = [];
    foreach($arItem['PROPERTIES'] as $prop){
        if($prop['CODE']=='MATERIAL') $props[$prop['CODE']] = ['XML_ID'=>$prop['VALUE_XML_ID'],'VALUE'=>$prop['VALUE']];
        elseif(!empty($prop['VALUE'])) $props[$prop['CODE']] = $prop['VALUE'];
    }
    $priceRaw = isset($props['PRICE']) ? (int)preg_replace('/\D/','',$props['PRICE']) : 0;
    $isReady = !empty($props['READY']) && $props['READY']=='Y';
    $isInProcess = !empty($props['IN_PROCESS']) && $props['IN_PROCESS']=='Y';
    $statusLabel = $isReady ? 'Готовый дом' : ($isInProcess ? 'Идёт строительство' : 'Проект');
    $statusClass = $isReady ? 'zx-chip--ready' : 'zx-chip--progress';
    $mat = isset($props['MATERIAL']['VALUE']) ? $props['MATERIAL']['VALUE'] : '';
    $img = $arItem['PREVIEW_PICTURE']['SRC'] ?? ($arItem['DETAIL_PICTURE']['SRC'] ?? '');
?>
  <a class="zx-proj-card zx-hover-lift" href="<?=$arItem['DETAIL_PAGE_URL']?>">
    <div class="zx-img-ph zx-proj-card__img">
      <?if($img):?><img src="<?=$img?>" alt="<?=htmlspecialchars($arItem['NAME'])?>" loading="lazy"><?endif?>
      <div class="zx-proj-card__badges">
        <span class="zx-chip <?=$statusClass?>"><span class="zx-chip__dot"></span><?=$statusLabel?></span>
        <?if($mat):?><span class="zx-chip"><?=htmlspecialchars($mat)?></span><?endif?>
      </div>
    </div>
    <div class="zx-proj-card__body">
      <div class="zx-proj-card__head">
        <h3 class="zx-proj-card__title font__HEADING_CARD_TITLE"><?=htmlspecialchars($arItem['NAME'])?></h3>
        <div class="zx-proj-card__price zx-mono"><?=zx_sim_price_short($priceRaw)?></div>
      </div>
      <div class="zx-proj-card__specs">
        <?if(!empty($props['SQUARE'])):?><span><b><?=$props['SQUARE']?></b> м²</span><?endif?>
        <?if(!empty($props['FLOORS'])):?><span>·</span><span><b><?=$props['FLOORS']?></b> эт.</span><?endif?>
        <?if(!empty($props['BEDROOMS'])):?><span>·</span><span><b><?=$props['BEDROOMS']?></b> спал.</span><?endif?>
      </div>
    </div>
  </a>
<?endforeach?>
</div>
