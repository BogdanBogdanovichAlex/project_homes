<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);

$standardCache = array("CACHE_TYPE" => "A", "CACHE_TIME" => "3600");

// Настройки страницы из админки (iblock 69 / homes_settings, элемент catalog).
// Если элемент не найден или поле пустое — используются текущие дефолты.
$cfg = function_exists('zemex_get_homes_settings') ? zemex_get_homes_settings('catalog') : [];
$heroEyebrow = !empty($cfg['HERO_EYEBROW']) ? $cfg['HERO_EYEBROW'] : 'Проекты домов для постройки';
$heroTitle   = !empty($cfg['HERO_TITLE'])   ? $cfg['HERO_TITLE']   : 'Дом вашей мечты — за 90 дней';
$heroLead    = !empty($cfg['HERO_LEAD'])    ? $cfg['HERO_LEAD']    : 'Готовые и строящиеся проекты от проверенных застройщиков. Фиксированная цена, прозрачные сроки и планировки на любой бюджет.';
$heroBgSrc   = !empty($cfg['HERO_BG_SRC'])  ? $cfg['HERO_BG_SRC']  : '';
?>

<!-- ============ СЕЛЛИНГ-ХИРО ============ -->
<section class="doma-hero">
    <div class="doma-hero__bg"<?=$heroBgSrc ? ' style="background-image:url('.htmlspecialchars($heroBgSrc).')"' : ''?>></div>
    <div class="doma-hero__overlay"></div>
    <div class="c-sel--div__CONTAINER">
    <div class="doma-hero__inner">
        <ul class="c-hero--ul__BC doma-hero__bc" itemscope itemtype="http://schema.org/BreadcrumbList">
            <li class="c-hero--li__BC">
                <a class="c-hero--a__BC font__BODY_TEXT_CAPTION" href="/">Главная</a>
                <meta itemprop="position" content="1" />
            </li>
            <li class="c-hero--li__BC">
                <a class="__c-hero--a__BC__SEL c-hero--a__BC font__BODY_TEXT_CAPTION" href="/doma-i-kottedzhi/"><span>&#9679;</span>Дома и коттеджи</a>
                <meta itemprop="position" content="2" />
            </li>
            <div style="clear:both"></div>
        </ul>

        <div class="doma-hero__grid">
            <div class="doma-hero__text">
                <span class="doma-hero__eyebrow font__BODY_TEXT_CAPTION"><?=htmlspecialchars($heroEyebrow)?></span>
                <h1 class="doma-hero__h1 font__HEADING_PAGE_TITLE"><?=htmlspecialchars($heroTitle)?></h1>
                <p class="doma-hero__lead font__BODY_TEXT_PRIMARY"><?=htmlspecialchars($heroLead)?></p>
                <div class="doma-hero__ctas">
                    <a class="doma-hero__btn doma-hero__btn--primary font__BUTTONS_BUTTON" href="#doma-items">Подобрать проект</a>
                    <a class="doma-hero__btn doma-hero__btn--ghost font__BUTTONS_BUTTON" data-fancybox="" data-src="#hidden-form" href="javascript:;" data-scope="Консультация со страницы Дома">Получить консультацию</a>
                </div>
            </div>
            <div class="doma-hero__stats">
                <div class="doma-hero__stat">
                    <div class="doma-hero__stat-value">от&nbsp;90</div>
                    <div class="doma-hero__stat-label font__BODY_TEXT_CAPTION">дней<br>до&nbsp;готового дома</div>
                </div>
                <div class="doma-hero__stat">
                    <div class="doma-hero__stat-value">50+</div>
                    <div class="doma-hero__stat-label font__BODY_TEXT_CAPTION">проектов<br>в&nbsp;каталоге</div>
                </div>
                <div class="doma-hero__stat">
                    <div class="doma-hero__stat-value">5&nbsp;лет</div>
                    <div class="doma-hero__stat-label font__BODY_TEXT_CAPTION">гарантии<br>на&nbsp;конструктив</div>
                </div>
            </div>
        </div>
    </div>
    </div>
</section>

<?
// Wizard config — admin: /bitrix/admin/zemex_homes_wizard.php
$wizRaw = COption::GetOptionString('main', 'zemex_homes_wizard', '');
$wiz = $wizRaw ? json_decode($wizRaw, true) : null;
if (!is_array($wiz)) {
    $wiz = [
        'trigger' => ['icon'=>'🏡','title'=>'Не знаете, какой дом подойдёт?','subtitle'=>'Подбор за 1 минуту по семье и бюджету — мы найдём 3 проекта под ваш запрос','cta'=>'Подобрать дом →'],
        'head'    => ['eyebrow'=>'Подбор за 1 минуту · 3 шага','title'=>'Не знаете, какой дом подойдёт?','lead'=>'Расскажите о семье и бюджете — мы подберём 3 проекта, которые подходят именно вам. Не нужно разбираться в квадратах и технологиях.'],
        'step_labels' => ['Семья','Бюджет','Уточнения'],
        'step1' => ['title'=>'Сколько человек будет жить в доме?','subtitle'=>'Стандарты комфорта: ~30 м² на человека плюс общие зоны.','cards'=>[
            ['family'=>2,'icon'=>'👤👤','label'=>'1–2 человека','area_min'=>60,'area_max'=>100,'bedrooms'=>'1'],
            ['family'=>3,'icon'=>'👤👤👶','label'=>'3 человека','area_min'=>90,'area_max'=>130,'bedrooms'=>'2'],
            ['family'=>4,'icon'=>'👨‍👩‍👧‍👦','label'=>'4 человека','area_min'=>120,'area_max'=>170,'bedrooms'=>'3'],
            ['family'=>5,'icon'=>'👨‍👩‍👧‍👦+','label'=>'5 и больше','area_min'=>150,'area_max'=>220,'bedrooms'=>'4+'],
        ]],
        'step2' => ['title'=>'Какой у вас бюджет?','subtitle'=>'Можно указать всю сумму или платёж по ипотеке — посчитаем сами.','mortgage_rate'=>6,'mortgage_term_months'=>240],
        'step3' => ['title'=>'Несколько уточнений','subtitle'=>'Это поможет выбрать оптимальный материал и технологию строительства.','groups'=>[
            'when'=>['label'=>'Когда нужен дом?','options'=>[['val'=>'urgent','label'=>'Срочно (сезон 2026)'],['val'=>'quarter','label'=>'Через 3–6 месяцев'],['val'=>'year','label'=>'До года'],['val'=>'future','label'=>'Просто смотрю']]],
            'floors'=>['label'=>'Этажность','options'=>[['val'=>'any','label'=>'Не важно'],['val'=>'1','label'=>'1 этаж'],['val'=>'2','label'=>'2 этажа']]],
            'purpose'=>['label'=>'Дом для…','options'=>[['val'=>'permanent','label'=>'Постоянного проживания'],['val'=>'dacha','label'=>'Дачи / летнего жилья']]],
        ]],
        'finish_button' => 'Показать подходящие проекты →',
        'result_cta'    => 'Получить персональную подборку',
    ];
}
function zx_wiz_bedrooms($n){
    $n = trim((string)$n);
    if ($n === '' || $n === '0') return '';
    if (preg_match('/^[0-9]+\+$/', $n)) return $n.'&nbsp;спальни';
    $i = (int)$n;
    if ($i === 1) return '1&nbsp;спальня';
    if ($i >= 2 && $i <= 4) return $i.'&nbsp;спальни';
    return $i.'&nbsp;спален';
}
?>
<!-- ============ МАСТЕР ПОДБОРА ДОМА ============ -->
<div class="doma-wizard-wrap" id="domaWizardWrap">
  <div class="c-sel--div__CONTAINER">
    <!-- Свёрнутая шапка-приглашение -->
    <button type="button" class="doma-wizard-trigger" data-wizard-open>
      <span class="doma-wizard-trigger__icon"><?=$wiz['trigger']['icon']?></span>
      <span class="doma-wizard-trigger__text">
        <b><?=htmlspecialchars($wiz['trigger']['title'])?></b>
        <span><?=htmlspecialchars($wiz['trigger']['subtitle'])?></span>
      </span>
      <span class="doma-wizard-trigger__cta"><?=htmlspecialchars($wiz['trigger']['cta'])?></span>
    </button>
  </div>
