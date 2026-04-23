<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);

$props = [];
foreach($arResult['PROPERTIES'] as $prop){
    if(!empty($prop['VALUE'])) $props[$prop['CODE']] = $prop['VALUE'];
}

$logo = null;
if(!empty($props['LOGO'])) {
    $logo = CFile::GetFileArray($props['LOGO']);
}
?>
<div class="builder-detail">
    <div class="page_title">
        <div class="container">
            <h1 class="page_title__title"><?=$arResult['NAME']?></h1>
        </div>
    </div>

    <div class="builder-detail__top">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-4 col-lg-3">
                    <?if($logo):?>
                    <div class="builder-detail__logo">
                        <img src="<?=$logo['SRC']?>" alt="<?=htmlspecialchars($arResult['NAME'])?>">
                    </div>
                    <?elseif($arResult['PREVIEW_PICTURE']['SRC']):?>
                    <div class="builder-detail__logo">
                        <img src="<?=$arResult['PREVIEW_PICTURE']['SRC']?>" alt="<?=htmlspecialchars($arResult['NAME'])?>">
                    </div>
                    <?endif?>
                </div>
                <div class="col-md-8 col-lg-9">
                    <div class="builder-detail__info">
                        <div class="builder-detail__stats">
                            <?if(!empty($props['EXPERIENCE'])):?>
                            <div class="builder-detail__stat">
                                <div class="builder-detail__stat-val"><?=$props['EXPERIENCE']?></div>
                                <div class="builder-detail__stat-label">лет на рынке</div>
                            </div>
                            <?endif?>
                            <?if(!empty($props['HOUSES_COUNT'])):?>
                            <div class="builder-detail__stat">
                                <div class="builder-detail__stat-val"><?=$props['HOUSES_COUNT']?></div>
                                <div class="builder-detail__stat-label">домов построено</div>
                            </div>
                            <?endif?>
                            <?if(!empty($props['REGION'])):?>
                            <div class="builder-detail__stat">
                                <div class="builder-detail__stat-val"><?=$props['REGION']?></div>
                                <div class="builder-detail__stat-label">регион работы</div>
                            </div>
                            <?endif?>
                        </div>
                        <div class="builder-detail__contacts">
                            <?if(!empty($props['PHONE'])):?>
                            <a class="builder-detail__contact" href="tel:<?=preg_replace('/[^+\d]/', '', $props['PHONE'])?>">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.62 10.79C8.06 13.62 10.38 15.93 13.21 17.38L15.41 15.18C15.68 14.91 16.08 14.82 16.43 14.94C17.55 15.31 18.76 15.51 20 15.51C20.55 15.51 21 15.96 21 16.51V20C21 20.55 20.55 21 20 21C10.61 21 3 13.39 3 4C3 3.45 3.45 3 4 3H7.5C8.05 3 8.5 3.45 8.5 4C8.5 5.25 8.7 6.45 9.07 7.57C9.18 7.92 9.1 8.31 8.82 8.59L6.62 10.79Z" fill="currentColor"/></svg>
                                <?=$props['PHONE']?>
                            </a>
                            <?endif?>
                            <?if(!empty($props['EMAIL'])):?>
                            <a class="builder-detail__contact" href="mailto:<?=$props['EMAIL']?>">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20 4H4C2.9 4 2.01 4.9 2.01 6L2 18C2 19.1 2.9 20 4 20H20C21.1 20 22 19.1 22 18V6C22 4.9 21.1 4 20 4ZM20 8L12 13L4 8V6L12 11L20 6V8Z" fill="currentColor"/></svg>
                                <?=$props['EMAIL']?>
                            </a>
                            <?endif?>
                            <?if(!empty($props['WEBSITE'])):?>
                            <a class="builder-detail__contact" href="<?=$props['WEBSITE']?>" target="_blank" rel="noopener">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM11 19.93C7.05 19.44 4 16.08 4 12C4 11.38 4.08 10.79 4.21 10.21L9 15V16C9 17.1 9.9 18 11 18V19.93ZM17.9 17.39C17.64 16.58 16.9 16 16 16H15V13C15 12.45 14.55 12 14 12H8V10H10C10.55 10 11 9.55 11 9V7H13C14.1 7 15 6.1 15 5V4.59C17.93 5.77 20 8.65 20 12C20 14.08 19.2 15.97 17.9 17.39Z" fill="currentColor"/></svg>
                                <?=$props['WEBSITE']?>
                            </a>
                            <?endif?>
                        </div>
                        <div class="builder-detail__actions">
                            <a class="projects__btn" data-fancybox="" data-src="#hidden-form" href="javascript:;" data-scope="Связаться с застройщиком <?=$arResult['NAME']?>">Связаться с застройщиком</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?if($arResult['DETAIL_TEXT']):?>
    <div class="builder-detail__text">
        <div class="container">
            <?=$arResult['DETAIL_TEXT']?>
        </div>
    </div>
    <?endif?>

    <?if(!empty($props['GALLERY'])):?>
    <div class="builder-detail__gallery">
        <div class="container">
            <h2 class="builder-detail__section-title">Наши работы</h2>
            <div class="builder-detail__gallery-grid">
                <?foreach((array)$props['GALLERY'] as $fileId):
                    $pic = CFile::GetFileArray($fileId);
                    if(!$pic) continue;
                    $thumb = CFile::ResizeImageGet($pic, ['width' => 400, 'height' => 300], BX_RESIZE_IMAGE_EXACT);
                ?>
                <a href="<?=$pic['SRC']?>" data-fancybox="builder-gallery" class="builder-detail__gallery-item">
                    <img src="<?=$thumb['src']?>" alt="<?=htmlspecialchars($arResult['NAME'])?>">
                </a>
                <?endforeach?>
            </div>
        </div>
    </div>
    <?endif?>

    <div class="builder-detail__footer">
        <div class="container">
            <a class="project__back" href="<?=$arResult['LIST_PAGE_URL']?>">← Все застройщики</a>
        </div>
    </div>
</div>
