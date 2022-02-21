<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle('Импорт транзакций');
$APPLICATION->IncludeComponent('auspex:pool.transactions', '');
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");