<?

class FormGenerator
{
    public static function getFieldTitle($arQuestion)
    {
        ?>
        <div class="col-sm-3 modal-text text-right">
            <?= $arQuestion["CAPTION"] ?>
            <span class="form-required starrequired"><?= $arQuestion["REQUIRED"] == "Y" ? "*" : "" ?></span>
        </div>
        <?php
    }

    public static function getTextFieldHtml($fieldCODE, $arQuestion, $value = "")
    {
        $fieldStructure = $arQuestion['STRUCTURE'][0];
        $fieldType = $fieldStructure["FIELD_TYPE"];
        $classes = "";
        $formName = "form_" . $fieldType . "_" . $fieldStructure["ID"];
        $formNameData = $fieldCODE;

        // Если в FIELD_PARAM не указан class — подставляем дефолтный, чтобы поле выглядело
        // одинаково с NAME/PHONE. Поля, где class задан явно в админке, не перезаписываются.
        $fieldParam = (string)($fieldStructure["FIELD_PARAM"] ?? '');
        if ($fieldType !== 'hidden' && stripos($fieldParam, 'class=') === false) {
            $fieldParam = 'class="c-mortgage--input font__BODY_TEXT_PRIMARY mv" ' . $fieldParam;
        }
        //if ($fieldCODE == "PHONE" || mb_strtolower($arQuestion["CAPTION"]) == "телефон") $classes .= "form__phone-number";
        ?>

        <?if($fieldType == "hidden"):?>
            <input <?=$fieldStructure["FIELD_PARAM"]?> value="<?= !empty($value) ? $value : $fieldStructure["VALUE"] ?>" type="<?= $fieldType ?>" name="<?= $formName ?>" data-name="<?=$formNameData?>">
        <?else:?>
            <label class="c-mortgage--label font__BODY_TEXT_CAPTION">
                <?=$arQuestion['CAPTION'];?>
                <input <?=$fieldParam?> value="<?= !empty($value) ? $value : $fieldStructure["VALUE"] ?>" type="<?= $fieldType ?>" name="<?= $formName ?>" data-name="<?=$formNameData?>">
                <span class="text c--span__ERROR font__BODY_TEXT_CAPTION"><?=$fieldStructure['ERROR_MESS']?></span>
            </label>
        <?endif?>
        <?php
    }

    public static function getTextFieldWriteUs($fieldCODE, $arQuestion, $value = ""){
        $fieldStructure = $arQuestion['STRUCTURE'][0];
        $fieldType = $fieldStructure["FIELD_TYPE"];
        $classes = "";
        $formName = "form_" . $fieldType . "_" . $fieldStructure["ID"];
        $formNameData = $fieldCODE;

        //if ($fieldCODE == "PHONE" || mb_strtolower($arQuestion["CAPTION"]) == "телефон") $classes .= "form__phone-number";
        ?>
        
        <?if($fieldType == "hidden"):?>
            <input <?=$fieldStructure["FIELD_PARAM"]?> value="<?= !empty($value) ? $value : $fieldStructure["VALUE"] ?>" type="<?= $fieldType ?>" name="<?= $formName ?>">
        <?else:?>
            <label class="hm-quizModal1--label cs font__BODY_TEXT_CAPTION">
                <?=$arQuestion['CAPTION'];?>
                <input <?=$fieldStructure["FIELD_PARAM"]?> value="<?= !empty($value) ? $value : $fieldStructure["VALUE"] ?>" type="<?= $fieldType ?>" name="<?= $formName ?>" data-name="<?=$formNameData?>">
                <span class="text c--span__ERROR font__BODY_TEXT_CAPTION"><?=$fieldStructure['ERROR_MESS']?></span>
            </label>
        <?endif?>
        <?php        
    }
    public static function getTextField($fieldCODE, $arQuestion, $value = "")
    {
        $fieldStructure = $arQuestion['STRUCTURE'][0];
        ?>
            <?php // if ($fieldStructure["FIELD_TYPE"] != "hidden") self::getFieldTitle($arQuestion) ?>
            <?php self::getTextFieldHtml($fieldCODE, $arQuestion, $value); ?>
        <?php
    }

    public static function getTextareaField($fieldCODE, $arQuestion, $value = "")
    {
        $fieldStructure = $arQuestion['STRUCTURE'][0];
        $fieldType = $fieldStructure["FIELD_TYPE"];
        $classes = "";
        $formName = "form_" . $fieldType . "_" . $fieldStructure["ID"];
        $formNameData = $fieldCODE;

        $fieldParam = (string)($fieldStructure["FIELD_PARAM"] ?? '');
        if (stripos($fieldParam, 'class=') === false) {
            $fieldParam = 'class="c-mortgage--input font__BODY_TEXT_PRIMARY mv" rows="4" ' . $fieldParam;
        }
        ?>
        <label class="c-mortgage--label font__BODY_TEXT_CAPTION">
            <?=$arQuestion['CAPTION'];?>
            <textarea <?=$fieldParam?> name="<?= $formName ?>" data-name="<?=$formNameData?>"><?= !empty($value) ? $value : $fieldStructure["VALUE"] ?></textarea>
            <span class="text c--span__ERROR font__BODY_TEXT_CAPTION"><?=$fieldStructure['ERROR_MESS']?></span>
        </label>

        <?php
    }

