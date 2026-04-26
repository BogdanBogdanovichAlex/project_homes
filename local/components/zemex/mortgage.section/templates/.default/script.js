/**
 * Zemex — Калькулятор ипотеки / рассрочки v5
 * Изменения v5:
 * - Прямые слушатели на каждой группе (защита от перехвата пропагации)
 * - Шеврон корректно раскрывает И сворачивает открытую группу
 * - Черная кнопка "Показывать все ипотечные" работает как dropdown
 *   (клик открывает/закрывает список программ)
 * - Клик вне — закрывает dropdown
 * Изменения v4:
 * - Ставки банковских карточек реагируют на выбранную программу (IT / Семейная / Военная).
 *   При переключении программы все банки пересчитываются с единой ставкой.
 *   При "Показывать все" — у каждого банка используется его собственная ставка.
 * - Кнопка "Оставить заявку" открывает существующую модалку сайта
 *   (.vp-heroModal1 c классом __vp-heroModal1__VISIBLE + body.__c-body__FIXED).
 * - Кнопка "Оставить заявку" добавлена к карточкам рассрочки.
 * - Обработчик раскрытия групп работает корректно для всех групп (делегирование).
 */
(function () {
  'use strict';
  try { console.log('%c[ZXC] mortgage v5 loaded', 'color:#00bf3f;font-weight:700'); } catch(_){}

  /* ── Константы ────────────────────────────────────── */
  var DEFAULT_RATES = {
    all:         null,
    mortgage:    null,
    family:      6,
    it:          5,
    military:    6.75,
    installment: 0
  };
  // Ставки из админки (data-rates на #zxc-section) с fallback на дефолт
  var RATES = (function () {
    try {
      var sec = document.getElementById("zxc-section");
      if (sec && sec.dataset && sec.dataset.rates) {
        var obj = JSON.parse(sec.dataset.rates);
        var merged = {};
        for (var k in DEFAULT_RATES) merged[k] = DEFAULT_RATES[k];
        for (var k2 in obj) {
          var v = obj[k2];
          merged[k2] = (v === null || v === "" || isNaN(v)) ? null : parseFloat(v);
        }
        return merged;
      }
    } catch (e) { try { console.warn("[ZXC] RATES parse failed", e); } catch(_){} }
    return DEFAULT_RATES;
  })();
  try { console.log("[ZXC] RATES loaded:", RATES); } catch(_){}

  // Fallback-селекторы для поиска формы на странице, если модалки нет
  var APPLY_SELECTORS = [
    '#mortgage-form',
    '[data-form="mortgage"]',
    '[data-form="2"]',
    '.c-mortgage',
    '.js-contact-form'
  ];

  /* ── Утилиты ──────────────────────────────────────── */
  function fmt(n) {
    return Math.round(n).toLocaleString('ru-RU');
  }
  function parse(str) {
    return parseFloat(String(str || '').replace(/\s|&nbsp;/g, '').replace(',', '.')) || 0;
  }
  function clamp(v, min, max) { return Math.max(min, Math.min(max, v)); }
  function annuity(principal, annualPct, months) {
    if (principal <= 0 || months <= 0) return 0;
    if (annualPct <= 0) return Math.round(principal / months);
    var r = annualPct / 100 / 12;
    var pow = Math.pow(1 + r, months);
    return Math.round(principal * r * pow / (pow - 1));
  }
  function qs(sel, root)  { return (root || document).querySelector(sel); }
  function qsa(sel, root) { return (root || document).querySelectorAll(sel); }

  /* ── Состояние + DOM ──────────────────────────────── */
  var state = { prog: 'family' };
  var el = null;
  var section = null;

  function grabDom() {
    section = qs('#zxc-section');
    if (!section) return false;
    el = {
      costSlider : qs('#zx-cost-slider', section),
      costFill   : qs('#zx-cost-fill',   section),
      costVal    : qs('#zx-cost-val',    section),

      downSlider : qs('#zx-down-slider', section),
      downFill   : qs('#zx-down-fill',   section),
      downRub    : qs('#zx-down-rub',    section),
      downPct    : qs('#zx-down-pct',    section),

      termSlider : qs('#zx-term-slider', section),
      termFill   : qs('#zx-term-fill',   section),
      termVal    : qs('#zx-term-val',    section),

      resultVal  : qs('#zx-result-val',  section),
      resultLabel: qs('.js-result-label', section),
      termLabel  : qs('.js-term-label',  section),
      termUnit   : qs('.js-term-unit',   section),

      rateHint   : qs('.zxc__rate-hint', section),

      panelMort  : qs('#zx-panel-mort',  section),
      panelInst  : qs('#zx-panel-inst',  section),
      groups     : qs('.js-groups',      section),
      sortBtn    : qs('.js-sort',        section),

      progAll    : qs('.zxc__prog-all.js-prog', section),
      progTabs   : qsa('.zxc__prog-tab.js-prog',   section),
      ctaBtn     : qs('.js-cta',                    section),
    };
    return true;
  }

  /* ── Трек слайдера ────────────────────────────────── */
  function syncTrack(slider, fill) {
    if (!slider || !fill) return;
    var min = +slider.min, max = +slider.max;
    var pct = ((+slider.value - min) / (max - min)) * 100;
    fill.style.width = clamp(pct, 0, 100) + '%';
  }

  /* ── Пересчёт главного результата и банков ────────── */
  function recalc() {
    if (!el.costSlider) return;
    var cost    = +el.costSlider.value;
    var downPct = +el.downSlider.value;
    var term    = +el.termSlider.value;
    var downAmt = cost * downPct / 100;
    var loan    = Math.max(0, cost - downAmt);
    var isInst  = state.prog === 'installment';

    if (el.downRub) el.downRub.value = fmt(downAmt);
    if (el.downPct) el.downPct.textContent = downPct + '%';

    // Главный результат
    if (isInst) {
      if (el.resultVal) el.resultVal.textContent = fmt(loan) + ' ₽';
    } else {
      // Для "all" — усреднённая 6%, для остальных — ставка программы
      var rate    = RATES[state.prog] != null ? RATES[state.prog] : 6;
      var months  = term * 12;
      var monthly = annuity(loan, rate, months);
      if (el.resultVal) el.resultVal.textContent = fmt(monthly) + ' ₽';
    }

    // Пересчёт каждого банка (и группы)
    updateBankValues(loan, term);

    // Пересчёт карточек рассрочки
    var totalMonths = isInst ? term : term * 12;
    updateInstallmentCards(loan, totalMonths);
  }

  /* ── Пересчёт карточек рассрочки ─────── */
  function updateInstallmentCards(loan, totalMonths) {
    if (!section) return;
    qsa('.js-pcard', section).forEach(function (card) {
      var instMonths = parseFloat(card.dataset.instMonths) || 0;
      var instShare  = parseFloat(card.dataset.instShare)  || 0;
      var mortRate   = parseFloat(card.dataset.mortRate)   || 0;
      if (instMonths <= 0 || instShare <= 0 || totalMonths <= 0) return;

      var shareAmt    = loan * instShare / 100;
      var payFirst    = shareAmt / instMonths;
      var remaining   = Math.max(0, loan - shareAmt);
      var afterMonths = Math.max(1, totalMonths - instMonths);
      var payAfter    = annuity(remaining, mortRate, afterMonths);
      var instTotal   = shareAmt + payAfter * afterMonths;

      var mortMonthly = annuity(loan, mortRate, totalMonths);
      var mortTotal   = mortMonthly * totalMonths;
      var savings     = Math.max(0, Math.round(mortTotal - instTotal));

      var set = function (sel, val) { var e = qs(sel, card); if (e) e.textContent = val; };
      set('.js-proc-val', '0% → ' + mortRate + '%');
      set('.js-proc-sub', 'первые ' + instMonths + ' мес, затем ипотека');
      set('.js-pay-val',  fmt(payFirst) + ' → ' + fmt(payAfter) + ' ₽');
      set('.js-pay-sub',  'в месяц');
      set('.js-total',    fmt(Math.round(instTotal)) + ' ₽');
      set('.js-badge',    savings > 0 ? 'Экономия ' + fmt(savings) + ' ₽' : '');
    });
  }

  /* ── Пересчёт значений банков под калькулятор ─────── */
  /*
   * При выбранной конкретной программе (IT / Family / Military) — ставки всех банков
   * заменяются на ставку программы (визуально и в расчёте).
   * При "all" — у каждого банка используется его собственная ставка.
   */
  function updateBankValues(loan, termYears) {
    if (!el.groups) return;
    var months      = termYears * 12;
    var progRate    = RATES[state.prog];       // null для "all"
    var forceRate   = progRate != null && state.prog !== 'installment';

    qsa('.zxc__group', el.groups).forEach(function (gEl) {
      var ownRate    = parseFloat(gEl.dataset.rateNum || '0') || 0;
      var usedRate   = forceRate ? progRate : ownRate;
      var monthly    = annuity(loan, usedRate, months);

      // Бейдж группы (от X ₽/мес)
      var gMonthlyEl = qs('.js-group-monthly', gEl);
      if (gMonthlyEl) gMonthlyEl.textContent = fmt(monthly);

      // Ставка в заголовке группы: "от 5%" / "от 6%" и т.п.
      var gRateEl = qs('.js-group-rate', gEl);
      if (gRateEl) {
        if (forceRate) {
          gRateEl.textContent = 'от ' + progRate + '%';
        } else {
          gRateEl.textContent = gRateEl.dataset.base || gRateEl.textContent;
        }
      }

      // Банки внутри группы
      qsa('.zxc__bank', gEl).forEach(function (bEl) {
        var bankOwn    = parseFloat(bEl.dataset.bankRate || '0') || ownRate;
        var bankUsed   = forceRate ? progRate : bankOwn;
        var bankMonthly= annuity(loan, bankUsed, months);

        var bMonthEl = qs('.js-bank-monthly', bEl);
        if (bMonthEl) bMonthEl.textContent = fmt(bankMonthly);

        var bRateEl = qs('.js-bank-rate', bEl);
        if (bRateEl) {
          if (forceRate) {
            bRateEl.textContent = 'от ' + progRate + '%';
          } else {
            bRateEl.textContent = bRateEl.dataset.base || bRateEl.textContent;
          }
        }
      });
    });
  }

  /* ── Переключение программы ───────────────────────── */
  function switchProg(prog) {
    if (!RATES.hasOwnProperty(prog)) prog = 'all';
    state.prog = prog;
    var isInst = prog === 'installment';

    if (el.progAll) {
      el.progAll.classList.toggle('prog-inactive', prog !== 'mortgage');
      el.progAll.setAttribute('aria-pressed', prog === 'mortgage' ? 'true' : 'false');
    }
    el.progTabs.forEach(function (tab) {
      var active = tab.dataset.prog === prog;
      tab.classList.toggle('active', active);
      tab.setAttribute('aria-pressed', active ? 'true' : 'false');
    });

    if (el.panelMort) el.panelMort.style.display = isInst ? 'none' : '';
    if (el.panelInst) el.panelInst.style.display = isInst ? ''      : 'none';

    if (el.resultLabel) el.resultLabel.textContent = isInst ? 'Сумма рассрочки' : 'Ежемесячный платёж';
    if (el.termLabel)   el.termLabel.textContent   = isInst ? 'Срок рассрочки, мес' : 'Срок финансирования, лет';
    if (el.termUnit)    el.termUnit.textContent    = isInst ? 'мес' : 'лет';

    if (el.termSlider) {
      if (isInst) {
        el.termSlider.min = '1'; el.termSlider.max = '120';
        if (+el.termSlider.value > 120) el.termSlider.value = '36';
      } else {
        el.termSlider.min = '1'; el.termSlider.max = '30';
        if (+el.termSlider.value > 30) el.termSlider.value = '20';
      }
    }

    if (el.rateHint) {
      if (isInst) el.rateHint.textContent = '';
      else if (prog === 'all' || prog === 'mortgage') el.rateHint.textContent = 'Ставка: от 6% годовых';
      else el.rateHint.textContent = 'Ставка: ' + RATES[prog] + '% годовых';
    }

    syncTrack(el.termSlider, el.termFill);
    if (el.termVal && el.termSlider) el.termVal.value = el.termSlider.value;
    recalc();
  }

  /* ── Слайдеры ─────────────────────────────────────── */
  function bindSlider(slider, onChange) {
    if (!slider) return;
    slider.addEventListener('input', onChange);
  }
  function bindValInput(input, slider, parseMap) {
    if (!input || !slider) return;
    input.addEventListener('change', function () {
      var raw = parseMap(parse(this.value));
      raw = clamp(raw, +slider.min, +slider.max);
      slider.value = raw;
      slider.dispatchEvent(new Event('input'));
    });
  }

  function initSliders() {
    // Стоимость
    bindSlider(el.costSlider, function () {
      syncTrack(el.costSlider, el.costFill);
      if (el.costVal) el.costVal.value = fmt(+el.costSlider.value);
      recalc();
    });
    bindValInput(el.costVal, el.costSlider, function (v) { return v; });

    // Первоначальный взнос (ползунок — %, рубли — поле ввода)
    bindSlider(el.downSlider, function () {
      syncTrack(el.downSlider, el.downFill);
      recalc();
    });
    bindValInput(el.downRub, el.downSlider, function (rub) {
      var cost = el.costSlider ? +el.costSlider.value : 1;
      return cost > 0 ? Math.round(rub / cost * 100) : 0;
    });

    // Срок
    bindSlider(el.termSlider, function () {
      syncTrack(el.termSlider, el.termFill);
      if (el.termVal) el.termVal.value = el.termSlider.value;
      recalc();
    });
    bindValInput(el.termVal, el.termSlider, function (v) { return v; });

    // Первичная синхронизация треков
    syncTrack(el.costSlider, el.costFill);
    syncTrack(el.downSlider, el.downFill);
    syncTrack(el.termSlider, el.termFill);
  }

  /* ── Группы: развернуть/свернуть + keyboard ───────── */
  function toggleGroup(group, forceState) {
    if (!group) return;
    var open = typeof forceState === 'boolean'
      ? forceState
      : !group.classList.contains('zxc__group--open');
    group.classList.toggle('zxc__group--open', open);
    var head = qs('.zxc__group-head', group);
    if (head) head.setAttribute('aria-expanded', open ? 'true' : 'false');
  }

  function initGroups() {
    if (!section) return;

    // 1) Прямые слушатели на каждой группе — не зависят от пропагации других скриптов
    qsa('.zxc__group', section).forEach(function (group) {
      var head     = qs('.zxc__group-head', group);
      var expand   = qs('.js-expand',   group);
      var collapse = qs('.js-collapse', group);

      function onHeadClick(e) {
        // Если клик по кнопке "Оставить заявку" — не тогглим
        if (e.target.closest('.js-apply')) return;
        e.stopPropagation();
        toggleGroup(group);
      }
      if (head)     head.addEventListener('click', onHeadClick);
      if (expand)   expand.addEventListener('click', function (e) {
        e.stopPropagation(); toggleGroup(group);
      });
      if (collapse) collapse.addEventListener('click', function (e) {
        e.stopPropagation(); toggleGroup(group, false);
      });

      // Keyboard на заголовке
      if (head) {
        head.addEventListener('keydown', function (e) {
          if (e.target.closest('.js-apply')) return;
          if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            toggleGroup(group);
          }
        });
      }
    });

    // 2) Делегирование apply (и для рассрочки, и для ипотеки) — на всю секцию
    section.addEventListener('click', function (e) {
      var applyBtn = e.target.closest('.js-apply');
      if (applyBtn) {
        e.preventDefault();
        e.stopPropagation();
        return onApplyClick(applyBtn);
      }
    });
  }

  /* ── Сортировка групп по ставке ───────────────────── */
  function initSort() {
    if (!el.sortBtn || !el.groups) return;
    el.sortBtn.addEventListener('click', function () {
      var order = el.sortBtn.dataset.order === 'asc' ? 'desc' : 'asc';
      el.sortBtn.dataset.order = order;
      el.sortBtn.classList.toggle('is-desc', order === 'desc');
      var labelEl = el.sortBtn.querySelector('span');
      var labelText = labelEl ? labelEl.textContent.trim() : 'Сортировать';
      el.sortBtn.setAttribute('aria-label',
        labelText + (order === 'asc' ? ': по возрастанию' : ': по убыванию'));

      var items = Array.prototype.slice.call(qsa('.zxc__group', el.groups));
      items.sort(function (a, b) {
        var ra = parseFloat(a.dataset.rateNum) || 0;
        var rb = parseFloat(b.dataset.rateNum) || 0;
        return order === 'asc' ? ra - rb : rb - ra;
      });
      items.forEach(function (g) { el.groups.appendChild(g); });
    });
  }

  /* ── Открытие сайтовой модалки заявки ─────────────── */
  function openSiteModal(btn) {
    // 1) Модалка сайта (vp-heroModal1 + form_container c data-form="6")
    var modal = (section && section.querySelector('.vp-heroModal1.form_container'))
             || (section && section.querySelector('.vp-heroModal1'))
             || document.querySelector('.vp-heroModal1.form_container.zxc-modal')
             || document.querySelector('.vp-heroModal1.zxc-modal');
    if (modal) {
      // Пробуем прокинуть контекст в скрытые поля (если они есть в загруженной форме)
      var form = modal.querySelector('form');
      if (form && btn) {
        var bank = btn.getAttribute('data-bank');
        var rate = btn.getAttribute('data-rate');
        var scope = btn.getAttribute('data-scope');

        var bankInp  = form.querySelector('input[data-name="BANK"], input[name="BANK"]');
        var rateInp  = form.querySelector('input[data-name="RATE"], input[name="RATE"]');
        var scopeInp = form.querySelector('input[data-name="SCOPE"], input[name="SCOPE"]');
        var progInp  = form.querySelector('input[data-name="PROG"], input[name="PROG"]');

        if (bankInp  && bank)  bankInp.value = bank;
        if (rateInp  && rate)  rateInp.value = rate + '%';
        if (scopeInp && scope) scopeInp.value = scope;
        if (progInp) progInp.value = state.prog;
      }

      modal.classList.add('__vp-heroModal1__VISIBLE');
      var body = document.querySelector('.c-body') || document.body;
      body.classList.add('__c-body__FIXED');

      // Прячем в модалке поля "Стоимость участка" и "Первоначальный взнос"
      var hideHints = ['стоимость участка', 'первоначальный взнос'];
      var hideInModal = function () {
        qsa('label, .c-mortgage--label', modal).forEach(function (lb) {
          var txt = (lb.textContent || '').toLowerCase();
          for (var i = 0; i < hideHints.length; i++) {
            if (txt.indexOf(hideHints[i]) !== -1) { lb.style.display = 'none'; return; }
          }
        });
      };
      hideInModal();
      setTimeout(hideInModal, 150);
      setTimeout(hideInModal, 600);

      // Закрытие: крестик + клик по бэкдропу + Escape
      if (!modal._zxcCloseBound) {
        modal._zxcCloseBound = true;
        var closeModal = function () {
          modal.classList.remove('__vp-heroModal1__VISIBLE');
          (document.querySelector('.c-body') || document.body).classList.remove('__c-body__FIXED');
        };
        // Крестик (добавим, если форма без своего close-кнопка)
        var injectClose = function () {
          if (modal.querySelector('.zxc-modal__close')) return;
          var inner = modal.querySelector('form') || modal.firstElementChild || modal;
          if (!inner || inner === modal) return;
          var btn = document.createElement('button');
          btn.type = 'button';
          btn.className = 'zxc-modal__close';
          btn.setAttribute('aria-label', 'Закрыть');
          btn.innerHTML = '&times;';
          btn.addEventListener('click', function (e) { e.stopPropagation(); closeModal(); });
          inner.appendChild(btn);
        };
        injectClose();
        setTimeout(injectClose, 150);
        setTimeout(injectClose, 600);
        // Клик по фону (сам modal, не форма)
        modal.addEventListener('click', function (e) {
          if (e.target === modal) closeModal();
        });
        // Escape
        document.addEventListener('keydown', function (e) {
          if (e.key === 'Escape' && modal.classList.contains('__vp-heroModal1__VISIBLE')) closeModal();
        });
      }
      return true;
    }
    return false;
  }

  /* ── Клик "Оставить заявку" ──────────────────────── */
  function onApplyClick(btn) {
    var detail = {
      scope: btn.getAttribute('data-scope') || 'group',
      bank:  btn.getAttribute('data-bank') || null,
      rate:  parseFloat(btn.getAttribute('data-rate')) || null,
      cost:  el.costSlider ? +el.costSlider.value : null,
      downPct: el.downSlider ? +el.downSlider.value : null,
      term:  el.termSlider ? +el.termSlider.value : null,
      prog:  state.prog,
    };

    // 1) Кастомное событие для внешних интеграций
    var ev = new CustomEvent('zxc:apply', { detail: detail, bubbles: true, cancelable: true });
    btn.dispatchEvent(ev);
    if (ev.defaultPrevented) return;

    // 2) Модалка сайта (предпочтительно)
    if (openSiteModal(btn)) return;

    // 3) Фолбэк: скролл к существующей форме ипотеки
    var target = null;
    for (var i = 0; i < APPLY_SELECTORS.length; i++) {
      target = document.querySelector(APPLY_SELECTORS[i]);
      if (target) break;
    }
    if (target) {
      target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      var firstInput = target.querySelector('input,textarea,select,button');
      if (firstInput) setTimeout(function () { firstInput.focus(); }, 600);
    } else {
      window.location.href = '/uchastki-dlya-biznesa/#zxc-section';
    }
  }

  /* ── CTA "Рассчитать стоимость" ──────────────────── */
  function initCta() {
    if (!el.ctaBtn) return;
    el.ctaBtn.addEventListener('click', function (e) {
      // Эмитим событие для возможных обработчиков
      var ev = new CustomEvent('zxc:cta', {
        detail: {
          cost: +el.costSlider.value,
          downPct: +el.downSlider.value,
          term: +el.termSlider.value,
          prog: state.prog,
        },
        bubbles: true,
        cancelable: true
      });
      el.ctaBtn.dispatchEvent(ev);
      if (ev.defaultPrevented) return;

      // Фолбэк — открываем модалку или скроллим к форме
      onApplyClick(el.ctaBtn);
    });
  }

  /* ── URL-префил ───────────────────────────────────── */
  function applyUrlParams() {
    var p = new URLSearchParams(window.location.search);
    var cost    = p.get('cost');
    var downP   = p.get('down');     // %
    var downRub = p.get('downRub');  // ₽
    var term    = p.get('term');
    var prog    = p.get('prog');
    var scroll  = p.get('scroll');

    if (cost && el.costSlider) {
      el.costSlider.value = clamp(+cost, +el.costSlider.min, +el.costSlider.max);
    }
    if (downRub && el.costSlider && el.downSlider) {
      var c = +el.costSlider.value || 1;
      el.downSlider.value = clamp(Math.round((+downRub / c) * 100), +el.downSlider.min, +el.downSlider.max);
    } else if (downP && el.downSlider) {
      el.downSlider.value = clamp(+downP, +el.downSlider.min, +el.downSlider.max);
    }
    if (term && el.termSlider) el.termSlider.value = +term;

    syncTrack(el.costSlider, el.costFill);
    syncTrack(el.downSlider, el.downFill);
    syncTrack(el.termSlider, el.termFill);
    if (el.costVal && el.costSlider) el.costVal.value = fmt(+el.costSlider.value);
    if (el.termVal && el.termSlider) el.termVal.value = el.termSlider.value;

    if (prog && RATES.hasOwnProperty(prog)) {
      switchProg(prog);
    } else {
      switchProg(state.prog);
    }

    if (scroll === '1' && section) {
      setTimeout(function () {
        section.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }, 300);
    }
  }

  /* ── Глобальный API: установить цену без скролла ──── */
  window.zxcSetCost = function (cost) {
    if (!el || !el.costSlider) return;
    var n = Math.round(+cost);
    if (!n || isNaN(n)) return;
    el.costSlider.value = clamp(n, +el.costSlider.min, +el.costSlider.max);
    syncTrack(el.costSlider, el.costFill);
    if (el.costVal) el.costVal.value = fmt(+el.costSlider.value);
    recalc();
  };

  /* ── Глобальный API для карточек участка ──────────── */
  window.zxcScrollAndFill = function (opts) {
    opts = opts || {};
    if (!el || !section) return;

    if (opts.cost && el.costSlider) {
      el.costSlider.value = clamp(+opts.cost, +el.costSlider.min, +el.costSlider.max);
      syncTrack(el.costSlider, el.costFill);
      if (el.costVal) el.costVal.value = fmt(+el.costSlider.value);
    }
    if (opts.downRub && el.costSlider && el.downSlider) {
      var c = +el.costSlider.value || 1;
      el.downSlider.value = clamp(Math.round((+opts.downRub / c) * 100), +el.downSlider.min, +el.downSlider.max);
      syncTrack(el.downSlider, el.downFill);
    } else if (opts.down && el.downSlider) {
      el.downSlider.value = clamp(+opts.down, +el.downSlider.min, +el.downSlider.max);
      syncTrack(el.downSlider, el.downFill);
    }
    if (opts.term && el.termSlider) {
      el.termSlider.value = clamp(+opts.term, +el.termSlider.min, +el.termSlider.max);
      syncTrack(el.termSlider, el.termFill);
      if (el.termVal) el.termVal.value = el.termSlider.value;
    }

    if (opts.prog && RATES.hasOwnProperty(opts.prog)) {
      switchProg(opts.prog);
    } else {
      recalc();
    }

    section.scrollIntoView({ behavior: 'smooth', block: 'start' });
  };

  /* ── Инициализация ────────────────────────────────── */
  function init() {
    if (!grabDom()) return;

    initSliders();
    initGroups();
    initSort();
    initCta();

    // Dropdown: черная кнопка — раскрывает/скрывает список программ
    var progContainer = qs('.zxc__prog', section);
    if (el.progAll && progContainer) {
      el.progAll.addEventListener('click', function (e) {
        e.stopPropagation();
        // Если сейчас НЕ базовая ипотека — клик возвращает в неё и закрывает выпадашку
        if (state.prog !== 'mortgage') {
          switchProg('mortgage');
          progContainer.classList.remove('zxc__prog--open');
          return;
        }
        // Иначе — просто тогглим список вариантов
        progContainer.classList.toggle('zxc__prog--open');
      });
      // Закрытие по клику вне контейнера
      document.addEventListener('click', function (e) {
        if (!progContainer.contains(e.target)) {
          progContainer.classList.remove('zxc__prog--open');
        }
      });
    }
    el.progTabs.forEach(function (tab) {
      tab.addEventListener('click', function (e) {
        e.stopPropagation();
        switchProg(tab.dataset.prog);
        if (progContainer) progContainer.classList.remove('zxc__prog--open');
      });
    });

    switchProg(state.prog);
    applyUrlParams();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
