<?
$arResult['NAV_NUM'] = $arResult['NAV_RESULT']->NavNum; 
$arResult['NAV_PAGE_NOMER'] = $arResult['NAV_RESULT']->NavPageNomer; 
$arResult['NAV_PAGE_COUNT'] = $arResult['NAV_RESULT']->NavPageCount; 
$arResult['SECTION_CODE'] = $arParams["SECTION_CODE"];
$this->__component->SetResultCacheKeys([
    'NAV_NUM',
    'NAV_PAGE_NOMER',
    'NAV_PAGE_COUNT',
    'SECTION_CODE',
]);

foreach($arResult['ITEMS'] as $key => &$arItem) {
	$preview_text = str_replace(' style="text-align: center;"', '', $arItem['PREVIEW_TEXT']);
	$arItem['PREVIEW_TEXT'] = $preview_text;
}
?>