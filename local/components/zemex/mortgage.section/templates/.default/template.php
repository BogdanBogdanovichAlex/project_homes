<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @var CBitrixComponentTemplate $this */

$this->setFrameMode(true);

$programs   = $arResult["PROGRAMS"]    ?? [];
$bankGroups = $arResult["BANK_GROUPS"] ?? [];
$banksTotal = (int)($arResult["BANKS_TOTAL"]  ?? 0);
$groupTotal = (int)($arResult["GROUPS_TOTAL"] ?? 0);

// Админ-редактируемые тексты
$heroTitle  = $arParams["HERO_TITLE"] ?? ($arParams["FORM_TITLE"] ?? "Рассчитайте варианты покупки");
$heroSubArr = $arParams["FORM_DESCRIPTION"] ?? [];
$heroSub    = is_array($heroSubArr) ? implode(" ", $heroSubArr) : (string)$heroSubArr;
if ($heroSub === "") {
    $heroSub = "Сами подадим заявку в банки для получения партнерской скидки, не&nbsp;передавая ваши контакты ни одному из банков и не надоедая вам звонками";
}
$ctaText    = $arParams["CTA_TEXT"]    ?? "Рассчитать стоимость";
$applyText  = $arParams["APPLY_TEXT"]  ?? "Оставить заявку";
$sortLabel  = $arParams["SORT_LABEL"]  ?? "Сортировать";

// Версия для cache-busting — привязана ко времени изменения файлов
$ver = max(
    @filemtime(__DIR__ . "/style.css")  ?: 1,
    @filemtime(__DIR__ . "/script.js") ?: 1
);
?>

