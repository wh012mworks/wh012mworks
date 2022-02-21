<?php
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$request = $_REQUEST;



if ( !empty($request['PROPERTY_121']) && count($request['PROPERTY_121']) == 2 ) {
    
    $request['PROPERTY_227'] = $request['PROPERTY_121']['opportunity'];
    $request['PROPERTY_226'] = $request['PROPERTY_121']['currency'] == 'USD' ? 147 : 146;
    
    $request['PROPERTY_121'] = implode('|', $request['PROPERTY_121']);
}

if ( !empty($_FILES['PROPERTY_140']) ) {

    $fileData = CFile::MakeFileArray($_FILES['PROPERTY_140']['tmp_name']);
    $fileData['name'] = $_FILES['PROPERTY_140']['name'];
    
    $request['PROPERTY_140'] = $fileData;
}


$request["NAME"] = !empty($request['NAME']) ? $request['NAME'] : 'Без примечания';

$propertyList = array(
    135 => date('d.m.Y'),
    122 => CUser::GetID(),
);
foreach ($request as $key => $val) {
    if (strpos($key, 'PROPERTY_') !== false) {
        $propertyList[str_replace('PROPERTY_','',$key)] = $val;
    }
}

$el = new CIBlockElement;
$arLoadProductArray = Array(
    "IBLOCK_ID"      => 27,
    "PROPERTY_VALUES"=> $propertyList,
    "NAME"           => !empty($request['NAME']) ? $request['NAME'] : 'Без примечания',
    "ACTIVE"         => "Y",
);


// $arLoadProductArray = array(
//     'IBLOCK_TYPE_ID' => 'lists',
//     "IBLOCK_ID"      => 27,
//     'ELEMENT_CODE'   => md5(uniqid(rand(), true)) . rand(1,999),
//     "NAME"           => !empty($request['NAME']) ? $request['NAME'] : 'Без примечания',
//     'FIELDS'         => $request,
// );


$PRODUCT_ID = $el->Add($arLoadProductArray);

// echo '<pre>';
// print_r($arLoadProductArray);
// echo '</pre>';
// echo '<pre>';
// print_r($PRODUCT_ID);
// echo '</pre>';


if($PRODUCT_ID) {
 
} else {
//     echo "Ошибка: ".$el->LAST_ERROR;
}