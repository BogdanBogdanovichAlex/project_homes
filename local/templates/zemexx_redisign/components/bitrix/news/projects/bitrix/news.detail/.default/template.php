<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);

use Bitrix\Main\Page\Asset;
Asset::getInstance()->addCss(SITE_TEMPLATE_PATH."/css/zx-design.css");

$properties = [];
foreach($arResult['PROPERTIES'] as $prop){
    if(!empty($prop['VALUE'])) $properties[$prop['CODE']] = $prop['VALUE'];
}
$fullName = $arResult['NAME'];
$material = is_array($properties['MATERIAL']??null) ? ($properties['MATERIAL'][0]??'') : ($properties['MATERIAL']??'');
$isReady  = !empty($properties['READY']) && $properties['READY']==='Y';
$isInProcess = !empty($properties['IN_PROCESS']) && $properties['IN_PROCESS']==='Y';
$statusLabel = $isReady ? 'Готовый дом' : ($isInProcess ? 'Идёт строительство' : 'Проект');
$statusClass = $isReady ? 'zx-chip--ready' : 'zx-chip--progress';

$priceInt = !empty($properties['PRICE']) ? (int)preg_replace('/[^0-9]/','', $properties['PRICE']) : 0;
$priceMonthly = $priceInt ? round($priceInt * 0.0072 / 1000) * 1000 : 0;

$gallery = [];
if(!empty($arResult['DETAIL_PICTURE']['SRC'])) $gallery[] = ['src'=>$arResult['DETAIL_PICTURE']['SRC'],'label'=>'Фасад'];
elseif(!empty($arResult['PREVIEW_PICTURE']['SRC'])) $gallery[] = ['src'=>$arResult['PREVIEW_PICTURE']['SRC'],'label'=>'Фасад'];
$labelsTop = ['Фасад','Терраса','Гостиная','Кухня','Спальня','Санузел','Интерьер','Фасад ночью'];
if(!empty($properties['GALLERY_TOP'])){
    foreach($properties['GALLERY_TOP'] as $i=>$fid){
        $p = CFile::GetFileArray($fid);
        if(!empty($p['SRC'])) $gallery[] = ['src'=>$p['SRC'], 'label'=>$labelsTop[$i]??'Фото '.($i+1)];
    }
}
$galleryExtra = [];
if(!empty($properties['GALLERY_BOTTOM'])){
    foreach($properties['GALLERY_BOTTOM'] as $fid){
        $p = CFile::GetFileArray($fid);
        if(!empty($p['SRC'])) $galleryExtra[] = $p['SRC'];
    }
}
$allPhotos = array_merge(array_column($gallery, 'src'), $galleryExtra);

$plans = [];
if(!empty($properties['FIRST_FLOOR_IMG'])){
    $f = CFile::GetFileArray($properties['FIRST_FLOOR_IMG']);
    if(!empty($f['SRC'])) $plans[] = ['title'=>'1 этаж', 'src'=>$f['SRC'], 'desc'=>$properties['FIRST_FLOOR_DESC']??''];
}
if(!empty($properties['SECOND_FLOOR_IMG'])){
    $s = CFile::GetFileArray($properties['SECOND_FLOOR_IMG']);
    if(!empty($s['SRC'])) $plans[] = ['title'=>'2 этаж', 'src'=>$s['SRC'], 'desc'=>$properties['SECOND_FLOOR_DESC']??''];
}

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

$subtitle = !empty($arResult['PREVIEW_TEXT']) ? strip_tags($arResult['PREVIEW_TEXT']) : '';

// Hero slides для бэкграунда swiper (используем галерею + основное фото)
$heroSlides = $allPhotos;
if(empty($heroSlides) && !empty($arResult['PREVIEW_PICTURE']['SRC'])) $heroSlides[] = $arResult['PREVIEW_PICTURE']['SRC'];
if(empty($heroSlides)) $heroSlides[] = SITE_TEMPLATE_PATH.'/images/placeholder.jpg';

// Калькулятор — база
$basePrice  = $priceInt ?: 5000000;
$baseSquare = !empty($properties['SQUARE']) ? (float)$properties['SQUARE'] : 100;

// Калькулятор — конфиг опций (admin: /bitrix/admin/zemex_homes_calc.php).
// Если в админке ничего не сохранено или JSON битый — используются дефолты.
$calcRaw = COption::GetOptionString('main', 'zemex_homes_calc', '');
$calcCfg = $calcRaw ? json_decode($calcRaw, true) : null;
if (!is_array($calcCfg)) {
    $calcCfg = [
        'walls' => ['label' => 'Материал стен', 'options' => [
            ['label'=>'Газобетон D500','price'=>0],['label'=>'Кирпич','price'=>450000],['label'=>'Клеёный брус','price'=>850000],
        ]],
        'roof' => ['label' => 'Материал крыши', 'options' => [
            ['label'=>'Металлочерепица','price'=>0],['label'=>'Композитная черепица','price'=>180000],['label'=>'Фальцевая кровля','price'=>260000],
        ]],
        'windows' => ['label' => 'Окна', 'options' => [
            ['label'=>'Двухкамерный','price'=>0],['label'=>'Энергосберегающий','price'=>120000],['label'=>'Тёплый алюминий','price'=>280000],
        ]],
        'insulation' => ['label' => 'Утепление контура', 'options' => [
            ['label'=>'Стандарт 100 мм','price'=>0],['label'=>'Усиленное 150 мм','price'=>90000],['label'=>'Премиум 200 мм','price'=>180000],
        ]],
        'foundation' => ['label' => 'Фундамент', 'options' => [
            ['label'=>'Свайно-ростверковый','price'=>0],['label'=>'Монолитная плита','price'=>250000],['label'=>'Утеплённая лента','price'=>400000],
        ]],
        'extras' => [
            ['label'=>'Тёплый пол','price'=>220000],['label'=>'Рекуператор','price'=>180000],['label'=>'Умный дом','price'=>120000],
            ['label'=>'Панорамные окна','price'=>190000],['label'=>'Терраса','price'=>350000],['label'=>'Навес / гараж','price'=>550000],
            ['label'=>'Сауна','price'=>280000],['label'=>'Камин','price'=>180000],
        ],
    ];
}
function zx_calc_fmt_plus($price) {
    if (!$price) return 'в&nbsp;базе';
    return '+'.number_format((int)$price, 0, ',', "\xc2\xa0").'&nbsp;₽';
}

