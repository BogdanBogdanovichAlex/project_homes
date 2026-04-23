<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);

use Bitrix\Main\Page\Asset;
Asset::getInstance()->addCss(SITE_TEMPLATE_PATH."/css/zx-design.css");

$items = $arResult['ITEMS'];

// Fallback демо-данные, чтобы страница не была пустой
$usingDemo = empty($items);
if($usingDemo){
  $items = [
    ['ID'=>'demo-1','NAME'=>'Земельный Экспресс Строй','DETAIL_PAGE_URL'=>'#','PREVIEW_TEXT'=>'Строительство домов из газобетона и клеёного бруса под ключ',
     'PREVIEW_PICTURE'=>[],'DETAIL_PICTURE'=>[],'__TYPES'=>['kamennye','derevyannye'],'PROPERTIES'=>[
       ['CODE'=>'REGION','VALUE'=>'Московская область'],
       ['CODE'=>'EXPERIENCE','VALUE'=>'7'],['CODE'=>'HOUSES_COUNT','VALUE'=>'240'],
       ['CODE'=>'MIN_AREA','VALUE'=>'90'],['CODE'=>'BUILD_TIME','VALUE'=>'3'],['CODE'=>'PRICE_PER_M','VALUE'=>'35'],
       ['CODE'=>'PHONE','VALUE'=>'+7 (495) 989-10-70'],
     ]],
    ['ID'=>'demo-2','NAME'=>'Rubkoff Wood','DETAIL_PAGE_URL'=>'#','PREVIEW_TEXT'=>'Строительство и проектирование деревянных домов и бань',
     'PREVIEW_PICTURE'=>[],'DETAIL_PICTURE'=>[],'__TYPES'=>['derevyannye'],'PROPERTIES'=>[
       ['CODE'=>'REGION','VALUE'=>'Подмосковье'],['CODE'=>'EXPERIENCE','VALUE'=>'12'],['CODE'=>'HOUSES_COUNT','VALUE'=>'320'],
       ['CODE'=>'MIN_AREA','VALUE'=>'140'],['CODE'=>'BUILD_TIME','VALUE'=>'3'],['CODE'=>'PRICE_PER_M','VALUE'=>'46'],
     ]],
    ['ID'=>'demo-3','NAME'=>'Green House Stroy','DETAIL_PAGE_URL'=>'#','PREVIEW_TEXT'=>'Строительство домов: Барнхаус, Фахверк, в стиле «Хай-Тек»',
     'PREVIEW_PICTURE'=>[],'DETAIL_PICTURE'=>[],'__TYPES'=>['karkasnye','modulnye'],'PROPERTIES'=>[
       ['CODE'=>'REGION','VALUE'=>'Москва и МО'],['CODE'=>'EXPERIENCE','VALUE'=>'9'],['CODE'=>'HOUSES_COUNT','VALUE'=>'180'],
       ['CODE'=>'MIN_AREA','VALUE'=>'72'],['CODE'=>'BUILD_TIME','VALUE'=>'3'],['CODE'=>'PRICE_PER_M','VALUE'=>'45'],
     ]],
    ['ID'=>'demo-4','NAME'=>'Brick House','DETAIL_PAGE_URL'=>'#','PREVIEW_TEXT'=>'Проектирование и строительство домов из камня и пеноблоков',
     'PREVIEW_PICTURE'=>[],'DETAIL_PICTURE'=>[],'__TYPES'=>['kamennye'],'PROPERTIES'=>[
       ['CODE'=>'REGION','VALUE'=>'Тверская область'],['CODE'=>'EXPERIENCE','VALUE'=>'11'],['CODE'=>'HOUSES_COUNT','VALUE'=>'275'],
       ['CODE'=>'MIN_AREA','VALUE'=>'80'],['CODE'=>'BUILD_TIME','VALUE'=>'4'],['CODE'=>'PRICE_PER_M','VALUE'=>'35'],
     ]],
    ['ID'=>'demo-5','NAME'=>'Modul Home','DETAIL_PAGE_URL'=>'#','PREVIEW_TEXT'=>'Модульные дома с готовыми инженерными системами',
     'PREVIEW_PICTURE'=>[],'DETAIL_PICTURE'=>[],'__TYPES'=>['modulnye','karkasnye'],'PROPERTIES'=>[
       ['CODE'=>'REGION','VALUE'=>'Московская область'],['CODE'=>'EXPERIENCE','VALUE'=>'6'],['CODE'=>'HOUSES_COUNT','VALUE'=>'120'],
       ['CODE'=>'MIN_AREA','VALUE'=>'60'],['CODE'=>'BUILD_TIME','VALUE'=>'2'],['CODE'=>'PRICE_PER_M','VALUE'=>'40'],
     ]],
  ];
}

