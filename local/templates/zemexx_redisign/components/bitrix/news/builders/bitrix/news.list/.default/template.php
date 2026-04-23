<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);

use Bitrix\Main\Page\Asset;
Asset::getInstance()->addCss(SITE_TEMPLATE_PATH."/css/zx-design.css");
Asset::getInstance()->addString('<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Golos+Text:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">');

$items = $arResult['ITEMS'];
$total = is_array($items) ? count($items) : 0;

$totalHouses = 0; $totalExperience = 0; $cnt = 0;
foreach($items as $arItem){
    foreach($arItem['PROPERTIES'] as $prop){
        if($prop['CODE']==='HOUSES_COUNT' && !empty($prop['VALUE'])) $totalHouses += (int)$prop['VALUE'];
        if($prop['CODE']==='EXPERIENCE' && !empty($prop['VALUE'])) { $totalExperience += (int)$prop['VALUE']; $cnt++; }
    }
}
$avgExperience = $cnt ? round($totalExperience/$cnt, 1) : 0;
?>
<div class="zx-scope">
  <section class="zx-cat-hero">
    <div class="zx-container">
      <div class="zx-crumbs">
        <a href="/">Главная</a>
        <span class="zx-crumbs__sep"></span>
        <span class="zx-crumbs__dot">●</span>
        <span>Застройщики</span>
      </div>
      <div class="zx-cat-hero__grid">
        <div>
          <div class="zx-eyebrow" style="margin-bottom:20px;">Партнёрские компании · <?=$total?></div>
          <h1 class="zx-display zx-cat-hero__title">Застройщики<span class="zx-cat-hero__title-dot">.</span></h1>
          <div class="zx-cat-hero__lead">Работаем только с проверенными командами. Каждая прошла аудит: проверка финустойчивости, качества объектов и репутации. Выбирайте партнёра под проект.</div>
        </div>
        <div class="zx-cat-hero__stats">
          <div><div class="zx-display zx-stat__n"><?=$total?></div><div class="zx-stat__lab">партнёров в сети</div></div>
          <?if($totalHouses):?><div><div class="zx-display zx-stat__n"><?=$totalHouses?></div><div class="zx-stat__lab">домов построено</div></div><?endif?>
          <?if($avgExperience):?><div><div class="zx-display zx-stat__n"><?=$avgExperience?></div><div class="zx-stat__lab">лет опыта (в среднем)</div></div><?endif?>
        </div>
      </div>
    </div>
  </section>

  <div class="zx-container">
    <?if(!empty($items)):?>
    <div class="zx-dev-grid">
      <?foreach($items as $arItem):
        $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
        $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
        $props = [];
        foreach($arItem['PROPERTIES'] as $prop){
            if(!empty($prop['VALUE'])) $props[$prop['CODE']] = $prop['VALUE'];
        }
        $logo = null;
        if(!empty($props['LOGO'])) $logo = CFile::GetFileArray($props['LOGO']);
        if(!$logo && !empty($arItem['PREVIEW_PICTURE']['SRC'])) $logo = ['SRC'=>$arItem['PREVIEW_PICTURE']['SRC']];
        $firstLetter = mb_strtoupper(mb_substr($arItem['NAME'], 0, 1));
      ?>
      <div class="zx-dev-card zx-hover-lift" id="<?=$this->GetEditAreaId($arItem['ID'])?>">
        <div class="zx-dev-card__head">
          <div class="zx-dev-block__logo">
            <?if($logo && !empty($logo['SRC'])):?>
              <img src="<?=$logo['SRC']?>" alt="<?=htmlspecialchars($arItem['NAME'])?>">
            <?else:?>
              <?=$firstLetter?>
            <?endif?>
          </div>
          <div style="flex:1;">
            <div class="zx-dev-card__meta">
              <?if(!empty($props['REGION'])):?><span>📍 <?=htmlspecialchars($props['REGION'])?></span><?endif?>
            </div>
            <div class="zx-dev-card__name"><?=htmlspecialchars($arItem['NAME'])?></div>
            <?if(!empty($arItem['PREVIEW_TEXT'])):?>
              <div class="zx-dev-card__tagline"><?=strip_tags($arItem['PREVIEW_TEXT'])?></div>
            <?endif?>
          </div>
        </div>

        <?if(!empty($props['EXPERIENCE']) || !empty($props['HOUSES_COUNT']) || !empty($props['REGION'])):?>
        <div class="zx-dev-card__stats">
          <?if(!empty($props['EXPERIENCE'])):?>
            <div class="zx-mini-stat"><div class="zx-mini-stat__n"><?=$props['EXPERIENCE']?></div><div class="zx-mini-stat__l">лет опыта</div></div>
          <?endif?>
          <?if(!empty($props['HOUSES_COUNT'])):?>
            <div class="zx-mini-stat"><div class="zx-mini-stat__n"><?=$props['HOUSES_COUNT']?></div><div class="zx-mini-stat__l">домов</div></div>
          <?endif?>
          <?if(!empty($props['PHONE'])):?>
            <div class="zx-mini-stat"><div class="zx-mini-stat__n" style="font-size:15px;"><?=htmlspecialchars($props['PHONE'])?></div><div class="zx-mini-stat__l">телефон</div></div>
          <?endif?>
          <?if(!empty($props['WEBSITE'])):?>
            <div class="zx-mini-stat"><a href="<?=htmlspecialchars($props['WEBSITE'])?>" target="_blank" rel="noopener" style="color:var(--zx-accent-deep);font-size:13px;">сайт →</a></div>
          <?endif?>
        </div>
        <?endif?>

        <div class="zx-dev-card__actions">
          <a class="zx-btn zx-btn--primary zx-btn--sm" href="<?=$arItem['DETAIL_PAGE_URL']?>">О компании →</a>
          <a class="zx-btn zx-btn--ghost zx-btn--sm" data-fancybox="" data-src="#hidden-form" href="javascript:;" data-scope="Связаться с <?=htmlspecialchars($arItem['NAME'])?>" style="flex:0 0 auto;">Написать</a>
        </div>
      </div>
      <?endforeach?>
    </div>
    <?else:?>
    <div class="zx-cat-empty">
      <p>Список застройщиков пополняется. Свяжитесь с нами для получения информации о партнёрах.</p>
      <a class="zx-btn zx-btn--primary" data-fancybox="" data-src="#hidden-form" href="javascript:;" data-scope="Запрос застройщика" style="margin-top:16px;">Связаться с нами</a>
    </div>
    <?endif?>
  </div>

  <section class="zx-cta-dark">
    <div class="zx-container">
      <div class="zx-cta-dark__grid">
        <div>
          <div class="zx-eyebrow zx-cta-dark__eyebrow">Станьте партнёром</div>
          <div class="zx-display zx-cta-dark__title">Строите дома? Разместите проекты у нас</div>
          <div class="zx-cta-dark__lead">Работа по договору-оферте, аудит за 5 рабочих дней, доступ к клиентам и инструменты для ведения сделок.</div>
        </div>
        <div class="zx-cta-dark__actions">
          <a class="zx-btn zx-btn--primary" data-fancybox="" data-src="#hidden-form" href="javascript:;" data-scope="Партнёрская заявка">Оставить заявку →</a>
          <a class="zx-btn zx-btn--ghost-dark" href="/partnership/">Условия партнёрства</a>
        </div>
      </div>
    </div>
  </section>

  <?if($arParams["DISPLAY_BOTTOM_PAGER"] && $arResult["NAV_STRING"]):?>
  <div class="zx-container" style="margin-top:40px;"><?=$arResult["NAV_STRING"]?></div>
  <?endif?>
</div>
