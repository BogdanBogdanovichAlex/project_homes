<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);

use Bitrix\Main\Page\Asset;
Asset::getInstance()->addCss(SITE_TEMPLATE_PATH."/css/zx-design.css");

$props = [];
foreach ($arResult['PROPERTIES'] as $prop) {
    if (!empty($prop['VALUE'])) $props[$prop['CODE']] = $prop['VALUE'];
}

$logo = !empty($props['LOGO']) ? CFile::GetFileArray($props['LOGO']) : null;
$cover = !empty($arResult['DETAIL_PICTURE']['SRC']) ? $arResult['DETAIL_PICTURE']['SRC'] : (!empty($arResult['PREVIEW_PICTURE']['SRC']) ? $arResult['PREVIEW_PICTURE']['SRC'] : null);
$firstLetter = mb_strtoupper(mb_substr($arResult['NAME'], 0, 1));

$gallery = [];
if (!empty($props['GALLERY'])) {
    foreach ((array)$props['GALLERY'] as $fid) {
        $f = CFile::GetFileArray($fid);
        if ($f && !empty($f['SRC'])) $gallery[] = $f;
    }
}

// Чистим телефон для tel:
$telHref = !empty($props['PHONE']) ? preg_replace('/[^+\d]/', '', $props['PHONE']) : '';

// Подтягиваем 4 свежих проекта из инфоблока 43 (для блока «проекты застройщика»)
// Примечание: связи проект↔застройщик в схеме нет, поэтому показываем последние N
$houseExamples = [];
if (CModule::IncludeModule('iblock')) {
    $rs = CIBlockElement::GetList(
        ["SORT" => "ASC", "ID" => "DESC"],
        ["IBLOCK_ID" => 43, "ACTIVE" => "Y"],
        false,
        ["nTopCount" => 4],
        ["ID","NAME","CODE","DETAIL_PAGE_URL","PREVIEW_PICTURE","PROPERTY_PRICE","PROPERTY_SQUARE","PROPERTY_MATERIAL"]
    );
    while ($el = $rs->GetNext()) {
        $pic = !empty($el['PREVIEW_PICTURE']) ? CFile::GetFileArray($el['PREVIEW_PICTURE']) : null;
        $houseExamples[] = [
            'name'  => $el['NAME'],
            'url'   => $el['DETAIL_PAGE_URL'],
            'src'   => $pic['SRC'] ?? '',
            'price' => $el['PROPERTY_PRICE_VALUE'] ?? '',
            'square'=> $el['PROPERTY_SQUARE_VALUE'] ?? '',
            'material' => $el['PROPERTY_MATERIAL_VALUE'] ?? '',
        ];
    }
}

// Дефолтные шаги аудита (если в настройках раздела не заданы — берутся из homes_settings.builders)
$buildersCfg = function_exists('zemex_get_homes_settings') ? zemex_get_homes_settings('builders') : [];
$auditLines = !empty($buildersCfg['AUDIT_LINES']) && is_array($buildersCfg['AUDIT_LINES']) ? $buildersCfg['AUDIT_LINES'] : [
    'Финансовая устойчивость | Запрашиваем выписки, проверяем долги и судебные разбирательства за 3 года.',
    'Лицензии и допуски | Проверяем наличие СРО, разрешений на строительство, страхование ответственности.',
    'Качество объектов | Выезжаем на 2-3 готовых объекта, общаемся с владельцами, делаем технический осмотр.',
    'Репутация и сделки | Анализируем отзывы, проверяем количество завершённых сделок и сроки.',
];

$h = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };
?>