<section class="zxc" id="zxc-section" aria-labelledby="zxc-title"
         data-rates='<?= htmlspecialcharsbx(json_encode($arResult["RATES"] ?? (object)[], JSON_UNESCAPED_UNICODE)) ?>'>
  <div class="zxc__wrap">

    <header class="zxc__head">
      <h2 class="zxc__title" id="zxc-title"><?= htmlspecialcharsbx($heroTitle) ?></h2>
      <p class="zxc__sub"><?= $heroSub ?></p>
    </header>

    <div class="zxc__body">

      <!-- ════ Калькулятор ════ -->
      <div class="zxc__calc">

        <!-- Программа -->
        <div class="zxc__prog" role="group" aria-label="Тип ипотечной программы">
          <span class="zxc__prog-label">Тип программы</span>
          <div class="zxc__prog-tabs">
            <button type="button" class="zxc__prog-tab js-prog" data-prog="mortgage" aria-pressed="true">Ипотека</button>
            <button type="button" class="zxc__prog-tab js-prog" data-prog="family"   aria-pressed="false">Семейная ипотека</button>
            <button type="button" class="zxc__prog-tab js-prog" data-prog="it"       aria-pressed="false">IT-ипотека</button>
            <button type="button" class="zxc__prog-tab js-prog" data-prog="military" aria-pressed="false">Военная ипотека</button>
            <button type="button" class="zxc__prog-tab js-prog" data-prog="installment" aria-pressed="false">Рассматриваю рассрочку</button>
          </div>
          <span class="zxc__rate-hint" aria-live="polite">Ставка: 6% годовых</span>
        </div>

        <!-- Стоимость -->
        <div class="zxc__slider">
          <div class="zxc__slider-row">
            <label class="zxc__slider-label" for="zx-cost-val">Стоимость участка, ₽</label>
            <input type="text" class="zxc__slider-val" id="zx-cost-val" value="10 000 000" inputmode="numeric" aria-label="Стоимость участка в рублях">
          </div>
          <div class="zxc__track" id="zx-cost-track">
            <div class="zxc__track-fill" id="zx-cost-fill"></div>
            <input type="range" class="zxc__range" id="zx-cost-slider"
                   min="500000" max="100000000" step="100000" value="10000000"
                   aria-label="Стоимость участка, ползунок">
          </div>
          <div class="zxc__track-limits"><span>500 000</span><span>100 000 000</span></div>
        </div>

        <!-- Первоначальный взнос -->
        <div class="zxc__slider">
          <div class="zxc__slider-row">
            <label class="zxc__slider-label" for="zx-down-rub">Первоначальный взнос, ₽</label>
            <div class="zxc__slider-val-duo">
              <input type="text" class="zxc__slider-val" id="zx-down-rub" value="1 400 000" inputmode="numeric" aria-label="Первоначальный взнос в рублях">
              <span class="zxc__slider-sep" aria-hidden="true">|</span>
              <span class="zxc__slider-pct" id="zx-down-pct">14%</span>
            </div>
          </div>
          <div class="zxc__track" id="zx-down-track">
            <div class="zxc__track-fill" id="zx-down-fill"></div>
            <input type="range" class="zxc__range" id="zx-down-slider"
                   min="10" max="90" step="1" value="14"
                   aria-label="Первоначальный взнос, процент">
          </div>
          <div class="zxc__track-limits"><span>10%</span><span>90%</span></div>
        </div>

        <!-- Срок -->
        <div class="zxc__slider">
          <div class="zxc__slider-row">
            <label class="zxc__slider-label js-term-label" for="zx-term-val">Срок финансирования, лет</label>
            <div class="zxc__slider-val-duo">
              <input type="text" class="zxc__slider-val" id="zx-term-val" value="20" inputmode="numeric" aria-label="Срок кредита">
              <span class="zxc__slider-unit js-term-unit">лет</span>
            </div>
          </div>
          <div class="zxc__track">
            <div class="zxc__track-fill" id="zx-term-fill"></div>
            <input type="range" class="zxc__range" id="zx-term-slider"
                   min="1" max="30" step="1" value="20"
                   aria-label="Срок кредита, ползунок">
          </div>
          <div class="zxc__track-limits"><span>1 год</span><span>30 лет</span></div>
        </div>

        <!-- Результат -->
        <div class="zxc__result">
          <div class="zxc__result-meta">
            <span class="zxc__result-label js-result-label">Ежемесячный платёж</span>
            <span class="zxc__result-hint">Расчёт предварительный. Окончательные условия зависят от результатов проверки документов и оценки платёжеспособности</span>
          </div>
          <span class="zxc__result-val" id="zx-result-val" aria-live="polite">—</span>
        </div>

        <button type="button" class="zxc__cta js-cta"><?= htmlspecialcharsbx($ctaText) ?></button>
      </div><!-- /.zxc__calc -->

      <!-- ════ Правая панель ════ -->
      <div class="zxc__right">

        <!-- Ипотека -->
        <div class="zxc__panel" id="zx-panel-mort" role="region" aria-label="Предложения банков">
          <div class="zxc__panel-head">
            <span class="zxc__panel-count">
              Подобрали <b><?= $banksTotal ?></b> <?= $banksTotal === 1 ? 'предложение' : (($banksTotal % 10 >= 2 && $banksTotal % 10 <= 4 && ($banksTotal % 100 < 10 || $banksTotal % 100 >= 20)) ? 'предложения' : 'предложений') ?> от <b><?= $banksTotal ?></b> <?= $banksTotal === 1 ? 'банка' : (($banksTotal % 10 >= 2 && $banksTotal % 10 <= 4 && ($banksTotal % 100 < 10 || $banksTotal % 100 >= 20)) ? 'банков' : 'банков') ?>
            </span>
            <button type="button" class="zxc__sort-btn js-sort" aria-label="<?= htmlspecialcharsbx($sortLabel) ?>: по возрастанию" data-order="asc">
              <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M2 4h12M4 8h8M6 12h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
              <span><?= htmlspecialcharsbx($sortLabel) ?></span>
            </button>
          </div>

          <div class="zxc__groups js-groups">
            <?php foreach ($bankGroups as $gi => $group): ?>
            <article class="zxc__group<?= !empty($group['OPEN']) ? ' zxc__group--open' : '' ?>"
                     data-gi="<?= (int)$gi ?>"
                     data-rate-num="<?= htmlspecialcharsbx((string)$group['RATE_NUM']) ?>">
              <div class="zxc__group-head js-group-head" role="button" tabindex="0"
                   aria-expanded="<?= !empty($group['OPEN']) ? 'true' : 'false' ?>"
                   aria-controls="zxc-group-<?= (int)$gi ?>-banks">
                <span class="zxc__group-rate js-group-rate" data-base="<?= htmlspecialcharsbx($group['RATE']) ?>"><?= htmlspecialcharsbx($group['RATE']) ?></span>
                <span class="zxc__group-meta">
                  от <span class="zxc__group-monthly js-group-monthly">—</span>
                  <span class="zxc__group-period">₽/мес · <?= htmlspecialcharsbx($group['PERIOD']) ?></span>
                </span>
                <button type="button" class="zxc__apply-btn js-apply"
                        data-rate="<?= htmlspecialcharsbx((string)$group['RATE_NUM']) ?>"
                        data-scope="group"><?= htmlspecialcharsbx($applyText) ?></button>
                <button type="button" class="zxc__expand-btn js-expand" aria-label="Развернуть банки">
                  <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true"><path d="M3 5l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </button>
              </div>
              <div class="zxc__group-banks" id="zxc-group-<?= (int)$gi ?>-banks">
                <?php foreach ($group['BANKS'] as $bi => $bank): ?>
                <div class="zxc__bank" data-bank-rate="<?= htmlspecialcharsbx((string)$group['RATE_NUM']) ?>">
                  <?php if (!empty($bank['LOGO'])): ?>
                  <div class="zxc__bank-logo zxc__bank-logo--img" aria-hidden="true">
                    <img src="<?= htmlspecialcharsbx($bank['LOGO']) ?>" alt="<?= htmlspecialcharsbx($bank['NAME']) ?>" loading="lazy">
                  </div>
                  <?php else: ?>
                  <div class="zxc__bank-logo" style="background:<?= htmlspecialcharsbx($bank['COLOR']) ?>;color:<?= htmlspecialcharsbx($bank['TEXT_COLOR']) ?>" aria-hidden="true">
                    <?= htmlspecialcharsbx(mb_strtoupper(mb_substr($bank['NAME'], 0, 1))) ?>
                  </div>
                  <?php endif; ?>
                  <div class="zxc__bank-info">
                    <span class="zxc__bank-name"><?= htmlspecialcharsbx($bank['NAME']) ?></span>
                    <span class="zxc__bank-rate">
                      <b class="js-bank-rate" data-base="<?= htmlspecialcharsbx($bank['RATE']) ?>"><?= htmlspecialcharsbx($bank['RATE']) ?></b>
                      &middot; от <b class="js-bank-monthly">—</b> ₽/мес &middot; <?= htmlspecialcharsbx($bank['PERIOD']) ?>
                    </span>
                    <span class="zxc__bank-details">
                      Первый взнос от <?= htmlspecialcharsbx($bank['DOWN']) ?>
                    </span>
                  </div>
                  <button type="button" class="zxc__apply-btn js-apply"
                          data-bank="<?= htmlspecialcharsbx($bank['NAME']) ?>"
                          data-rate="<?= htmlspecialcharsbx((string)$group['RATE_NUM']) ?>"
                          data-scope="bank"><?= htmlspecialcharsbx($applyText) ?></button>
                </div>
                <?php endforeach; ?>
                <button type="button" class="zxc__collapse-btn js-collapse">
                  Свернуть
                  <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true"><path d="M3 9l4-4 4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </button>
              </div>
            </article>
            <?php endforeach; ?>
          </div>
        </div><!-- /#zx-panel-mort -->

        <!-- Рассрочка -->
        <div class="zxc__panel zxc__panel--inst" id="zx-panel-inst" style="display:none" role="region" aria-label="Варианты рассрочки">
          <?php foreach ($programs as $pi => $prog): ?>
          <article class="zxc__pcard js-pcard<?= !empty($prog['ACTIVE']) ? ' zxc__pcard--active' : '' ?>"
                   data-inst-months="<?= htmlspecialcharsbx((string)($prog['INST_MONTHS'] ?? 0)) ?>"
                   data-inst-share="<?= htmlspecialcharsbx((string)($prog['INST_SHARE'] ?? 0)) ?>"
                   data-mort-rate="<?= htmlspecialcharsbx((string)($prog['MORT_RATE'] ?? 0)) ?>">
            <p class="zxc__pcard-title"><?= htmlspecialcharsbx($prog['TITLE']) ?></p>
            <div class="zxc__pcard-body">
              <div class="zxc__pcard-col">
                <span class="zxc__pcard-label">Процентная ставка</span>
                <span class="zxc__pcard-val js-proc-val"><?= htmlspecialcharsbx($prog['PROC_VAL']) ?></span>
                <span class="zxc__pcard-sub js-proc-sub"><?= htmlspecialcharsbx($prog['PROC_SUB']) ?></span>
              </div>
              <div class="zxc__pcard-col">
                <span class="zxc__pcard-label">Ежемесячный платёж</span>
                <span class="zxc__pcard-val js-pay-val"><?= htmlspecialcharsbx($prog['PAY_VAL']) ?></span>
                <span class="zxc__pcard-sub js-pay-sub"><?= htmlspecialcharsbx($prog['PAY_SUB']) ?></span>
              </div>
              <div class="zxc__pcard-col zxc__pcard-col--total">
                <span class="zxc__pcard-total js-total"><?= htmlspecialcharsbx($prog['TOTAL']) ?></span>
                <span class="zxc__pcard-badge js-badge"><?= htmlspecialcharsbx($prog['BADGE']) ?></span>
              </div>
            </div>
            <div class="zxc__pcard-foot">
              <button type="button" class="zxc__apply-btn zxc__apply-btn--inst js-apply"
                      data-scope="installment"
                      data-prog-index="<?= (int)$pi ?>"><?= htmlspecialcharsbx($applyText) ?></button>
            </div>
          </article>
          <?php endforeach; ?>
        </div>

      </div><!-- /.zxc__right -->
    </div><!-- /.zxc__body -->
  </div>

  <!-- Сайтовая форма (подгружается через _custom.js по .form_container) -->
  <div class="vp-heroModal1 form_container zxc-modal"
       data-header="Поможем с ипотекой"
       data-form_class="vp-heroModal1--form__FORM"
       data-form="<?= defined('FORM_MORTGAGE') ? FORM_MORTGAGE : 2 ?>"></div>
  <div class="vp-heroModal2 sucsess_heroModal zxc-modal-success">
    <div class="vp-heroModal2--div__READY">
      <img class="vp-heroModal2--img__READY" src="<?= SITE_TEMPLATE_PATH ?>/images/vp-hero-ready.svg" alt="галочка">
      <h2 class="vp-heroModal2--h2 font__HEADING_SECTION_TITLE">Спасибо за заявку!</h2>
      <p class="vp-heroModal2--p__READY font__BODY_TEXT_PRIMARY">Ваша заявка принята в обработку. Мы свяжемся с вами в ближайшее время.</p>
      <button class="vp-heroModal2--button__CLEAR_DT font__BUTTONS_BUTTON">Понятно</button>
    </div>
  </div>
</section>

