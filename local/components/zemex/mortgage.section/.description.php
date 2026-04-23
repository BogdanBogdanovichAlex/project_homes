<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arComponentDescription = [
    "NAME" => "Ипотечный калькулятор и банки",
    "DESCRIPTION" => "Секция с формой ипотеки и списком банков",
    "ICON" => "/bitrix/images/fileman/htmledit2/php.gif",
    "CACHE_PATH" => "Y",
    "SORT" => 100,
    "PATH" => [
        "ID" => "zemex.core",
        "NAME" => "Zemex Core Components",
        "CHILD" => [
            "ID" => "business",
            "NAME" => "Бизнес компоненты",
            "SORT" => 10,
        ]
    ],
];
?>