$total = count($items);

// Типы домов — fixed
$houseTypes = [
  'all'          => ['label'=>'Все дома',       'icon'=>'🏘'],
  'kamennye'     => ['label'=>'Каменные',       'icon'=>'🧱'],
  'derevyannye'  => ['label'=>'Деревянные',     'icon'=>'🪵'],
  'karkasnye'    => ['label'=>'Каркасные',      'icon'=>'🏗'],
  'modulnye'     => ['label'=>'Модульные',      'icon'=>'📦'],
];

// Агрегаты
$totalHouses = 0; $totalExperience = 0; $cnt = 0;
foreach($items as $arItem){
  foreach($arItem['PROPERTIES'] as $prop){
    if($prop['CODE']==='HOUSES_COUNT' && !empty($prop['VALUE'])) $totalHouses += (int)$prop['VALUE'];
    if($prop['CODE']==='EXPERIENCE' && !empty($prop['VALUE'])) { $totalExperience += (int)$prop['VALUE']; $cnt++; }
  }
}
$avgExperience = $cnt ? round($totalExperience/$cnt, 1) : 0;
?>
<div class="zx-scope zx-devs-page">
  <section class="zx-dev-hero">
    <div class="c-sel--div__CONTAINER">
      <div class="zx-crumbs zx-dev-hero__crumbs">
        <a href="/">Главная</a>
        <span class="zx-crumbs__sep"></span>
        <span class="zx-crumbs__dot">●</span>
        <span>Застройщики</span>
      </div>

      <div class="zx-dev-hero__grid">
        <div class="zx-dev-hero__main">
          <div class="zx-eyebrow" style="margin-bottom:18px;">
            <span style="color:var(--text-accent);">●</span> Проверенных партнёров · <?=$total?>
          </div>
          <h1 class="zx-dev-hero__title font__HEADING_PAGE_TITLE">Застройщики<span class="zx-hero__title-dot">.</span></h1>
          <p class="zx-dev-hero__lead font__BODY_TEXT_PRIMARY">
            Работаем только с проверенными командами. Каждая прошла аудит: финансовая устойчивость, качество объектов, репутация. Выбирайте партнёра под свой проект дома.
          </p>
          <div class="zx-dev-hero__ctas">
            <a class="zx-btn zx-btn--primary font__BUTTONS_BUTTON" href="#zxDevList">Выбрать застройщика →</a>
            <a class="zx-btn zx-btn--ghost font__BUTTONS_BUTTON" data-fancybox="" data-src="#hidden-form" href="javascript:;" data-scope="Стать партнёром">Стать партнёром</a>
          </div>
        </div>

        <div class="zx-dev-hero__aside">
          <div class="zx-dev-hero__stat">
            <div class="zx-dev-hero__stat-icon">🛡</div>
            <div>
              <div class="zx-dev-hero__stat-val"><?=$total?></div>
              <div class="zx-dev-hero__stat-lab">партнёров в сети</div>
            </div>
          </div>
          <?if($totalHouses):?>
          <div class="zx-dev-hero__stat">
            <div class="zx-dev-hero__stat-icon">🏡</div>
            <div>
              <div class="zx-dev-hero__stat-val"><?=$totalHouses?>+</div>
              <div class="zx-dev-hero__stat-lab">домов сдано</div>
            </div>
          </div>
          <?endif?>
          <?if($avgExperience):?>
          <div class="zx-dev-hero__stat">
            <div class="zx-dev-hero__stat-icon">⭐</div>
            <div>
              <div class="zx-dev-hero__stat-val"><?=$avgExperience?></div>
              <div class="zx-dev-hero__stat-lab">лет опыта (в среднем)</div>
            </div>
          </div>
          <?endif?>
          <div class="zx-dev-hero__stat zx-dev-hero__stat--accent">
            <div class="zx-dev-hero__stat-icon">✓</div>
            <div>
              <div class="zx-dev-hero__stat-val">100%</div>
              <div class="zx-dev-hero__stat-lab">прошли аудит</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Фильтр по типам домов -->
  <div class="zx-dev-filter" id="zxDevList">
    <div class="c-sel--div__CONTAINER">
      <div class="zx-dev-filter__row" data-zx-types>
        <?foreach($houseTypes as $key=>$t):?>
          <button type="button" class="zx-dev-filter__tab <?=$key==='all'?'is-active':''?>" data-type="<?=$key?>">
            <span class="zx-dev-filter__icon"><?=$t['icon']?></span>
            <span><?=$t['label']?></span>
          </button>
        <?endforeach?>
      </div>
    </div>
  </div>

  <div class="c-sel--div__CONTAINER">
    <div class="zx-dev-list" id="zxDevItems">
      <?foreach($items as $idx=>$arItem):
        if(!$usingDemo){
          $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
          $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
        }
        $props = [];
        foreach($arItem['PROPERTIES'] as $prop){
          if(!empty($prop['VALUE'])) $props[$prop['CODE']] = $prop['VALUE'];
        }
        $logo = null;
        if(!$usingDemo && !empty($props['LOGO'])) $logo = CFile::GetFileArray($props['LOGO']);
        $heroImg = '';
        if(!empty($arItem['DETAIL_PICTURE']['SRC'])) $heroImg = $arItem['DETAIL_PICTURE']['SRC'];
        elseif(!empty($arItem['PREVIEW_PICTURE']['SRC'])) $heroImg = $arItem['PREVIEW_PICTURE']['SRC'];
        $firstLetter = mb_strtoupper(mb_substr($arItem['NAME'], 0, 1));
        $flip = $idx % 2 === 1;

        // Определяем типы домов (HOUSE_TYPES — multi-select в iblock, или __TYPES для демо)
        $types = [];
        if(isset($arItem['__TYPES'])) $types = $arItem['__TYPES'];
        elseif(!empty($props['HOUSE_TYPES'])) {
          $types = is_array($props['HOUSE_TYPES']) ? $props['HOUSE_TYPES'] : [$props['HOUSE_TYPES']];
        }
        $typesAttr = implode(' ', $types);
      ?>
      <article class="zx-dev-item <?=$flip?'is-flipped':''?>" data-types="<?=htmlspecialchars($typesAttr)?>" <?if(!$usingDemo):?>id="<?=$this->GetEditAreaId($arItem['ID'])?>"<?endif?>>
        <div class="zx-dev-item__image zx-img-ph">
          <?if($heroImg):?>
            <img src="<?=$heroImg?>" alt="<?=htmlspecialchars($arItem['NAME'])?>" loading="lazy">
          <?endif?>
          <div class="zx-dev-item__logo">
            <?if($logo && !empty($logo['SRC'])):?>
              <img src="<?=$logo['SRC']?>" alt="<?=htmlspecialchars($arItem['NAME'])?>">
            <?else:?>
              <span><?=$firstLetter?></span>
            <?endif?>
          </div>
        </div>
        <div class="zx-dev-item__content">
          <?if(!empty($arItem['PREVIEW_TEXT'])):?>
            <div class="zx-dev-item__sub font__BODY_TEXT_CAPTION"><?=strip_tags($arItem['PREVIEW_TEXT'])?></div>
          <?endif?>
          <h2 class="zx-dev-item__title font__HEADING_BLOCK_TITLE">«<?=htmlspecialchars($arItem['NAME'])?>»</h2>

          <?if(!empty($types)):?>
          <div class="zx-dev-item__tags">
            <?foreach($types as $t): if(!isset($houseTypes[$t])) continue;?>
              <span class="zx-dev-item__tag"><?=$houseTypes[$t]['icon']?> <?=$houseTypes[$t]['label']?></span>
            <?endforeach?>
          </div>
          <?endif?>

          <div class="zx-dev-item__stats">
            <?if(!empty($props['MIN_AREA'])):?>
            <div class="zx-dev-stat">
              <div class="zx-dev-stat__icon">📐</div>
              <div><div class="zx-dev-stat__val">от <b><?=$props['MIN_AREA']?></b> м²</div><div class="zx-dev-stat__lab">Площадь домов</div></div>
            </div>
            <?elseif(!empty($props['HOUSES_COUNT'])):?>
            <div class="zx-dev-stat">
              <div class="zx-dev-stat__icon">🏡</div>
              <div><div class="zx-dev-stat__val"><b><?=$props['HOUSES_COUNT']?>+</b></div><div class="zx-dev-stat__lab">Домов построено</div></div>
            </div>
            <?endif?>

            <?if(!empty($props['BUILD_TIME'])):?>
            <div class="zx-dev-stat">
              <div class="zx-dev-stat__icon">⏱</div>
              <div><div class="zx-dev-stat__val">от <b><?=$props['BUILD_TIME']?></b> мес.</div><div class="zx-dev-stat__lab">Срок возведения</div></div>
            </div>
            <?elseif(!empty($props['EXPERIENCE'])):?>
            <div class="zx-dev-stat">
              <div class="zx-dev-stat__icon">⭐</div>
              <div><div class="zx-dev-stat__val"><b><?=$props['EXPERIENCE']?></b> лет</div><div class="zx-dev-stat__lab">Опыт на рынке</div></div>
            </div>
            <?endif?>

            <?if(!empty($props['PRICE_PER_M'])):?>
            <div class="zx-dev-stat">
              <div class="zx-dev-stat__icon">💰</div>
              <div><div class="zx-dev-stat__val">от <b><?=$props['PRICE_PER_M']?></b> тыс. ₽/м²</div><div class="zx-dev-stat__lab">Цена строительства</div></div>
            </div>
            <?elseif(!empty($props['REGION'])):?>
            <div class="zx-dev-stat">
              <div class="zx-dev-stat__icon">📍</div>
              <div><div class="zx-dev-stat__val"><?=htmlspecialchars($props['REGION'])?></div><div class="zx-dev-stat__lab">Регион</div></div>
            </div>
            <?endif?>
          </div>

          <div class="zx-dev-item__actions">
            <a class="zx-btn zx-btn--primary font__BUTTONS_BUTTON" href="<?=$arItem['DETAIL_PAGE_URL']?>">О компании →</a>
            <a class="zx-btn zx-btn--ghost font__BUTTONS_BUTTON" data-fancybox="" data-src="#hidden-form" href="javascript:;" data-scope="Связаться с <?=htmlspecialchars($arItem['NAME'])?>">Связаться</a>
            <?if(!empty($props['PHONE'])):?>
              <a class="zx-dev-item__phone" href="tel:<?=preg_replace('/[^0-9+]/','',$props['PHONE'])?>"><?=htmlspecialchars($props['PHONE'])?></a>
            <?endif?>
          </div>
        </div>
      </article>
      <?endforeach?>
    </div>

    <div class="zx-cat-empty" id="zxDevEmpty" style="display:none;">По выбранному типу домов застройщики не найдены</div>

    <?if($usingDemo):?>
    <div class="zx-demo-notice font__BODY_TEXT_CAPTION">
      <b>Это демо-карточки</b> — в инфоблоке «Застройщики» пока нет активных записей. После добавления реальных компаний этот блок будет использовать их данные автоматически.
    </div>
    <?endif?>
  </div>

  <section class="zx-cta-dark">
    <div class="c-sel--div__CONTAINER">
      <div class="zx-cta-dark__grid">
        <div>
          <div class="zx-eyebrow zx-cta-dark__eyebrow">Станьте партнёром</div>
          <h2 class="zx-cta-dark__title font__HEADING_SECTION_TITLE">Строите дома? Разместите проекты у нас</h2>
          <p class="zx-cta-dark__lead font__BODY_TEXT_PRIMARY">Работа по договору-оферте, аудит за 5 рабочих дней, доступ к клиентам и инструменты для ведения сделок.</p>
        </div>
        <div class="zx-cta-dark__actions">
          <a class="zx-btn zx-btn--primary font__BUTTONS_BUTTON" data-fancybox="" data-src="#hidden-form" href="javascript:;" data-scope="Партнёрская заявка">Оставить заявку →</a>
          <a class="zx-btn zx-btn--ghost-dark font__BUTTONS_BUTTON" href="/partnership/">Условия партнёрства</a>
        </div>
      </div>
    </div>
  </section>

  <?if(!$usingDemo && $arParams["DISPLAY_BOTTOM_PAGER"] && $arResult["NAV_STRING"]):?>
  <div class="c-sel--div__CONTAINER" style="margin-top:40px;"><?=$arResult["NAV_STRING"]?></div>
  <?endif?>
</div>

<script>
(function(){
  var row = document.querySelector('[data-zx-types]');
  var items = Array.prototype.slice.call(document.querySelectorAll('#zxDevItems .zx-dev-item'));
  var empty = document.getElementById('zxDevEmpty');
  if(!row) return;
  row.addEventListener('click', function(e){
    var b = e.target.closest('.zx-dev-filter__tab'); if(!b) return;
    var t = b.dataset.type;
    row.querySelectorAll('.zx-dev-filter__tab').forEach(function(x){ x.classList.toggle('is-active', x===b); });
    var visible = 0;
    items.forEach(function(el){
      var types = (el.dataset.types||'').split(' ');
      var show = t === 'all' || types.indexOf(t) !== -1;
      el.style.display = show ? '' : 'none';
      if(show) visible++;
    });
    if(empty) empty.style.display = visible ? 'none' : '';
  });
})();
</script>