</div>

<section class="doma-wizard" id="domaWizard" hidden>
  <div class="c-sel--div__CONTAINER">
    <div class="doma-wizard__shell">
      <button type="button" class="doma-wizard__close" data-wizard-close aria-label="Свернуть">
        <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2 2l10 10M12 2L2 12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
      </button>
      <div class="doma-wizard__head">
        <span class="doma-wizard__eyebrow"><?=htmlspecialchars($wiz['head']['eyebrow'])?></span>
        <h2 class="font__HEADING_SECTION_TITLE doma-wizard__h2"><?=htmlspecialchars($wiz['head']['title'])?></h2>
        <p class="doma-wizard__lead"><?=htmlspecialchars($wiz['head']['lead'])?></p>
      </div>

      <div class="doma-wizard__progress" data-wizard-progress>
        <?foreach($wiz['step_labels'] as $i => $lbl):?>
          <div class="doma-wizard__progress-step<?=$i===0?' is-active':''?>" data-step-dot="<?=$i+1?>"><span><?=$i+1?></span><b><?=htmlspecialchars($lbl)?></b></div>
        <?endforeach?>
      </div>

      <!-- ШАГ 1: семья -->
      <div class="doma-wizard__step is-active" data-step="1">
        <h3 class="doma-wizard__step-title"><?=htmlspecialchars($wiz['step1']['title'])?></h3>
        <p class="doma-wizard__step-sub"><?=htmlspecialchars($wiz['step1']['subtitle'])?></p>
        <div class="doma-wizard__cards">
          <?foreach($wiz['step1']['cards'] as $card):
            $bedroomsLabel = zx_wiz_bedrooms($card['bedrooms']);
            $rangeLabel = 'от&nbsp;'.(int)$card['area_min'].'&nbsp;до&nbsp;'.(int)$card['area_max'].'&nbsp;м²';
          ?>
          <button type="button" class="doma-wizard__card" data-family="<?=(int)$card['family']?>" data-area-min="<?=(int)$card['area_min']?>" data-area-max="<?=(int)$card['area_max']?>" data-bedrooms="<?=htmlspecialchars($card['bedrooms'])?>">
            <div class="doma-wizard__card-icon"><?=$card['icon']?></div>
            <b><?=htmlspecialchars($card['label'])?></b>
            <span><?=$rangeLabel?><?=$bedroomsLabel ? ' · '.$bedroomsLabel : ''?></span>
          </button>
          <?endforeach?>
        </div>
        <div class="doma-wizard__nav">
          <span></span>
          <button type="button" class="doma-wizard__btn doma-wizard__btn--primary" data-wizard-next disabled>Дальше →</button>
        </div>
      </div>

      <!-- ШАГ 2: бюджет -->
      <div class="doma-wizard__step" data-step="2">
        <h3 class="doma-wizard__step-title"><?=htmlspecialchars($wiz['step2']['title'])?></h3>
        <p class="doma-wizard__step-sub"><?=htmlspecialchars($wiz['step2']['subtitle'])?></p>

        <div class="doma-wizard__toggle" data-wizard-mode-toggle>
          <button type="button" class="is-active" data-mode="cash">💰 Полная сумма</button>
          <button type="button" data-mode="mortgage">🏦 Возьму ипотеку</button>
        </div>

        <div class="doma-wizard__field" data-mode-field="cash">
          <label class="doma-wizard__label">
            Готовы потратить
            <output class="doma-wizard__output" data-wizard-cash-out>5 000 000 ₽</output>
          </label>
          <input type="range" min="2000000" max="20000000" step="500000" value="5000000" data-wizard-input="cash">
          <div class="doma-wizard__edges"><span>2 млн&nbsp;₽</span><span>20 млн&nbsp;₽</span></div>
        </div>

        <div class="doma-wizard__field" data-mode-field="mortgage" hidden>
          <div class="doma-wizard__field-row">
            <div>
              <label class="doma-wizard__label">
                Первоначальный взнос
                <output class="doma-wizard__output" data-wizard-down-out>1 500 000 ₽</output>
              </label>
              <input type="range" min="500000" max="10000000" step="100000" value="1500000" data-wizard-input="down">
              <div class="doma-wizard__edges"><span>500 тыс&nbsp;₽</span><span>10 млн&nbsp;₽</span></div>
            </div>
            <div>
              <label class="doma-wizard__label">
                Платёж в&nbsp;месяц
                <output class="doma-wizard__output" data-wizard-monthly-out>50 000 ₽</output>
              </label>
              <input type="range" min="20000" max="200000" step="5000" value="50000" data-wizard-input="monthly">
              <div class="doma-wizard__edges"><span>20 тыс&nbsp;₽</span><span>200 тыс&nbsp;₽</span></div>
            </div>
          </div>
          <div class="doma-wizard__hint-row">
            <div class="doma-wizard__hint-icon">ℹ️</div>
            <div>Доступный бюджет&nbsp;= первоначальный взнос&nbsp;+ кредит. Платёж&nbsp;<b data-wizard-monthly-show>50 000&nbsp;₽</b>&nbsp;× <?=(int)$wiz['step2']['mortgage_term_months']?>&nbsp;мес. под&nbsp;<?=$wiz['step2']['mortgage_rate']?>%&nbsp;= кредит до&nbsp;<b data-wizard-loan>~6,9&nbsp;млн&nbsp;₽</b>. Итого бюджет&nbsp;≈&nbsp;<b data-wizard-budget>8,4&nbsp;млн&nbsp;₽</b>.</div>
          </div>
        </div>

        <div class="doma-wizard__nav">
          <button type="button" class="doma-wizard__btn doma-wizard__btn--ghost" data-wizard-back>← Назад</button>
          <button type="button" class="doma-wizard__btn doma-wizard__btn--primary" data-wizard-next>Дальше →</button>
        </div>
      </div>

      <!-- ШАГ 3: уточнения -->
      <div class="doma-wizard__step" data-step="3">
        <h3 class="doma-wizard__step-title"><?=htmlspecialchars($wiz['step3']['title'])?></h3>
        <p class="doma-wizard__step-sub"><?=htmlspecialchars($wiz['step3']['subtitle'])?></p>

        <?foreach($wiz['step3']['groups'] as $gKey => $g): if(empty($g['options'])) continue;?>
        <div class="doma-wizard__group">
          <label class="doma-wizard__group-label"><?=htmlspecialchars($g['label'])?></label>
          <div class="doma-wizard__pills" data-wizard-group="<?=htmlspecialchars($gKey)?>">
            <?foreach($g['options'] as $i => $o):?>
              <button type="button"<?=$i===0?' class="is-active"':''?> data-val="<?=htmlspecialchars($o['val'])?>"><?=htmlspecialchars($o['label'])?></button>
            <?endforeach?>
          </div>
        </div>
        <?endforeach?>

        <div class="doma-wizard__nav">
          <button type="button" class="doma-wizard__btn doma-wizard__btn--ghost" data-wizard-back>← Назад</button>
          <button type="button" class="doma-wizard__btn doma-wizard__btn--primary doma-wizard__btn--lg" data-wizard-finish><?=htmlspecialchars($wiz['finish_button'])?></button>
        </div>
      </div>

      <!-- РЕЗУЛЬТАТ -->
      <div class="doma-wizard__step doma-wizard__step--result" data-step="result">
        <div class="doma-wizard__result-summary" data-wizard-summary></div>
        <div class="doma-wizard__result-grid" data-wizard-results></div>
        <div class="doma-wizard__result-tip" data-wizard-tip hidden></div>
        <div class="doma-wizard__nav">
          <button type="button" class="doma-wizard__btn doma-wizard__btn--ghost" data-wizard-restart>↺ Пройти заново</button>
          <a class="doma-wizard__btn doma-wizard__btn--primary" data-fancybox="" data-src="#hidden-form" href="javascript:;" data-scope="Подбор дома по мастеру"><?=htmlspecialchars($wiz['result_cta'])?></a>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ============ КАРТОЧКИ ПРОЕКТОВ ============ -->
