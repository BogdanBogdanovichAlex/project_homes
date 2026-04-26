<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);

use Bitrix\Main\Page\Asset;
Asset::getInstance()->addCss(SITE_TEMPLATE_PATH."/css/zx-design.css");

$items = $arResult['ITEMS'];

// Fallback демо-данные, чтобы страница не была пустой
$usingDemo = empty($items);
if($usingDemo){
  $demoImg = function($id){ return 'https://images.unsplash.com/'.$id.'?auto=format&fit=crop&w=1200&q=80'; };
  $items = [
    ['ID'=>'demo-1','NAME'=>'Земельный Экспресс Строй','DETAIL_PAGE_URL'=>'#','PREVIEW_TEXT'=>'Строительство домов из газобетона и клеёного бруса под ключ',
     'PREVIEW_PICTURE'=>['SRC'=>$demoImg('photo-1600585154340-be6161a56a0c')],
     'DETAIL_PICTURE'=>['SRC'=>$demoImg('photo-1600585154340-be6161a56a0c')],
     '__BRAND'=>['abbr'=>'ZX','bg'=>'#00BF3F','fg'=>'#FFFFFF','grad'=>'#00A337'],
     '__TYPES'=>['kamennye','derevyannye'],'PROPERTIES'=>[
       ['CODE'=>'REGION','VALUE'=>'Московская область'],
       ['CODE'=>'EXPERIENCE','VALUE'=>'7'],['CODE'=>'HOUSES_COUNT','VALUE'=>'240'],
       ['CODE'=>'MIN_AREA','VALUE'=>'90'],['CODE'=>'BUILD_TIME','VALUE'=>'3'],['CODE'=>'PRICE_PER_M','VALUE'=>'35'],
       ['CODE'=>'PHONE','VALUE'=>'+7 (495) 989-10-70'],
     ]],
    ['ID'=>'demo-2','NAME'=>'Rubkoff Wood','DETAIL_PAGE_URL'=>'#','PREVIEW_TEXT'=>'Строительство и проектирование деревянных домов и бань',
     'PREVIEW_PICTURE'=>['SRC'=>$demoImg('photo-1600596542815-ffad4c1539a9')],
     'DETAIL_PICTURE'=>['SRC'=>$demoImg('photo-1600596542815-ffad4c1539a9')],
     '__BRAND'=>['abbr'=>'RW','bg'=>'#7A4F2D','fg'=>'#FFFFFF','grad'=>'#5C3A1E'],
     '__TYPES'=>['derevyannye'],'PROPERTIES'=>[
       ['CODE'=>'REGION','VALUE'=>'Подмосковье'],['CODE'=>'EXPERIENCE','VALUE'=>'12'],['CODE'=>'HOUSES_COUNT','VALUE'=>'320'],
       ['CODE'=>'MIN_AREA','VALUE'=>'140'],['CODE'=>'BUILD_TIME','VALUE'=>'3'],['CODE'=>'PRICE_PER_M','VALUE'=>'46'],
     ]],
    ['ID'=>'demo-3','NAME'=>'Green House Stroy','DETAIL_PAGE_URL'=>'#','PREVIEW_TEXT'=>'Строительство домов: Барнхаус, Фахверк, в стиле «Хай-Тек»',
     'PREVIEW_PICTURE'=>['SRC'=>$demoImg('photo-1564013799919-ab600027ffc6')],
     'DETAIL_PICTURE'=>['SRC'=>$demoImg('photo-1564013799919-ab600027ffc6')],
     '__BRAND'=>['abbr'=>'GH','bg'=>'#2E7D5B','fg'=>'#FFFFFF','grad'=>'#1F5C42'],
     '__TYPES'=>['karkasnye','modulnye'],'PROPERTIES'=>[
       ['CODE'=>'REGION','VALUE'=>'Москва и МО'],['CODE'=>'EXPERIENCE','VALUE'=>'9'],['CODE'=>'HOUSES_COUNT','VALUE'=>'180'],
       ['CODE'=>'MIN_AREA','VALUE'=>'72'],['CODE'=>'BUILD_TIME','VALUE'=>'3'],['CODE'=>'PRICE_PER_M','VALUE'=>'45'],
     ]],
    ['ID'=>'demo-4','NAME'=>'Brick House','DETAIL_PAGE_URL'=>'#','PREVIEW_TEXT'=>'Проектирование и строительство домов из камня и пеноблоков',
     'PREVIEW_PICTURE'=>['SRC'=>$demoImg('photo-1580587771525-78b9dba3b914')],
     'DETAIL_PICTURE'=>['SRC'=>$demoImg('photo-1580587771525-78b9dba3b914')],
     '__BRAND'=>['abbr'=>'BH','bg'=>'#B5462E','fg'=>'#FFFFFF','grad'=>'#923620'],
     '__TYPES'=>['kamennye'],'PROPERTIES'=>[
       ['CODE'=>'REGION','VALUE'=>'Тверская область'],['CODE'=>'EXPERIENCE','VALUE'=>'11'],['CODE'=>'HOUSES_COUNT','VALUE'=>'275'],
       ['CODE'=>'MIN_AREA','VALUE'=>'80'],['CODE'=>'BUILD_TIME','VALUE'=>'4'],['CODE'=>'PRICE_PER_M','VALUE'=>'35'],
     ]],
    ['ID'=>'demo-5','NAME'=>'Modul Home','DETAIL_PAGE_URL'=>'#','PREVIEW_TEXT'=>'Модульные дома с готовыми инженерными системами',
     'PREVIEW_PICTURE'=>['SRC'=>$demoImg('photo-1600047509807-ba8f99d2cdde')],
     'DETAIL_PICTURE'=>['SRC'=>$demoImg('photo-1600047509807-ba8f99d2cdde')],
     '__BRAND'=>['abbr'=>'MH','bg'=>'#1E5BB5','fg'=>'#FFFFFF','grad'=>'#13418C'],
     '__TYPES'=>['modulnye','karkasnye'],'PROPERTIES'=>[
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

// Настройки страницы (iblock 69 / homes_settings, элемент builders)
$buildersCfg = function_exists('zemex_get_homes_settings') ? zemex_get_homes_settings('builders') : [];
$bEyebrow = !empty($buildersCfg['HERO_EYEBROW']) ? $buildersCfg['HERO_EYEBROW'] : 'Проверенных партнёров';
$bTitle   = !empty($buildersCfg['HERO_TITLE'])   ? $buildersCfg['HERO_TITLE']   : 'Застройщики';
$bLead    = !empty($buildersCfg['HERO_LEAD'])    ? $buildersCfg['HERO_LEAD']    : 'Работаем только с проверенными командами. Каждая прошла аудит: финансовая устойчивость, качество объектов, репутация. Выбирайте партнёра под свой проект дома.';
?>
<div class="zx-scope zx-devs-page">
  <section class="zx-dev-hero">
    <div class="c-sel--div__CONTAINER">
      <nav class="zx-crumbs zx-dev-hero__crumbs" aria-label="Хлебные крошки" itemscope itemtype="https://schema.org/BreadcrumbList">
        <span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
          <a itemprop="item" href="/"><span itemprop="name">Главная</span></a>
          <meta itemprop="position" content="1" />
        </span>
        <span class="zx-crumbs__sep"></span>
        <span class="zx-crumbs__dot">●</span>
        <span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
          <span itemprop="name"><?=htmlspecialchars($bTitle)?></span>
          <meta itemprop="position" content="2" />
        </span>
      </nav>

      <div class="zx-dev-hero__grid">
        <div class="zx-dev-hero__main">
          <div class="zx-eyebrow" style="margin-bottom:18px;">
            <span style="color:var(--text-accent);">●</span> <?=htmlspecialchars($bEyebrow)?> · <?=$total?>
          </div>
          <h1 class="zx-dev-hero__title font__HEADING_PAGE_TITLE"><?=htmlspecialchars($bTitle)?><span class="zx-hero__title-dot">.</span></h1>
          <p class="zx-dev-hero__lead font__BODY_TEXT_PRIMARY">
            <?=htmlspecialchars($bLead)?>
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
          <?
            $brand = isset($arItem['__BRAND']) ? $arItem['__BRAND'] : null;
            $logoStyle = $brand
                ? 'background:linear-gradient(135deg,'.$brand['bg'].','.$brand['grad'].');color:'.$brand['fg'].';padding:0;'
                : '';
          ?>
          <div class="zx-dev-item__logo<?=$brand?' zx-dev-item__logo--brand':''?>" style="<?=$logoStyle?>">
            <?if($logo && !empty($logo['SRC'])):?>
              <img src="<?=$logo['SRC']?>" alt="<?=htmlspecialchars($arItem['NAME'])?>">
            <?elseif($brand):?>
              <span class="zx-dev-item__logo-abbr"><?=htmlspecialchars($brand['abbr'])?></span>
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

  <!-- Как мы проверяем застройщиков -->
  <section class="zx-dev-audit">
    <div class="c-sel--div__CONTAINER">
      <div class="zx-dev-audit__head">
        <div class="zx-eyebrow"><span style="color:var(--text-accent);">●</span> Как мы работаем</div>
        <h2 class="zx-dev-audit__title font__HEADING_SECTION_TITLE">Аудит застройщиков в&nbsp;4&nbsp;шага</h2>
        <p class="zx-dev-audit__lead font__BODY_TEXT_PRIMARY">До появления карточки на сайте каждая компания проходит обязательную проверку. Публикуем только тех, кто соответствует стандартам качества и ведения сделок.</p>
      </div>
      <?
      $auditLines = !empty($buildersCfg['AUDIT_LINES']) && is_array($buildersCfg['AUDIT_LINES']) ? $buildersCfg['AUDIT_LINES'] : [
          'Финансовая устойчивость | Проверяем бухгалтерию, наличие судебных дел и долгов, историю расчётов с подрядчиками.',
          'Лицензии и допуски | СРО, разрешения, технические допуски. Договоры с поставщиками материалов и поверенным техническим надзором.',
          'Качество объектов | Выезды на 3–5 уже сданных домов застройщика. Оцениваем геометрию, тепловой контур, инженерные системы.',
          'Репутация | Собираем обратную связь от клиентов за последние 2 года — анонимно и без фильтра. Карточка открывается только при 4.5+.',
      ];
      ?>
      <div class="zx-dev-audit__grid">
        <?foreach($auditLines as $idx => $line):
          $parts = function_exists('zemex_split_pipe') ? zemex_split_pipe($line, 2) : array_map('trim', explode('|', $line, 2));
          $stepTitle = $parts[0] ?? '';
          $stepDesc  = $parts[1] ?? '';
          $num = str_pad((string)($idx + 1), 2, '0', STR_PAD_LEFT);
        ?>
        <div class="zx-dev-audit__step">
          <div class="zx-dev-audit__num"><?=$num?></div>
          <h3 class="zx-dev-audit__step-title font__HEADING_CARD_TITLE"><?=htmlspecialchars($stepTitle)?></h3>
          <?if($stepDesc):?><p class="font__BODY_TEXT_PRIMARY"><?=htmlspecialchars($stepDesc)?></p><?endif?>
        </div>
        <?endforeach?>
      </div>
    </div>
  </section>

  <!-- Преимущества работы с партнёрами -->
  <section class="zx-dev-benefits">
    <div class="c-sel--div__CONTAINER">
      <div class="zx-dev-benefits__grid">
        <div class="zx-dev-benefits__head">
          <div class="zx-eyebrow"><span style="color:var(--text-accent);">●</span> Преимущества</div>
          <h2 class="zx-dev-benefits__title font__HEADING_SECTION_TITLE">Что вы получаете,<br>выбирая нашего партнёра</h2>
        </div>
        <div class="zx-dev-benefits__list">
          <div class="zx-dev-benefit">
            <div class="zx-dev-benefit__icon">🛡</div>
            <div>
              <div class="zx-dev-benefit__title font__HEADING_CARD_TITLE">Фиксированная цена в договоре</div>
              <div class="zx-dev-benefit__desc font__BODY_TEXT_PRIMARY">Никаких «внезапных» доплат — смета закрыта до старта стройки.</div>
            </div>
          </div>
          <div class="zx-dev-benefit">
            <div class="zx-dev-benefit__icon">⏱</div>
            <div>
              <div class="zx-dev-benefit__title font__HEADING_CARD_TITLE">Сроки прописаны штрафами</div>
              <div class="zx-dev-benefit__desc font__BODY_TEXT_PRIMARY">За каждый день просрочки — неустойка по договору, без переговоров.</div>
            </div>
          </div>
          <div class="zx-dev-benefit">
            <div class="zx-dev-benefit__icon">🏦</div>
            <div>
              <div class="zx-dev-benefit__title font__HEADING_CARD_TITLE">Ипотека от 6% годовых</div>
              <div class="zx-dev-benefit__desc font__BODY_TEXT_PRIMARY">Партнёрские банки — Альфа, Сбер, ВТБ и&nbsp;ГПБ. Подаём заявку сами.</div>
            </div>
          </div>
          <div class="zx-dev-benefit">
            <div class="zx-dev-benefit__icon">👷</div>
            <div>
              <div class="zx-dev-benefit__title font__HEADING_CARD_TITLE">Независимый технадзор</div>
              <div class="zx-dev-benefit__desc font__BODY_TEXT_PRIMARY">Контроль на каждом этапе стройки включён в стоимость — без доплат.</div>
            </div>
          </div>
          <div class="zx-dev-benefit">
            <div class="zx-dev-benefit__icon">📜</div>
            <div>
              <div class="zx-dev-benefit__title font__HEADING_CARD_TITLE">Гарантия 5 лет</div>
              <div class="zx-dev-benefit__desc font__BODY_TEXT_PRIMARY">На конструктив. Страхование стройки в обязательном порядке.</div>
            </div>
          </div>
          <div class="zx-dev-benefit">
            <div class="zx-dev-benefit__icon">🔑</div>
            <div>
              <div class="zx-dev-benefit__title font__HEADING_CARD_TITLE">Дом «под ключ» за 90 дней</div>
              <div class="zx-dev-benefit__desc font__BODY_TEXT_PRIMARY">С фундаментом, кровлей, инженеркой и фасадом — готов к заселению.</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Отзывы -->
  <section class="zx-dev-reviews">
    <div class="c-sel--div__CONTAINER">
      <div class="zx-dev-reviews__head">
        <div class="zx-eyebrow"><span style="color:var(--text-accent);">●</span> Отзывы клиентов</div>
        <h2 class="zx-dev-reviews__title font__HEADING_SECTION_TITLE">Что говорят те,<br>кто уже построил дом</h2>
      </div>
      <div class="zx-dev-reviews__grid">
        <figure class="zx-dev-review">
          <div class="zx-dev-review__rating" aria-label="Оценка 5 из 5">★★★★★</div>
          <blockquote class="font__BODY_TEXT_PRIMARY">Долго выбирали застройщика, в итоге остановились на варианте с&nbsp;аудитом через «Земельный Экспресс». Смета не выросла, сроки сдвинулись всего на&nbsp;4&nbsp;дня — это на&nbsp;стройке — чудо.</blockquote>
          <figcaption>
            <div class="zx-dev-review__name">Анна Ш.</div>
            <div class="zx-dev-review__meta font__BODY_TEXT_CAPTION">Дом 152 м² · Московская область · Газобетон</div>
          </figcaption>
        </figure>
        <figure class="zx-dev-review">
          <div class="zx-dev-review__rating" aria-label="Оценка 5 из 5">★★★★★</div>
          <blockquote class="font__BODY_TEXT_PRIMARY">Оценил техническую компетентность менеджера: сразу обозначил слабые места участка, предложил адекватный фундамент. Стройка заняла 97 дней, технадзор показал всё в деталях.</blockquote>
          <figcaption>
            <div class="zx-dev-review__name">Денис К.</div>
            <div class="zx-dev-review__meta font__BODY_TEXT_CAPTION">Дом 105 м² · Тверская область · Клеёный брус</div>
          </figcaption>
        </figure>
        <figure class="zx-dev-review">
          <div class="zx-dev-review__rating" aria-label="Оценка 4.8 из 5">★★★★★</div>
          <blockquote class="font__BODY_TEXT_PRIMARY">Рассматривали несколько застройщиков, сравнение по одинаковым критериям у&nbsp;вас сильно упростило выбор. По итогу — ипотека 6,2% через партнёрскую программу, переплата минимальна.</blockquote>
          <figcaption>
            <div class="zx-dev-review__name">Семья Кравченко</div>
            <div class="zx-dev-review__meta font__BODY_TEXT_CAPTION">Дом 126.5 м² · Подмосковье · Ипотека от 6%</div>
          </figcaption>
        </figure>
      </div>
    </div>
  </section>

  <!-- FAQ -->
  <section class="zx-faq">
    <div class="c-sel--div__CONTAINER">
      <div class="zx-section-head">
        <div>
          <div class="zx-eyebrow" style="margin-bottom:12px;"><span style="color:var(--text-accent);">●</span> FAQ</div>
          <h2 class="font__HEADING_SECTION_TITLE" style="margin:0;">Частые вопросы по&nbsp;работе с&nbsp;застройщиками</h2>
        </div>
      </div>
      <details class="zx-faq__item">
        <summary><span class="font__HEADING_CARD_TITLE">Сколько стоит услуга подбора застройщика?</span><span class="zx-faq__plus" aria-hidden="true"></span></summary>
        <div class="zx-faq__body font__BODY_TEXT_PRIMARY">Для клиента подбор бесплатен — мы работаем по&nbsp;партнёрской модели с&nbsp;самими застройщиками. Цена дома в&nbsp;договоре не&nbsp;увеличивается из-за этого.</div>
      </details>
      <details class="zx-faq__item">
        <summary><span class="font__HEADING_CARD_TITLE">Можно ли выбрать застройщика самому и&nbsp;прийти с&nbsp;ним к&nbsp;вам?</span><span class="zx-faq__plus" aria-hidden="true"></span></summary>
        <div class="zx-faq__body font__BODY_TEXT_PRIMARY">Да. Проверим выбранную компанию по&nbsp;нашему аудиту (1–2&nbsp;недели). Если есть риски — сообщим и&nbsp;предложим альтернативы из&nbsp;сети партнёров.</div>
      </details>
      <details class="zx-faq__item">
        <summary><span class="font__HEADING_CARD_TITLE">Что будет, если застройщик не&nbsp;достроит дом?</span><span class="zx-faq__plus" aria-hidden="true"></span></summary>
        <div class="zx-faq__body font__BODY_TEXT_PRIMARY">Все партнёры страхуют стройку по&nbsp;договору. При срыве сроков — неустойка. При банкротстве — подключаем резервного застройщика в&nbsp;сети и&nbsp;передаём проект без потери оплаченных работ.</div>
      </details>
      <details class="zx-faq__item">
        <summary><span class="font__HEADING_CARD_TITLE">Как оформляется ипотека на&nbsp;строительство?</span><span class="zx-faq__plus" aria-hidden="true"></span></summary>
        <div class="zx-faq__body font__BODY_TEXT_PRIMARY">Подаём одну анкету сразу в&nbsp;7&nbsp;партнёрских банков. Получаем предложения за&nbsp;2–3&nbsp;рабочих дня. Вы выбираете лучшую ставку, мы&nbsp;сопровождаем сделку до&nbsp;подписания договора.</div>
      </details>
      <details class="zx-faq__item">
        <summary><span class="font__HEADING_CARD_TITLE">Чем отличается технадзор от&nbsp;приёмки дома?</span><span class="zx-faq__plus" aria-hidden="true"></span></summary>
        <div class="zx-faq__body font__BODY_TEXT_PRIMARY">Технадзор — это непрерывный контроль на&nbsp;каждом этапе: фундамент, стены, кровля, инженерка. Приёмка — финальная проверка по&nbsp;чек-листу перед подписанием акта. Обе услуги включены.</div>
      </details>
    </div>
  </section>

  <?
  $ctaTitle  = !empty($buildersCfg['CTA_TITLE'])  ? $buildersCfg['CTA_TITLE']  : 'Строите дома? Разместите проекты у нас';
  $ctaLead   = !empty($buildersCfg['CTA_LEAD'])   ? $buildersCfg['CTA_LEAD']   : 'Работа по договору-оферте, аудит за 5 рабочих дней, доступ к клиентам и инструменты для ведения сделок.';
  $ctaButton = !empty($buildersCfg['CTA_BUTTON']) ? $buildersCfg['CTA_BUTTON'] : 'Оставить заявку →';
  $ctaLink   = !empty($buildersCfg['CTA_LINK'])   ? $buildersCfg['CTA_LINK']   : '';
  ?>
  <section class="zx-cta-dark">
    <div class="c-sel--div__CONTAINER">
      <div class="zx-cta-dark__grid">
        <div>
          <div class="zx-eyebrow zx-cta-dark__eyebrow">Станьте партнёром</div>
          <h2 class="zx-cta-dark__title font__HEADING_SECTION_TITLE"><?=htmlspecialchars($ctaTitle)?></h2>
          <p class="zx-cta-dark__lead font__BODY_TEXT_PRIMARY"><?=htmlspecialchars($ctaLead)?></p>
        </div>
        <div class="zx-cta-dark__actions">
          <?if($ctaLink):?>
            <a class="zx-btn zx-btn--primary font__BUTTONS_BUTTON" href="<?=htmlspecialchars($ctaLink)?>" data-scope="Партнёрская заявка"><?=htmlspecialchars($ctaButton)?></a>
          <?else:?>
            <a class="zx-btn zx-btn--primary font__BUTTONS_BUTTON" data-fancybox="" data-src="#hidden-form" href="javascript:;" data-scope="Партнёрская заявка"><?=htmlspecialchars($ctaButton)?></a>
          <?endif?>
          <a class="zx-btn zx-btn--ghost-dark font__BUTTONS_BUTTON" href="/partnership/">Условия партнёрства</a>
        </div>
      </div>
    </div>
  </section>

  <?if(!$usingDemo && $arParams["DISPLAY_BOTTOM_PAGER"] && $arResult["NAV_STRING"]):?>
  <div class="c-sel--div__CONTAINER" style="margin-top:40px;"><?=$arResult["NAV_STRING"]?></div>
  <?endif?>
</div>

<style>
/* === Секции «Аудит», «Преимущества», «Отзывы» === */
.zx-devs-page .zx-dev-audit,
.zx-devs-page .zx-dev-benefits,
.zx-devs-page .zx-dev-reviews { padding: 48px 0; }
@media (min-width: 992px){
  .zx-devs-page .zx-dev-audit,
  .zx-devs-page .zx-dev-benefits,
  .zx-devs-page .zx-dev-reviews { padding: 64px 0; }
}

.zx-devs-page .zx-dev-audit__head,
.zx-devs-page .zx-dev-reviews__head { max-width: 720px; margin: 0 0 32px; }
.zx-devs-page .zx-dev-audit__title,
.zx-devs-page .zx-dev-reviews__title,
.zx-devs-page .zx-dev-benefits__title { margin: 12px 0 10px; letter-spacing: -0.01em; }
.zx-devs-page .zx-dev-audit__lead { color: var(--text-secondary,#6F737A); max-width: 640px; margin: 0; }

/* Аудит 2×2 / 4 */
.zx-devs-page .zx-dev-audit__grid {
  display: grid; gap: 18px;
  grid-template-columns: 1fr;
}
@media (min-width: 720px){ .zx-devs-page .zx-dev-audit__grid { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 1200px){ .zx-devs-page .zx-dev-audit__grid { grid-template-columns: repeat(4, 1fr); } }
.zx-devs-page .zx-dev-audit__step {
  background: #fff;
  border: 1px solid #ECEEF2;
  border-radius: 20px;
  padding: 26px 24px;
  transition: border-color .15s, box-shadow .15s;
}
.zx-devs-page .zx-dev-audit__step:hover { border-color: #CDD2DB; box-shadow: 0 2px 10px rgba(16,24,40,.05); }
.zx-devs-page .zx-dev-audit__num {
  font-family: 'Montserrat', sans-serif;
  font-size: 28px; font-weight: 700; color: #00BF3F;
  letter-spacing: -0.02em; line-height: 1; margin-bottom: 16px;
}
.zx-devs-page .zx-dev-audit__step-title { margin: 0 0 8px; font-size: 18px; line-height: 1.35; }
.zx-devs-page .zx-dev-audit__step p { margin: 0; color: var(--text-secondary,#6F737A); line-height: 1.5; }

/* Преимущества — 2 колонки: заголовок + список */
.zx-devs-page .zx-dev-benefits { background: var(--bg-secondary, #F9FBFC); }
.zx-devs-page .zx-dev-benefits__grid {
  display: grid; gap: 32px;
  grid-template-columns: 1fr;
}
@media (min-width: 992px){ .zx-devs-page .zx-dev-benefits__grid { grid-template-columns: minmax(300px, 400px) 1fr; gap: 56px; align-items: start; } }
.zx-devs-page .zx-dev-benefits__list {
  display: grid; gap: 20px 28px;
  grid-template-columns: 1fr;
}
@media (min-width: 720px){ .zx-devs-page .zx-dev-benefits__list { grid-template-columns: 1fr 1fr; } }
.zx-devs-page .zx-dev-benefit { display: flex; gap: 16px; align-items: flex-start; }
.zx-devs-page .zx-dev-benefit__icon {
  flex-shrink: 0; width: 44px; height: 44px;
  display: flex; align-items: center; justify-content: center;
  font-size: 20px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(7,23,53,.06);
}
.zx-devs-page .zx-dev-benefit__title { margin: 0 0 4px; font-size: 16px; line-height: 1.35; }
.zx-devs-page .zx-dev-benefit__desc { margin: 0; color: var(--text-secondary,#6F737A); font-size: 15px; line-height: 1.5; }

/* Отзывы */
.zx-devs-page .zx-dev-reviews__grid {
  display: grid; gap: 18px;
  grid-template-columns: 1fr;
}
@media (min-width: 720px){ .zx-devs-page .zx-dev-reviews__grid { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 1200px){ .zx-devs-page .zx-dev-reviews__grid { grid-template-columns: repeat(3, 1fr); } }
.zx-devs-page .zx-dev-review {
  margin: 0;
  background: #fff;
  border: 1px solid #ECEEF2;
  border-radius: 20px;
  padding: 24px 24px;
  display: flex; flex-direction: column; gap: 14px;
}
.zx-devs-page .zx-dev-review__rating { color: #F5A623; letter-spacing: 2px; font-size: 16px; line-height: 1; }
.zx-devs-page .zx-dev-review blockquote { margin: 0; font-size: 15px; line-height: 1.55; color: var(--text-primary,#11181C); font-family: 'PT Sans', sans-serif; font-weight: 400; }
.zx-devs-page .zx-dev-review figcaption { margin-top: auto; border-top: 1px solid #ECEEF2; padding-top: 14px; }
.zx-devs-page .zx-dev-review__name { font-family: 'Montserrat', sans-serif; font-weight: 600; font-size: 15px; }
.zx-devs-page .zx-dev-review__meta { color: var(--text-secondary,#6F737A); font-size: 13px; margin-top: 2px; }
</style>

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
    function open(scope){
      var scopeInp = modal.querySelector('input[data-name="SCOPE"]');
      if(scopeInp) scopeInp.value = scope || '';
      var titleEl = modal.querySelector('.vp-heroModal1--h2, .form_header, h2, h3');
      if(titleEl && scope){
        var headerMap = {
          'Связаться с': 'Связаться с застройщиком',
          'Партнёрская': 'Заявка на партнёрство',
          'Стать партн':  'Заявка на партнёрство'
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
