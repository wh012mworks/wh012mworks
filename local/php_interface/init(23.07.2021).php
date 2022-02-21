<?php

AddEventHandler("iblock", "OnAfterIBlockElementAdd", "OnAfterIBlockElementAdd");
AddEventHandler("iblock", "OnBeforeIBlockElementAdd", "OnBeforeIBlockElementAdd");
AddEventHandler("im", "OnBeforeConfirmNotify", "OnBeforeConfirmNotify");
/*
function getCateroriesSum($arParams)
{
    $arSelect = Array(
        'ID',
        'NAME',
        'PROPERTY_136',
        'PROPERTY_121',
        );
    $arFilter = Array(
        "IBLOCK_ID" => 27,
        );
    
    if ( !empty( $arParams['USER_ID'] ) ) {
        $arFilter['PROPERTY_122'] = $arParams['USER_ID'];
    }
    
    $objectDb = CIBlockElement::GetList(array(), $arFilter, false, array(), $arSelect);
    
    $totalsArray = array();
    $currencyList = array();
    while ( $record = $objectDb->fetch() ) {
        
        $prop = $record['PROPERTY_121_VALUE'];
        if ( !empty($prop) ) {
            $prop = explode('|', $prop);
            $price = $prop[0];
            $currency = !empty($prop[1]) ? $prop[1] : 'unknown';
            
            $currencyList[$currency] = $currency;
            
            if ( !isset($totalsArray[$record['PROPERTY_136_VALUE']][$currency]) ) {
                $totalsArray[$record['PROPERTY_136_VALUE']][$currency] = 0;
            }
            
            $totalsArray[$record['PROPERTY_136_VALUE']][$currency] += $price;
        }
    }
    
    return array(
        'currencyList' => $currencyList,
        'totalsArray'  => $totalsArray,
    );
}
*/

function getCateroriesSum($arParams)
{
    $arSelect = Array(
        'ID',
        'NAME',
        'PROPERTY_136',
        'PROPERTY_121',
        'PROPERTY_226',
        'PROPERTY_227',
    );
    $arFilter = Array(
        "IBLOCK_ID" => 27,
    );
    
    if ( !empty( $arParams['USER_ID'] ) ) {
        $arFilter['PROPERTY_122'] = $arParams['USER_ID'];
    }
    
    $objectDb = CIBlockElement::GetList(array(), $arFilter, false, array(), $arSelect);
    
    $totalsArray = array();
    $currencyList = array();
    while ( $record = $objectDb->fetch() ) {
        
        if ( !empty($record['PROPERTY_227_VALUE']) ) {
            $price   = $record['PROPERTY_227_VALUE'];
            $currency = !empty($record['PROPERTY_226_VALUE']) ? $record['PROPERTY_226_VALUE'] : 'unknown';
            
            $currencyList[$currency] = $currency;
            
            if ( !isset($totalsArray[$record['PROPERTY_136_VALUE']][$currency]) ) {
                $totalsArray[$record['PROPERTY_136_VALUE']][$currency] = 0;
            }
            
            $totalsArray[$record['PROPERTY_136_VALUE']][$currency] += $price;
        }
    }
    
    return array(
        'currencyList' => $currencyList,
        'totalsArray'  => $totalsArray,
    );
}

function getCurrencyTotals($cateroriesSum, $neededCurrecy)
{
    $total = 0;
    foreach ( $cateroriesSum['totalsArray'] as $categoryId => $prices ) {
        foreach ( $cateroriesSum['currencyList'] as $currency ) { 
		
            if ( $currency == $neededCurrecy ) {
    		    $price = 0;
    		    if ( !empty($prices[$currency]) ) {
    		        $price = $prices[$currency];
    		    }
    		    
    		    if ( $categoryId == 178 ) {
    		        $price = $price * -1;
    		    }
    		    if ( $categoryId == 179 ) {
    		        $price = $price * -1;
    		    }
    		    
    		    if ( $categoryId == 181 ) {
    		        $price = $price * -1;
    		    }
    		    
    		    
    		    
    		    if ( $categoryId == 182 ) {
    		        $price = $price * -1;
    		    }
    		    
    		    $total += $price;
            }
		}
    }
    return $total;
}

