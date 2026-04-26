<?
if (!defined('ERROR_404')){
	$arResult['URL'] = "/news/";
	if($arResult['NAV_PAGE_NOMER'] > 1){
		$APPLICATION->AddHeadString('<link rel="canonical" href="https://' .SITE_SERVER_NAME.$arResult['URL'] .'">');
	}	
 
	if (isset($arResult['NAV_NUM'], $arResult['NAV_PAGE_NOMER'], $arResult['NAV_PAGE_COUNT'], $arResult['URL'])){
		if ($arResult['NAV_PAGE_COUNT'] > $arResult['NAV_PAGE_NOMER']) { // rel next
			$next = $arResult['NAV_PAGE_NOMER'] + 1;
			$urlNextRel = $arResult['URL']."?PAGEN_1=".$next;       
		} 
		if ($arResult['NAV_PAGE_NOMER'] > 1) { // rel prev
			$prev = $arResult['NAV_PAGE_NOMER'] - 1;
			if($prev > 1){
				$urlPrevRel = $arResult['URL']."?PAGEN_1=".$prev; 
			}
			else{
				$urlPrevRel = $arResult['URL'];
			}
		} 
		if (isset($urlNextRel)) {
			$APPLICATION->AddHeadString('<link rel="next" href="https://' .SITE_SERVER_NAME.$urlNextRel .'">');
		} 
		if (isset($urlPrevRel)) {
			$APPLICATION->AddHeadString('<link rel="prev" href="https://' .SITE_SERVER_NAME.$urlPrevRel .'">');
		} 
	}
}
?>