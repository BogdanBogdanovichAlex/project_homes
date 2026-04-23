<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);

$standardCache = array("CACHE_TYPE" => "A", "CACHE_TIME" => "3600");
?>

<!-- ============ СЕЛЛИНГ-ХИРО ============ -->
<section class="doma-hero">
    <div class="doma-hero__bg"></div>
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
                <span class="doma-hero__eyebrow font__BODY_TEXT_CAPTION">Проекты домов для постройки</span>
                <h1 class="doma-hero__h1">Дом вашей мечты — <span>за 90 дней</span></h1>
                <p class="doma-hero__lead font__BODY_TEXT_PRIMARY">Готовые и строящиеся проекты от&nbsp;проверенных застройщиков. Фиксированная цена, прозрачные сроки и&nbsp;планировки на&nbsp;любой бюджет.</p>
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

<!-- ============ ОПИСАНИЕ УСЛУГИ / ПРЕИМУЩЕСТВА ============ -->
<section class="doma-features">
    <div class="c-sel--div__CONTAINER">
        <div class="doma-intro">
            <div class="doma-intro__left">
                <h2 class="font__HEADING_SECTION_TITLE">Как мы строим</h2>
            </div>
            <div class="doma-intro__right">
                <p class="font__BODY_TEXT_PRIMARY" style="color:var(--text-secondary);">Берём на&nbsp;себя весь цикл — от&nbsp;выбора проекта и&nbsp;подбора участка до&nbsp;ввода дома в&nbsp;эксплуатацию. Вы&nbsp;получаете фиксированную смету, прозрачные этапы и&nbsp;гарантию на&nbsp;конструктив.</p>
            </div>
        </div>

        <div class="doma-features__grid">
            <div class="doma-features__item">
                <div class="doma-features__num">01</div>
                <h3 class="doma-features__title font__HEADING_BLOCK_TITLE">Готовые проекты</h3>
                <p class="doma-features__text font__BODY_TEXT_PRIMARY">Более&nbsp;50 типовых проектов с&nbsp;готовой документацией. Просчитанная смета, финальная цена без&nbsp;скрытых доплат.</p>
            </div>
            <div class="doma-features__item">
                <div class="doma-features__num">02</div>
                <h3 class="doma-features__title font__HEADING_BLOCK_TITLE">Фиксированные сроки</h3>
                <p class="doma-features__text font__BODY_TEXT_PRIMARY">Сроки прописываются в&nbsp;договоре. Средний срок строительства «под&nbsp;ключ»&nbsp;— 90&nbsp;дней с&nbsp;момента заливки фундамента.</p>
            </div>
            <div class="doma-features__item">
                <div class="doma-features__num">03</div>
                <h3 class="doma-features__title font__HEADING_BLOCK_TITLE">Качество материалов</h3>
                <p class="doma-features__text font__BODY_TEXT_PRIMARY">Работаем с&nbsp;газобетоном D500, клеёным брусом и&nbsp;каркасом по&nbsp;финской технологии. Все поставщики проверены.</p>
            </div>
            <div class="doma-features__item">
                <div class="doma-features__num">04</div>
                <h3 class="doma-features__title font__HEADING_BLOCK_TITLE">Гарантия 5&nbsp;лет</h3>
                <p class="doma-features__text font__BODY_TEXT_PRIMARY">Гарантия на&nbsp;конструктив 5&nbsp;лет, страхование строительства и&nbsp;независимый технадзор на&nbsp;каждом этапе.</p>
            </div>
        </div>
    </div>
</section>

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
            <h2 class="doma-final__h2">3 проекта<br>под ваш бюджет</h2>
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