<?$APPLICATION->IncludeComponent(
    "bitrix:news.list",
    "doma",
    Array(
        "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
        "IBLOCK_ID" => $arParams["IBLOCK_ID"],
        "NEWS_COUNT" => $arParams["NEWS_COUNT"],
        "SORT_BY1" => $arParams["SORT_BY1"],
        "SORT_ORDER1" => $arParams["SORT_ORDER1"],
        "SORT_BY2" => $arParams["SORT_BY2"],
        "SORT_ORDER2" => $arParams["SORT_ORDER2"],
        "FIELD_CODE" => $arParams["LIST_FIELD_CODE"],
        "PROPERTY_CODE" => $arParams["LIST_PROPERTY_CODE"],
        "DETAIL_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["detail"],
        "SECTION_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["section"],
        "IBLOCK_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["news"],
        "DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],
        "SET_TITLE" => $arParams["SET_TITLE"],
        "SET_LAST_MODIFIED" => $arParams["SET_LAST_MODIFIED"],
        "MESSAGE_404" => $arParams["MESSAGE_404"],
        "SET_STATUS_404" => $arParams["SET_STATUS_404"],
        "SHOW_404" => $arParams["SHOW_404"],
        "FILE_404" => $arParams["FILE_404"],
        "INCLUDE_IBLOCK_INTO_CHAIN" => $arParams["INCLUDE_IBLOCK_INTO_CHAIN"],
        "CACHE_TYPE" => $arParams["CACHE_TYPE"],
        "CACHE_TIME" => $arParams["CACHE_TIME"],
        "CACHE_FILTER" => $arParams["CACHE_FILTER"],
        "CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
        "DISPLAY_TOP_PAGER" => $arParams["DISPLAY_TOP_PAGER"],
        "DISPLAY_BOTTOM_PAGER" => $arParams["DISPLAY_BOTTOM_PAGER"],
        "PAGER_TITLE" => $arParams["PAGER_TITLE"],
        "PAGER_TEMPLATE" => $arParams["PAGER_TEMPLATE"],
        "PAGER_SHOW_ALWAYS" => $arParams["PAGER_SHOW_ALWAYS"],
        "PAGER_DESC_NUMBERING" => $arParams["PAGER_DESC_NUMBERING"],
        "PAGER_DESC_NUMBERING_CACHE_TIME" => $arParams["PAGER_DESC_NUMBERING_CACHE_TIME"],
        "PAGER_SHOW_ALL" => $arParams["PAGER_SHOW_ALL"],
        "PAGER_BASE_LINK_ENABLE" => $arParams["PAGER_BASE_LINK_ENABLE"],
        "PAGER_BASE_LINK" => $arParams["PAGER_BASE_LINK"],
        "PAGER_PARAMS_NAME" => $arParams["PAGER_PARAMS_NAME"],
        "DISPLAY_DATE" => $arParams["DISPLAY_DATE"],
        "DISPLAY_NAME" => "Y",
        "DISPLAY_PICTURE" => $arParams["DISPLAY_PICTURE"],
        "DISPLAY_PREVIEW_TEXT" => $arParams["DISPLAY_PREVIEW_TEXT"],
        "PREVIEW_TRUNCATE_LEN" => $arParams["PREVIEW_TRUNCATE_LEN"],
        "ACTIVE_DATE_FORMAT" => $arParams["LIST_ACTIVE_DATE_FORMAT"],
        "USE_PERMISSIONS" => $arParams["USE_PERMISSIONS"],
        "GROUP_PERMISSIONS" => $arParams["GROUP_PERMISSIONS"],
        "FILTER_NAME" => $arParams["FILTER_NAME"],
        "HIDE_LINK_WHEN_NO_DETAIL" => $arParams["HIDE_LINK_WHEN_NO_DETAIL"],
        "CHECK_DATES" => $arParams["CHECK_DATES"],
    ),
    $component
);?>

