<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle('Импорт пулов');
$APPLICATION->IncludeComponent('auspex:pool.import', '');
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");