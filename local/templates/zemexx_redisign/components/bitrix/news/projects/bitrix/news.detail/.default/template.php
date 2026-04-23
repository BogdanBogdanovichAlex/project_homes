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
?>
<style>body > .breadcrumbs, .content > .breadcrumbs, .bx-breadcrumb{display:none !important;}</style>
<div class="zx-scope">
  <!-- HERO (из бэкапа) -->
  <section class="doma-detail-v2__hero">
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
        <ul class="doma-detail-v2__bc">
          <li><a class="font__BODY_TEXT_CAPTION" href="/">Главная</a></li>
          <li><a class="font__BODY_TEXT_CAPTION" href="/doma-i-kottedzhi/"><span>●</span>Дома и коттеджи</a></li>
        </ul>

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
        <div class="doma-detail-v2__bar-stats">
          <?if(!empty($properties['SQUARE'])):?>
            <div class="doma-detail-v2__bar-stat"><span>Площадь</span><b><?=$properties['SQUARE']?> м²</b></div>
          <?endif?>
          <?if(!empty($properties['FLOORS'])):?>
            <div class="doma-detail-v2__bar-stat"><span>Этажность</span><b><?=$properties['FLOORS']?></b></div>
          <?endif?>
          <?if(!empty($properties['BEDROOMS'])):?>
            <div class="doma-detail-v2__bar-stat"><span>Спальни</span><b><?=$properties['BEDROOMS']?></b></div>
          <?endif?>
          <?if(!empty($properties['BUILD_TIME'])):?>
            <div class="doma-detail-v2__bar-stat"><span>Срок</span><b><?=htmlspecialchars($properties['BUILD_TIME'])?></b></div>
          <?endif?>
          <?if($material):?>
            <div class="doma-detail-v2__bar-stat"><span>Материал</span><b><?=htmlspecialchars($material)?></b></div>
          <?endif?>
        </div>
        <div class="doma-detail-v2__bar-price">
          <?if($priceInt):?>
            <div>
              <span class="doma-detail-v2__bar-price-label">Стоимость от</span>
              <b class="doma-detail-v2__bar-price-val"><?=number_format($priceInt,0,',',' ')?> ₽</b>
              <?if($priceMonthly):?>
                <span class="doma-detail-v2__bar-price-sub">🏦 В ипотеку ~ <?=number_format($priceMonthly,0,',',' ')?> ₽ / мес · от 6%</span>
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
        <a class="zx-btn zx-btn--ghost zx-btn--sm font__BUTTONS_BUTTON" style="margin-left:12px;" data-fancybox="" data-src="#hidden-form" href="javascript:;" data-scope="PDF-планировка — <?=htmlspecialchars($fullName)?>">Скачать PDF</a>
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
          <div class="zx-dev-block__logo">З</div>
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
              <label class="font__BODY_TEXT_PRIMARY">Комплектация</label>
              <div class="doma-detail-v2__calc-pills" data-calc-group="package">
                <button type="button" class="is-active" data-val="1" data-label="Контур">Контур</button>
                <button type="button" data-val="1.35" data-label="Оптима">Оптима</button>
                <button type="button" data-val="1.75" data-label="Премиум">Премиум</button>
              </div>
            </div>
            <div class="doma-detail-v2__calc-field">
              <label class="font__BODY_TEXT_PRIMARY">Площадь дома: <b data-calc-square><?=$baseSquare?></b> м²</label>
              <input type="range" min="<?=max(40,$baseSquare-40)?>" max="<?=$baseSquare+80?>" step="5" value="<?=$baseSquare?>" data-calc-range="square">
            </div>
            <div class="doma-detail-v2__calc-field">
              <label class="font__BODY_TEXT_PRIMARY">Фундамент</label>
              <div class="doma-detail-v2__calc-pills" data-calc-group="foundation">
                <button type="button" class="is-active" data-val="0" data-label="Свайно-ростверковый">Свайно-ростверковый</button>
                <button type="button" data-val="250000" data-label="Монолитная плита">Монолитная плита (+250 000 ₽)</button>
                <button type="button" data-val="400000" data-label="Лента утепл.">Лента утепл. (+400 000 ₽)</button>
              </div>
            </div>
            <div class="doma-detail-v2__calc-field">
              <label class="font__BODY_TEXT_PRIMARY">Дополнительно</label>
              <div class="doma-detail-v2__calc-checks">
                <label><input type="checkbox" data-calc-add="350000" data-label="Терраса"><span>Терраса (+350 000 ₽)</span></label>
                <label><input type="checkbox" data-calc-add="550000" data-label="Навес / гараж"><span>Навес / гараж (+550 000 ₽)</span></label>
                <label><input type="checkbox" data-calc-add="180000" data-label="Камин"><span>Камин (+180 000 ₽)</span></label>
                <label><input type="checkbox" data-calc-add="120000" data-label="Умный дом"><span>Умный дом (+120 000 ₽)</span></label>
              </div>
            </div>
          </div>
          <aside class="doma-detail-v2__calc-result">
            <div class="doma-detail-v2__calc-result-inner">
              <p class="font__BODY_TEXT_CAPTION" style="color:rgba(255,255,255,.75);margin:0;">Ориентировочная стоимость</p>
              <p class="doma-detail-v2__calc-total font__HEADING_SECTION_TITLE" data-calc-total>—</p>
              <p class="font__BODY_TEXT_CAPTION doma-detail-v2__calc-monthly" data-calc-monthly></p>
              <p class="font__BODY_TEXT_CAPTION" style="color:rgba(255,255,255,.65);margin:4px 0 16px;">* расчёт предварительный, точную стоимость подтвердит менеджер</p>
              <a class="doma-detail-v2__calc-cta font__BUTTONS_BUTTON" data-fancybox="" data-src="#hidden-form" href="javascript:;" data-scope="Калькулятор — <?=htmlspecialchars($fullName)?>" data-calc-cta>Получить точный расчёт</a>
              <div class="doma-detail-v2__calc-summary" data-calc-summary></div>
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

  <section class="zx-reviews">
    <div class="c-sel--div__CONTAINER">
      <div class="zx-section-head">
        <div>
          <div class="zx-eyebrow" style="margin-bottom:8px;">Что говорят клиенты</div>
          <h2 class="font__HEADING_SECTION_TITLE" style="margin:0;">Реальные отзывы</h2>
        </div>
      </div>
      <div class="zx-reviews__grid">
        <figure class="zx-review">
          <div class="zx-review__stars">★★★★★</div>
          <blockquote class="font__BODY_TEXT_PRIMARY">«Строили дом 96 м² за 4 месяца. Сроки выдержали, качество отличное. Отдельное спасибо прорабу за постоянную связь и отчёты по фото.»</blockquote>
          <figcaption><b>Анна М.</b><span>дом 96 м², 2024</span></figcaption>
        </figure>
        <figure class="zx-review">
          <div class="zx-review__stars">★★★★★</div>
          <blockquote class="font__BODY_TEXT_PRIMARY">«Самое ценное — никаких внезапных доплат. Всё по смете. Ребята профи, рекомендуем друзьям. Уже 2 года живём — ни одной претензии к качеству.»</blockquote>
          <figcaption><b>Дмитрий К.</b><span>дом 128 м², 2023</span></figcaption>
        </figure>
        <figure class="zx-review">
          <div class="zx-review__stars">★★★★★</div>
          <blockquote class="font__BODY_TEXT_PRIMARY">«Помогли и с проектом, и с ипотекой — семейная под 6%. Въехали через 3,5 месяца после подписания. Дом тёплый, всё продумано до мелочей.»</blockquote>
          <figcaption><b>Елена и Сергей</b><span>семейная ипотека, 2024</span></figcaption>
        </figure>
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
      $faq = [
        ['Можно ли изменить планировку под себя?','Да. Архитекторы адаптируют проект под ваш участок, образ жизни и бюджет. Первые корректировки — бесплатно.'],
        ['Что входит в стоимость и что не входит?','В базу — всё из выбранной комплектации (Контур/Оптима/Премиум), точный состав в таблице. Не входит: участок, подключение к сетям, благоустройство. Это обсуждаем отдельно и фиксируем в смете.'],
        ['Как фиксируется цена?','Цена фиксируется в договоре в день подписания. Никаких индексаций и «доплат за материалы» после — все риски на нас.'],
        ['Какие документы нужны для ипотеки?','Паспорт, справка о доходах (2-НДФЛ или справка банка), СНИЛС. Оформим заявку в банки-партнёры — одобрение за 3 дня, ставка от 6%.'],
        ['Что с гарантией?','5 лет на конструктив (фундамент, стены, кровля) и инженерные сети. В течение срока устраняем за свой счёт.'],
        ['А если пока не готов строить?','Запишитесь на бесплатную экскурсию в готовый дом — посмотрите качество вживую. Без обязательств.'],
      ];
      foreach($faq as $i=>$q):?>
        <details class="zx-faq__item" <?=$i===0?'open':''?>>
          <summary><span class="font__HEADING_CARD_TITLE"><?=$q[0]?></span><span class="zx-faq__plus"></span></summary>
          <div class="zx-faq__body font__BODY_TEXT_PRIMARY"><?=$q[1]?></div>
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
    var state = { package: { val:1, label:'Контур' }, foundation: { val:0, label:'Свайно-ростверковый' }, square: baseSq, adds: {} };
    var totalEl = box.querySelector('[data-calc-total]');
    var sumEl   = box.querySelector('[data-calc-summary]');
    var sqEl    = box.querySelector('[data-calc-square]');
    var ctaEl   = box.querySelector('[data-calc-cta]');
    var monthEl = box.querySelector('[data-calc-monthly]');
    function fmt(n){ return Math.round(n).toLocaleString('ru-RU').replace(/,/g,' ') + ' ₽'; }
    function render(){
      var total = perM*state.square*state.package.val + state.foundation.val;
      Object.values(state.adds).forEach(function(v){ total += v.val; });
      if(totalEl) totalEl.textContent = fmt(total);
      if(sqEl) sqEl.textContent = state.square;
      if(monthEl){
        var m = Math.round(total * 0.0072 / 1000) * 1000;
        monthEl.innerHTML = '🏦 В ипотеку ~ <b>' + m.toLocaleString('ru-RU').replace(/,/g,' ') + ' ₽ / мес</b> · от 6%';
      }
      var lines = ['Комплектация: ' + state.package.label, 'Площадь: ' + state.square + ' м²', 'Фундамент: ' + state.foundation.label];
      var addLabels = Object.values(state.adds).map(function(v){ return v.label; });
      if(addLabels.length) lines.push('Доп.: ' + addLabels.join(', '));
      if(sumEl) sumEl.innerHTML = lines.map(function(l){ return '<span>'+l+'</span>'; }).join('');
      if(ctaEl) ctaEl.setAttribute('data-scope', 'Калькулятор ~' + fmt(total));
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
    render();
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
