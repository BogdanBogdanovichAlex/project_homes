<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/** @var array $arCurrentValues */

$arComponentParameters = [
    "GROUPS" => [
        "FORM_SETTINGS" => [
            "NAME" => "Настройки формы",
            "SORT" => 100,
        ],
        "BANKS_SETTINGS" => [
            "NAME" => "Настройки банков",
            "SORT" => 200,
        ],
    ],
    "PARAMETERS" => [
        "HERO_TITLE" => [
            "PARENT" => "FORM_SETTINGS",
            "NAME" => "Заголовок блока (над калькулятором)",
            "TYPE" => "STRING",
            "DEFAULT" => "Рассчитайте варианты покупки",
        ],
        "FORM_TITLE" => [
            "PARENT" => "FORM_SETTINGS",
            "NAME" => "Заголовок формы (legacy)",
            "TYPE" => "STRING",
            "DEFAULT" => "Поможем с ипотекой",
        ],
        "CTA_TEXT" => [
            "PARENT" => "FORM_SETTINGS",
            "NAME" => "Текст кнопки калькулятора",
            "TYPE" => "STRING",
            "DEFAULT" => "Рассчитать стоимость",
        ],
        "FORM_DESCRIPTION" => [
            "PARENT" => "FORM_SETTINGS",
            "NAME" => "Описание формы",
            "TYPE" => "STRING",
            "MULTIPLE" => "Y",
            "DEFAULT" => [
                "Сами подадим заявку в банки для получения партнерской скидки,",
                "не передавая ваши контакты ни одному из банков и не надоедая вам звонками"
            ],
        ],
        "BUTTON_TEXT" => [
            "PARENT" => "FORM_SETTINGS",
            "NAME" => "Текст кнопки",
            "TYPE" => "STRING",
            "DEFAULT" => "Связаться с менеджером",
        ],
        "MIN_PAYMENT" => [
            "PARENT" => "FORM_SETTINGS",
            "NAME" => "Минимальный первоначальный взнос",
            "TYPE" => "STRING",
            "DEFAULT" => "60000",
        ],
        "MAX_PAYMENT" => [
            "PARENT" => "FORM_SETTINGS",
            "NAME" => "Максимальный первоначальный взнос",
            "TYPE" => "STRING",
            "DEFAULT" => "100000",
        ],
        "SHOW_BANKS" => [
            "PARENT" => "BANKS_SETTINGS",
            "NAME" => "Показывать список банков",
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "Y",
        ],
        "BANKS_COUNT" => [
            "PARENT" => "BANKS_SETTINGS",
            "NAME" => "Количество банков для отображения",
            "TYPE" => "STRING",
            "DEFAULT" => "4",
        ],
        "CACHE_TYPE" => [
            "PARENT" => "CACHE_SETTINGS",
            "NAME" => "Тип кеширования",
            "TYPE" => "LIST",
            "VALUES" => [
                "A" => "Авто",
                "Y" => "Кешировать",
                "N" => "Не кешировать",
            ],
            "DEFAULT" => "A",
        ],
        "CACHE_TIME" => [
            "PARENT" => "CACHE_SETTINGS",
            "NAME" => "Время кеширования (сек.)",
            "TYPE" => "STRING",
            "DEFAULT" => "3600",
        ],
    ],
];
?>