// Derived floors (iblock не имеет FLOORS — считаем по наличию 2-го этажа)
$floorsDerived = !empty($properties['SECOND_FLOOR_IMG']) || !empty($properties['SECOND_FLOOR_DESC']) ? 2 : 1;
$buildTimeText = !empty($properties['BUILD_TIME']) ? $properties['BUILD_TIME'] : 'до 90 дней';
?>
<style>body > .breadcrumbs, .content > .breadcrumbs, .bx-breadcrumb{display:none !important;}</style>
<div class="zx-scope">

  <!-- HERO (из бэкапа) -->
  <section class="doma-detail-v2__hero">
    <!-- Хлебные крошки — оверлей поверх hero -->
    <nav class="zx-detail-bc zx-detail-bc--over" aria-label="Хлебные крошки">
      <div class="c-sel--div__CONTAINER">
        <ol itemscope itemtype="https://schema.org/BreadcrumbList">
          <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
            <a itemprop="item" href="/"><span itemprop="name">Главная</span></a>
            <meta itemprop="position" content="1" />
          </li>
          <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
            <a itemprop="item" href="/doma-i-kottedzhi/"><span itemprop="name">Дома и&nbsp;коттеджи</span></a>
            <meta itemprop="position" content="2" />
          </li>
          <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
            <span itemprop="name"><?=htmlspecialchars($fullName)?></span>
            <meta itemprop="position" content="3" />
          </li>
        </ol>
      </div>
    </nav>
    <div class="vp-hero--div__SWIPER swiper doma-detail-v2__swiper">
      <div class="vp-hero--div__SWIPER_WRAPPER swiper-wrapper">
        <?foreach($heroSlides as $src):?>
          <div class="vp-hero--div__SWIPER_SLIDE swiper-slide" style="background:url('<?=$src?>') center/cover;"></div>
        <?endforeach?>
      </div>
      <?if(count($heroSlides)>1):?>
        <div class="vp-hero--div__SWIPER_PAGINATION swiper-pagination"></div>
        <button class="swiper-button-prev" type="button" aria-label="Предыдущее фото"></button>
        <button class="swiper-button-next" type="button" aria-label="Следующее фото"></button>
      <?endif?>

      <div class="doma-detail-v2__hero-content">
        <div class="doma-detail-v2__heroInfo">
          <div class="doma-detail-v2__tags">
            <span class="doma-detail-v2__tag doma-detail-v2__tag--hot font__BODY_TEXT_CAPTION">🔥 Осталось 2 слота на сезон</span>
            <?if($material):?>
              <span class="doma-detail-v2__tag font__BODY_TEXT_CAPTION"><?=htmlspecialchars($material)?></span>
            <?endif?>
            <?if(!empty($properties['BUILD_TIME'])):?>
              <span class="doma-detail-v2__tag font__BODY_TEXT_CAPTION">🏗 <?=htmlspecialchars($properties['BUILD_TIME'])?></span>
            <?endif?>
            <?if($isReady):?>
              <span class="doma-detail-v2__tag doma-detail-v2__tag--accent font__BODY_TEXT_CAPTION">✓ Готов к заселению</span>
            <?endif?>
          </div>
          <h1 class="doma-detail-v2__h1 font__HEADING_PAGE_TITLE"><?=htmlspecialchars($fullName)?></h1>
          <?if($subtitle):?>
            <div class="doma-detail-v2__lead font__BODY_TEXT_PRIMARY"><?=$subtitle?></div>
          <?endif?>
        </div>
      </div>
    </div>
  </section>

  <?$APPLICATION->IncludeComponent(
    "zemex:hero.advantages",
    "",
    array(
      "ADVANTAGE_1_TITLE" => "5 лет гарантии",
      "ADVANTAGE_1_DESC"  => "На конструктив и инженерные системы дома",
      "ADVANTAGE_2_TITLE" => "Фиксированная цена",
      "ADVANTAGE_2_DESC"  => "Смета в договоре — никаких доплат после подписания",
      "ADVANTAGE_3_TITLE" => "Дом за 90 дней",
      "ADVANTAGE_3_DESC"  => "От заливки фундамента до вручения ключей",
      "ADVANTAGE_4_TITLE" => "Ипотека от 6%",
      "ADVANTAGE_4_DESC"  => "Семейная, IT, стандартная — поможем с оформлением",
      "AUTO_SCROLL" => "Y",
      "AUTO_SCROLL_INTERVAL" => "5000",
      "CACHE_TYPE" => "A",
      "CACHE_TIME" => "3600",
    ),
    false
  );?>

  <!-- Ключевые параметры + CTA -->
  <section class="doma-detail-v2__keystats-wrap">
    <div class="c-sel--div__CONTAINER">
      <div class="doma-detail-v2__bar">
        <div class="doma-detail-v2__bar-left">
        <div class="doma-detail-v2__bar-stats">
          <?if(!empty($properties['SQUARE'])):?>
            <div class="doma-detail-v2__bar-stat">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3h18v18H3z"/><path d="M3 9h3M3 15h3M9 3v3M15 3v3"/></svg>
              <span>Площадь</span><b><?=$properties['SQUARE']?> м²</b>
              <em>жилая + терраса</em>
            </div>
          <?endif?>
          <div class="doma-detail-v2__bar-stat">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21V10l9-6 9 6v11"/><path d="M3 14h18"/></svg>
            <span>Этажность</span><b><?=$floorsDerived?></b>
            <em><?=$floorsDerived>1?'мастер-спальня наверху':'всё на одном уровне'?></em>
          </div>
          <?if($material):?>
            <div class="doma-detail-v2__bar-stat">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18v4H3zM3 14h18v4H3z"/><path d="M8 6v4M14 6v4M6 14v4M12 14v4M18 14v4"/></svg>
              <span>Материал</span><b><?=htmlspecialchars($material)?></b>
              <em>стены 400&nbsp;мм + утепление</em>
            </div>
          <?endif?>
          <div class="doma-detail-v2__bar-stat">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
            <span>Срок сдачи</span><b><?=htmlspecialchars($buildTimeText)?></b>
            <em>от заливки до ключей</em>
          </div>
          <div class="doma-detail-v2__bar-stat">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2l8 4v6c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-4z"/><path d="M9 12l2 2 4-4"/></svg>
            <span>Гарантия</span><b>5 лет</b>
            <em>на конструктив и сети</em>
          </div>
        </div>
        <div class="doma-detail-v2__bar-includes">
          <span class="doma-detail-v2__bar-includes-lab">В&nbsp;базовую стоимость входит:</span>
          <ul>
            <li>Фундамент</li>
            <li>Коробка и&nbsp;перегородки</li>
            <li>Кровля под ключ</li>
            <li>Утепление 150&nbsp;мм</li>
            <li>Окна и&nbsp;двери</li>
            <li>Фасад</li>
          </ul>
        </div>
        </div>
        <div class="doma-detail-v2__bar-price">
          <?if($priceInt):?>
            <div class="doma-detail-v2__bar-price-inner">
              <span class="doma-detail-v2__bar-price-label">Стоимость от</span>
              <b class="doma-detail-v2__bar-price-val"><?=number_format($priceInt,0,',',' ')?> ₽</b>
              <?if($priceMonthly):?>
                <span class="doma-detail-v2__bar-price-sub">🏦 В ипотеку ~ <?=number_format($priceMonthly,0,',',' ')?> ₽/мес · от 6%</span>
              <?endif?>
            </div>
          <?endif?>
          <div class="doma-detail-v2__bar-btns">
            <a class="doma-detail-v2__bar-btn-primary font__BUTTONS_BUTTON" data-fancybox="" data-src="#hidden-form" href="javascript:;" data-scope="Расчёт проекта <?=htmlspecialchars($fullName)?>">Хочу получить расчёт</a>
            <a class="doma-detail-v2__bar-btn-ghost font__BUTTONS_BUTTON" data-fancybox="" data-src="#hidden-form" href="javascript:;" data-scope="Вопрос по проекту <?=htmlspecialchars($fullName)?>">Задать вопрос</a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <div class="c-sel--div__CONTAINER" style="margin-top:64px;">
    <div class="zx-tabs" id="zxTabs">
      <?if(!empty($plans)):?><button type="button" class="zx-tab is-active" data-tab="plan">Планировка</button><?endif?>
      <button type="button" class="zx-tab <?=empty($plans)?'is-active':''?>" data-tab="gallery">Галерея</button>
      <?if(!empty($arResult['DETAIL_TEXT'])):?><button type="button" class="zx-tab" data-tab="specs">Характеристики</button><?endif?>
      <button type="button" class="zx-tab" data-tab="developer">Застройщик</button>
    </div>

    <?if(!empty($plans)):?>
    <div class="zx-tab-panel is-active" data-panel="plan">
      <div class="zx-plan__tabs" id="zxPlanTabs">
        <?foreach($plans as $i=>$pl):?>
          <button type="button" class="zx-plan__tab <?=$i===0?'is-active':''?>" data-plan="<?=$i?>"><?=$pl['title']?></button>
        <?endforeach?>
        <?
        $pdfPlanSrc = '';
        if (!empty($properties['PDF_PLAN'])) {
            $pdfFile = CFile::GetFileArray($properties['PDF_PLAN']);
            if ($pdfFile && !empty($pdfFile['SRC'])) $pdfPlanSrc = $pdfFile['SRC'];
        }
        if ($pdfPlanSrc):
        ?>
          <a class="zx-btn zx-btn--ghost zx-btn--sm font__BUTTONS_BUTTON" style="margin-left:12px;" href="<?=htmlspecialchars($pdfPlanSrc)?>" download target="_blank" rel="noopener">Скачать PDF</a>
        <?else:?>
          <a class="zx-btn zx-btn--ghost zx-btn--sm font__BUTTONS_BUTTON" style="margin-left:12px;" data-fancybox="" data-src="#hidden-form" href="javascript:;" data-scope="Запрос PDF планировки — <?=htmlspecialchars($fullName)?>">Запросить PDF</a>
        <?endif?>
      </div>
      <?foreach($plans as $i=>$pl):?>
        <div class="zx-plan__panel <?=$i===0?'is-active':''?>" data-plan-panel="<?=$i?>">
          <a class="zx-plan__image" href="<?=$pl['src']?>" data-zx-lb="plan">
            <img src="<?=$pl['src']?>" alt="<?=htmlspecialchars($pl['title'])?>" loading="lazy">
          </a>
          <?if(!empty($pl['desc'])):?>
            <div class="zx-plan__desc font__BODY_TEXT_PRIMARY"><?=$pl['desc']?></div>
          <?endif?>
        </div>
      <?endforeach?>
    </div>
    <?endif?>

    <div class="zx-tab-panel <?=empty($plans)?'is-active':''?>" data-panel="gallery">
      <?if(!empty($allPhotos)):?>
      <div class="zx-gallery-tab">
        <?foreach(array_slice($allPhotos, 0, 8) as $i=>$src):
          $cls = ($i===2 || $i===7) ? ' zx-gallery-tab__item--wide' : '';
        ?>
          <a class="zx-img-ph zx-gallery-tab__item<?=$cls?>" href="<?=$src?>" data-zx-lb="gallery">
            <img src="<?=$src?>" alt="" loading="lazy">
          </a>
        <?endforeach?>
      </div>
      <?else:?>
        <div class="zx-cat-empty">Галерея скоро появится</div>
      <?endif?>
    </div>

    <?if(!empty($arResult['DETAIL_TEXT'])):?>
    <div class="zx-tab-panel" data-panel="specs">
      <div class="font__BODY_TEXT_PRIMARY" style="max-width:920px;"><?=$arResult['DETAIL_TEXT']?></div>
    </div>
    <?endif?>

    <div class="zx-tab-panel" data-panel="developer">
      <div class="zx-dev-block">
        <div class="zx-dev-block__head">
          <div class="zx-dev-block__logo zx-dev-block__logo--brand">
            <span class="zx-dev-block__logo-abbr">ZX</span>
          </div>
          <div>
            <div class="zx-eyebrow" style="margin-bottom:4px;">Застройщик проекта</div>
            <h3 class="zx-dev-block__name font__HEADING_BLOCK_TITLE">Земельный экспресс</h3>
            <div class="zx-dev-block__meta">
              <span>★ 4.9</span><span>·</span><span>128 отзывов</span><span>·</span><span>с 2019 года</span>
            </div>
          </div>
        </div>
        <div class="zx-dev-block__stats">
          <div><div class="zx-mini-stat__n">500+</div><div class="zx-mini-stat__l">проектов сдано</div></div>
          <div><div class="zx-mini-stat__n">24</div><div class="zx-mini-stat__l">в работе</div></div>
          <div><div class="zx-mini-stat__n">5 лет</div><div class="zx-mini-stat__l">гарантия</div></div>
          <div><div class="zx-mini-stat__n">90</div><div class="zx-mini-stat__l">дней в среднем</div></div>
          <a class="zx-btn zx-btn--primary zx-btn--sm font__BUTTONS_BUTTON" href="/zastrojshhiki/">Все застройщики →</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Калькулятор проекта (из бэкапа) -->
  <section class="doma-detail-v2__calc">
    <div class="c-sel--div__CONTAINER">
      <div class="doma-detail-v2__calc-box" data-calc data-base-price="<?=$basePrice?>" data-base-square="<?=$baseSquare?>">
        <div class="doma-detail-v2__calc-head">
          <p class="font__HEADING_SECTION_TITLE" style="margin:0;">Калькулятор проекта</p>
          <p class="font__BODY_TEXT_CAPTION" style="color:var(--text-secondary);margin:8px 0 0;">Подберите параметры — цена пересчитывается автоматически</p>
        </div>
        <div class="doma-detail-v2__calc-grid">
          <div class="doma-detail-v2__calc-controls">

            <div class="doma-detail-v2__calc-field">
              <label class="font__BODY_TEXT_PRIMARY">Площадь дома: <b data-calc-square><?=$baseSquare?></b> м²</label>
              <input type="range" min="<?=max(40,$baseSquare-40)?>" max="<?=$baseSquare+80?>" step="5" value="<?=$baseSquare?>" data-calc-range="square">
              <div class="zx-calc-range-edges"><span><?=max(40,$baseSquare-40)?> м²</span><span><?=$baseSquare+80?> м²</span></div>
            </div>

            <!-- Текущая комплектация дома -->
            <div class="zx-calc-spec" data-spec>
              <div class="zx-calc-spec__head">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M3 8l3 3 7-7" stroke="#00BF3F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>В&nbsp;комплектацию входит</span>
              </div>
              <div class="zx-calc-spec__grid">
                <div class="zx-calc-spec__item"><span class="zx-calc-spec__ico">🧱</span><div><small>Стены</small><b data-spec-val="walls">Газобетон D500</b></div></div>
                <div class="zx-calc-spec__item"><span class="zx-calc-spec__ico">🏠</span><div><small>Крыша</small><b data-spec-val="roof">Металлочерепица</b></div></div>
                <div class="zx-calc-spec__item"><span class="zx-calc-spec__ico">🪟</span><div><small>Окна</small><b data-spec-val="windows">Двухкамерные</b></div></div>
                <div class="zx-calc-spec__item"><span class="zx-calc-spec__ico">🧊</span><div><small>Утепление</small><b data-spec-val="insulation">Стандарт 100&nbsp;мм</b></div></div>
                <div class="zx-calc-spec__item"><span class="zx-calc-spec__ico">🏗</span><div><small>Фундамент</small><b data-spec-val="foundation">Свайно-ростверковый</b></div></div>
              </div>
              <div class="zx-calc-spec__adds" data-spec-adds hidden>
                <span class="zx-calc-spec__adds-label">Доп.&nbsp;опции:</span>
                <span data-spec-adds-list></span>
              </div>
            </div>

            <!-- Триггер раскрытия улучшений -->
            <button type="button" class="zx-calc-upsell" data-extras-toggle aria-expanded="false">
              <span class="zx-calc-upsell__icon">🎨</span>
              <span class="zx-calc-upsell__text">
                <b>Улучшить дом</b>
                <span>Стены, крыша, окна, утепление, доп.&nbsp;опции — покажем как поднять качество и&nbsp;комфорт</span>
              </span>
              <span class="zx-calc-upsell__cta">
                <span class="zx-calc-upsell__cta-text" data-cta-open>Развернуть</span>
                <span class="zx-calc-upsell__cta-text zx-calc-upsell__cta-text--alt" data-cta-close>Свернуть</span>
                <span class="zx-calc-upsell__chevron" aria-hidden="true">
                  <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M3 5l4 4 4-4" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
              </span>
            </button>

            <!-- Свёрнутая часть: материалы, фундамент, доп. опции -->
            <div class="zx-calc-extras" data-extras hidden>

            <?
            // Опции калькулятора — берутся из админки (/bitrix/admin/zemex_homes_calc.php).
            // Подсказки (zx-calc-hint__pop) остаются захардкоженными — их редактирование не вынесено.
            $catHints = [
              'walls'      => ['Что вы получаете при апгрейде стен:', 'оптимальный баланс цены и тепла. Срок службы 70+ лет.', '+30% к сроку службы, лучше шумоизоляция, выше пожаробезопасность.', 'экологичность, тёплая стена без отделки, эстетика «премиум».', 'На что влияет материал стен'],
              'roof'       => ['На что влияет крыша:', 'стандарт, гарантия 20 лет.', 'гарантия до 50 лет, премиум-внешний вид, не выгорает.', 'герметичность швов, срок 70+ лет, стиль «лофт».', 'На что влияет материал крыши'],
              'windows'    => ['Зачем доплачивать за окна:', 'базовая комплектация.', '−30% теплопотерь, экономия на отоплении до 15 000 ₽/год.', 'большие проёмы без потери тепла, идеально для панорамных окон.', 'На что влияют окна'],
              'insulation' => ['Что даёт усиленное утепление:', 'соответствует СНиП.', '−20% к счетам за газ/электричество, тёплая стена даже в −30°C.', 'пассивный дом: счёт за тепло снижается до 30%, окупается за 5–7 лет.', 'На что влияет утепление'],
              'foundation' => ['Какой фундамент выбрать:', 'для лёгких домов, подходит большинству участков.', 'для тяжёлых стен (кирпич), сложного грунта, гарантия от трещин.', 'даёт тёплый цокольный этаж, можно использовать как кладовую/котельную.', 'На что влияет фундамент'],
            ];
            foreach (['walls','roof','windows','insulation','foundation'] as $cat):
              if (empty($calcCfg[$cat])) continue;
              $cc = $calcCfg[$cat];
              $hint = $catHints[$cat];
            ?>
            <div class="doma-detail-v2__calc-field">
              <label class="font__BODY_TEXT_PRIMARY">
                <?=htmlspecialchars($cc['label'])?>
                <span class="zx-calc-hint" tabindex="0" role="button" aria-label="<?=htmlspecialchars($hint[4])?>">
                  <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><circle cx="7" cy="7" r="6" stroke="currentColor" stroke-width="1.4"/><path d="M7 6.2v3.6M7 4.4v.4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                  <span class="zx-calc-hint__pop">
                    <b><?=htmlspecialchars($hint[0])?></b>
                    <ul>
                      <?foreach ($cc['options'] as $i => $opt):?>
                        <li><u><?=htmlspecialchars($opt['label'])?></u> — <?=htmlspecialchars($hint[$i+1] ?? '')?></li>
                      <?endforeach?>
                    </ul>
                  </span>
                </span>
              </label>
              <div class="doma-detail-v2__calc-pills zx-calc-pills--stack" data-calc-group="<?=$cat?>">
                <?foreach ($cc['options'] as $i => $opt):
                  $isBase = ($i === 0);
                ?>
                <button type="button"<?=$isBase ? ' class="is-active"' : ''?> data-val="<?=(int)$opt['price']?>" data-label="<?=htmlspecialchars($opt['label'])?>">
                  <span><?=htmlspecialchars($opt['label'])?></span>
                  <small><?=$isBase ? 'в&nbsp;базе' : zx_calc_fmt_plus($opt['price'])?></small>
                </button>
                <?endforeach?>
              </div>
            </div>
            <?endforeach?>

            <div class="doma-detail-v2__calc-field">
              <label class="font__BODY_TEXT_PRIMARY">
                Дополнительно
                <span class="zx-calc-hint" tabindex="0" role="button" aria-label="Дополнительные опции">
                  <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><circle cx="7" cy="7" r="6" stroke="currentColor" stroke-width="1.4"/><path d="M7 6.2v3.6M7 4.4v.4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                  <span class="zx-calc-hint__pop">
                    <b>Что улучшит комфорт:</b>
                    <ul>
                      <li><u>Тёплый пол</u> — −15% к&nbsp;затратам на&nbsp;отопление, ровный прогрев без батарей.</li>
                      <li><u>Рекуператор</u> — свежий воздух без сквозняков и&nbsp;потерь тепла.</li>
                      <li><u>Умный дом</u> — управление светом/климатом со&nbsp;смартфона, экономия на&nbsp;коммуналке до&nbsp;20%.</li>
                      <li><u>Панорамные окна</u> — больше света, эффект «дом на&nbsp;природе».</li>
                    </ul>
                  </span>
                </span>
              </label>
              <div class="doma-detail-v2__calc-checks">
                <?foreach (($calcCfg['extras'] ?? []) as $e):?>
                  <label><input type="checkbox" data-calc-add="<?=(int)$e['price']?>" data-label="<?=htmlspecialchars($e['label'])?>"><span><?=htmlspecialchars($e['label'])?><small><?=zx_calc_fmt_plus($e['price'])?></small></span></label>
                <?endforeach?>
              </div>
            </div>

            </div><!-- /.zx-calc-extras -->

          </div>
          <aside class="doma-detail-v2__calc-result">
            <div class="doma-detail-v2__calc-result-inner">
              <p class="font__BODY_TEXT_CAPTION" style="color:rgba(255,255,255,.75);margin:0;">Ориентировочная стоимость</p>
              <p class="doma-detail-v2__calc-total font__HEADING_SECTION_TITLE" data-calc-total>—</p>
              <p class="font__BODY_TEXT_CAPTION doma-detail-v2__calc-monthly" data-calc-monthly></p>
              <p class="font__BODY_TEXT_CAPTION" style="color:rgba(255,255,255,.65);margin:4px 0 16px;">* расчёт предварительный, точную стоимость подтвердит менеджер</p>
              <a class="doma-detail-v2__calc-cta font__BUTTONS_BUTTON" data-fancybox="" data-src="#hidden-form" href="javascript:;" data-scope="Калькулятор — <?=htmlspecialchars($fullName)?>" data-calc-cta>Получить точный расчёт</a>
            </div>
          </aside>
        </div>
      </div>
    </div>
  </section>

  <!-- Ипотечный блок -->
  <section class="zx-mortgage-wrap">
    <?$APPLICATION->IncludeComponent(
        "zemex:mortgage.section",
        "",
        array(
            "HERO_TITLE" => "Поможем с ипотекой на ".$fullName,
            "FORM_DESCRIPTION" => array(
                "Подадим заявку в партнёрские банки и получим ставку со скидкой,",
                "не передавая ваши контакты напрямую и без навязчивых звонков."
            ),
            "CACHE_TYPE" => "A",
            "CACHE_TIME" => "3600",
        ),
        false
    );?>
  </section>

  <!-- Блок акций (promo.cards — тот же, что на главной) -->
  <?$APPLICATION->IncludeComponent(
    "zemex:promo.cards",
    "",
    array(
      "MAIN_BANNER_TITLE" => "Дом вашей мечты — за 90 дней",
      "MAIN_BANNER_DESC" => "Более 50 готовых проектов домов с фиксированной ценой в договоре. От подбора участка до вручения ключей под ключ.",
      "MAIN_BANNER_TAG" => "Проекты домов",
      "MAIN_BANNER_BUTTON" => "Выбрать проект",
      "MAIN_BANNER_LINK" => "/doma-i-kottedzhi/",
      "MAIN_BANNER_BG" => "/local/templates/zemexx_redisign/images/business-family-bg.webp",

      "MORTGAGE_CARD_TITLE" => "Семейная ипотека от 6%",
      "MORTGAGE_CARD_DESC" => "Оформим заявку в банки-партнёры и получим ставку со скидкой. Одобрение за 3 дня",
      "MORTGAGE_CARD_TAG" => "Банки-партнёры",
      "MORTGAGE_CARD_BUTTON" => "Получить условия",
      "MORTGAGE_CARD_LINK" => "#zxc-section",
      "MORTGAGE_CARD_TIMER_END" => date('Y-m-d H:i:s', strtotime('+30 days')),
      "MORTGAGE_CARD_TIMER_TEXT" => "До окончания\nпредложения",

      "BENEFITS_CARD_TITLE" => "Расчёт за 1 минуту",
      "BENEFITS_CARD_DESC" => "Подберите параметры — цена обновится автоматически",
      "BENEFITS_CARD_TAG" => "Калькулятор",
      "BENEFITS_CARD_BUTTON" => "Рассчитать стоимость",

      "SIDE_BANNER_TITLE" => "Фиксируем цену\nв договоре",
      "SIDE_BANNER_BUTTON" => "Подробнее",
      "SIDE_BANNER_LINK" => "/actions/",
      "SIDE_BANNER_BG" => "/local/templates/zemexx_redisign/images/near-forest.webp",

      "CACHE_TYPE" => "A",
      "CACHE_TIME" => "3600",
    ),
    false
  );?>

  <?
  $detailCfg = function_exists('zemex_get_homes_settings') ? zemex_get_homes_settings('detail') : [];
  $reviewLines = !empty($detailCfg['REVIEWS']) && is_array($detailCfg['REVIEWS']) ? $detailCfg['REVIEWS'] : [
      'Анна М. | дом 96 м², 2024 | «Строили дом 96 м² за 4 месяца. Сроки выдержали, качество отличное. Отдельное спасибо прорабу за постоянную связь и отчёты по фото.»',
      'Дмитрий К. | дом 128 м², 2023 | «Самое ценное — никаких внезапных доплат. Всё по смете. Ребята профи, рекомендуем друзьям. Уже 2 года живём — ни одной претензии к качеству.»',
      'Елена и Сергей | семейная ипотека, 2024 | «Помогли и с проектом, и с ипотекой — семейная под 6%. Въехали через 3,5 месяца после подписания. Дом тёплый, всё продумано до мелочей.»',
  ];
  ?>
  <section class="zx-reviews">
    <div class="c-sel--div__CONTAINER">
      <div class="zx-section-head">
        <div>
          <div class="zx-eyebrow" style="margin-bottom:8px;">Что говорят клиенты</div>
          <h2 class="font__HEADING_SECTION_TITLE" style="margin:0;">Реальные отзывы</h2>
        </div>
      </div>
      <div class="zx-reviews__grid">
        <?foreach($reviewLines as $line):
          $parts = function_exists('zemex_split_pipe') ? zemex_split_pipe($line, 3) : array_map('trim', explode('|', $line, 3));
          $author = $parts[0] ?? '';
          $meta   = $parts[1] ?? '';
          $text   = $parts[2] ?? '';
          if(!$text) continue;
        ?>
        <figure class="zx-review">
          <div class="zx-review__stars">★★★★★</div>
          <blockquote class="font__BODY_TEXT_PRIMARY"><?=htmlspecialchars($text)?></blockquote>
          <figcaption><b><?=htmlspecialchars($author)?></b><?if($meta):?><span><?=htmlspecialchars($meta)?></span><?endif?></figcaption>
        </figure>
        <?endforeach?>
      </div>
    </div>
  </section>

  <section class="zx-faq">
    <div class="c-sel--div__CONTAINER">
      <div class="zx-section-head">
        <div>
          <div class="zx-eyebrow" style="margin-bottom:8px;">Ответы на частые вопросы</div>
          <h2 class="font__HEADING_SECTION_TITLE" style="margin:0;">Что важно знать</h2>
        </div>
      </div>
      <?
      $faqLines = !empty($detailCfg['FAQ_LINES']) && is_array($detailCfg['FAQ_LINES']) ? $detailCfg['FAQ_LINES'] : [
        'Можно ли изменить планировку под себя? | Да. Архитекторы адаптируют проект под ваш участок, образ жизни и бюджет. Первые корректировки — бесплатно.',
        'Что входит в стоимость и что не входит? | В базу — всё из выбранной комплектации (Контур/Оптима/Премиум), точный состав в таблице. Не входит: участок, подключение к сетям, благоустройство. Это обсуждаем отдельно и фиксируем в смете.',
        'Как фиксируется цена? | Цена фиксируется в договоре в день подписания. Никаких индексаций и «доплат за материалы» после — все риски на нас.',
        'Какие документы нужны для ипотеки? | Паспорт, справка о доходах (2-НДФЛ или справка банка), СНИЛС. Оформим заявку в банки-партнёры — одобрение за 3 дня, ставка от 6%.',
        'Что с гарантией? | 5 лет на конструктив (фундамент, стены, кровля) и инженерные сети. В течение срока устраняем за свой счёт.',
        'А если пока не готов строить? | Запишитесь на бесплатную экскурсию в готовый дом — посмотрите качество вживую. Без обязательств.',
      ];
      foreach($faqLines as $i=>$line):
        $parts = function_exists('zemex_split_pipe') ? zemex_split_pipe($line, 2) : array_map('trim', explode('|', $line, 2));
        $q = $parts[0] ?? '';
        $a = $parts[1] ?? '';
        if(!$q) continue;
      ?>
        <details class="zx-faq__item" <?=$i===0?'open':''?>>
          <summary><span class="font__HEADING_CARD_TITLE"><?=htmlspecialchars($q)?></span><span class="zx-faq__plus"></span></summary>
          <div class="zx-faq__body font__BODY_TEXT_PRIMARY"><?=htmlspecialchars($a)?></div>
        </details>
      <?endforeach?>
    </div>
  </section>

  <div class="c-sel--div__CONTAINER" style="margin-top:96px;">
    <div class="zx-similar-head">
      <div>
        <div class="zx-eyebrow" style="margin-bottom:8px;">Похожие проекты</div>
        <h2 class="font__HEADING_SECTION_TITLE" style="margin:0;">Что ещё посмотреть</h2>
      </div>
      <a class="zx-btn zx-btn--ghost font__BUTTONS_BUTTON" href="/doma-i-kottedzhi/">Весь каталог →</a>
    </div>
    <?
    global $arrFilterOthers;
    $arrFilterOthers = ['!ID' => $arResult['ID']];
    $APPLICATION->IncludeComponent(
        "bitrix:news.list",
        "zx_similar",
        array(
            "IBLOCK_TYPE" => "content",
            "IBLOCK_ID" => $arResult['IBLOCK_ID'],
            "NEWS_COUNT" => "3",
            "SORT_BY1" => "SORT",
            "SORT_ORDER1" => "ASC",
            "FILTER_NAME" => "arrFilterOthers",
            "FIELD_CODE" => array("DETAIL_PICTURE","PREVIEW_PICTURE"),
            "PROPERTY_CODE" => array("SQUARE","PRICE","FLOORS","BEDROOMS","MATERIAL","READY","IN_PROCESS"),
            "CHECK_DATES" => "Y",
            "CACHE_TYPE" => "A",
            "CACHE_TIME" => "3600",
            "CACHE_GROUPS" => "Y",
            "DISPLAY_TOP_PAGER" => "N",
            "DISPLAY_BOTTOM_PAGER" => "N",
            "SET_TITLE" => "N",
            "SET_LAST_MODIFIED" => "N",
            "INCLUDE_IBLOCK_INTO_CHAIN" => "N",
            "ADD_SECTIONS_CHAIN" => "N",
            "HIDE_LINK_WHEN_NO_DETAIL" => "N",
            "INCLUDE_SUBSECTIONS" => "Y"
        )
    );
    ?>
  </div>
