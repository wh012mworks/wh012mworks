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
    $fieldsArray = CIBlockProperty::GetList(Array(), Array('IBLOCK_ID' => 29));
    
    while( $field = $fieldsArray->fetch() ) {
        
//         print_r($field);
//         die;
        
//         $propName = 'PROPERTY_' . $field['ID'];
        $propName = $field['ID'];
        
        if ( $field['IS_REQUIRED'] == 'Y' ) {
            $fieldsRequired[$propName] = $propName;
        }
        $fieldsNames[$field['NAME']] = $propName;
        
//         echo '<pre>';
//         print_r($field);
//         echo '</pre>';
        //[USER_TYPE] => Money
        
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
    
    $fieldsArray = CIBlock::GetFields(29);
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

// print_r($_FILES);

$currencyRelation = [
    '980' => 'UAH',
    '840' => 'USD',
    '978' => 'EUR',
];

if ( !empty($_REQUEST['action']) ) {
    switch ($_REQUEST['action']) {
        case 'analyze':
            
            $getFieldsData = getFieldsData();
//             print_r($getFieldsData['fieldsRequired']);
            
            $fileData = csv_to_array($_FILES['file']['tmp_name'], $delimiter=';');
            
//             print_r($fileData['header']);

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
                     
                    $index++;
                    if ( !empty($getFieldsData['fieldsNames'][$header]) ) {
                        
                        $propertyRelations[$key] = $getFieldsData['fieldsNames'][$header];
                        $propertyRelationsКRev[$header] = $key;
                        
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
            
            if ( !isset($propertyRelationsКRev['Contract ID']) ) {
                echo die('Ошибка. В Таблице нет колонки "Contract ID"');
            }
            
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
                
                $uniqueIds = [];
                if ( !empty($fileData['data']) ) {
                    $errorsCount = 0;
                    foreach ( $fileData['data'] as $row => $data ) {
                        
                        $errors = [];
                        
                        $uniqueIds[] = $data[$propertyRelationsКRev['Contract ID']];
                        
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
                        $errors = implode('<br>', $errors);
                        if ( $errors ) {
                            $errorsCount++;
                            $willErrors++;
                            ?><tr><td><?=$row?></td><td><?=$errors?></td></tr><?php
                        } else {
                            $willSuccess++;
                        }
                    }
                }
                
                $numRows = 0;
                if ( !empty($uniqueIds) ) {
                    $sql = "SELECT IBLOCK_ELEMENT_ID,IBLOCK_PROPERTY_ID,VALUE FROM b_iblock_element_property WHERE VALUE IN (".implode(',', $uniqueIds).") AND IBLOCK_PROPERTY_ID = 239";
                    $dbResult = $DB->Query($sql);
                    $numRows  = $dbResult->result->num_rows ? $dbResult->result->num_rows : 0;
                    
                    $elementsIds = [];
                    while ($record = $dbResult->fetch())
                    {
                        $elementsIds[] = $record['IBLOCK_ELEMENT_ID'];
                    }
                    
                    if ( !empty($_REQUEST['pool']) && !empty($elementsIds) ) {
                        $poolId = $_REQUEST['pool'];
                        
                        $sql = "SELECT IBLOCK_ELEMENT_ID,IBLOCK_PROPERTY_ID,VALUE FROM b_iblock_element_property WHERE IBLOCK_ELEMENT_ID IN (".implode(',', $elementsIds).") AND IBLOCK_PROPERTY_ID = 141 AND VALUE='{$poolId}'";
                        $dbResult = $DB->Query($sql);
                        $numRows  = $dbResult->result->num_rows ? $dbResult->result->num_rows : 0;
                    }
                    

                    
                }
                
                if ( !$errorsCount ) {
                    ?><tr><td></td><td>Ошибок не найдено</td></tr><?php
                }
                ?>
            </table>
            <br>
            <div class="alert alert-success" role="alert">
          		Будет загружено без ошибок<b><?=$willSuccess?></b><br>
          		Будет обновлено <b><?=$numRows?></b> элементов<br>
            </div>
            <div class="alert alert-danger" role="alert">
          		Будет загружено с ошибками: <b><?=$willErrors?></b> 
            </div>
                
            <?php
            if ( $hasErrors || $errorsCount ) {
                ?><button class="ui-btn ui-btn-danger-light" id="btnImport">Все равно импортировать</button><?php
            } else {
                ?><button class="ui-btn ui-btn-success" id="btnImport">Импортировать</button><?php
            }

            break;
            
        case 'import':
            
            if ( !empty($_REQUEST['pool']) ) {
                $poolId = $_REQUEST['pool'];
                
                $getFieldsData = getFieldsData();
                $fileData      = csv_to_array($_FILES['file']['tmp_name'], $delimiter=';');
                
                $hasErrors = 0;
                $headersValidators = '';
                $currencyFieldKey = '';
                $index = 0;
                $propertyRelations = [];
                $propertyRelationsКRev = [];
                if ( !empty($fileData['header']) ) {
                    foreach ( $fileData['header'] as $key => $header ) {
                        $isRequired = '';
                        
                        
                        if ( in_array($header, ["Валюта зобов'язання", 'Валюта зобовязання', 'Валюта кредиту']) ) {
                            $currencyFieldKey = $key;
                        }
                        
                        $index++;
                        if ( !empty($getFieldsData['fieldsNames'][$header]) ) {
                            $propertyRelations[$key] = $getFieldsData['fieldsNames'][$header];
                            $propertyRelationsКRev[$header] = $key;
                        } else {
                            $hasErrors++;
                        }
                    }
                }
                
                    
                global $DB;
                $element = new CIBlockElement;
                
                $uniqueIds         = [];
                $updateElementsIds = [];
                
                $results = [
                    'success' => 0,
                    'errors' => 0,
                ];
                if ( !empty($fileData['data']) ) {
                    $errorsCount = 0;
                    foreach ( $fileData['data'] as $row => $data ) {
                        $uniqueIds[] = $data[$propertyRelationsКRev['Contract ID']];
                    }
                    $numRows = 0;
                    if ( !empty($uniqueIds) ) {
                        $sql = "SELECT IBLOCK_ELEMENT_ID,IBLOCK_PROPERTY_ID,VALUE FROM b_iblock_element_property WHERE VALUE IN (".implode(',', $uniqueIds).") AND IBLOCK_PROPERTY_ID = 239";
                        $dbResult = $DB->Query($sql);
                        
                        $elementsIds  = [];
                        while ($record = $dbResult->fetch())
                        {
                            $elementsIds[] = $record['IBLOCK_ELEMENT_ID'];
//                             $updateElementsIds[$record['VALUE']] = $record['IBLOCK_ELEMENT_ID'];
                        }
                        
                        if ( !empty($_REQUEST['pool']) && !empty($elementsIds) ) {
                            $poolId = $_REQUEST['pool'];
                            
                            $sql = "SELECT IBLOCK_ELEMENT_ID,IBLOCK_PROPERTY_ID,VALUE FROM b_iblock_element_property WHERE IBLOCK_ELEMENT_ID IN (".implode(',', $elementsIds).") AND IBLOCK_PROPERTY_ID = 141 AND VALUE='{$poolId}'";
                            $dbResult = $DB->Query($sql);
                            while ($record = $dbResult->fetch())
                            {
                                $updateElementsIds[$record['VALUE']] = $record['IBLOCK_ELEMENT_ID'];
                            }
                        }
                    }
                    
                    foreach ( $fileData['data'] as $row => $data ) {
                        $dataToBitrix = [];
                        foreach ( $data as $key => $fieldVal ) {
                            
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
                        $dataToBitrix[141] = $poolId;
                        
                        if ( !empty($updateElementsIds[$data[$propertyRelationsКRev['Contract ID']]]) ) {
//                             $arLoadProductArray = Array(
//                                 "PROPERTY_VALUES"=> $dataToBitrix,
//                                 "NAME"           => "Элемент",
//                             );
                            
//                             $res = $element->Update($updateElementsIds[$data[$propertyRelationsКRev['Contract ID']]], $arLoadProductArray);
//                             if ( $res ) {
//                                 $results['success']++;
//                             } else {
//                                 $results['error'][$row+1] = $element->LAST_ERROR;
//                                 $results['errors']++;
//                             }
                            
                            foreach ( $dataToBitrix as $propKey => $val ) {
                                $res = $element->SetPropertyValuesEx($updateElementsIds[$data[$propertyRelationsКRev['Contract ID']]], false, array($propKey => $val));
//                                 if ( $res ) {
                                    $results['success']++;
//                                 } else {
//                                     $results['error'][$row+1] = $element->LAST_ERROR;
//                                     $results['errors']++;
//                                 }
                            }
                            
                        } else {
                            $arLoadProductArray = Array(
                                "IBLOCK_ID"      => 29,
                                "PROPERTY_VALUES"=> $dataToBitrix,
                                "NAME"           => !empty($dataToBitrix['NAME']) ? $dataToBitrix['NAME'] : $data[$propertyRelationsКRev['Contract ID']],
                            );
                            
//                             echo '<pre>';
//                             print_r($arLoadProductArray);
//                             echo '</pre>';
                            $PRODUCT_ID = $element->Add($arLoadProductArray);
                            if ( $PRODUCT_ID ) {
                                $results['success']++;
                            } else {
                                $results['error'][$row+1] = $element->LAST_ERROR;
                                $results['errors']++;
                            }
                        }
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
            } else {
                ?>
                <div class="alert alert-danger" role="alert">
                    <h2>Не выбран пул!</h2>
                </div>
                <?php
            }
            
            break;
    }
}