<!-- ============ С КЕМ МЫ РАБОТАЕМ (ЗАСТРОЙЩИКИ) ============ -->
<?
// Тянем застройщиков из инфоблока #68 (тот же источник, что /zastrojshhiki/)
$arBuilders = [];
$typeLabels = [
    'kamennye'    => ['label'=>'Каменные',   'icon'=>'🧱'],
    'derevyannye' => ['label'=>'Деревянные', 'icon'=>'🪵'],
    'karkasnye'   => ['label'=>'Каркасные',  'icon'=>'🏗'],
    'modulnye'    => ['label'=>'Модульные',  'icon'=>'📦'],
];
if(CModule::IncludeModule("iblock")){
    $rsBuilders = CIBlockElement::GetList(
        ["SORT"=>"ASC","NAME"=>"ASC"],
        ["IBLOCK_ID"=>68, "ACTIVE"=>"Y"],
        false,
        ["nTopCount"=>4],
        ["ID","NAME","DETAIL_PAGE_URL","PREVIEW_TEXT","CODE",
         "PROPERTY_LOGO","PROPERTY_REGION","PROPERTY_EXPERIENCE","PROPERTY_HOUSES_COUNT",
         "PROPERTY_MIN_AREA","PROPERTY_BUILD_TIME","PROPERTY_PRICE_PER_M"]
    );
    while($ob = $rsBuilders->GetNext()){
        $logoSrc = null;
        if(!empty($ob["PROPERTY_LOGO_VALUE"])){
            $f = CFile::GetFileArray($ob["PROPERTY_LOGO_VALUE"]);
            if($f) $logoSrc = $f["SRC"];
        }
        $arBuilders[] = [
            "NAME"      => $ob["NAME"],
            "SUB"       => strip_tags($ob["PREVIEW_TEXT"]),
            "URL"       => $ob["DETAIL_PAGE_URL"] ?: "/zastrojshhiki/",
            "REGION"    => $ob["PROPERTY_REGION_VALUE"],
            "MIN_AREA"  => (int)$ob["PROPERTY_MIN_AREA_VALUE"],
            "BUILD_TIME"=> (int)$ob["PROPERTY_BUILD_TIME_VALUE"],
            "PRICE_PER_M"=>(int)$ob["PROPERTY_PRICE_PER_M_VALUE"],
            "TYPES"     => [],
            "LOGO_SRC"  => $logoSrc,
            "BRAND"     => null,
        ];
    }
}
// Fallback — повторяет демо-данные страницы /zastrojshhiki/ (включая брендовые логотипы)
if(empty($arBuilders)){
    $arBuilders = [
        ["NAME"=>"Земельный Экспресс Строй","SUB"=>"Дома из газобетона и клеёного бруса под ключ","URL"=>"/zastrojshhiki/","REGION"=>"Московская область","MIN_AREA"=>90,"BUILD_TIME"=>3,"PRICE_PER_M"=>35,"TYPES"=>["kamennye","derevyannye"],
         "LOGO_SRC"=>null,"BRAND"=>["abbr"=>"ZX","bg"=>"#00BF3F","fg"=>"#FFFFFF","grad"=>"#00A337"]],
        ["NAME"=>"Rubkoff Wood","SUB"=>"Проектирование и строительство деревянных домов и бань","URL"=>"/zastrojshhiki/","REGION"=>"Подмосковье","MIN_AREA"=>140,"BUILD_TIME"=>3,"PRICE_PER_M"=>46,"TYPES"=>["derevyannye"],
         "LOGO_SRC"=>null,"BRAND"=>["abbr"=>"RW","bg"=>"#7A4F2D","fg"=>"#FFFFFF","grad"=>"#5C3A1E"]],
        ["NAME"=>"Green House Stroy","SUB"=>"Дома «Барнхаус», «Фахверк», в&nbsp;стиле «Хай-Тек»","URL"=>"/zastrojshhiki/","REGION"=>"Москва и МО","MIN_AREA"=>72,"BUILD_TIME"=>3,"PRICE_PER_M"=>45,"TYPES"=>["karkasnye","modulnye"],
         "LOGO_SRC"=>null,"BRAND"=>["abbr"=>"GH","bg"=>"#2E7D5B","fg"=>"#FFFFFF","grad"=>"#1F5C42"]],
        ["NAME"=>"Brick House","SUB"=>"Проектирование и строительство домов из камня и пеноблоков","URL"=>"/zastrojshhiki/","REGION"=>"Тверская область","MIN_AREA"=>80,"BUILD_TIME"=>4,"PRICE_PER_M"=>35,"TYPES"=>["kamennye"],
         "LOGO_SRC"=>null,"BRAND"=>["abbr"=>"BH","bg"=>"#B5462E","fg"=>"#FFFFFF","grad"=>"#923620"]],
    ];
}

