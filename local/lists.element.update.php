<?php 
require_once('/home/bitrix/www/bitrix/modules/main/include/prolog_before.php');
global $USER;
$USER->Authorize(1);





// $arSelect = Array(
//     'ID',
//     'NAME',
//     'PROPERTY_124',
// );
// $arFilter = Array(
//     "IBLOCK_ID"     => 21,
//     "ID" => 551
// );
// $levelDb = CIBlockElement::GetList(array('SORT' => 'ASC'), $arFilter, false, array(), $arSelect);

// while ( $level = $levelDb->fetch() ) {
//     echo '<pre>';
//     print_r($level);
//     echo '</pre>';
// }