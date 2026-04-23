# Компонент "Поможем с ипотекой" (zemex:mortgage.section)

## Как обновить данные через админку Bitrix

### 1. Тексты (заголовок, подзаголовок, текст кнопок)

**Через публичный режим (рекомендуется):**
1. Залогиниться в админку: https://stage.zemexx.ru/bitrix/admin/
2. Перейти на страницу с блоком: https://stage.zemexx.ru/uchastki-dlya-biznesa/
3. Включить режим "Правка" (верхняя панель Bitrix)
4. Навести курсор на блок "Рассчитайте варианты покупки" → шестерёнка "⚙ Параметры компонента"
5. В открывшейся форме заполнить:
   - **Заголовок блока** — текст над калькулятором
   - **Описание формы** — подзаголовок (массив строк)
   - **Текст кнопки калькулятора** — CTA внутри калькулятора
   - **Текст кнопки** — кнопка отправки формы (legacy)
6. "Сохранить" → кеш очистится автоматически

**Через файловую систему:**
- Параметры жёстко заданы в файле:
  `/uchastki-dlya-biznesa/index.php` (строки 1394–1410)

### 2. Банки и ставки

Пока что список банков и ставок захардкожен в файле:
`/local/components/zemex/mortgage.section/component.php`

Чтобы изменить банки:
- Открыть файл через FTP/SSH
- Отредактировать массив `$rawBanks` (добавить/удалить/изменить)
- Очистить кеш Bitrix (`/bitrix/admin/cache.php`)

**TODO:** вынести банки в инфоблок "Банки" для редактирования через админку.

### 3. Процентные ставки по программам

Ставки задаются в `script.js`:
```js
var RATES = { all: 6, family: 6, it: 5, military: 6.75, installment: 0 };
```

### 4. Файлы компонента (все 4 копии должны быть одинаковыми)

1. `/local/components/zemex/mortgage.section/` — основная копия (используется сайтом)
2. `/local/modules/zemex/install/components/zemex/mortgage.section/`
3. `/local/modules/zemex.core/install/components/zemex.core/mortgage.section/`
4. `/zemex_module_package/local/modules/zemex/install/components/zemex/mortgage.section/`

При любом изменении файлов компонента (template.php, style.css, script.js, component.php) необходимо синхронизировать все 4 копии:
```bash
SRC=/home/bitrix/ext_www/zemexx.ru/local/components/zemex/mortgage.section
for f in template.php style.css script.js component.php .parameters.php; do
  for DST in \
    /home/bitrix/ext_www/zemexx.ru/local/modules/zemex/install/components/zemex/mortgage.section \
    /home/bitrix/ext_www/zemexx.ru/local/modules/zemex.core/install/components/zemex.core/mortgage.section \
    /home/bitrix/ext_www/zemexx.ru/zemex_module_package/local/modules/zemex/install/components/zemex/mortgage.section; do
    [ -f $SRC/templates/.default/$f ] && cp $SRC/templates/.default/$f $DST/templates/.default/$f 2>/dev/null
    [ -f $SRC/$f ] && cp $SRC/$f $DST/$f 2>/dev/null
  done
done
```

### 5. Кнопка "Рассчитать ипотеку" в карточках участка

Подключается универсально через data-атрибуты (обработчик глобальный — `/local/templates/zemexx_redisign/footer.php` → `zxc_bridge.js`):

```html
<button data-zxc-calc
        data-zxc-price="5000000"      <!-- стоимость участка, ₽ -->
        data-zxc-down="1000000"       <!-- первый взнос, ₽ (опц.) -->
        data-zxc-down-pct="20"        <!-- или первый взнос, % (опц.) -->
        data-zxc-term="15"            <!-- срок, лет (опц.) -->
        data-zxc-prog="family">       <!-- all/family/it/military/installment (опц.) -->
  Рассчитать ипотеку
</button>
```

**Поведение:**
- Если блок ипотеки есть на текущей странице (`#zxc-section`) — скролл + префил
- Если нет — переход на `/uchastki-dlya-biznesa/?cost=...&downRub=...&term=...&prog=...&scroll=1#zxc-section`

**Кастомизация через события (для разработчиков):**

```js
// Перехват клика "Оставить заявку" в блоке банков
document.addEventListener('zxc:apply', function (e) {
  console.log(e.detail);  // { scope, bank, rate, cost, downPct, term, prog }
  e.preventDefault();      // отменить фолбэк-поведение (скролл к форме)
  // показать свою модалку с данными
});

// Перехват клика главной CTA "Рассчитать стоимость"
document.addEventListener('zxc:cta', function (e) {
  console.log(e.detail);
});
```

### 6. URL-параметры (прямая ссылка на префил)

```
/uchastki-dlya-biznesa/?cost=5000000&downRub=1000000&term=15&prog=family&scroll=1#zxc-section
```

| Параметр  | Тип    | Описание                                              |
|-----------|--------|-------------------------------------------------------|
| cost      | число  | Стоимость участка, ₽                                  |
| downRub   | число  | Первый взнос, ₽ (приоритет над `down`)              |
| down      | число  | Первый взнос, % (10–90)                               |
| term      | число  | Срок кредита, лет                                     |
| prog      | строка | Программа: all / family / it / military / installment |
| scroll    | 1      | Автоскролл к блоку                                    |

### 7. Ставки по программам

В `script.js`, объект `RATES`:
```js
var RATES = { all: 6, family: 6, it: 5, military: 6.75, installment: 0 };
```

### 8. Сортировка, аккордеоны, клавиатура

- Сортировка групп по ставке: кликните кнопку "Сортировать" (переключает asc ↔ desc)
- Аккордеон группы: клик по заголовку / шеврону / Enter или Space (если сфокусирован)
- Все кнопки "Оставить заявку" (и у группы, и у банка) эмитят `zxc:apply` с контекстом

### 9. Кеш

После любых правок компонента:
После любых правок компонента:
1. Админка → Настройки → Производительность → Автокеширование → Очистить файлы кеша
2. Либо SSH: `find /home/bitrix/ext_www/zemexx.ru/bitrix/{cache,managed_cache,html_pages} -type f -delete`
3. Ctrl+F5 в браузере (обход browser cache)