<div class="zx-scope zx-builder-detail">

  <section class="zx-bd-hero" <?=$cover ? 'style="--zx-bd-bg:url(' . $h($cover) . ')"' : ''?>>
    <div class="zx-bd-hero__bg" aria-hidden="true"></div>
    <div class="c-sel--div__CONTAINER">
      <nav class="zx-crumbs zx-bd-hero__crumbs" aria-label="Хлебные крошки" itemscope itemtype="https://schema.org/BreadcrumbList">
        <span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
          <a itemprop="item" href="/"><span itemprop="name">Главная</span></a><meta itemprop="position" content="1" />
        </span>
        <span class="zx-crumbs__sep"></span><span class="zx-crumbs__dot">●</span>
        <span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
          <a itemprop="item" href="<?=$h($arResult['LIST_PAGE_URL'])?>"><span itemprop="name">Застройщики</span></a><meta itemprop="position" content="2" />
        </span>
        <span class="zx-crumbs__sep"></span><span class="zx-crumbs__dot">●</span>
        <span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
          <span itemprop="name"><?=$h($arResult['NAME'])?></span><meta itemprop="position" content="3" />
        </span>
      </nav>

      <div class="zx-bd-hero__inner">
        <div class="zx-bd-hero__logo">
          <?if($logo):?>
            <img src="<?=$h($logo['SRC'])?>" alt="<?=$h($arResult['NAME'])?>">
          <?else:?>
            <span class="zx-bd-hero__letter"><?=$firstLetter?></span>
          <?endif?>
        </div>
        <div class="zx-bd-hero__head">
          <div class="zx-eyebrow zx-bd-hero__eyebrow">
            <span style="color:var(--text-accent,#00bf3f);">●</span> Партнёр «Земельного экспресса» · аудит пройден
          </div>
          <h1 class="zx-bd-hero__title font__HEADING_PAGE_TITLE"><?=$h($arResult['NAME'])?></h1>
          <?if(!empty($arResult['PREVIEW_TEXT'])):?>
            <p class="zx-bd-hero__lead font__BODY_TEXT_PRIMARY"><?=$h(strip_tags($arResult['PREVIEW_TEXT']))?></p>
          <?endif?>

          <div class="zx-bd-hero__chips">
            <?if(!empty($props['EXPERIENCE'])):?>
              <span class="zx-bd-chip"><b><?=$h($props['EXPERIENCE'])?></b> лет на&nbsp;рынке</span>
            <?endif?>
            <?if(!empty($props['HOUSES_COUNT'])):?>
              <span class="zx-bd-chip"><b><?=$h($props['HOUSES_COUNT'])?>+</b> домов построено</span>
            <?endif?>
            <?if(!empty($props['REGION'])):?>
              <span class="zx-bd-chip">📍&nbsp;<?=$h($props['REGION'])?></span>
            <?endif?>
          </div>

          <div class="zx-bd-hero__ctas">
            <a class="zx-btn zx-btn--primary font__BUTTONS_BUTTON" data-fancybox="" data-src="#hidden-form" href="javascript:;" data-scope="Связаться с <?=$h($arResult['NAME'])?>" data-project="<?=$h($arResult['NAME'])?>">Связаться с&nbsp;застройщиком</a>
            <a class="zx-btn zx-btn--ghost font__BUTTONS_BUTTON" href="/doma-i-kottedzhi/">Все&nbsp;проекты&nbsp;→</a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="zx-bd-block">
    <div class="c-sel--div__CONTAINER">
      <div class="zx-bd-grid">

        <div class="zx-bd-main">

          <?if(!empty($arResult['DETAIL_TEXT'])):?>
          <article class="zx-bd-prose font__BODY_TEXT_PRIMARY">
            <?=$arResult['DETAIL_TEXT']?>
          </article>
          <?endif?>

          <section class="zx-bd-stats">
            <div class="zx-bd-stat">
              <div class="zx-bd-stat__num font__HEADING_SECTION_TITLE"><?=$h($props['EXPERIENCE'] ?? '—')?></div>
              <div class="zx-bd-stat__lab">лет&nbsp;на&nbsp;рынке</div>
            </div>
            <div class="zx-bd-stat">
              <div class="zx-bd-stat__num font__HEADING_SECTION_TITLE"><?=$h(($props['HOUSES_COUNT'] ?? '—') . (empty($props['HOUSES_COUNT']) ? '' : '+'))?></div>
              <div class="zx-bd-stat__lab">домов&nbsp;построено</div>
            </div>
            <div class="zx-bd-stat">
              <div class="zx-bd-stat__num font__HEADING_SECTION_TITLE">5&nbsp;лет</div>
              <div class="zx-bd-stat__lab">гарантия&nbsp;по&nbsp;договору</div>
            </div>
            <div class="zx-bd-stat">
              <div class="zx-bd-stat__num font__HEADING_SECTION_TITLE">90&nbsp;дн</div>
              <div class="zx-bd-stat__lab">средний&nbsp;срок&nbsp;строительства</div>
            </div>
          </section>

          <section class="zx-bd-audit">
            <div class="zx-eyebrow"><span style="color:var(--text-accent,#00bf3f);">●</span> Что мы проверили</div>
            <h2 class="zx-bd-block__title font__HEADING_SECTION_TITLE">Аудит этого&nbsp;застройщика</h2>
            <p class="zx-bd-block__lead font__BODY_TEXT_PRIMARY">До появления карточки на&nbsp;сайте компания прошла стандартную четырёхступенчатую проверку.</p>
            <ol class="zx-bd-audit__list">
              <?foreach($auditLines as $idx => $line):
                $parts = function_exists('zemex_split_pipe') ? zemex_split_pipe($line, 2) : array_map('trim', explode('|', $line, 2));
                $stepTitle = $parts[0] ?? '';
                $stepDesc  = $parts[1] ?? '';
                $num = str_pad((string)($idx + 1), 2, '0', STR_PAD_LEFT);
              ?>
              <li class="zx-bd-audit__step">
                <span class="zx-bd-audit__num"><?=$num?></span>
                <div>
                  <h3 class="zx-bd-audit__step-title font__HEADING_CARD_TITLE"><?=$h($stepTitle)?></h3>
                  <?if($stepDesc):?>
                    <p class="font__BODY_TEXT_PRIMARY"><?=$h($stepDesc)?></p>
                  <?endif?>
                </div>
              </li>
              <?endforeach?>
            </ol>
          </section>

          <?if(!empty($gallery)):?>
          <section class="zx-bd-gallery">
            <div class="zx-eyebrow"><span style="color:var(--text-accent,#00bf3f);">●</span> Объекты застройщика</div>
            <h2 class="zx-bd-block__title font__HEADING_SECTION_TITLE">Галерея работ</h2>
            <div class="zx-bd-gallery__grid">
              <?foreach($gallery as $i => $f):
                $thumb = CFile::ResizeImageGet($f, ['width' => 720, 'height' => 480], BX_RESIZE_IMAGE_PROPORTIONAL);
              ?>
                <a class="zx-bd-gallery__item" href="<?=$h($f['SRC'])?>" data-fancybox="bd-gallery" data-caption="<?=$h($arResult['NAME'])?> · фото <?=$i+1?>">
                  <img src="<?=$h($thumb['src'] ?? $f['SRC'])?>" loading="lazy" alt="<?=$h($arResult['NAME'])?>">
                  <span class="zx-bd-gallery__zoom" aria-hidden="true">⤢</span>
                </a>
              <?endforeach?>
            </div>
          </section>
          <?endif?>

          <?if(!empty($houseExamples)):?>
          <section class="zx-bd-projects">
            <div class="zx-bd-projects__head">
              <div>
                <div class="zx-eyebrow"><span style="color:var(--text-accent,#00bf3f);">●</span> Проекты в&nbsp;каталоге</div>
                <h2 class="zx-bd-block__title font__HEADING_SECTION_TITLE">Что можем построить</h2>
              </div>
              <a class="zx-btn zx-btn--ghost zx-btn--sm font__BUTTONS_BUTTON" href="/doma-i-kottedzhi/">Все проекты&nbsp;→</a>
            </div>
            <div class="zx-bd-projects__grid">
              <?foreach($houseExamples as $hp):?>
                <a class="zx-bd-project" href="<?=$h($hp['url'])?>">
                  <?if($hp['src']):?><div class="zx-bd-project__img" style="background-image:url('<?=$h($hp['src'])?>')"></div>
                  <?else:?><div class="zx-bd-project__img zx-bd-project__img--ph">🏠</div><?endif?>
                  <div class="zx-bd-project__body">
                    <h3 class="zx-bd-project__name font__BODY_TEXT_PRIMARY"><?=$h($hp['name'])?></h3>
                    <div class="zx-bd-project__meta">
                      <?if($hp['square']):?><span><?=$h($hp['square'])?>&nbsp;м²</span><?endif?>
                      <?if($hp['material']):?><span>·</span><span><?=$h($hp['material'])?></span><?endif?>
                    </div>
                    <?if($hp['price']):?>
                      <div class="zx-bd-project__price"><?=$h(number_format((int)preg_replace('/[^0-9]/','',$hp['price']),0,',',"\xc2\xa0"))?>&nbsp;₽</div>
                    <?endif?>
                  </div>
                </a>
              <?endforeach?>
            </div>
          </section>
          <?endif?>
        </div>

        <aside class="zx-bd-side">
          <div class="zx-bd-card">
            <div class="zx-bd-card__head">Связаться напрямую</div>
            <ul class="zx-bd-card__contacts">
              <?if(!empty($props['PHONE'])):?>
                <li><a href="tel:<?=$h($telHref)?>"><span class="zx-bd-card__ico">📞</span><span><?=$h($props['PHONE'])?></span></a></li>
              <?endif?>
              <?if(!empty($props['EMAIL'])):?>
                <li><a href="mailto:<?=$h($props['EMAIL'])?>"><span class="zx-bd-card__ico">✉️</span><span><?=$h($props['EMAIL'])?></span></a></li>
              <?endif?>
              <?if(!empty($props['WEBSITE'])):?>
                <li><a href="<?=$h($props['WEBSITE'])?>" target="_blank" rel="noopener"><span class="zx-bd-card__ico">🌐</span><span><?=$h(preg_replace('#^https?://#i','',$props['WEBSITE']))?></span></a></li>
              <?endif?>
            </ul>
            <a class="zx-btn zx-btn--primary zx-bd-card__cta font__BUTTONS_BUTTON" data-fancybox="" data-src="#hidden-form" href="javascript:;" data-scope="Связаться с <?=$h($arResult['NAME'])?>" data-project="<?=$h($arResult['NAME'])?>">Оставить заявку</a>
            <p class="zx-bd-card__hint font__BODY_TEXT_CAPTION">Передаём заявку напрямую застройщику. Без накруток и&nbsp;посредников.</p>
          </div>

          <div class="zx-bd-trust">
            <div class="zx-bd-trust__row"><span>✓</span><span><b>Договор</b> с&nbsp;фиксированной ценой</span></div>
            <div class="zx-bd-trust__row"><span>✓</span><span><b>Гарантия 5&nbsp;лет</b> на&nbsp;конструктив</span></div>
            <div class="zx-bd-trust__row"><span>✓</span><span><b>Поэтапная оплата</b> по&nbsp;факту работ</span></div>
            <div class="zx-bd-trust__row"><span>✓</span><span><b>Бесплатный выезд</b> на&nbsp;участок</span></div>
          </div>
        </aside>

      </div>
    </div>
  </section>

  <section class="zx-cta-dark">
    <div class="c-sel--div__CONTAINER">
      <div class="zx-cta-dark__grid">
        <div>
          <div class="zx-eyebrow zx-cta-dark__eyebrow">Готовы начать</div>
          <h2 class="zx-cta-dark__title font__HEADING_SECTION_TITLE">Постройте дом с&nbsp;<?=$h($arResult['NAME'])?></h2>
          <p class="zx-cta-dark__lead font__BODY_TEXT_PRIMARY">Оставьте заявку — менеджер «Земельного экспресса» свяжется в&nbsp;течение 15&nbsp;минут, согласует встречу и&nbsp;подготовит расчёт под ваш участок.</p>
        </div>
        <div class="zx-cta-dark__actions">
          <a class="zx-btn zx-btn--primary font__BUTTONS_BUTTON" data-fancybox="" data-src="#hidden-form" href="javascript:;" data-scope="Заявка на расчёт от <?=$h($arResult['NAME'])?>" data-project="<?=$h($arResult['NAME'])?>">Получить расчёт</a>
          <a class="zx-btn zx-btn--ghost-dark font__BUTTONS_BUTTON" href="<?=$h($arResult['LIST_PAGE_URL'])?>">← Все застройщики</a>
        </div>
      </div>
    </div>
  </section>

