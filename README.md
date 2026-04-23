# project_homes — Zemexx. Дома, коттеджи, застройщики

Код разделов «Дома и коттеджи» и «Застройщики» сайта [zemexx.ru](https://zemexx.ru) (CMS 1С-Битрикс). Живёт внутри шаблона `zemexx_redisign`. Стейдж: [stage.zemexx.ru](https://stage.zemexx.ru).

## Структура

```
doma-i-kottedzhi/index.php     Страница каталога проектов домов
zastrojshhiki/index.php        Страница списка застройщиков

local/components/zemex/
  mortgage.section/            Блок «Поможем с ипотекой на дом»

local/templates/zemexx_redisign/
  components/bitrix/
    news/projects/             Шаблон компонента каталога домов (list + detail)
    news/builders/             Шаблон компонента застройщиков
    news.list/builders/        Отдельный list-шаблон застройщиков
  css/
    zx-design.css              Дизайн-токены и карточки проектов
    builders_doma.css          Общие стили разделов
```

## Ветки

- `main` — текущее состояние на стейдже
- `feature/projects-catalog` — каталог домов: карточки, фильтр, trustbar
- `feature/project-detail` — детальная страница проекта
- `feature/builders` — раздел застройщиков
- `feature/mortgage-section` — калькулятор ипотеки

## Деплой

Пути соответствуют реальной файловой системе на сервере. Выкладка — `rsync`/`scp` в `/home/bitrix/ext_www/zemexx.ru/`.