</div>

<div class="zx-sticky-cta" id="zxStickyCta">
  <div class="zx-sticky-cta__inner">
    <?if($priceInt):?>
      <div class="zx-sticky-cta__price">
        <div class="zx-sticky-cta__price-lab">от</div>
        <div class="zx-sticky-cta__price-val"><?=zx_price_short($priceInt)?></div>
      </div>
    <?endif?>
    <a class="zx-btn zx-btn--primary zx-sticky-cta__btn font__BUTTONS_BUTTON" data-fancybox="" data-src="#hidden-form" href="javascript:;" data-scope="Расчёт проекта <?=htmlspecialchars($fullName)?>">Получить расчёт →</a>
  </div>
</div>

<div class="zx-lb" id="zxLb" aria-hidden="true">
  <button class="zx-lb__close" type="button" aria-label="Закрыть">×</button>
  <button class="zx-lb__nav zx-lb__nav--prev" type="button" aria-label="Предыдущее">‹</button>
  <button class="zx-lb__nav zx-lb__nav--next" type="button" aria-label="Следующее">›</button>
  <img class="zx-lb__img" alt="">
  <div class="zx-lb__counter"></div>
</div>

<script>
(function(){
  // Swiper для hero-слайдов
  if(typeof Swiper !== 'undefined' && document.querySelector('.doma-detail-v2__swiper')){
    new Swiper('.doma-detail-v2__swiper',{
      loop: true,
      autoplay:{ delay: 5000, disableOnInteraction: false },
      pagination:{ el: '.doma-detail-v2__swiper .swiper-pagination', clickable: true },
      navigation:{
        prevEl: '.doma-detail-v2__swiper .swiper-button-prev',
        nextEl: '.doma-detail-v2__swiper .swiper-button-next'
      },
      speed: 700
    });
  }

  // Tabs (Галерея / Характеристики / Застройщик)
  var tabs = document.getElementById('zxTabs');
  if(tabs){
    tabs.addEventListener('click', function(e){
      var b = e.target.closest('.zx-tab'); if(!b) return;
      var key = b.dataset.tab;
      tabs.querySelectorAll('.zx-tab').forEach(function(x){ x.classList.toggle('is-active', x===b); });
      document.querySelectorAll('.zx-tab-panel').forEach(function(p){
        p.classList.toggle('is-active', p.dataset.panel === key);
      });
    });
  }

  // Планировка — переключение этажей
  var planTabs = document.getElementById('zxPlanTabs');
  if(planTabs){
    planTabs.addEventListener('click', function(e){
      var b = e.target.closest('[data-plan]'); if(!b) return;
      var idx = b.dataset.plan;
      planTabs.querySelectorAll('[data-plan]').forEach(function(x){ x.classList.toggle('is-active', x===b); });
      document.querySelectorAll('[data-plan-panel]').forEach(function(p){ p.classList.toggle('is-active', p.dataset.planPanel === idx); });
    });
  }

  // Калькулятор проекта (из бэкапа)
  document.querySelectorAll('[data-calc]').forEach(function(box){
    var basePrice = parseInt(box.dataset.basePrice, 10) || 0;
    var baseSq    = parseFloat(box.dataset.baseSquare) || 100;
    var perM = baseSq > 0 ? basePrice / baseSq : 0;
    var groupLabels = {
      walls:      'Стены',
      roof:       'Крыша',
      windows:    'Окна',
      insulation: 'Утепление',
      foundation: 'Фундамент'
    };
    var state = {
      walls:      { val:0, label:'Газобетон D500' },
      roof:       { val:0, label:'Металлочерепица' },
      windows:    { val:0, label:'Двухкамерный' },
      insulation: { val:0, label:'Стандарт 100 мм' },
      foundation: { val:0, label:'Свайно-ростверковый' },
      square: baseSq,
      adds: {}
    };
    var totalEl = box.querySelector('[data-calc-total]');
    var sqEl    = box.querySelector('[data-calc-square]');
    var ctaEl   = box.querySelector('[data-calc-cta]');
    var monthEl = box.querySelector('[data-calc-monthly]');
    var specEls = {};
    box.querySelectorAll('[data-spec-val]').forEach(function(el){ specEls[el.dataset.specVal] = el; });
    var addsBlock = box.querySelector('[data-spec-adds]');
    var addsList  = box.querySelector('[data-spec-adds-list]');
    function fmt(n){ return Math.round(n).toLocaleString('ru-RU').replace(/,/g,' ') + ' ₽'; }
    function render(){
      var total = perM*state.square
        + state.walls.val
        + state.roof.val
        + state.windows.val
        + state.insulation.val
        + state.foundation.val;
      Object.values(state.adds).forEach(function(v){ total += v.val; });
      if(totalEl) totalEl.textContent = fmt(total);
      if(sqEl) sqEl.textContent = state.square;
      if(monthEl){
        var m = Math.round(total * 0.0072 / 1000) * 1000;
        monthEl.innerHTML = '🏦 В ипотеку ~ <b>' + m.toLocaleString('ru-RU').replace(/,/g,' ') + ' ₽ / мес</b> · от 6%';
      }
      // Обновляем блок текущей комплектации
      Object.keys(groupLabels).forEach(function(k){
        if(specEls[k] && state[k] && state[k].label){
          specEls[k].textContent = state[k].label;
          specEls[k].classList.toggle('is-upgraded', state[k].val > 0);
        }
      });
      // Доп. опции — показываем только если выбраны
      var addLabels = Object.values(state.adds).map(function(v){ return v.label; });
      if(addsBlock && addsList){
        if(addLabels.length){
          addsList.textContent = addLabels.join(', ');
          addsBlock.hidden = false;
        } else {
          addsBlock.hidden = true;
        }
      }
      if(ctaEl) ctaEl.setAttribute('data-scope', 'Калькулятор ~' + fmt(total));
      // Синхронизируем стоимость в блоке ипотеки
      if(typeof window.zxcSetCost === 'function') window.zxcSetCost(total);
    }
    box.querySelectorAll('[data-calc-group]').forEach(function(g){
      g.querySelectorAll('button').forEach(function(b){
        b.addEventListener('click', function(){
          g.querySelectorAll('button').forEach(function(x){ x.classList.remove('is-active'); });
          b.classList.add('is-active');
          state[g.dataset.calcGroup] = { val: parseFloat(b.dataset.val), label: b.dataset.label };
          render();
        });
      });
    });
    box.querySelectorAll('[data-calc-range]').forEach(function(r){
      r.addEventListener('input', function(){ state[r.dataset.calcRange] = parseFloat(r.value); render(); });
    });
    box.querySelectorAll('[data-calc-add]').forEach(function(c){
      c.addEventListener('change', function(){
        var v = parseFloat(c.dataset.calcAdd), l = c.dataset.label;
        if(c.checked) state.adds[l] = { val: v, label: l }; else delete state.adds[l];
        render();
      });
    });
    // Раскрытие/сворачивание блока «Улучшить дом»
    var extrasBtn = box.querySelector('[data-extras-toggle]');
    var extras    = box.querySelector('[data-extras]');
    if(extrasBtn && extras){
      extrasBtn.addEventListener('click', function(){
        var isOpen = !extras.hidden;
        extras.hidden = isOpen;
        extrasBtn.classList.toggle('is-open', !isOpen);
        extrasBtn.setAttribute('aria-expanded', String(!isOpen));
      });
    }
    // Подсказки: клик/клавиатура для мобильных и accessibility
    box.querySelectorAll('.zx-calc-hint').forEach(function(h){
      h.addEventListener('click', function(e){
        e.stopPropagation();
        var open = h.classList.contains('is-open');
        box.querySelectorAll('.zx-calc-hint.is-open').forEach(function(x){ x.classList.remove('is-open'); });
        if(!open) h.classList.add('is-open');
      });
      h.addEventListener('keydown', function(e){
        if(e.key === 'Enter' || e.key === ' '){ e.preventDefault(); h.click(); }
        if(e.key === 'Escape') h.classList.remove('is-open');
      });
    });
    document.addEventListener('click', function(e){
      if(!e.target.closest('.zx-calc-hint')){
        box.querySelectorAll('.zx-calc-hint.is-open').forEach(function(x){ x.classList.remove('is-open'); });
      }
    });
    render();
    // Дожидаемся появления API ипотечного блока и пушим стоимость
    var zxcWait = 0;
    var zxcTimer = setInterval(function(){
      zxcWait++;
      if(typeof window.zxcSetCost === 'function'){
        clearInterval(zxcTimer);
        render();
      } else if(zxcWait > 30){ // ~3s — сдаёмся
        clearInterval(zxcTimer);
      }
    }, 100);
  });

  var stickyCta = document.getElementById('zxStickyCta');
  var heroSec = document.querySelector('.doma-detail-v2__hero');
  if(stickyCta && heroSec){
    window.addEventListener('scroll', function(){
      var show = window.scrollY > heroSec.offsetTop + heroSec.offsetHeight * 0.7;
      stickyCta.classList.toggle('is-visible', show);
    }, { passive: true });
  }

  var lb = document.getElementById('zxLb');
  if(lb){
    var lbImg = lb.querySelector('.zx-lb__img');
    var lbCounter = lb.querySelector('.zx-lb__counter');
    var groups = {};
    document.querySelectorAll('[data-zx-lb]').forEach(function(a){
      var g = a.dataset.zxLb; groups[g] = groups[g] || []; groups[g].push(a.href);
    });
    var cg = null, ci = 0;
    function open(g, i){ cg=g; ci=i; lbImg.src = groups[g][i]; lbCounter.textContent = (i+1)+' / '+groups[g].length; lb.classList.add('is-open'); lb.setAttribute('aria-hidden','false'); document.body.style.overflow='hidden'; }
    function close(){ lb.classList.remove('is-open'); lb.setAttribute('aria-hidden','true'); document.body.style.overflow=''; }
    function nav(d){ if(!cg) return; var arr=groups[cg]; ci=(ci+d+arr.length)%arr.length; lbImg.src=arr[ci]; lbCounter.textContent=(ci+1)+' / '+arr.length; }
    document.addEventListener('click', function(e){
      var a = e.target.closest('[data-zx-lb]'); if(!a) return;
      e.preventDefault();
      var g = a.dataset.zxLb;
      open(g, groups[g].indexOf(a.href));
    });
    lb.querySelector('.zx-lb__close').addEventListener('click', close);
    lb.querySelector('.zx-lb__nav--prev').addEventListener('click', function(){nav(-1);});
    lb.querySelector('.zx-lb__nav--next').addEventListener('click', function(){nav(1);});
    lb.addEventListener('click', function(e){ if(e.target===lb) close(); });
    document.addEventListener('keydown', function(e){
      if(!lb.classList.contains('is-open')) return;
      if(e.key==='Escape') close();
      if(e.key==='ArrowLeft') nav(-1);
      if(e.key==='ArrowRight') nav(1);
    });
  }
})();
</script>

