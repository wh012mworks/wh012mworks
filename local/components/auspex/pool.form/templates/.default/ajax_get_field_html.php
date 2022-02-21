<?php 
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");


function getUserInfo()
{
    /*
     * Get Users List
     */
    $filter = array(
        'ACTIVE' => 'Y'
    );
    $rsUsers = CUser::GetList(($by="personal_country"), ($order="desc"), $filter, array('*'));
    
    $usersDataArray  = array();
    $usersNamesArray = array();
    while($userData = $rsUsers->fetch()) {
        
        if ( !empty($userData['EMAIL']) ) {
            $userName = '';
            if ( !empty($userData['NAME']) || !empty($userData['LAST_NAME']) ) {
                $userName = trim("{$userData['NAME']} {$userData['LAST_NAME']}");
            } elseif ( !empty($userData['EMAIL']) ) {
                $userName = $userData['EMAIL'];
            } else {
                $userName = $userData['LOGIN'];
            }
            
            $usersNamesArray[$userData['ID']] = $userName;
        }
        
    }
    asort($usersNamesArray);
    return $usersNamesArray;
}

function getFieldHtml($properyData, $onlyOptions = 0)
{
    
    
//     echo '<pre>';
//     print_r($properyData);
//     echo '</pre>';
    
    switch ($properyData['PROPERTY_TYPE']) {
        case 'S':
            if ( !empty( $properyData['USER_TYPE'] ) ) {
                
                switch ($properyData['USER_TYPE']) {
                    case 'employee':
                        
                        $userList = getUserInfo();
                        
                        asort($userList);
                        
                        $option = '';
                        foreach ( $userList as $userId => $userName ) {
                            $option .= '<option value="'.$userId.'">'.$userName.'</option>';
                        }
                        return '<div class="crm-entity-widget-content-block-field-container"><select class="crm-entity-widget-content-select" id="PROPERTY_'.$properyData['ID'].'" name="PROPERTY_'.$properyData['ID'].'">
                                    <option value="">- не выбрано -</option>
                                    '.$option.'
                                </select></div>';
                        break;
                        
                    case 'Money':
                        return '<div class="crm-entity-widget-content-block-field-container">
                                    <input type="number" style="width:70%;" class="crm-entity-widget-content-input" id="PROPERTY_'.$properyData['ID'].'" name="PROPERTY_'.$properyData['ID'].'[opportunity]">
                                    <select style="width:30%;" class="crm-entity-widget-content-select" id="PROPERTY_'.$properyData['ID'].'" name="PROPERTY_'.$properyData['ID'].'[currency]">
                                        <option value="USD">Доллар США</option>
                                        <option value="UAH">Гривна</option>
                                    </select>
                                </div>';
                        break;
                        
                    default:
                        ;
                        break;
                }
                
            } else {
                return '<div class="crm-entity-widget-content-block-field-container"><input type="text" class="crm-entity-widget-content-input" id="PROPERTY_'.$properyData['ID'].'" name="PROPERTY_'.$properyData['ID'].'"></div>';
            }
            
            break;
            
        case 'N':
            return '<div class="crm-entity-widget-content-block-field-container"><input type="number" class="crm-entity-widget-content-input" id="PROPERTY_'.$properyData['ID'].'" name="PROPERTY_'.$properyData['ID'].'"></div>';
            break;
            
            
        case 'F':
//             return '<input type="file" class="ui-btn ui-btn-icon-page" id="PROPERTY_'.$properyData['ID'].'" name="PROPERTY_'.$properyData['ID'].'" value="Файл">';
            return '<div class="crm-entity-widget-content-block-field-container"><label class="ui-btn ui-btn-icon-page">
                        <input type="file" style="border-width: 0px;" id="PROPERTY_'.$properyData['ID'].'" name="PROPERTY_'.$properyData['ID'].'"/>
                    </label></div>';
            break;
            
        case 'E':
            $select = '';
            if ( !empty( $properyData['LINK_IBLOCK_ID'] ) ) {
                $arSelect = Array(
                    'ID',
                    'NAME',
                );
                $arFilter = Array(
                    "IBLOCK_ID" => $properyData['LINK_IBLOCK_ID'],
                );
                $listDb = CIBlockElement::GetList(array(), $arFilter, false, array(), $arSelect);
                
                $counter = 0;
                
                $options = '';
                while ( $record = $listDb->fetch() ) {
                    $counter++;
                    $options .= '<option value="'.$record['ID'].'">'.$record['NAME'].'</option>';
                }
                
                if ( $counter > 0 ) {
                    $options = '<option value="">- не выбрано -</option>' . $options;
                } else {
                    $options = '<option value="">- пусто -</option>' . $options;
                }
                
                if ( $onlyOptions ) {
                    $select = $options;
                    
                } else {
                    
                    if ( $properyData['ID'] == 211 ) {
                        $select = '<div class="row"><div class="col"><select class="crm-entity-widget-content-select selectpicker" id="PROPERTY_'.$properyData['ID'].'" name="PROPERTY_'.$properyData['ID'].'[]" multiple>' . $options . '</select></div></div>';
                    } else {
                        $select = '<div class="crm-entity-widget-content-block-field-container"><select class="crm-entity-widget-content-select" id="PROPERTY_'.$properyData['ID'].'" name="PROPERTY_'.$properyData['ID'].'">' . $options . '</select></div>';
                    }
                    
                    if ( $properyData['LINK_IBLOCK_ID'] == 26 ) {
                        $select .= ' <div class="row"><div class="col"><select class="crm-entity-widget-content-select selectpicker" id="PROPERTY_130" name="PROPERTY_130[]" multiple><option value="">- пусто -</option></select></div></div>';
                    }
                }
            }
            return $select;
            break;
            
        default:
            break;
    }
}


