<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/** @var CBitrixComponent $this */

// ═══ ID инфоблоков ═══
$MORTGAGE_IBLOCK_ID    = 52; // Поможем с ипотекой (банки)
$PROGRAMS_IBLOCK_ID    = 66; // Ипотечные программы (ставки по типам)
$INSTALLMENTS_IBLOCK_ID = 67; // Программы рассрочки

$arResult["PROGRAMS"] = [];
$arResult["RATES"]    = ["all" => null, "mortgage" => null, "family" => 6, "it" => 5, "military" => 6.75, "installment" => 0];
$arResult["PROG_META"] = []; // [code => ['name' => ..., 'rate' => ...]]

if (CModule::IncludeModule("iblock")) {

    /* ── Программы рассрочки (правая панель) ── */
    $it = CIBlockElement::GetList(
        ["SORT" => "ASC"],
        ["IBLOCK_ID" => $INSTALLMENTS_IBLOCK_ID, "ACTIVE" => "Y"],
        false,
        false,
        ["ID", "NAME", "SORT",
         "PROPERTY_PROC_VAL", "PROPERTY_PROC_SUB",
         "PROPERTY_PAY_VAL", "PROPERTY_PAY_SUB",
         "PROPERTY_TOTAL", "PROPERTY_BADGE", "PROPERTY_LINK",
         "PROPERTY_INST_MONTHS", "PROPERTY_INST_SHARE", "PROPERTY_MORT_RATE"]
    );
    $idx = 0;
    while ($row = $it->Fetch()) {
        $arResult["PROGRAMS"][] = [
            "TITLE"       => $row["NAME"],
            "PROC_VAL"    => (string)$row["PROPERTY_PROC_VAL_VALUE"],
            "PROC_SUB"    => (string)$row["PROPERTY_PROC_SUB_VALUE"],
            "PAY_VAL"     => (string)$row["PROPERTY_PAY_VAL_VALUE"],
            "PAY_SUB"     => (string)$row["PROPERTY_PAY_SUB_VALUE"],
            "TOTAL"       => (string)$row["PROPERTY_TOTAL_VALUE"],
            "BADGE"       => (string)$row["PROPERTY_BADGE_VALUE"],
            "LINK"        => (string)($row["PROPERTY_LINK_VALUE"] ?: "#"),
            "INST_MONTHS" => (float)str_replace(",", ".", (string)$row["PROPERTY_INST_MONTHS_VALUE"]),
            "INST_SHARE"  => (float)str_replace(",", ".", (string)$row["PROPERTY_INST_SHARE_VALUE"]),
            "MORT_RATE"   => (float)str_replace(",", ".", (string)$row["PROPERTY_MORT_RATE_VALUE"]),
            "ACTIVE"      => $idx === 1,
        ];
        $idx++;
    }

    /* ── Ставки по типам программ ── */
    $it = CIBlockElement::GetList(
        ["SORT" => "ASC"],
        ["IBLOCK_ID" => $PROGRAMS_IBLOCK_ID, "ACTIVE" => "Y"],
        false, false,
        ["ID", "NAME", "SORT", "PROPERTY_CODE_KEY", "PROPERTY_RATE"]
    );
    while ($row = $it->Fetch()) {
        $code = (string)$row["PROPERTY_CODE_KEY_VALUE"];
        if ($code === "") continue;
        $rateRaw = (string)$row["PROPERTY_RATE_VALUE"];
        $rate = $rateRaw === "" ? null : (float)str_replace(",", ".", $rateRaw);
        $arResult["RATES"][$code] = $rate;
        $arResult["PROG_META"][$code] = ["name" => $row["NAME"], "rate" => $rate];
    }
}

/* ── Банки из инфоблока "Поможем с ипотекой" (ID=52) ── */
$rawBanks = [];