function sentCasaNotify($currentUserId, $currency)
{
    CModule::IncludeModule("im");
    
    $arMessageFields = array(
        "TO_USER_ID"     => $currentUserId,
        "FROM_USER_ID"   => $currentUserId,
        "NOTIFY_TYPE"    => 2,
        "NOTIFY_MODULE"  => "im",
        "NOTIFY_TAG"     => '',
        "NOTIFY_MESSAGE" => 'Каса ' . $currency . ' не може бути мінусовою!',
    );
    $notify = CIMNotify::Add($arMessageFields);
}

function OnBeforeIBlockElementAdd(&$arFields)
{
    $currentUserId =  CUser::GetID();
    $arParams = ['USER_ID' => $currentUserId];
    $getCateroriesSum = getCateroriesSum($arParams);
    
//     file_put_contents('/home/bitrix/www/local/php_interface/getCateroriesSum - custom.log', date('d.m.Y H:i:s') . "\n" . print_r($money, true) . "\n", FILE_APPEND);
    
    if ( $arFields['IBLOCK_ID'] == 27 && $arFields['PROPERTY_VALUES'][136] == 299 ) {
        
        $el = new CIBlockElement;
        
        if ( !empty($arFields['PROPERTY_VALUES']['121']) ) {
            
            $exchangeSum = explode('|', $arFields['PROPERTY_VALUES']['121']);
            $cource      = $arFields['PROPERTY_VALUES']['112'];
            
            $money    = getCurrencyTotals($getCateroriesSum, $exchangeSum[1]);
            $newMoney = $money - $exchangeSum[0];
            
            if ( $newMoney < 0 ) {
                sentCasaNotify($currentUserId, $exchangeSum[1]);
                return false;
            }
            
            if ( $exchangeSum[1] == 'USD' ) {
                $newVal    = round($exchangeSum[0] * $cource, 1);
                $newCurrency = 'UAH';
                
            } elseif ( $exchangeSum[1] == 'UAH' ) {
                $newVal    = round($exchangeSum[0] / $cource, 1);
                $newCurrency = 'USD';
                
            }
            
            $arFields['PROPERTY_VALUES']['216'] = "{$exchangeSum[1]} ({$exchangeSum[0]}) => $newCurrency ($newVal)";
            
            $arLoadProductArray = Array(
                "IBLOCK_ID"      => 27,
                "PROPERTY_VALUES"=> $arFields['PROPERTY_VALUES'],
                "NAME"           => $arFields['NAME'],
                "ACTIVE"         => "Y",
            );
            $arLoadProductArray['PROPERTY_VALUES']['136'] = 179; // расходи
            $elementId = $el->Add($arLoadProductArray);
            
            if ( $elementId ) {
                $arLoadProductArray = Array(
                    "IBLOCK_ID"      => 27,
                    "PROPERTY_VALUES"=> $arFields['PROPERTY_VALUES'],
                    "NAME"           => $arFields['NAME'],
                    "ACTIVE"         => "Y",
                );
                $arLoadProductArray['PROPERTY_VALUES']['136'] = 180; // доходи
                $arLoadProductArray['PROPERTY_VALUES']['121'] = $newVal . '|' . $newCurrency; // доходи
                
                $elementId = $el->Add($arLoadProductArray);
            }
            
        }
        return false;
        
    }
    
    if ( $arFields['IBLOCK_ID'] == 27 ) {
        
        if ( !empty($arFields['PROPERTY_VALUES']['121']) && $arFields['PROPERTY_VALUES']['136'] == 179 ) {
            $exchangeSum = explode('|', $arFields['PROPERTY_VALUES']['121']);
            $money    = getCurrencyTotals($getCateroriesSum, $exchangeSum[1]);
            $newMoney = $money - $exchangeSum[0];
            if ( $newMoney < 0 ) {
                sentCasaNotify($currentUserId, $exchangeSum[1]);
                return false;
            }
        }
        
        if (!empty($arFields['PROPERTY_VALUES'][110]) && !empty($arFields['PROPERTY_VALUES'][136]) && $arFields['PROPERTY_VALUES'][136] == 178 ) {
            $arFields['PROPERTY_VALUES'][215] = 123;
        }
    }
}