$partnersCount = count($arBuilders);
// Агрегаты для верхних KPI — берём из всех 5 партнёров (как на странице застройщиков)
$totalHousesAll = 1135;  // статичный счётчик со страницы застройщиков
$avgExpAll      = 9;
$partnersAll    = 5;
?>
<?if(!empty($arBuilders)):?>
<section class="doma-partners">
    <div class="c-sel--div__CONTAINER">
        <div class="doma-partners__head">
            <div class="doma-partners__head-left">
                <span class="doma-partners__eyebrow">Партнёрская сеть · <?=$partnersAll?> компаний</span>
                <h2 class="doma-partners__h2 font__HEADING_SECTION_TITLE">С&nbsp;кем мы&nbsp;работаем</h2>
                <p class="doma-partners__lead">Работаем только с&nbsp;проверенными командами. Каждая прошла аудит: финустойчивость, качество объектов, репутация. Вы&nbsp;выбираете партнёра под свой проект&nbsp;— мы&nbsp;даём фиксированную смету, договор и&nbsp;гарантию&nbsp;5&nbsp;лет.</p>
            </div>
            <div class="doma-partners__head-stats">
                <div class="doma-partners__kpi">
                    <div class="doma-partners__kpi-n"><?=$totalHousesAll?>+</div>
                    <div class="doma-partners__kpi-l">домов<br>сдано</div>
                </div>
                <div class="doma-partners__kpi">
                    <div class="doma-partners__kpi-n"><?=$avgExpAll?></div>
                    <div class="doma-partners__kpi-l">лет опыта<br>в среднем</div>
                </div>
                <div class="doma-partners__kpi">
                    <div class="doma-partners__kpi-n">100%</div>
                    <div class="doma-partners__kpi-l">прошли<br>аудит</div>
                </div>
            </div>
        </div>

        <div class="doma-partners__grid">
            <?foreach($arBuilders as $i => $b):
                $firstLetter = mb_strtoupper(mb_substr($b["NAME"], 0, 1));
                $types = isset($b["TYPES"]) ? (array)$b["TYPES"] : [];
            ?>
            <div class="doma-partners__item">
                <div class="doma-partners__badge">
                    <svg width="12" height="12" viewBox="0 0 14 14" fill="none"><path d="M7 1l1.763 3.573 3.944.573-2.854 2.781.674 3.928L7 9.99l-3.527 1.855.674-3.928L1.293 5.146l3.944-.573L7 1z" fill="currentColor"/></svg>
                    Проверен
                </div>

                <?
                    $brand = !empty($b["BRAND"]) ? $b["BRAND"] : null;
                    $logoSrc = !empty($b["LOGO_SRC"]) ? $b["LOGO_SRC"] : null;
                    $logoStyle = $brand ? 'background:linear-gradient(135deg,'.$brand['bg'].','.$brand['grad'].');color:'.$brand['fg'].';border-color:transparent;' : '';
                ?>
                <div class="doma-partners__head-row">
                    <div class="doma-partners__logo<?=$brand?' doma-partners__logo--brand':''?>" style="<?=$logoStyle?>">
                        <?if($logoSrc):?>
                            <img src="<?=$logoSrc?>" alt="<?=htmlspecialchars($b["NAME"])?>" loading="lazy">
                        <?elseif($brand):?>
                            <span class="doma-partners__logo-abbr"><?=htmlspecialchars($brand['abbr'])?></span>
                        <?else:?>
                            <span><?=$firstLetter?></span>
                        <?endif?>
                    </div>
                    <div class="doma-partners__head-text">
                        <h3 class="doma-partners__title"><?=htmlspecialchars($b["NAME"])?></h3>
                        <?if(!empty($b["REGION"])):?>
                            <div class="doma-partners__region">
                                <svg width="11" height="14" viewBox="0 0 11 14" fill="none"><path d="M5.5 0C2.46 0 0 2.46 0 5.5 0 9.63 5.5 14 5.5 14S11 9.63 11 5.5C11 2.46 8.54 0 5.5 0zm0 7.5a2 2 0 110-4 2 2 0 010 4z" fill="currentColor"/></svg>
                                <?=htmlspecialchars($b["REGION"])?>
                            </div>
                        <?endif?>
                    </div>
                </div>

                <?if(!empty($b["SUB"])):?>
                    <p class="doma-partners__sub"><?=$b["SUB"]?></p>
                <?endif?>

                <?if(!empty($types)):?>
                <div class="doma-partners__tags">
                    <?foreach($types as $t): if(!isset($typeLabels[$t])) continue;?>
                        <span class="doma-partners__tag"><?=$typeLabels[$t]['icon']?> <?=$typeLabels[$t]['label']?></span>
                    <?endforeach?>
                </div>
                <?endif?>

                <?if(!empty($b["MIN_AREA"]) || !empty($b["BUILD_TIME"]) || !empty($b["PRICE_PER_M"])):?>
                <div class="doma-partners__metrics">
                    <?if(!empty($b["MIN_AREA"])):?>
                    <div class="doma-partners__metric">
                        <div class="doma-partners__metric-icon">📐</div>
                        <div>
                            <b>от&nbsp;<?=$b["MIN_AREA"]?>&nbsp;м²</b>
                            <span>площадь домов</span>
                        </div>
                    </div>
                    <?endif?>
                    <?if(!empty($b["BUILD_TIME"])):?>
                    <div class="doma-partners__metric">
                        <div class="doma-partners__metric-icon">⏱</div>
                        <div>
                            <b>от&nbsp;<?=$b["BUILD_TIME"]?>&nbsp;мес.</b>
                            <span>срок возведения</span>
                        </div>
                    </div>
                    <?endif?>
                    <?if(!empty($b["PRICE_PER_M"])):?>
                    <div class="doma-partners__metric doma-partners__metric--accent">
                        <div class="doma-partners__metric-icon">💰</div>
                        <div>
                            <b>от&nbsp;<?=$b["PRICE_PER_M"]?>&nbsp;тыс.&nbsp;₽/м²</b>
                            <span>цена строительства</span>
                        </div>
                    </div>
                    <?endif?>
                </div>
                <?endif?>

                <div class="doma-partners__actions">
                    <a class="doma-partners__btn doma-partners__btn--primary" href="<?=htmlspecialchars($b["URL"])?>">О&nbsp;компании →</a>
                    <a class="doma-partners__btn doma-partners__btn--ghost" data-fancybox="" data-src="#hidden-form" href="javascript:;" data-scope="Связаться с <?=htmlspecialchars($b["NAME"])?>">Связаться</a>
                </div>
            </div>
            <?endforeach?>
        </div>

        <div class="doma-partners__footer">
            <div class="doma-partners__footer-icon">
                <svg width="32" height="32" viewBox="0 0 32 32" fill="none"><circle cx="16" cy="16" r="15" stroke="currentColor" stroke-width="1.5"/><path d="M12 13a4 4 0 118 0c0 2.5-4 3-4 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><circle cx="16" cy="22" r="1.2" fill="currentColor"/></svg>
            </div>
            <div class="doma-partners__footer-text">
                <b>Не&nbsp;знаете, кого выбрать?</b>
                <span>Подберём 3&nbsp;подходящих застройщика под ваш бюджет и&nbsp;участок за&nbsp;1&nbsp;минуту.</span>
            </div>
            <div class="doma-partners__footer-actions">
                <a class="doma-partners__btn doma-partners__btn--primary doma-partners__btn--lg" data-fancybox="" data-src="#hidden-form" href="javascript:;" data-scope="Подбор застройщика">Подобрать застройщика</a>
                <a class="doma-partners__btn doma-partners__btn--ghost doma-partners__btn--lg" href="/zastrojshhiki/">Все партнёры →</a>
            </div>
        </div>
    </div>
