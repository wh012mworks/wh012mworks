<?php
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$request = $_REQUEST;


if ( $request['currency'] == 'USD' ) {
    $result = $request['exVal'] * $request['course'];
} else {
    $result = $request['exVal'] / $request['course'];
    $result = round($result, 2);
}
echo $result;

// echo '<pre>';
// print_r($arLoadProductArray);
// echo '</pre>';
// echo '<pre>';
// print_r($request);
// echo '</pre>';
// echo '<pre>';
// print_r($result);
// echo '</pre>';

