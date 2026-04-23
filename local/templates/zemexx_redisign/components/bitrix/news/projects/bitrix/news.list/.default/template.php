<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
?>
<?if($arResult['ITEMS']):?>
	<?foreach($arResult["ITEMS"] as $arItem):?>
		<?
		$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
		$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));

		foreach($arItem['PROPERTIES'] as $prop){
			if($prop['CODE'] == 'MATERIAL') {
				$properties[$prop['CODE']] = array(
					'XML_ID' => $prop['VALUE_XML_ID'],
					'VALUE' => $prop['VALUE'],
				);
			} else {
				if(!empty($prop['VALUE'])){
			        $properties[$prop['CODE']] = $prop['VALUE'];
			    }
			}
		}
		?>
		<div class="projects__project" id="<?=$this->GetEditAreaId($arItem['ID']);?>" data-material="<?=$properties['MATERIAL']['XML_ID']?>">
			<h3 class="projects__title"><a href="<?=$arItem['DETAIL_PAGE_URL']?>"><?=$arItem['NAME']?></a></h3>
			<div class="row">
				<?if($arItem['PREVIEW_TEXT']):?>
					<div class="col-lg-6">
						<div class="projects__text">
							<?=$arItem['PREVIEW_TEXT']?>
						</div>
					</div>
				<?endif?>
				<div class="col-lg-6 projects__right">
					<div class="projects__values">
						<?if($properties['SQUARE']):?>
							<div class="projects__value">
								<span class="projects__value_text projects__value_text-square">Площадь:</span>
								<span class="projects__value_val"><?=$properties['SQUARE']?></span>
							</div>
						<?endif?>
						<div class="projects__value">
							<span class="projects__value_text projects__value_text-price">Цена:</span>
							<span class="projects__value_val">от <?=($properties['PRICE'] ? $properties['PRICE'].' <span class="rub">₽</span>' : ' по запросу')?></span>
						</div>
					</div>
					<a class="projects__call" data-fancybox="" data-src="#hidden-form" href="javascript:;" data-scope="&laquo;Связаться&raquo; в списке домов">Связаться</a>
				</div>
			</div>
			<div class="projects__photos">
				<div class="row">
					<?if($arItem['PREVIEW_PICTURE']):?>
						<div class="col-sm-6">
							<a href="<?=$arItem['DETAIL_PAGE_URL']?>" class="projects__photo">
								<img src="<?=$arItem['PREVIEW_PICTURE']['SRC']?>" alt="">
							</a>
						</div>
					<?endif?>
					<?if($arItem['DETAIL_PICTURE']):?>
						<div class="col-sm-6">
							<a href="<?=$arItem['DETAIL_PAGE_URL']?>" class="projects__photo">
								<img src="<?=$arItem['DETAIL_PICTURE']['SRC']?>" alt="">
							</a>
						</div>
					<?endif?>
				</div>
			</div>
			<div class="projects__btns">
				<a class="projects__btn" href="<?=$arItem['DETAIL_PAGE_URL']?>">Подробнее о проекте</a>
				<a class="projects__btn projects__btn-2" data-fancybox="" data-src="#hidden-form" href="javascript:;" data-scope="Консультация менеджера в списке домов">Хочу консультацию менеджера</a>
			</div>
		</div>
	<?endforeach?>
	<div class="pagination">
		<?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
			<?=$arResult["NAV_STRING"]?>
		<?endif;?>
	</div>
<?endif?>