</div>

<style>
.zx-builder-detail{--zx-bd-bg:url('<?=$h(SITE_TEMPLATE_PATH.'/images/placeholder.jpg')?>');}
.zx-bd-hero{position:relative;padding:36px 0 56px;color:#fff;overflow:hidden;}
.zx-bd-hero__bg{position:absolute;inset:0;background:var(--zx-bd-bg) center/cover no-repeat;filter:brightness(.45) saturate(.85);z-index:0;}
.zx-bd-hero::after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,rgba(10,61,26,.55) 0%,rgba(10,61,26,.85) 100%);z-index:1;pointer-events:none;}
.zx-bd-hero > *{position:relative;z-index:2;}
.zx-bd-hero__crumbs a, .zx-bd-hero__crumbs span{color:rgba(255,255,255,.85);}
.zx-bd-hero__crumbs a:hover{color:#fff;}
.zx-bd-hero__inner{display:grid;grid-template-columns:120px 1fr;gap:24px;align-items:center;margin-top:28px;}
.zx-bd-hero__logo{
    width:120px;height:120px;border-radius:24px;
    background:#fff;display:flex;align-items:center;justify-content:center;
    box-shadow:0 18px 40px rgba(0,0,0,.3);overflow:hidden;flex:0 0 120px;
}
.zx-bd-hero__logo img{max-width:80%;max-height:80%;object-fit:contain;}
.zx-bd-hero__letter{font-size:54px;font-weight:800;color:var(--text-accent,#00bf3f);line-height:1;}
.zx-bd-hero__eyebrow{color:rgba(255,255,255,.85);margin-bottom:14px;}
.zx-bd-hero__title{color:#fff;margin:0 0 12px;font-size:clamp(28px,4vw,52px);}
.zx-bd-hero__lead{color:rgba(255,255,255,.92);max-width:760px;line-height:1.55;margin:0 0 20px;}
.zx-bd-hero__chips{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:22px;}
.zx-bd-chip{
    display:inline-flex;align-items:center;gap:6px;
    background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.25);
    color:#fff;padding:7px 14px;border-radius:999px;
    font-size:13.5px;backdrop-filter:blur(2px);
}
.zx-bd-chip b{font-weight:800;color:#fff;}
.zx-bd-hero__ctas{display:flex;flex-wrap:wrap;gap:10px;}

@media(max-width:760px){
    .zx-bd-hero__inner{grid-template-columns:80px 1fr;gap:16px;}
    .zx-bd-hero__logo{width:80px;height:80px;border-radius:18px;flex-basis:80px;}
    .zx-bd-hero__letter{font-size:36px;}
}

/* ── Грид-блок (main + sticky aside) ── */
.zx-bd-block{padding:56px 0;background:var(--bg-primary,#fff);}
.zx-bd-grid{display:grid;grid-template-columns:1fr;gap:32px;}
@media(min-width:1000px){.zx-bd-grid{grid-template-columns:1fr 360px;gap:48px;align-items:start;}}
.zx-bd-main{display:flex;flex-direction:column;gap:48px;}
.zx-bd-block__title{margin:8px 0 6px;color:var(--text-primary,#0a0a0a);}
.zx-bd-block__lead{color:var(--text-secondary,#5b6473);margin:0 0 22px;line-height:1.6;}

.zx-bd-prose{
    color:var(--text-primary,#0a0a0a);font-size:16px;line-height:1.7;
    background:#f7faf8;border-left:3px solid var(--text-accent,#00bf3f);
    padding:20px 24px;border-radius:0 12px 12px 0;
}
.zx-bd-prose p{margin:0 0 12px;}
.zx-bd-prose p:last-child{margin:0;}

.zx-bd-stats{
    display:grid;grid-template-columns:repeat(2,1fr);gap:14px;
}
@media(min-width:760px){.zx-bd-stats{grid-template-columns:repeat(4,1fr);}}
.zx-bd-stat{
    background:#fff;border:1px solid var(--deviders,#e5e7eb);border-radius:14px;
    padding:22px 20px;text-align:center;
}
.zx-bd-stat__num{
    color:var(--text-accent,#00bf3f);font-size:36px;line-height:1;font-weight:800;
    margin-bottom:8px;letter-spacing:-.02em;
}
.zx-bd-stat__lab{color:var(--text-secondary,#5b6473);font-size:13.5px;line-height:1.4;}

.zx-bd-audit__list{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:18px;}
.zx-bd-audit__step{display:flex;gap:16px;background:#fff;border:1px solid var(--deviders,#e5e7eb);border-radius:14px;padding:18px 20px;}
.zx-bd-audit__num{
    flex:0 0 44px;width:44px;height:44px;border-radius:50%;
    background:linear-gradient(135deg,#00bf3f 0%,#005f26 100%);
    color:#fff;font-weight:800;font-size:14px;
    display:flex;align-items:center;justify-content:center;
    box-shadow:0 8px 18px rgba(0,191,63,.25);
}
.zx-bd-audit__step-title{margin:2px 0 4px;color:var(--text-primary,#0a0a0a);font-size:16px;font-weight:700;}
.zx-bd-audit__step p{margin:0;color:var(--text-secondary,#5b6473);font-size:14px;line-height:1.55;}

.zx-bd-gallery__grid{
    display:grid;gap:12px;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
}
.zx-bd-gallery__item{
    position:relative;display:block;border-radius:14px;overflow:hidden;
    aspect-ratio:4/3;background:#f0f1f3;
}
.zx-bd-gallery__item img{width:100%;height:100%;object-fit:cover;transition:transform .35s ease;}
.zx-bd-gallery__item:hover img{transform:scale(1.04);}
.zx-bd-gallery__zoom{
    position:absolute;top:10px;right:12px;width:32px;height:32px;border-radius:50%;
    background:rgba(0,0,0,.55);color:#fff;display:flex;align-items:center;justify-content:center;
    font-size:14px;opacity:0;transition:opacity .2s;
}
.zx-bd-gallery__item:hover .zx-bd-gallery__zoom{opacity:1;}

.zx-bd-projects__head{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:22px;}
.zx-bd-projects__grid{display:grid;gap:16px;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));}
.zx-bd-project{
    display:flex;flex-direction:column;background:#fff;border:1px solid var(--deviders,#e5e7eb);
    border-radius:14px;overflow:hidden;text-decoration:none;color:inherit;
    transition:transform .2s, box-shadow .2s, border-color .2s;
}
.zx-bd-project:hover{transform:translateY(-2px);border-color:var(--text-accent,#00bf3f);box-shadow:0 14px 30px rgba(0,0,0,.07);}
.zx-bd-project__img{height:160px;background:#eef1f4 center/cover no-repeat;}
.zx-bd-project__img--ph{display:flex;align-items:center;justify-content:center;font-size:38px;}
.zx-bd-project__body{padding:14px 16px 18px;display:flex;flex-direction:column;gap:6px;}
.zx-bd-project__name{margin:0;font-size:15px;line-height:1.35;font-weight:700;color:var(--text-primary,#0a0a0a);}
.zx-bd-project__meta{display:flex;flex-wrap:wrap;gap:6px;color:var(--text-secondary,#5b6473);font-size:12.5px;}
.zx-bd-project__price{margin-top:auto;font-weight:800;color:var(--text-accent,#00bf3f);font-size:16px;letter-spacing:-.01em;padding-top:8px;}

/* ── Sticky aside ── */
.zx-bd-side{display:flex;flex-direction:column;gap:18px;}
@media(min-width:1000px){.zx-bd-side{position:sticky;top:24px;}}
.zx-bd-card{
    background:linear-gradient(135deg,#0a3d1a 0%,#005f26 55%,#00bf3f 100%);
    color:#fff;border-radius:20px;padding:24px;
    box-shadow:0 20px 40px rgba(10,61,26,.25);
}
.zx-bd-card__head{font-weight:700;font-size:14px;text-transform:uppercase;letter-spacing:.05em;color:rgba(255,255,255,.85);margin-bottom:14px;}
.zx-bd-card__contacts{list-style:none;margin:0 0 18px;padding:0;display:flex;flex-direction:column;gap:8px;}
.zx-bd-card__contacts a{
    display:flex;align-items:center;gap:10px;padding:10px 12px;
    background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.18);
    border-radius:10px;color:#fff;text-decoration:none;font-size:14px;
    transition:background .2s;
}
.zx-bd-card__contacts a:hover{background:rgba(255,255,255,.2);}
.zx-bd-card__ico{font-size:18px;flex:0 0 24px;}
.zx-bd-card__cta{
    width:100%;display:flex;align-items:center;justify-content:center;
    background:#fff !important;color:#0a3d1a !important;
    padding:14px 18px;border-radius:12px;font-weight:700;
    transition:transform .15s, box-shadow .2s;
}
.zx-bd-card__cta:hover{transform:translateY(-1px);box-shadow:0 10px 24px rgba(0,0,0,.18);}
.zx-bd-card__hint{margin:14px 0 0;color:rgba(255,255,255,.7);font-size:12.5px;line-height:1.45;text-align:center;}

.zx-bd-trust{
    background:#fff;border:1px solid var(--deviders,#e5e7eb);border-radius:14px;
    padding:18px 20px;display:flex;flex-direction:column;gap:10px;
}
.zx-bd-trust__row{display:flex;align-items:flex-start;gap:10px;font-size:14px;color:var(--text-primary,#0a0a0a);line-height:1.45;}
.zx-bd-trust__row > span:first-child{flex:0 0 22px;width:22px;height:22px;border-radius:50%;background:#e9f8ee;color:var(--text-accent,#00bf3f);font-weight:800;display:flex;align-items:center;justify-content:center;font-size:12px;}
.zx-bd-trust__row b{font-weight:700;}

/* ── Финальный CTA ── */
.zx-cta-dark{background:linear-gradient(135deg,#0a3d1a 0%,#1a3a1f 100%);color:#fff;padding:56px 0;}
.zx-cta-dark__eyebrow{color:rgba(255,255,255,.7);}
.zx-cta-dark__title{color:#fff;margin:8px 0 14px;}
.zx-cta-dark__lead{color:rgba(255,255,255,.85);max-width:560px;line-height:1.55;}
.zx-cta-dark__grid{display:grid;grid-template-columns:1fr;gap:24px;align-items:center;}
@media(min-width:900px){.zx-cta-dark__grid{grid-template-columns:1.4fr 1fr;}}
.zx-cta-dark__actions{display:flex;flex-wrap:wrap;gap:10px;}
.zx-scope .zx-btn--ghost-dark{background:transparent;color:#fff;border:1px solid rgba(255,255,255,.4);}
.zx-scope .zx-btn--ghost-dark:hover{background:rgba(255,255,255,.1);color:#fff;}
</style>

<!-- ============ FEEDBACK MODAL ============ -->
<div class="vp-heroModal1 form_container zx-feedback-modal"
     data-header="Связаться с застройщиком"
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
    function open(scope, project){
      var scopeInp = modal.querySelector('input[data-name="SCOPE"]');
      if(scopeInp) scopeInp.value = scope || '';
      var pInp = modal.querySelector('input[data-name="PROJECT"]');
      if(pInp) pInp.value = project || '';
      var titleEl = modal.querySelector('.vp-heroModal1--h2, .form_header, h2, h3');
      if(titleEl && scope){
        var headerMap = {
          'Связаться с':       'Связаться с застройщиком',
          'Заявка на расчёт':  'Заявка на расчёт',
          'Партнёрская':       'Заявка на партнёрство'
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
      open(btn.getAttribute('data-scope'), btn.getAttribute('data-project'));
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