</section>
<?endif?>

<!-- ============ БЛОК ИПОТЕКИ (новый калькулятор) ============ -->
<section class="doma-mortgage-wrap">
    <?$APPLICATION->IncludeComponent(
        "zemex:mortgage.section",
        "",
        array(
            "HERO_TITLE" => "Поможем с ипотекой на дом",
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

<!-- ============ ФИНАЛЬНЫЙ CTA (для готовых домов) ============ -->
<section class="doma-final">
    <div class="doma-final__bg"></div>
    <div class="doma-final__overlay"></div>
    <div class="c-sel--div__CONTAINER">
    <div class="doma-final__grid">
        <div class="doma-final__left">
            <span class="doma-final__eyebrow font__BODY_TEXT_CAPTION">Подбор за 1 минуту</span>
            <h2 class="doma-final__h2 font__HEADING_SECTION_TITLE">3 проекта<br>под ваш бюджет</h2>
            <p class="doma-final__lead font__BODY_TEXT_PRIMARY">Ответьте на пару вопросов — и&nbsp;получите персональную подборку готовых домов с&nbsp;расчётом стоимости «под ключ» и&nbsp;сроков строительства.</p>
            <ul class="doma-final__bullets">
                <li><span>✓</span> Фиксированная цена в&nbsp;договоре</li>
                <li><span>✓</span> Работаем по&nbsp;ипотеке всех ведущих банков</li>
                <li><span>✓</span> Гарантия 5&nbsp;лет на&nbsp;конструктив</li>
            </ul>
        </div>
        <div class="doma-final__card">
            <form class="doma-final__form" action="/local/ajax/feedback.php" method="post" data-form="doma-final">
                <input type="hidden" name="SCOPE" value="Подбор дома — финальный CTA">
                <div class="doma-final__field">
                    <label class="font__BODY_TEXT_CAPTION">Ваше имя</label>
                    <input type="text" name="NAME" placeholder="Иван" required>
                </div>
                <div class="doma-final__field">
                    <label class="font__BODY_TEXT_CAPTION">Номер телефона</label>
                    <input type="tel" name="PHONE" placeholder="+7 (___) ___-__-__" required>
                </div>
                <div class="doma-final__field">
                    <label class="font__BODY_TEXT_CAPTION">Тип дома</label>
                    <select name="HOUSE_TYPE">
                        <option value="">Любой</option>
                        <option>Одноэтажный</option>
                        <option>Двухэтажный</option>
                        <option>С мансардой</option>
                        <option>Коттедж</option>
                    </select>
                </div>
                <div class="doma-final__field">
                    <label class="font__BODY_TEXT_CAPTION">Бюджет, ₽</label>
                    <div class="doma-final__range">
                        <input type="number" name="BUDGET_MIN" placeholder="от 3 000 000">
                        <span>—</span>
                        <input type="number" name="BUDGET_MAX" placeholder="до 15 000 000">
                    </div>
                </div>
                <div class="doma-final__field">
                    <label class="font__BODY_TEXT_CAPTION">Когда нужен дом?</label>
                    <div class="doma-final__pills">
                        <label class="doma-final__pill"><input type="radio" name="WHEN" value="urgent" checked><span>Срочно</span></label>
                        <label class="doma-final__pill"><input type="radio" name="WHEN" value="quarter"><span>В этом квартале</span></label>
                        <label class="doma-final__pill"><input type="radio" name="WHEN" value="future"><span>В будущем</span></label>
                    </div>
                </div>
                <button type="submit" class="doma-final__submit font__BUTTONS_BUTTON">Получить подборку проектов</button>
                <p class="doma-final__privacy">Нажимая кнопку, вы соглашаетесь на&nbsp;<a href="/policy/" target="_blank">обработку персональных данных</a>.</p>
            </form>
        </div>
    </div>
    </div>
</section>

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
          'Консультация':   'Консультация по выбору дома',
          'Подбор дома':    'Персональный подбор проекта',
          'Подбор застрой': 'Подбор застройщика',
          'Связаться с':    'Связаться с застройщиком'
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
      // Не перехватываем кнопки внутри калькулятора ипотеки (у него своя модалка)
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

<!-- ============ ЛОГИКА МАСТЕРА ПОДБОРА ============ -->
<script>
(function(){
  var wiz = document.getElementById('domaWizard');
  if(!wiz) return;

  // Тоггл свёрнутого/развёрнутого состояния
  var wrap = document.getElementById('domaWizardWrap');
  var trigger = wrap ? wrap.querySelector('[data-wizard-open]') : null;
  var closeBtn = wiz.querySelector('[data-wizard-close]');
  function openWiz(){
    wiz.hidden = false;
    if(wrap) wrap.style.display = 'none';
    setTimeout(function(){ wiz.scrollIntoView({block:'start', behavior:'smooth'}); }, 30);
  }
  function closeWiz(){
    wiz.hidden = true;
    if(wrap){
      wrap.style.display = '';
      wrap.scrollIntoView({block:'start', behavior:'smooth'});
    }
  }
  if(trigger)  trigger.addEventListener('click', openWiz);
  if(closeBtn) closeBtn.addEventListener('click', closeWiz);

  var state = {
    step: 1,
    family: null, areaMin: 0, areaMax: 999, bedrooms: 0,
    mode: 'cash',
    cash: 5000000,
    down: 1500000, monthly: 50000,
    when: 'urgent', floors: 'any', purpose: 'permanent'
  };

  function fmt(n){ return Math.round(n).toLocaleString('ru-RU').replace(/,/g,' ') + ' ₽'; }
  function fmtShort(n){
    if(n >= 1000000) return (n/1000000).toFixed(n%1000000===0?0:1).replace('.', ',') + ' млн ₽';
    if(n >= 1000) return Math.round(n/1000) + ' тыс ₽';
    return n + ' ₽';
  }

  // Расчёт бюджета по ипотеке: PV = PMT * (1 - (1+r)^-n) / r
  var WIZ_TERM_M = <?=(int)$wiz['step2']['mortgage_term_months']?>;
  var WIZ_RATE_M = <?=(float)$wiz['step2']['mortgage_rate']?>/100/12;
  function calcBudget(){
    if(state.mode === 'cash') return state.cash;
    var loan = state.monthly * (1 - Math.pow(1+WIZ_RATE_M, -WIZ_TERM_M)) / WIZ_RATE_M;
    return state.down + loan;
  }
  function calcLoan(){
    return state.monthly * (1 - Math.pow(1+WIZ_RATE_M, -WIZ_TERM_M)) / WIZ_RATE_M;
  }

  // === ШАГ 1: семья ===
  wiz.querySelectorAll('.doma-wizard__card').forEach(function(c){
    c.addEventListener('click', function(){
      wiz.querySelectorAll('.doma-wizard__card').forEach(function(x){ x.classList.remove('is-active'); });
      c.classList.add('is-active');
      state.family   = parseInt(c.dataset.family,10);
      state.areaMin  = parseInt(c.dataset.areaMin,10);
      state.areaMax  = parseInt(c.dataset.areaMax,10);
      state.bedrooms = parseInt(c.dataset.bedrooms,10);
      var nextBtn = wiz.querySelector('[data-step="1"] [data-wizard-next]');
      if(nextBtn) nextBtn.disabled = false;
    });
  });

  // === ШАГ 2: бюджет ===
  var modeToggle = wiz.querySelector('[data-wizard-mode-toggle]');
  if(modeToggle){
    modeToggle.querySelectorAll('button').forEach(function(b){
      b.addEventListener('click', function(){
        modeToggle.querySelectorAll('button').forEach(function(x){ x.classList.remove('is-active'); });
        b.classList.add('is-active');
        state.mode = b.dataset.mode;
        wiz.querySelectorAll('[data-mode-field]').forEach(function(f){
          f.hidden = (f.dataset.modeField !== state.mode);
        });
      });
    });
  }
  var inputCash    = wiz.querySelector('[data-wizard-input="cash"]');
  var inputDown    = wiz.querySelector('[data-wizard-input="down"]');
  var inputMonthly = wiz.querySelector('[data-wizard-input="monthly"]');
  var outCash    = wiz.querySelector('[data-wizard-cash-out]');
  var outDown    = wiz.querySelector('[data-wizard-down-out]');
  var outMonthly = wiz.querySelector('[data-wizard-monthly-out]');
  var monthlyShow = wiz.querySelector('[data-wizard-monthly-show]');
  var loanShow   = wiz.querySelector('[data-wizard-loan]');
  var budgetShow = wiz.querySelector('[data-wizard-budget]');

  function refreshMortgage(){
    var loan = calcLoan(), budget = calcBudget();
    if(monthlyShow) monthlyShow.textContent = fmt(state.monthly);
    if(loanShow)    loanShow.textContent    = '~' + fmtShort(loan);
    if(budgetShow)  budgetShow.textContent  = '~' + fmtShort(budget);
  }
  if(inputCash) inputCash.addEventListener('input', function(){ state.cash = parseInt(this.value,10); if(outCash) outCash.textContent = fmt(state.cash); });
  if(inputDown) inputDown.addEventListener('input', function(){ state.down = parseInt(this.value,10); if(outDown) outDown.textContent = fmt(state.down); refreshMortgage(); });
  if(inputMonthly) inputMonthly.addEventListener('input', function(){ state.monthly = parseInt(this.value,10); if(outMonthly) outMonthly.textContent = fmt(state.monthly); refreshMortgage(); });
  refreshMortgage();

  // === ШАГ 3: пилюли ===
  wiz.querySelectorAll('[data-wizard-group]').forEach(function(g){
    g.querySelectorAll('button').forEach(function(b){
      b.addEventListener('click', function(){
        g.querySelectorAll('button').forEach(function(x){ x.classList.remove('is-active'); });
        b.classList.add('is-active');
        state[g.dataset.wizardGroup] = b.dataset.val;
      });
    });
  });

  // === Навигация ===
  function go(step){
    state.step = step;
    wiz.querySelectorAll('[data-step]').forEach(function(s){
      s.classList.toggle('is-active', s.dataset.step == step);
    });
    wiz.querySelectorAll('[data-step-dot]').forEach(function(d){
      var n = parseInt(d.dataset.stepDot,10);
      d.classList.toggle('is-active', step !== 'result' && n <= step);
      d.classList.toggle('is-done',   step !== 'result' && n < step);
    });
    var progressBar = wiz.querySelector('[data-wizard-progress]');
    if(progressBar) progressBar.style.display = (step === 'result') ? 'none' : '';
    wiz.scrollIntoView({block:'start', behavior:'smooth'});
  }
  wiz.querySelectorAll('[data-wizard-next]').forEach(function(b){
    b.addEventListener('click', function(){ go(state.step + 1); });
  });
  wiz.querySelectorAll('[data-wizard-back]').forEach(function(b){
    b.addEventListener('click', function(){ go(state.step - 1); });
  });

  // === Финал: подбор ===
  function readProjects(){
    return Array.prototype.slice.call(document.querySelectorAll('.zx-proj-card[data-price]')).map(function(card){
      var img = card.querySelector('.zx-proj-card__img img, img');
      var title = card.querySelector('.zx-proj-card__title, .zx-proj-card__name, h3');
      var price = parseInt(card.dataset.price, 10) || 0;
      var sq    = parseFloat(card.dataset.square) || 0;
      var floors= parseInt(card.dataset.floors,10) || 0;
      return {
        url: card.getAttribute('href'),
        img: img ? (img.getAttribute('src') || img.getAttribute('data-src') || '') : '',
        name: title ? title.textContent.trim() : '',
        price: price, square: sq, floors: floors,
        ready: card.dataset.ready === 'yes',
        inprocess: card.dataset.inprocess === 'yes'
      };
    }).filter(function(p){ return p.price > 0 && p.square > 0; });
  }

  function pickResults(){
    var budget = calcBudget();
    var all = readProjects();
    if(!all.length) return { matched: [], cheapest: null, budget: budget };

    var matched = all.filter(function(p){
      var areaOk  = p.square >= state.areaMin - 10 && p.square <= state.areaMax + 30;
      var priceOk = p.price <= budget;
      var floorOk = state.floors === 'any' || (state.floors === '1' && p.floors === 1) || (state.floors === '2' && p.floors >= 2);
      return areaOk && priceOk && floorOk;
    });

    // Сортировка: чем ближе к рекомендованной площади и дешевле — тем выше
    var idealArea = (state.areaMin + state.areaMax) / 2;
    matched.sort(function(a,b){
      var scoreA = Math.abs(a.square - idealArea) + (a.price/budget)*30;
      var scoreB = Math.abs(b.square - idealArea) + (b.price/budget)*30;
      return scoreA - scoreB;
    });

    var cheapest = all.slice().sort(function(a,b){ return a.price - b.price; })[0];
    return { matched: matched.slice(0,3), cheapest: cheapest, budget: budget, total: all.length };
  }

  function renderResults(){
    var res = pickResults();
    var summary = wiz.querySelector('[data-wizard-summary]');
    var grid    = wiz.querySelector('[data-wizard-results]');
    var tip     = wiz.querySelector('[data-wizard-tip]');

    var familyTxt = state.family >= 5 ? '5+ человек' : (state.family + ' чел.');
    var areaTxt   = state.areaMin + '–' + state.areaMax + ' м²';
    var budgetTxt = fmtShort(res.budget);
    if(summary){
      summary.innerHTML =
        '<div class="doma-wizard__sum-row"><span>Семья</span><b>' + familyTxt + '</b></div>' +
        '<div class="doma-wizard__sum-row"><span>Площадь</span><b>' + areaTxt + '</b></div>' +
        '<div class="doma-wizard__sum-row"><span>Бюджет</span><b>' + budgetTxt + '</b></div>';
    }

    if(grid){
      if(res.matched.length){
        grid.innerHTML = res.matched.map(function(p){
          return '<a class="doma-wizard__rcard" href="' + p.url + '">' +
            (p.img ? '<div class="doma-wizard__rcard-img" style="background-image:url(' + p.img + ');"></div>' : '<div class="doma-wizard__rcard-img"></div>') +
            '<div class="doma-wizard__rcard-body">' +
              '<div class="doma-wizard__rcard-meta">' + p.square + ' м²' + (p.floors ? ' · ' + p.floors + (p.floors > 1 ? ' этажа' : ' этаж') : '') + '</div>' +
              '<h4 class="doma-wizard__rcard-name">' + p.name + '</h4>' +
              '<div class="doma-wizard__rcard-price">' + fmtShort(p.price) + '</div>' +
              '<span class="doma-wizard__rcard-go">Смотреть проект →</span>' +
            '</div>' +
          '</a>';
        }).join('');
        if(tip) tip.hidden = true;
      } else if(res.cheapest){
        // Ничего не подошло — показываем минимум и подсказку
        grid.innerHTML =
          '<a class="doma-wizard__rcard doma-wizard__rcard--alt" href="' + res.cheapest.url + '">' +
            (res.cheapest.img ? '<div class="doma-wizard__rcard-img" style="background-image:url(' + res.cheapest.img + ');"></div>' : '<div class="doma-wizard__rcard-img"></div>') +
            '<div class="doma-wizard__rcard-body">' +
              '<div class="doma-wizard__rcard-meta">⚠️ Минимум из&nbsp;каталога · ' + res.cheapest.square + ' м²</div>' +
              '<h4 class="doma-wizard__rcard-name">' + res.cheapest.name + '</h4>' +
              '<div class="doma-wizard__rcard-price">' + fmtShort(res.cheapest.price) + '</div>' +
              '<span class="doma-wizard__rcard-go">Смотреть проект →</span>' +
            '</div>' +
          '</a>';

        var gap = res.cheapest.price - res.budget;
        var advice = '';
        if(gap > 0){
          if(state.mode === 'cash'){
            advice = 'Не&nbsp;хватает <b>~' + fmtShort(gap) + '</b>. Возьмите ипотеку — при платеже <b>30 000 ₽/мес</b> и&nbsp;первоначальном <b>' + fmtShort(state.cash * 0.3) + '</b> бюджет вырастет до&nbsp;<b>' + fmtShort(state.cash + 4000000) + '</b> и&nbsp;вы&nbsp;откроете больше проектов.';
          } else {
            var addMonthly = Math.ceil(gap / 1000 * 7.2 / 100) * 1000;
            advice = 'Не&nbsp;хватает <b>~' + fmtShort(gap) + '</b>. Можно увеличить ежемесячный платёж на&nbsp;<b>~' + fmt(addMonthly) + '</b> или поднять первоначальный взнос — и&nbsp;этот проект станет доступен.';
          }
        } else {
          advice = 'Под ваш запрос не&nbsp;нашлось проектов в&nbsp;нужной площади. Попробуйте ослабить ограничения — например, рассмотреть 1&nbsp;этаж вместо «не важно».';
        }
        if(tip){
          tip.innerHTML =
            '<div class="doma-wizard__tip-icon">💡</div>' +
            '<div><b>Подходящих по&nbsp;всем параметрам нет.</b><br>' + advice + '</div>';
          tip.hidden = false;
        }
      } else {
        grid.innerHTML = '<div class="doma-wizard__empty">Каталог пока обновляется. Оставьте заявку — мы&nbsp;подберём проекты под ваш запрос вручную.</div>';
        if(tip) tip.hidden = true;
      }
    }
    go('result');
  }

  wiz.querySelectorAll('[data-wizard-finish]').forEach(function(b){
    b.addEventListener('click', renderResults);
  });
  wiz.querySelectorAll('[data-wizard-restart]').forEach(function(b){
    b.addEventListener('click', function(){
      // Сбрасываем все активные
      wiz.querySelectorAll('.doma-wizard__card').forEach(function(c){ c.classList.remove('is-active'); });
      var nextBtn = wiz.querySelector('[data-step="1"] [data-wizard-next]');
      if(nextBtn) nextBtn.disabled = true;
      state.family = null;
      go(1);
    });
  });
})();
</script>
