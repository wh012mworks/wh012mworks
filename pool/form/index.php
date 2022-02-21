<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle('Форма');
$APPLICATION->IncludeComponent('auspex:pool.form', '');
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");