    public static function getRadioField($fieldCODE, $arQuestion)
    {
        ?>
        <div class="form-radio">
            <?php foreach ($arQuestion["STRUCTURE"] as $item): ?>
                <div>
                    <input type="radio"
                           id="<?= $item["ID"] ?>"
                           name="form_radio_<?= $fieldCODE ?>"
                           value="<?= $item["ID"] ?>"
                        <?= $item["FIELD_PARAM"] ?>
                    >
                    <label for="<?= $item["ID"] ?>"> <?= $item["MESSAGE"] ?> </label>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }

    public static function getCheckboxField($fieldCODE, $arQuestion, $formID, $note)
    {
        ?>
        <div class="hm-quiz--div__SWIPER_SLIDE swiper-slide">
            <fieldset class="hm-quiz--fieldset" form="<?=$formID?>">
                <legend class="hm-quiz--legend font__HEADING_CARD_TITLE">
                    <?=$arQuestion['CAPTION'];?>
                </legend>
                <?php foreach ($arQuestion["STRUCTURE"] as $item): ?>
                    <label class="hm-quiz--label font__BODY_TEXT_PRIMARY" for="<?= $item["ID"] ?>">
                        <?= $item["MESSAGE"] ?>
                        <input 
                            id="<?= $item["ID"] ?>"
                            class="hm-quiz--input__CHECKBOX" 
                            type="checkbox" 
                            name="form_checkbox_<?= $fieldCODE ?>[]" 
                            value="<?= $item["ID"] ?>"
                        >
                        <div class="hm-quiz--div__CHECK"></div>
                        <img class="hm-quiz--img__CHECK" src="<?=SITE_TEMPLATE_PATH?>/images/hm-quiz-check.svg" alt="выбрано">
                    </label>                

                <?php endforeach; ?>
            </fieldset>
            <?if($note == 'first'):?>
                <div class="hm-quiz--div__BUTTONS">
                    <button class="hm-quiz--button__NEXT font__BUTTONS_BUTTON" type="button">Следующий вопрос</button>
                </div>
            <?elseif($note == 'last'):?>
                <div class="hm-quiz--div__BUTTONS">
                    <button class="hm-quiz--button__PREV font__BUTTONS_BUTTON" type="button">Предыдущий вопрос</button>
                    <button class="hm-quiz--button__TRANSITION font__BUTTONS_BUTTON" type="button">Закончить опрос</button>
                </div>
            <?else:?>
                <div class="hm-quiz--div__BUTTONS">
                    <button class="hm-quiz--button__PREV font__BUTTONS_BUTTON" type="button">Предыдущий вопрос</button>
                    <button class="hm-quiz--button__NEXT font__BUTTONS_BUTTON" type="button">Следующий вопрос</button>
                </div>
            <?endif?>
        </div>
        <?php
    }

    public static function getDropDownFieldHtml($fieldCODE, $arQuestion)
    {
        $fieldStructure = $arQuestion['STRUCTURE'][0];
        ?>
        <select class="order-b__input"
                name="form_dropdown_<?= $fieldCODE ?>"
            <?= $fieldStructure["FIELD_PARAM"] ?>>
            <?php foreach ($arQuestion["STRUCTURE"] as $item): ?>
                <option value="<?= $item["ID"] ?>">
                    <svg class="" width="30px" height="30px">
                        <use xlink:href="#form_ask"></use>
                    </svg>
                
                    <?= $item["MESSAGE"] ?>
                </option>
            <?php endforeach; ?>
        </select>
        <img src="<?= SITE_TEMPLATE_PATH ?>/static/img/content/form_ask.png" loading="lazy" class="complex-b__icon" alt="">
        <?php
    }

    public static function getFileField($fieldCODE, $arQuestion)
    {
        $fieldStructure = $arQuestion['STRUCTURE'][0];
        ?>
            <div class="order-b__upload">
                <label for="file_<?=$fieldStructure['FIELD_ID']?>"><?=$arQuestion['CAPTION'];?></label>
                <input name="form_file_<?=$fieldStructure['ID']?>" type="file" id="file_<?=$fieldStructure['FIELD_ID']?>" size="0" value="<?= !empty($value) ? $value : $fieldStructure["VALUE"] ?>">
            </div>
        <? 
    }

    public static function getDropDownField($fieldCODE, $arQuestion)
    {
        ?>
            <?php self::getFieldTitle($arQuestion) ?>
            <div class="select-wrap">
                <?php self::getDropDownFieldHtml($fieldCODE, $arQuestion); ?>
            </div>
        <?php
    }

    public static function getDateTimeFieldHtml($fieldCODE, $timeField, $dateField)
    {
        $dateField['STRUCTURE'][0]["FIELD_PARAM"] .= " autocomplete='off'";
        ?>
        <div class="row">
            <?php self::getFieldTitle($timeField) ?>
            <div class="col-sm-2">
                <?php self::getDropDownFieldHtml($fieldCODE, $timeField); ?>
            </div>
            <div class="col-sm-5">
                <?php self::getTextFieldHtml($fieldCODE, $dateField) ?>
            </div>
        </div>
        <?php
    }
}