<!-- ============ FEEDBACK MODAL ============ -->
<div class="vp-heroModal1 form_container zx-feedback-modal"
     data-header="Задайте вопрос"
     data-form_class="vp-heroModal1--form__FORM"
     data-form="<?= defined('FORM_CONSULT') ? FORM_CONSULT : 7 ?>"></div>
<div class="vp-heroModal2 sucsess_heroModal zx-feedback-modal-success">
    <div class="vp-heroModal2--div__READY">
        <img class="vp-heroModal2--img__READY" src="<?= SITE_TEMPLATE_PATH ?>/images/vp-hero-ready.svg" alt="галочка">
        <h2 class="vp-heroModal2--h2 font__HEADING_SECTION_TITLE">Спасибо за заявку!</h2>
        <p class="vp-heroModal2--p__READY font__BODY_TEXT_PRIMARY">Мы свяжемся с вами в ближайшее время.</p>
        <button class="vp-heroModal2--button__CLEAR_DT font__BUTTONS_BUTTON">Понятно</button>
    </div>
</div>
<script>
(function(){
  function init(){
    var modal = document.querySelector('.vp-heroModal1.zx-feedback-modal');
    if(!modal || modal._zxBound) return;
    modal._zxBound = true;
    var body = document.querySelector('.c-body') || document.body;
    var ZX_PROJECT_NAME = <?=json_encode($fullName, JSON_UNESCAPED_UNICODE)?>;
    function open(scope){
      var scopeInp   = modal.querySelector('input[data-name="SCOPE"]');
      var projectInp = modal.querySelector('input[data-name="PROJECT"]');
      if(scopeInp)   scopeInp.value = scope || '';
      if(projectInp) projectInp.value = ZX_PROJECT_NAME || '';
      // Динамический заголовок модалки в зависимости от контекста
      var titleEl = modal.querySelector('.vp-heroModal1--h2, .form_header, h2, h3');
      if(titleEl && scope){
        var headerMap = {
          'Расчёт':           'Расчёт стоимости проекта',
          'Калькулятор':      'Точный расчёт от менеджера',
          'PDF':              'Запрос PDF планировки',
          'Запрос PDF':       'Запрос PDF планировки',
          'Вопрос':           'Задайте вопрос по проекту',
          'Консультация':     'Консультация по выбору дома',
          'Подбор':           'Персональный подбор'
        };
        var newTitle = '';
        for(var k in headerMap){
          if(scope.indexOf(k) === 0){ newTitle = headerMap[k]; break; }
        }
        if(newTitle) titleEl.textContent = newTitle;
      }
      modal.classList.add('__vp-heroModal1__VISIBLE');
      body.classList.add('__c-body__FIXED');
    }
    function close(){
      modal.classList.remove('__vp-heroModal1__VISIBLE');
      body.classList.remove('__c-body__FIXED');
    }
    document.addEventListener('click', function(e){
      var btn = e.target.closest('.js-zx-feedback, [data-src="#hidden-form"]');
      if(!btn) return;
      if(e.target.closest('.zxc')) return;
      e.preventDefault();
      open(btn.getAttribute('data-scope'));
    });
    modal.addEventListener('click', function(e){
      if(e.target === modal) close();
    });
    document.addEventListener('keydown', function(e){
      if(e.key === 'Escape' && modal.classList.contains('__vp-heroModal1__VISIBLE')) close();
    });
  }
  if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
</script>
