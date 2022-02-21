<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

CJSCore::Init(array("jquery","date"));


$APPLICATION->SetAdditionalCSS("/bitrix/components/bitrix/ui.sidepanel.wrapper/templates/.default/template.min.css");
$APPLICATION->SetAdditionalCSS("/bitrix/components/bitrix/main.ui.grid/templates/.default/style.min.css");
$APPLICATION->SetAdditionalCSS("/bitrix/js/ui/buttons/ui.buttons.min.css");
$APPLICATION->SetAdditionalCSS("/bitrix/components/bitrix/crm.entity.editor/templates/.default/style.min.css");
$APPLICATION->SetAdditionalCSS("/bitrix/js/crm/css/crm.min.css");

$APPLICATION->SetAdditionalCSS("/local/css/main.css");

$APPLICATION->SetAdditionalCSS("https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css");
$APPLICATION->AddHeadScript("https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js");
$APPLICATION->AddHeadScript("https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js");
$APPLICATION->AddHeadScript("https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js");


$APPLICATION->AddHeadScript("https://cdn.jsdelivr.net/momentjs/latest/moment.min.js");
$APPLICATION->AddHeadScript("https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js");
$APPLICATION->SetAdditionalCSS("https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css");


$APPLICATION->SetAdditionalCSS($this->GetFolder() . "/css/select2.css");
$APPLICATION->AddHeadScript($this->GetFolder() . "/js/select2.js");

$APPLICATION->SetAdditionalCSS($this->GetFolder() . "/css/ui-slidepanel-wrapper.css");
$APPLICATION->SetAdditionalCSS($this->GetFolder() . "/css/main-ui-grid.css");
$APPLICATION->SetAdditionalCSS($this->GetFolder() . "/css/ui.buttons.css");
$APPLICATION->SetAdditionalCSS($this->GetFolder() . "/css/crm-entity-editor.css");
$APPLICATION->SetAdditionalCSS($this->GetFolder() . "/css/crm.css");


function isAssoc(array $arr) {
    if (array() === $arr) return false;
    return array_keys($arr) !== range(0, count($arr) - 1);
}

$rowsTotals = array();

$fieldsEnums = [];
$fieldsNames = [];
$fieldsArray = CIBlockProperty::GetList(Array(), Array('IBLOCK_ID' => 29));

while( $field = $fieldsArray->fetch() ) {
    $fieldsNames[$field['NAME']] = 'PROPERTY_' . $field['ID'];
    
    if ( !empty($field['PROPERTY_TYPE']) && $field['PROPERTY_TYPE'] == 'L' ) {
        $GetPropertyEnum = CIBlockProperty::GetPropertyEnum($field['ID']);
        while( $fieldData = $GetPropertyEnum->fetch() ) {
            $fieldsEnums['PROPERTY_' . $fieldData['PROPERTY_ID']][$fieldData['VALUE']] = $fieldData['ID'];
        }
    }
}

$fieldsArray = CIBlock::GetFields(29);
foreach ($fieldsArray as $key => $field) {
    $fieldsNames[$field['NAME']] = $key;
}
// echo '<pre>';
// print_r($fieldsNames);
// echo '</pre>';


$arSelect = Array(
    'ID',
    'NAME',
);
$arFilter = Array(
    "IBLOCK_ID" => 26,
);
$listDb = CIBlockElement::GetList(array(), $arFilter, false, array(), $arSelect);

$options = '';
while ( $record = $listDb->fetch() ) {
    $options .= '<option value="'.$record['ID'].'">'.$record['NAME'].'</option>';
}
?>

<html>
	<head>
		
    	<style>
            .ul_buttons li {
                display:inline;
            }
            ul {
                padding-inline-start: 0px !important;
            }
            #result table, #result th, #result td {
                border: 1px solid black;
                border-collapse: collapse;
            }
    	</style>
	</head>
    <body>
        <div style="margin: 10px 10px 10px 10px;">
        
			<form id="create_block" enctype="multipart/form-data">
				<div class="row">
    				<div class="col-auto"><label>Пул:</label></div>
    				<div class="col">
        				<select class="crm-entity-widget-content-select selectpicker" id="pool" name="pool"><?=$options?></select>
    				</div>
				</div>
                <div class="row" id="additional_fields">
                	<div class="col-auto crm-entity-widget-content-block-field-container">
                    	<label class="ui-btn ui-btn-icon-page">
                        	<input type="file" style="border-width: 0px;" id="import_file" name="import_file"/>
                        </label>
                    </div>
                    <div class="col-auto">
           				<button type="button" class="ui-btn ui-btn-primary" id="btn_analyze">Анализировать</button>
       				</div>
					<div class="col-auto">
       					<span>(<a href="https://bitrix.dfg.com.ua/local/components/auspex/pool.import/mega_pool3.csv">Пример CSV файла</a>)</span>
   				 	</div>
                </div>
                <div id="result"></div>
                
			</form>
        <br>
        
        
   		<div id="wait" style="display:none; position: fixed; top: 0; right: 0; bottom: 0; z-index: 90; left: 0; background: #e9e9e9; opacity: 0.5;">
            <div id="loading-img" style="display: table; margin: 0 auto; margin-top: 250px;">
            	<img src="https://bitrix.dfg.com.ua/local/components/auspex/pool.form/templates/.default/images/loader.gif" width="64" height="64" />
            </div>
        </div>
            
        <script type="text/javascript">
        
            $(document).ready(function() {


            	$(document).ajaxStart(function(){
        		    $("#wait").css("display", "block");
        		});
        		$(document).ajaxComplete(function(){
        		    $("#wait").css("display", "none");
        		});

            	$('.selectpicker').select2({
        	    	placeholder: 'Выбрать'
        	    });

            	$(document).on('click', '#btn_analyze', function (e) {
                    e.preventDefault();

                    var fd = new FormData();
                    var files = $('#import_file')[0].files;

                    if(files.length > 0 ){
                        fd.append('file',files[0]);
                    }
                    fd.append('pool', $('#pool').find(":selected").val());
                    
                    $.ajax({
             		   	type: "POST",
             		   	url: '<?=$this->GetFolder()?>/ajax.php?action=analyze',
						data: fd,
		              	contentType: false,
		              	processData: false,
             		   	success: function(data)
             		   	{
//              		   		console.log(data);
							$('#result').html(data);
             			}
             	 	});
              	});

            	$(document).on('click', '#btnImport', function (e) {
                    e.preventDefault();
                    console.log('test');

                    if (confirm('Вы действительно хотите импортировать эту таблицу?')) {
                    	var fd = new FormData();
                        var files = $('#import_file')[0].files;

                        if(files.length > 0 ){
                            fd.append('file',files[0]);
                        }
                        fd.append('pool', $('#pool').find(":selected").val());
                        
                        $.ajax({
                 		   	type: "POST",
                 		   	url: '<?=$this->GetFolder()?>/ajax.php?action=import',
    						data: fd,
    		              	contentType: false,
    		              	processData: false,
                 		   	success: function(data)
                 		   	{
//                  		   		console.log(data);
    							$('#create_block').html(data);
                 			}
                 	 	});
                    } else {
                        
                    }
                    
              	});
        
            });
        </script>
    </body>
</html>