$request = $_REQUEST;

if ( isset($request['action']) ) {
    $arSelect = Array(
        'ID',
        'NAME',
        'PROPERTY_143', // Назва банку
        'PROPERTY_144', // ПІБ (назва) позичальника
        'PROPERTY_145', // ІПН позичальника (код ЄДРПОУ)
        'PROPERTY_146', // Номер кредитного договору
        'PROPERTY_156', // Регіон видачі
        'PROPERTY_180', // Короткий опис застави
        'PROPERTY_200', // Інформація по поручителю
    );
    $arFilter = Array(
        "IBLOCK_ID"    => 29,
        "PROPERTY_141" => $request['value'],
    );
    $listDb = CIBlockElement::GetList(array(), $arFilter, false, array(), $arSelect);
    
    $counter = 0;
    
    $options = '';
    while ( $record = $listDb->fetch() ) {
        
        $name = "{$record['PROPERTY_143_VALUE']} | {$record['PROPERTY_144_VALUE']} | {$record['PROPERTY_145_VALUE']} | {$record['PROPERTY_146_VALUE']} | {$record['PROPERTY_156_VALUE']} | {$record['PROPERTY_180_VALUE']} | {$record['PROPERTY_200_VALUE']}";
        
        $counter++;
        $options .= '<option value="'.$record['ID'].'">'.$name.'</option>';
    }
    
    if ( $counter > 0 ) {
        $options = '<option value="">- не выбрано -</option>' . $options;
    } else {
        $options = '<option value="">- пусто -</option>' . $options;
    }
    
    echo $options;
    
} else {
    $html = '';
    
//     print_r($_GET);
    
    $arFilter = Array('IBLOCK_ID' => 27);
    $additionalDb = CIBlockProperty::GetList(Array(), $arFilter);
    while ( $additionlField = $additionalDb->fetch() ) {
        if ( !empty($request['PROPERTY_' . $additionlField['ID']]) ) {
            
            if ( !empty($_GET['category']) && $_GET['category'] == 299 && $additionlField['ID'] == 121 ) {
                $html .= '
                    <div class="row">
                    	<div class="col">
                        	<label>
                        		<div class="crm-entity-widget-content-block-title">
                       				<span class="crm-entity-widget-content-block-title-text">'.$request['PROPERTY_' . $additionlField['ID']].'</span>
                       			</div>
                   			</label>
                            <div class="crm-entity-widget-content-block-inner">
                       			<div class="crm-entity-widget-content-block-field-container">
                                    <input type="number" style="width:70%;" class="crm-entity-widget-content-input" id="PROPERTY_'.$additionlField['ID'].'_opportunity" name="PROPERTY_'.$additionlField['ID'].'[opportunity]">
                                </div>
                   			</div>
               			</div>
                                        
                        <div class="col">
                        	<label>
                        		<div class="crm-entity-widget-content-block-title">
                       				<span class="crm-entity-widget-content-block-title-text">Со счета</span>
                       			</div>
                   			</label>
                            <div class="crm-entity-widget-content-block-inner">
                       			<div class="crm-entity-widget-content-block-field-container">
                                    <select style="width:30%;" class="crm-entity-widget-content-select" id="PROPERTY_'.$additionlField['ID'].'_currency" name="PROPERTY_'.$additionlField['ID'].'[currency]">
                                        <option value="USD">Доллар США</option>
                                        <option value="UAH">Гривна</option>
                                    </select>
                                </div>
                   			</div>
               			</div>
                    </div>
                ';
            } elseif ( !empty($_GET['category']) && $_GET['category'] == 299 && $additionlField['ID'] == 112 ) {
                $html .= '
                    
                    <div class="row">
                    	<div class="col">
                        	<label>
                        		<div class="crm-entity-widget-content-block-title">
                       				<span class="crm-entity-widget-content-block-title-text">'.$request['PROPERTY_' . $additionlField['ID']].'</span>
                       			</div>
                   			</label>
                            <div class="crm-entity-widget-content-block-inner">
                       				<div class="crm-entity-widget-content-block-field-container">
                                        <input type="number" class="crm-entity-widget-content-input" id="PROPERTY_'.$additionlField['ID'].'" name="PROPERTY_'.$additionlField['ID'].'">
                                         <span id="exchange_result"></span>
                                    </div>
                   			</div>
               			</div>
                    </div>
                ';
            } else {
                $html .= '
                    <div class="row">
                    	<div class="col">
                        	<label>
                        		<div class="crm-entity-widget-content-block-title">
                       				<span class="crm-entity-widget-content-block-title-text">'.$request['PROPERTY_' . $additionlField['ID']].'</span>
                       			</div>
                   			</label>
                            <div class="crm-entity-widget-content-block-inner">
                       				'.getFieldHtml($additionlField).'
                   			</div>
               			</div>
                    </div>
                ';
            }
        
        }
    }
    
    echo $html;
}











