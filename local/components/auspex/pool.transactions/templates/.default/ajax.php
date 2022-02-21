<?php
require_once('/home/bitrix/www/bitrix/modules/main/include/prolog_before.php');

function csv_to_array($csvFile='', $delimiter=';', $contactFields = 0)
{
    set_time_limit(0);
    setlocale('LC_ALL', 'ru_RU.UTF-8');
    //перекодирую в UTF8
    $csvData = file_get_contents($csvFile);
    $csvData = iconv('cp1251', 'utf-8', $csvData);
    $fname = tempnam("/tmp", "priceCsv");
    
    file_put_contents($fname, $csvData);
    
    $csvHandle = fopen($fname, 'r');
    
    if ($csvHandle === false) {
        throw new Exception('Не могу открыть файл с данными для импорта: ' . $fname);
    }
    
    $header = NULL;
    $rowNumber = 0;
    
    $overrideFilter = [];
    $firstRowEscaped = 0;
    $data = [];
    
    while ($row = fgetcsv($csvHandle, 100000, $delimiter, '"')) {
        $rowNumber += 1;
        $row = array_map('trim', $row);
        
        if(!$header) {
            $header = $row;
        } else {
            if ( count(array_filter($row)) ) {
                $data[$rowNumber] = $row;
            }
        }
    }
    $result = [
        'header' =>	$header,
        'data'   => $data
    ];
    return $result;
}

function getFieldsData()
{
    $fieldsEnums = [];
    $fieldsNames = [];
    $fieldsMoney = [];
    $fieldsRequired = [];
    $fieldsArray = CIBlockProperty::GetList(Array(), Array('IBLOCK_ID' => 27));
    
    while( $field = $fieldsArray->fetch() ) {
        $propName = $field['ID'];
        
        if ( $field['IS_REQUIRED'] == 'Y' ) {
            $fieldsRequired[$propName] = $propName;
        }
        $fieldsNames[$field['NAME']] = $propName;
        
        if ( !empty($field['PROPERTY_TYPE']) && $field['PROPERTY_TYPE'] == 'L' ) {
            $GetPropertyEnum = CIBlockProperty::GetPropertyEnum($field['ID']);
            while( $fieldData = $GetPropertyEnum->fetch() ) {
                $fieldsEnums[$fieldData['PROPERTY_ID']][$fieldData['VALUE']] = $fieldData['ID'];
                $fieldsEnums[$fieldData['PROPERTY_ID']][mb_strtolower($fieldData['VALUE'])] = $fieldData['ID'];
            }
        } elseif ( !empty($field['USER_TYPE']) && $field['USER_TYPE'] == 'Money' ) {
            $fieldsMoney[$field['ID']] = $field['ID'];
        }
    }
    
    $fieldsArray = CIBlock::GetFields(27);
    foreach ($fieldsArray as $key => $field) {
        $fieldsNames[$field['NAME']] = $key;
        
        if ( $field['IS_REQUIRED'] == 'Y' ) {
            $fieldsRequired[$propName] = $propName;
        }
    }
    return [
        'fieldsNames' => $fieldsNames,
        'fieldsEnums' => $fieldsEnums,
        'fieldsMoney' => $fieldsMoney,
        'fieldsRequired' => $fieldsRequired,
    ];
}

function getPoolNames()
{
    $arSelect = Array(
        'ID',
        'NAME',
    );
    $arFilter = Array(
        "IBLOCK_ID"     => 26,
    );
    $fieldsArray = CIBlockElement::GetList(array('SORT' => 'ASC'), $arFilter, false, array(), $arSelect);
    $poolNames = [];
    while( $field = $fieldsArray->fetch() ) {
        $poolNames[$field['NAME']] = $field['ID'];
    }
    return $poolNames;
}