if (CModule::IncludeModule("iblock")) {
    $colorMap = [
        "сбер"     => ["#21a038", "#fff"],
        "втб"      => ["#0077be", "#fff"],
        "газпром"  => ["#005baa", "#fff"],
        "альфа"    => ["#ef3124", "#fff"],
        "тинькофф" => ["#ffdd2d", "#1a1a1a"],
        "т-банк"   => ["#ffdd2d", "#1a1a1a"],
        "кубань"   => ["#00843d", "#fff"],
        "металл"   => ["#d52b1e", "#fff"],
    ];
    $iter = CIBlockElement::GetList(
        ["SORT" => "ASC", "NAME" => "ASC"],
        ["IBLOCK_ID" => $MORTGAGE_IBLOCK_ID, "ACTIVE" => "Y"],
        false,
        false,
        ["ID", "NAME", "SORT", "PROPERTY_PERSANT", "PROPERTY_PERIOD",
         "PROPERTY_CREDIT_SUM", "PROPERTY_MONTH_PAY", "PROPERTY_LOGO", "PROPERTY_BANK_LINK"]
    );
    while ($row = $iter->Fetch()) {
        $rateNum  = (float)str_replace([",", " "], [".", ""], (string)$row["PROPERTY_PERSANT_VALUE"]);
        $rateText = $rateNum > 0 ? ("от " . rtrim(rtrim(number_format($rateNum, 2, ".", ""), "0"), ".") . "%") : "—";
        $periodRaw = trim((string)$row["PROPERTY_PERIOD_VALUE"]);
        $period    = $periodRaw !== "" ? ("до " . preg_replace("/\D/", "", $periodRaw) . " лет") : "5–30 лет";
        $logoId   = (int)($row["PROPERTY_LOGO_VALUE"] ?? 0);
        $logoSrc  = $logoId ? CFile::GetPath($logoId) : "";

        $nameLower = mb_strtolower($row["NAME"]);
        $color = "#e7eaf0"; $textColor = "#1a1a1a";
        foreach ($colorMap as $key => $c) {
            if (mb_strpos($nameLower, $key) !== false) {
                $color = $c[0]; $textColor = $c[1]; break;
            }
        }

        $rawBanks[] = [
            "NAME"       => $row["NAME"],
            "RATE"       => $rateText,
            "RATE_NUM"   => $rateNum,
            "PERIOD"     => $period,
            "DOWN"       => "20,1%",
            "COLOR"      => $color,
            "TEXT_COLOR" => $textColor,
            "LOGO"       => $logoSrc,
            "LINK"       => (string)($row["PROPERTY_BANK_LINK_VALUE"] ?? ""),
        ];
    }
}

/* ── Фолбэк, если инфоблок пуст ── */
if (empty($rawBanks)) {
    $rawBanks = [
        ["NAME"=>"ВТБ","RATE"=>"от 5%","RATE_NUM"=>5,"PERIOD"=>"5–30 лет","DOWN"=>"20,1%","COLOR"=>"#0077be","TEXT_COLOR"=>"#fff","LOGO"=>"","LINK"=>""],
        ["NAME"=>"Газпромбанк","RATE"=>"от 6%","RATE_NUM"=>6,"PERIOD"=>"5–30 лет","DOWN"=>"20,1%","COLOR"=>"#005baa","TEXT_COLOR"=>"#fff","LOGO"=>"","LINK"=>""],
        ["NAME"=>"Сбер","RATE"=>"от 16%","RATE_NUM"=>16,"PERIOD"=>"5–30 лет","DOWN"=>"20,1%","COLOR"=>"#21a038","TEXT_COLOR"=>"#fff","LOGO"=>"","LINK"=>""],
        ["NAME"=>"АльфаБанк","RATE"=>"от 18%","RATE_NUM"=>18,"PERIOD"=>"5–30 лет","DOWN"=>"20,1%","COLOR"=>"#ef3124","TEXT_COLOR"=>"#fff","LOGO"=>"","LINK"=>""],
        ["NAME"=>"Т-Банк","RATE"=>"от 18%","RATE_NUM"=>18,"PERIOD"=>"5–30 лет","DOWN"=>"20,1%","COLOR"=>"#ffdd2d","TEXT_COLOR"=>"#1a1a1a","LOGO"=>"","LINK"=>""],
    ];
}

// Если рассрочек не задали — пусть будет заглушка
if (empty($arResult["PROGRAMS"])) {
    $arResult["PROGRAMS"] = [[
        "TITLE"    => "Программа рассрочки",
        "PROC_VAL" => "—", "PROC_SUB" => "",
        "PAY_VAL"  => "—", "PAY_SUB" => "",
        "TOTAL"    => "—", "BADGE" => "",
        "LINK"     => "#", "ACTIVE" => true,
    ]];
}

/* ── Группировка банков по ставке ── */
$groups = [];
foreach ($rawBanks as $bank) {
    $key = (string)$bank["RATE_NUM"];
    if (!isset($groups[$key])) {
        $groups[$key] = [
            "RATE"     => $bank["RATE"],
            "RATE_NUM" => $bank["RATE_NUM"],
            "PERIOD"   => $bank["PERIOD"],
            "BANKS"    => [],
            "OPEN"     => false,
        ];
    }
    $groups[$key]["BANKS"][] = $bank;
}
usort($groups, function($a, $b) { return $a["RATE_NUM"] <=> $b["RATE_NUM"]; });
$groups = array_values($groups);
foreach ($groups as &$__g) { $__g["OPEN"] = true; } unset($__g);

$arResult["BANK_GROUPS"]  = $groups;
$arResult["BANKS_TOTAL"]  = count($rawBanks);
$arResult["GROUPS_TOTAL"] = count($groups);

$this->IncludeComponentTemplate();
