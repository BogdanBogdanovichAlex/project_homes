/* ── Универсальный обработчик кнопок "Рассчитать ипотеку" ──
   Работает на любой странице. Подключается глобально в footer.php.

   Разметка кнопки:
     <button data-zxc-calc
             data-zxc-price="5000000"    ← стоимость участка (₽)
             data-zxc-down="1000000"     ← первый взнос (₽, опц.)
             data-zxc-down-pct="20"      ← или первый взнос (%, опц.)
             data-zxc-term="15"          ← срок (лет, опц.)
             data-zxc-prog="family">     ← программа (all/family/it/military/installment, опц.)
       Рассчитать ипотеку
     </button>

   Поведение:
   - Блок ипотеки есть на этой странице (#zxc-section) → скролл + префил;
   - Нет → переход на /uchastki-dlya-biznesa/?cost=...&downRub=...&term=...&prog=...&scroll=1
*/
(function () {
  'use strict';
  var CALC_PAGE = '/uchastki-dlya-biznesa/';

  function cleanNum(v) {
    if (v == null || v === '') return null;
    var n = String(v).replace(/[^0-9.]/g, '');
    return n ? Math.round(parseFloat(n)) : null;
  }

  function onClick(e) {
    var btn = e.target.closest('[data-zxc-calc]');
    if (!btn) return;
    e.preventDefault();

    var cost    = cleanNum(btn.getAttribute('data-zxc-price'));
    var downRub = cleanNum(btn.getAttribute('data-zxc-down'));
    var downPct = cleanNum(btn.getAttribute('data-zxc-down-pct'));
    var term    = cleanNum(btn.getAttribute('data-zxc-term'));
    var prog    = btn.getAttribute('data-zxc-prog') || '';

    // 1) Блок на этой странице → скролл + префил
    if (document.getElementById('zxc-section') && typeof window.zxcScrollAndFill === 'function') {
      window.zxcScrollAndFill({
        cost: cost, downRub: downRub, down: downPct, term: term, prog: prog
      });
      return;
    }

    // 2) Переход на страницу с блоком
    var qs = [];
    if (cost)    qs.push('cost='    + cost);
    if (downRub) qs.push('downRub=' + downRub);
    if (downPct) qs.push('down='    + downPct);
    if (term)    qs.push('term='    + term);
    if (prog)    qs.push('prog='    + encodeURIComponent(prog));
    qs.push('scroll=1');
    window.location.href = CALC_PAGE + '?' + qs.join('&') + '#zxc-section';
  }

  function boot() { document.addEventListener('click', onClick); }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