function getUserList()
{
    $filter = array(
    //     'BX_USER_ID' => true
    );
    $rsUsers = CUser::GetList(($by="personal_country"), ($order="desc"), $filter, array('*'));
    
    $userNames = [];
    while($userData = $rsUsers->fetch()) {
        $name1  = trim("{$userData['LAST_NAME']} {$userData['NAME']} {$userData['SECOND_NAME']}");
        $name2  = trim("{$userData['LAST_NAME']}{$userData['NAME']}{$userData['SECOND_NAME']}");
        $name3  = trim("{$userData['LAST_NAME']}_{$userData['NAME']}_{$userData['SECOND_NAME']}");
        $email = $userData['EMAIL'];
        
        $userNames[$name1] = $userData['ID'];
        $userNames[$name2] = $userData['ID'];
        $userNames[$name3] = $userData['ID'];
        $userNames[$email] = $userData['ID'];
    }
    return $userNames;
}

function getCategoryList()
{
    /*
     * Get category list
     */
    $arSelect = Array(
        'ID',
        'NAME',
        'PROPERTY_124',
    );
    $arFilter = Array(
        "IBLOCK_ID" => 21,
    );
    $levelDb = CIBlockElement::GetList(array('SORT' => 'ASC'), $arFilter, false, array(), $arSelect);
    
    $level1Array = array();
    while ( $level = $levelDb->fetch() ) {
        $level1Array[$level['NAME']] = $level['ID'];
        $level1Array[str_replace(" ","_",$level['NAME'])] = $level['ID'];
        $level1Array[str_replace("_"," ",$level['NAME'])] = $level['ID'];
    }
    return $level1Array;
}

function getLevel2List()
{
    $arSelect = Array(
        'ID',
        'NAME',
    );
    $arFilter = Array(
        "IBLOCK_ID" => 22,
    );
    $levelDb = CIBlockElement::GetList(array('SORT' => 'ASC'), $arFilter, false, array(), $arSelect);
    
    $level1Array = array();
    while ( $level = $levelDb->fetch() ) {
        $level1Array[$level['NAME']] = $level['ID'];
        $level1Array[str_replace(" ","_",$level['NAME'])] = $level['ID'];
        $level1Array[str_replace("_"," ",$level['NAME'])] = $level['ID'];
    }
    return $level1Array;
}

function findErrors($propertyRelationsКRev, $poolNames, $objectsList, $categoryList, $level2List, $propertyRelations, $getFieldsData, $data, $fileData, $usersList)
{
    $errors = [];
    
//     if ( !empty($data[$propertyRelationsКRev['Пул']])
//         && !empty($poolNames[$data[$propertyRelationsКRev['Пул']]])
//         && !empty($data[$propertyRelationsКRev['ПІБ боржника']])
//         && !empty($objectsList[$poolNames[$data[$propertyRelationsКRev['Пул']]]][$data[$propertyRelationsКRev['ПІБ боржника']]])
//         ) {
            
//     } else {
//         $errors[] = "<span>Не найден объект по этому пулу и ФИО должника</span>";
//     }
    
    if ( !empty($data[$propertyRelationsКRev['Категория']]) ) {
        if ( !empty($categoryList[$data[$propertyRelationsКRev['Категория']]]) ) {
            
        } else {
            $errors[] = "<span>Неизвестное значение поля 'Категория'</span>";
        }
    } else {
        $errors[] = "<span>Не найдена категория</span>";
    }
    
    
    if ( !empty($data[$propertyRelationsКRev['ФИО']]) ) {
        
        if ( !empty($usersList[$data[$propertyRelationsКRev['ФИО']]]) ) {
            
        } else {
            $errors[] = "<span>Не найдено пользователя с таким ФИО: {$data[$propertyRelationsКRev['ФИО']]}</span>";
        }
        
    } else {
        $errors[] = "<span>Не найдено значение ФИО</span>";
    }
    
    
//     if ( !empty($data[$propertyRelationsКRev['Статья']]) ) {
        
//         if ( !empty($level2List[$data[$propertyRelationsКRev['Статья']]]) ) {
            
//         } else {
//             $errors[] = "<span>Неизвестное значение поля 'Статья'</span>";
//         }
        
//     } else {
//         $errors[] = "<span>Не найдена Статья</span>";
//     }
    
    
    if ( !empty($data[$propertyRelationsКRev['$']]) ) {

    } else {
        $errors[] = "<span>Не найдена сумма ($)</span>";
    }
    
    
    
    foreach ( $data as $key => $fieldVal ) {
        
        $fieldProperty = $propertyRelations[$key];
        
        if ( isset($getFieldsData['fieldsEnums'][$fieldProperty]) ) {
            
            if ( !isset( $getFieldsData['fieldsEnums'][$fieldProperty][$fieldVal] ) ) {
                $errors[] = "<span>{$fileData['header'][$key]}: неизвестное значение \"<b>$fieldVal</b>\"</span>";
            }
        }
        
        if ( !$fieldVal && !empty($getFieldsData['fieldsRequired'][$fieldProperty]) ) {
            $errors[] = "<span>{$fileData['header'][$key]}: не заполнено</span>";
        }
    }
    return implode('<br>', $errors);
}
// print_r($_FILES);