function OnAfterIBlockElementAdd(&$arFields)
{
//     file_put_contents('/home/bitrix/www/local/php_interface/OnAfterIBlockElementAdd.log', date('d.m.Y H:i:s') . "\n" . print_r($arFields, true) . "\n", FILE_APPEND);
    
    if ( $arFields['IBLOCK_ID'] == 27 && !empty($arFields['PROPERTY_VALUES'][110]) && !empty($arFields['PROPERTY_VALUES'][136]) && $arFields['PROPERTY_VALUES'][136] == 178 ) {
        $arFields['PROPERTY_VALUES'][215] = 123;
   
        CModule::IncludeModule("im");
        
        $currentUserId = CUser::GetID();
        $currentUserData = CUser::GetByID($currentUserId)->fetch();
        $userName = $currentUserData['LOGIN'];
        if ( !empty($currentUserData['LAST_NAME']) || !empty($currentUserData['NAME']) ) {
            $userName = trim("{$currentUserData['LAST_NAME']} {$currentUserData['NAME']}");
        } elseif (  !empty($currentUserData['EMAIL']) ) {
            $userName = trim($currentUserData['EMAIL']);
        }
        
        $moneyArr = explode('|', $arFields['PROPERTY_VALUES'][121]);
        $money    = $moneyArr[0];
        $currency = $moneyArr[1];
        
        $message = "Подтвердите приход в кассу от [USER=$currentUserId]{$userName}[/USER] в размере [B]" . number_format($money,2,"."," ") . ' ' . $currency . '[/B]
    Примечание:' . $arFields['NAME'];
        
        //     file_put_contents('/home/bitrix/www/local/php_interface/currentUserData.log', date('d.m.Y H:i:s') . "\n" . print_r($elementData, true) . "\n", FILE_APPEND);
        
        $arMessageFields = array(
            "TO_USER_ID"     => $arFields['PROPERTY_VALUES'][110],
            "FROM_USER_ID"   => $currentUserId,
            "NOTIFY_TYPE"    => 1,
            "NOTIFY_MODULE"  => "im",
            "NOTIFY_TAG"     => $arFields['ID'],
            "NOTIFY_MESSAGE" => $message,
        );
        $notify = CIMNotify::Add($arMessageFields);
    }
}


function OnBeforeConfirmNotify($module, $transactionId, $submit, $arNotify)
{
    if ( $submit == 'Y' ) {
        CIBlockElement::SetPropertyValuesEx($transactionId, false, array(215 => 122));
        CIBlockElement::SetPropertyValuesEx($transactionId, false, array(123 => 82));
        
        $arSelect = Array("PROPERTY_122", "PROPERTY_121");
        $arFilter = Array(
            "IBLOCK_ID" => 27,
            'ID' => $transactionId
        );
        $elementDb = CIBlockElement::GetList(array(), $arFilter, false, array(), $arSelect);
        $elementData = $elementDb->fetch();
        
        $propertyList = array(
            135 => date('d.m.Y'),
            122 => CUser::GetID(),
            110 => CUser::GetID(),
            111 => $elementData['PROPERTY_122_VALUE'],
            121 => $elementData['PROPERTY_121_VALUE'],
            136 => 177,
        );
        
        $el = new CIBlockElement;
        $arLoadProductArray = Array(
            "IBLOCK_ID"      => 27,
            "PROPERTY_VALUES"=> $propertyList,
            "NAME"           => 'Без примечания',
            "ACTIVE"         => "Y",
        );
        $PRODUCT_ID = $el->Add($arLoadProductArray);
        
    } elseif ( $submit == 'N' ) {
        CIBlockElement::SetPropertyValuesEx($transactionId, false, array(215 => 124));
        CIBlockElement::SetPropertyValuesEx($transactionId, false, array(123 => 83));
    }
}


