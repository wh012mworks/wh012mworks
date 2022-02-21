<?php

// echo '<pre>';
// print_r($arFilter);
// echo '</pre>';

function json_encode_advanced(array $arr, $sequential_keys = true, $quotes = true, $beautiful_json = true) {
    
    $output = "{";
    $count = 0;
    foreach ($arr as $key => $value) {
        
        $output .= ($quotes ? '"' : '') . $key . ($quotes ? '"' : '') . ' : ';
        
        if (is_array($value)) {
            $output .= json_encode_advanced($value, $sequential_keys, $quotes, $beautiful_json);
        } else if (is_bool($value)) {
            $output .= ($value ? 'true' : 'false');
        } else if (is_numeric($value)) {
            $output .= $value;
        } else {
            $value = str_replace('"','\"', $value);
            $value = str_replace("'",'\"', $value);
            $value = str_replace("`",'\"', $value);
            $output .= ($quotes || $beautiful_json ? '"' : '') . $value . ($quotes || $beautiful_json ? '"' : '');
        }
        
        if (++$count < count($arr)) {
            $output .= ', ';
        }
    }
    
    $output .= "}";
    
    return $output;
}


// функція getCateroriesSum знаходиться в init.php
$getCateroriesSum = getCateroriesSum($arParams);





// echo '<pre>';
// print_r($getCateroriesSum);
// echo '</pre>';






/*
 * Get category list
 */
$currentUserId = CUser::GetID();

/*
 * LEVEL 1
 */
$arSelect = Array(
    'ID',
    'NAME',
    'PROPERTY_124',
);
$arFilter = Array(
    "IBLOCK_ID"     => 21,
    "!PROPERTY_124" => $currentUserId
);
$levelDb = CIBlockElement::GetList(array('SORT' => 'ASC'), $arFilter, false, array(), $arSelect);

$level1Array = array();
while ( $level = $levelDb->fetch() ) {
    $level1Array[$level['ID']] = $level['NAME'];
}


/*
 * LEVEL 2
 */
$arSelect = Array(
    'ID',
    'NAME',
    'PROPERTY_91',
    'PROPERTY_125',
);
$arFilter = Array(
    "IBLOCK_ID"     => 22,
    "!PROPERTY_125" => $currentUserId
);
$levelDb = CIBlockElement::GetList(array('SORT' => 'ASC'), $arFilter, false, array(), $arSelect);

$level2Array = array();
while ( $level = $levelDb->fetch() ) {
    $level2Array[$level['PROPERTY_91_VALUE']][$level['ID']] = $level['NAME'];
}

/*
 * LEVEL 3
 */
$arSelect = Array(
    'ID',
    'NAME',
    'PROPERTY_92',
    'PROPERTY_126',
);
$arFilter = Array(
    "IBLOCK_ID"     => 23,
    "!PROPERTY_126" => $currentUserId
);
$levelDb = CIBlockElement::GetList(array('SORT' => 'ASC'), $arFilter, false, array(), $arSelect);

$level3Array = array();
while ( $level = $levelDb->fetch() ) {
    $level3Array[$level['PROPERTY_92_VALUE']][$level['ID']] = $level['NAME'];
}

/*
 *  Додаткові поля по рівню 1
 */
$arSelect = Array(
    'ID', 
    'NAME', // ID поля
    'PROPERTY_115', // Елемент з рівня 1
    'PROPERTY_119', // Назва поля
);
$arFilter = Array(
    "IBLOCK_ID" => 28,
);
$additionalDb = CIBlockElement::GetList(array(), $arFilter, false, array(), $arSelect);

$additionlFieldArray = array();
$propertiesIds = arraY();
while ( $additionlField = $additionalDb->fetch() ) {
    
    $propertiesIds[] = str_replace("PROPERTY_", "", $additionlField['NAME']);
    
    $additionlFieldArray[$additionlField['PROPERTY_115_VALUE']][$additionlField['NAME']] = $additionlField['PROPERTY_119_VALUE'];
}

$propertiesIds = array_unique($propertiesIds);

// echo '<pre>';
// print_r($additionlFieldArray);
// echo '</pre>';

// $additionalFieldsHtml = array();
// $arFilter = Array('IBLOCK_ID' => 27);
// $additionalDb = CIBlockProperty::GetList(Array(), $arFilter);
// while ( $additionlField = $additionalDb->fetch() ) {
//     if ( in_array($additionlField['ID'], $propertiesIds) ) {
       
//         $additionalFieldsHtml["PROPERTY_" . $additionlField['ID']] =  getFieldHtml($additionlField);
//     }
// }

// echo '<pre>';
// print_r($additionalFieldsData);
// echo '</pre>';

// echo '<pre>';
// print_r($additionlFieldArray);
// echo '</pre>';


$arResult['level1Array'] = $level1Array;
$arResult['level2Array'] = $level2Array;
$arResult['level3Array'] = $level3Array;


$arResult['getCateroriesSum'] = $getCateroriesSum;


$arResult['additionalFieldsHtml'] = $additionalFieldsHtml;
$arResult['additionlFieldArray']  = $additionlFieldArray;

$arResult['level1Json'] = json_encode_advanced($level1Array);
$arResult['level2Json'] = json_encode_advanced($level2Array);
$arResult['level3Json'] = json_encode_advanced($level3Array);

$arResult['additionlFieldJson'] = json_encode_advanced($additionlFieldArray);



$this->IncludeComponentTemplate();