$currencyRelation = [
    '980' => 'UAH',
    '840' => 'USD',
    '978' => 'EUR',
];

if ( !empty($_REQUEST['action']) ) {
    
    $getFieldsData = getFieldsData();
    $fileData      = csv_to_array($_FILES['file']['tmp_name'], $delimiter=';');
    
    $currencyFieldKey = '';
    
    $hasErrors = 0;
    $headersValidators = '';
    $index = 0;
    $propertyRelations     = [];
    $propertyRelationsКRev = [];
    if ( !empty($fileData['header']) ) {
        foreach ( $fileData['header'] as $key => $header ) {
            
            $isRequired = '';
            
            if ( in_array($header, ["Валюта зобов'язання", 'Валюта зобовязання', 'Валюта кредиту']) ) {
                $currencyFieldKey = $key;
            }
            
            $propertyRelationsКRev[$header] = $key;
            
            $index++;
            if ( !empty($getFieldsData['fieldsNames'][$header]) ) {
                
                $propertyRelations[$key] = $getFieldsData['fieldsNames'][$header];
                if ( !empty($getFieldsData['fieldsRequired'][$propertyRelations[$key]]) ) {
                    $isRequired = "<span style='color:red'>(обязательное)</span>";
                }
                $headersValidators .= "<tr style='background-color: green;'><td style='width:30px;'>$index</td><td style='max-width:400px;'>{$header} {$isRequired}</td><td style='max-width:400px;'>{$getFieldsData['fieldsNames'][$header]}</td></tr>";
            } else {
                $headersValidators .= "<tr style='background-color: red;'><td style='width:30px;'>$index</td><td style='max-width:400px;'>{$header}</td><td style='max-width:400px;'>- не найдено совпадение -</td></tr>";
                $hasErrors++;
            }
        }
    }
    
    
    switch ($_REQUEST['action']) {
        case 'analyze':
            
            // ПУЛ не має бути обовязковий
            // ПІБ Боржника може бути пустий. Тоді не підтягнеться 
            // Забрати перевірку на мінусову касу!! Але залишити сповіщення . На Адміна і на Вадима Бруякіна
            // Стаття необовязкова
            // грн може бути пуста, а $ обовязклве
            // Переміщення між касами протестати і записати відео, щоб було видно, що з одної каси мінус, а в другу плюс (Приход от инвестора, Видача дивидентов, Видача из касси)
            
            
//             if ( !isset($propertyRelationsКRev['Пул']) ) {
//                 echo die('Ошибка. В Таблице нет колонки "Пул"');
//             }
            
            if ( !isset($propertyRelationsКRev['Категория']) ) {
                echo die('Ошибка. В Таблице нет колонки "Категория"');
            }
            
//             if ( !isset($propertyRelationsКRev['Статья']) ) {
//                 echo die('Ошибка. В Таблице нет колонки "Статья"');
//             }
            
//             if ( !isset($propertyRelationsКRev['грн']) ) {
//                 echo die('Ошибка. В Таблице нет колонки "грн"');
//             }
            
            if ( !isset($propertyRelationsКRev['ФИО']) ) {
                echo die('Ошибка. В Таблице нет колонки "ФИО"');
            }
            
//             if ( !isset($propertyRelationsКRev['ПІБ боржника']) ) {
//                 echo die('Ошибка. В Таблице нет колонки "ПІБ боржника"');
//             }
            
            $poolNames    = getPoolNames();
            $usersList    = getUserList();
            $categoryList = getCategoryList();
            $level2List   = getLevel2List();
            
//             echo '<pre>';
//             print_r($level2List);
//             echo '</pre>';
            
            
            if ( $hasErrors ) {
                ?>
					<div class="alert alert-danger" role="alert">
                  		Количество колонок, не совпавших с полями в битриксе: <b><?=$hasErrors?></b>
                    </div>
                    <br>
                    <div>
                        <table>
                        	<tr><th>#</th><th style='max-width:400px;'>Колонка таблиці</th><th style='max-width:400px;'>Поле бітрікса</th></tr>
                        	<?=$headersValidators?>
                        </table>
                    </div>
                <?php
            } else {
                ?>
					<div class="alert alert-success" role="alert">
                  		Все колонки таблицы совпадают с полями битрикса
                    </div>
                    <br>
                    <div>
                        <table>
                        	<tr><th>#</th><th style='max-width:400px;'>Колонка таблиці</th><th style='max-width:400px;'>Поле бітрікса</th></tr>
                        	<?=$headersValidators?>
                        </table>
                    </div>
                <?php
                
            }
            
            $willSuccess = 0;
            $willErrors  = 0;
            
            ?>
            <br>
            <h2>Анализ данных таблицы</h2>
            <table>
            	<tr><th>№ строки</th><th>Описание ошибки</th></tr>
                <?php
                
                global $DB;
                
                $debtorNames = [];
                if ( !empty($fileData['data']) ) {
                    $errorsCount = 0;
                    foreach ( $fileData['data'] as $row => $data ) {
                        if ( !empty($data[$propertyRelationsКRev['Пул']]) && !empty($data[$propertyRelationsКRev['ПІБ боржника']]) ) {
                            $debtorNames[] = $data[$propertyRelationsКRev['ПІБ боржника']];
                        }
                    }
                }
                $debtorNames = array_unique($debtorNames);
                
                $arSelect = Array(
                    'ID',
                    'NAME',
                    'PROPERTY_141', // ПУЛ
                    'PROPERTY_144', // ПІБ Позичальника
                );
                $arFilter = Array(
                    "IBLOCK_ID"    => 29,
                    "PROPERTY_144" => $debtorNames
                );
                $objDb = CIBlockElement::GetList(array('SORT' => 'ASC'), $arFilter, false, array(), $arSelect);
                $objectsList = [];
                while ( $obj = $objDb->fetch() ) {
                    $objectsList[$obj['PROPERTY_141_VALUE']][$obj['PROPERTY_144_VALUE']] = $obj['ID'];
                }
                
//                 echo '<pre>';
//                 print_r($objectsList);
//                 echo '</pre>';
                
                if ( !empty($fileData['data']) ) {
                    $errorsCount = 0;
                    foreach ( $fileData['data'] as $row => $data ) {
                        
                        $errors = findErrors($propertyRelationsКRev, $poolNames, $objectsList, $categoryList, $level2List, $propertyRelations, $getFieldsData, $data, $fileData, $usersList);
                        
                        if ( $errors ) {
                            $errorsCount++;
                            $willErrors++;
                            ?><tr><td><?=$row?></td><td><?=$errors?></td></tr><?php
                        } else {
                            
                            
                           
                            
                            $willSuccess++;
                        }
                    }
                }
                
                if ( !$errorsCount ) {
                    ?><tr><td></td><td>Ошибок не найдено</td></tr><?php
                }
                ?>
            </table>
            <br>
            <div class="alert alert-success" role="alert">
          		Будет загружено <b><?=$willSuccess?></b> элементов<br>
            </div>
            <div class="alert alert-danger" role="alert">
          		Не будет загружено <b><?=$willErrors?></b> элементов
            </div>
                
            <?php
            if ( $hasErrors || $errorsCount ) {
                ?><button class="ui-btn ui-btn-danger-light" id="btnImport">Все равно импортировать</button><?php
            } else {
                ?><button class="ui-btn ui-btn-success" id="btnImport">Импортировать</button><?php
            }

            break;
            
        case 'import':
            
            $poolNames    = getPoolNames();
            $usersList    = getUserList();
            $categoryList = getCategoryList();
            $level2List   = getLevel2List();
            
            global $DB;
            $element = new CIBlockElement;
            
            $results = [
                'success' => 0,
                'errors' => 0,
            ];
            
            $debtorNames = [];
            if ( !empty($fileData['data']) ) {
                $errorsCount = 0;
                foreach ( $fileData['data'] as $row => $data ) {
                    if ( !empty($data[$propertyRelationsКRev['Пул']]) && !empty($data[$propertyRelationsКRev['ПІБ боржника']]) ) {
                        $debtorNames[] = $data[$propertyRelationsКRev['ПІБ боржника']];
                    }
                }
            }
            $debtorNames = array_unique($debtorNames);
            
            $arSelect = Array(
                'ID',
                'NAME',
                'PROPERTY_141', // ПУЛ
                'PROPERTY_144', // ПІБ Позичальника
            );
            $arFilter = Array(
                "IBLOCK_ID"    => 29,
                "PROPERTY_144" => $debtorNames
            );
            $objDb = CIBlockElement::GetList(array('SORT' => 'ASC'), $arFilter, false, array(), $arSelect);
            $objectsList = [];
            while ( $obj = $objDb->fetch() ) {
                $objectsList[$obj['PROPERTY_141_VALUE']][$obj['PROPERTY_144_VALUE']] = $obj['ID'];
            }
            
            
            
//             echo '<pre>';
//             print_r($poolNames);
//             echo '</pre>';
            
            if ( !empty($fileData['data']) ) {
                $errorsCount = 0;
                foreach ( $fileData['data'] as $row => $data ) {
                    
                    $errors = findErrors($propertyRelationsКRev, $poolNames, $objectsList, $categoryList, $level2List, $propertyRelations, $getFieldsData, $data, $fileData, $usersList);
                    if ( $errors ) {
                        continue;
                    }
                    
                    $dataToBitrix = [];
                    
                    foreach ( $data as $key => $fieldVal ) {
                        
                        if ( !empty($propertyRelations[$key]) ) {
                            $fieldProperty = $propertyRelations[$key];
                            if ( isset($getFieldsData['fieldsEnums'][$fieldProperty]) ) {
                                
                                if ( !isset( $getFieldsData['fieldsEnums'][$fieldProperty][$fieldVal] ) ) {
                                    continue;
                                } else {
                                    $fieldVal = $getFieldsData['fieldsEnums'][$fieldProperty][$fieldVal];
                                }
                            }
                            
                            if ( isset($getFieldsData['fieldsMoney'][$fieldProperty]) ) {
                                if ( !empty( $currencyRelation[$data[$currencyFieldKey]] ) ) {
                                    $fieldVal = (float)$fieldVal . '|' . $currencyRelation[$data[$currencyFieldKey]];
                                    
                                } else {
                                    $fieldVal = (float)$fieldVal . '|UAH';
                                }
                            }
                            $dataToBitrix[$fieldProperty] = $fieldVal;
                        }
                    }
                    
                    if ( isset($objectsList[$poolNames[$data[$propertyRelationsКRev['Пул']]]][$data[$propertyRelationsКRev['ПІБ боржника']]]) ) {
                        $dataToBitrix[130] = $objectsList[$poolNames[$data[$propertyRelationsКRev['Пул']]]][$data[$propertyRelationsКRev['ПІБ боржника']]]; // Объект(ы)
                    }
                    if ( isset($poolNames[$data[$propertyRelationsКRev['Пул']]]) ) {
                        $dataToBitrix[211] = $poolNames[$data[$propertyRelationsКRev['Пул']]]; // Пул
                    }
                    if (isset($level2List[$data[$propertyRelationsКRev['Статья']]])) {
                        $dataToBitrix[133] = $level2List[$data[$propertyRelationsКRev['Статья']]]; // Статья (2 рівень)
                    }
                    $dataToBitrix[122] = $usersList[$data[$propertyRelationsКRev['ФИО']]]; // Відповідальний
                    $dataToBitrix[136] = $categoryList[$data[$propertyRelationsКRev['Категория']]]; // Категория
                    
                    //                             $dataToBitrix[110] = $usersList[$data[$propertyRelationsКRev['ФИО']]]; // Кому выдача
                    //                             $dataToBitrix[111] = $usersList[$data[$propertyRelationsКRev['ФИО']]]; // От кого получение
                    $dataToBitrix[123] = 82; // Чи прийнятий прихід в касу (82 - так)
                    $dataToBitrix[215] = 122; // Статус  (122 - Подтверждено)
                    
                    if ( !empty($data[$propertyRelationsКRev['$']]) ) {
                        $val = (float)str_replace(",",".",$data[$propertyRelationsКRev['$']]);
                        
                        $dataToBitrix[227] = $val; // Сумма
                        $dataToBitrix[121] = $val . '|USD'; // Сума/валюта
                        $dataToBitrix[226] = 147; // Валюта (UAH - 146, USD - 147)
                        
                    } else {
                        $val = (float)str_replace(",",".",$data[$propertyRelationsКRev['грн']]);
                        
                        $dataToBitrix[227] = $val; // Сумма
                        $dataToBitrix[121] = $val . '|UAH'; // Сума/валюта
                        $dataToBitrix[226] = 146; // Валюта (UAH - 146, USD - 147)
                    }
                    
                    
                    //                     $dataToBitrix[141] = $poolId;
                    
                    $arLoadProductArray = Array(
                        "IBLOCK_ID"      => 27,
                        "PROPERTY_VALUES"=> $dataToBitrix,
                        "NAME"           => !empty($data[$propertyRelationsКRev['Примечание']]) ? $data[$propertyRelationsКRev['Примечание']] : 'Без примечания',
                    );
                    
//                     echo '<pre>';
//                     print_r($arLoadProductArray);
//                     echo '</pre>';
                    
                    $PRODUCT_ID = $element->Add($arLoadProductArray);
                    
//                     echo '<pre>';
//                     print_r($PRODUCT_ID);
//                     echo '</pre>';
                    
                    if ( $PRODUCT_ID ) {
                        $results['success']++;
                    } else {
                        $results['error'][$row+1] = $element->LAST_ERROR;
                        $results['errors']++;
                    }
                }
                
                if ( !empty($results['success']) ) {
                    ?>
    				<div class="alert alert-success" role="alert">
    					Успешно импортировано <b><?=$results['success']?></b> строк
    				</div>
    				<?php
                }
                
                if ( !empty($results['error']) ) {
                    ?>
                    <div class="alert alert-danger" role="alert">
                        <h2>Импорт выполнился с ошибками! Количество ошибок: <b><?=$results['errors']?></b></h2>
                        <table
                        	<tr><th>Строка #</th><th>Ошибка</th></tr><?php
                        foreach ( $results['error'] as $row => $errorText ) {
                            ?><tr><td><?=$row?></td><td><?=$errorText?></td></tr><?php
                        }
                        ?></table>
                    </div>
                    <?php
                }
            }
            
            break;
    }
}