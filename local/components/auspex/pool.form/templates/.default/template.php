<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

CJSCore::Init(array("jquery","date"));


// $APPLICATION->SetAdditionalCSS($this->GetFolder()."/css/bootstrap.css");
// $APPLICATION->SetAdditionalCSS($this->GetFolder()."/css/checkbox_radio_bootstrap.css");

// echo '<pre>';
// print_r($this->GetFolder());
// echo '</pre>';

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
    	</style>
	</head>
    <body>
        <div style="margin: 10px 10px 10px 10px;">
        
            <table style="width: 100%;">
            	<tr>
            		<td style="width: 40%;">
            			<div class="container">
                			<table style="width: 100%;" class="table table-bordered">
                				<?php if ( !empty($arResult['getCateroriesSum']['currencyList']) ) { 
                				    
                				    $currencyTotals = array();
                				    ?>
                					<tr>
                						<th></th>
                    					<?php foreach ( $arResult['getCateroriesSum']['currencyList'] as $currency ) { ?>
            								<th><?=$currency?></th>
                    					<?php } ?>
                					</tr>
                					
                					<?php foreach ( $arResult['getCateroriesSum']['totalsArray'] as $categoryId => $prices ) { ?>
                    					<tr>
                    						<th><?=$arResult['level1Array'][$categoryId]?></th>
                        					<?php foreach ( $arResult['getCateroriesSum']['currencyList'] as $currency ) { 
                        					    
//                         					    echo '<pre>';
//                         					    print_r($currency);
//                         					    echo '</pre>';
                        					
                        					    $price = 0;
                        					    if ( !empty($prices[$currency]) ) {
                        					        $price = $prices[$currency];
                        					    } else {
                        					        if ( $categoryId == 299 ) {
                        					            
                        					            $out = !empty($prices['out'][$currency]) ? $prices['out'][$currency] : 0;
                        					            $in = !empty($prices['in'][$currency]) ? $prices['in'][$currency] : 0;
                        					            
                        					            $price = $in - $out;
                        					        }
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
                        					    
                        					    
                        					    if ( !isset($currencyTotals[$currency]) ) {
                        					        $currencyTotals[$currency] = 0;
                        					    }
                        					    
                        					    $currencyTotals[$currency] += $price;
                        					    ?>
                								<td><?=number_format($price,2,"."," ")?></td>
                        					<?php } ?>
                    					</tr>
                					<?php } ?>
                					
                					<tr>
                						<th>Всего</th>
                						<?php foreach ( $arResult['getCateroriesSum']['currencyList'] as $currency ) { ?>
            								<th><?=!empty( $currencyTotals[$currency] ) ? number_format($currencyTotals[$currency],2,"."," ") : number_format(0,2,"."," ")?></th>
                    					<?php } ?>
                					</tr>
                					
                				<?php } ?>
                			</table>
            			</div>
            		</td>
            		<td style="width: 60%;">
            			<form id="create_block" enctype="multipart/form-data">
            				<div class="row">
                                <div class="col">
                                	<label>
                                		<div class="crm-entity-widget-content-block-title">
                               				<span class="crm-entity-widget-content-block-title-text">Категории</span>
                               			</div>
                           			</label>
                                    <div class="crm-entity-widget-content-block-inner">
                               			<div class="crm-entity-widget-content-block-field-container" id="level1_block">
                               				<ul class="ul_buttons">
                                        	<?php foreach ( $arResult['level1Array'] as $categoryId => $categoryName ) { 
                                        	
                                        	    if ($categoryId == 177) {
                                        	        continue;
                                        	    }
                                        	    
                                    	    ?>
                                        		<li><a href="#" class="ui-btn ui-btn-primary select_category" style="margin-top: 5px;" data-categoryid="<?=$categoryId?>"><?=$categoryName?></a></li>
                                        	<?php } ?>
                                        	</ul>
                           				</div>
                           				
                               			<div class="crm-entity-widget-content-block-field-container" id="level2_block" style="display: none;">
                               				<select class="crm-entity-widget-content-select" id="level2" name="PROPERTY_133">
                                            	<option value="">- не выбрано -</option>
                               				</select>
                           				</div>
                           				
                               			<div class="crm-entity-widget-content-block-field-container" id="level3_block" style="display: none;">
                               				<select class="crm-entity-widget-content-select" id="level3" name="PROPERTY_134">
                                            	<option value="">- не выбрано -</option>
                               				</select>
                           				</div>
                           			</div>
                                </div>
                            </div>
                            <input type="hidden" value="" name="PROPERTY_136" id="PROPERTY_136">
                            <div id="additional_fields">
                            <?php 
                            $options = '';
                            $options .= '<option value="1">Обєкт132 | ИНН:1112222 | Менеджер: Мелькик Роман</option>';
                            $options .= '<option value="2">Обєкт133 | ИНН:1113333 | Менеджер: Мелькик Роман</option>';
                            $options .= '<option value="3">Обєкт134 | ИНН:3333222 | Менеджер: Мелькик Роман</option>';
                            $options .= '<option value="4">Обєкт135 | ИНН:8888888 | Менеджер: Мелькик Роман</option>';
                            
//                             echo '<select class="selectpicker" id="PROPERTY_1231" style="width: 100%;" name="PROPERTY_1231" multiple>' . $options . '</select>';
                            ?>
                            </div>
                            <div class="row">
                            	<div class="col">
                                	<label>
                                		<div class="crm-entity-widget-content-block-title">
                               				<span class="crm-entity-widget-content-block-title-text">Примечание</span>
                               			</div>
                           			</label>
                                    <div class="crm-entity-widget-content-block-inner">
                               			<div class="crm-entity-widget-content-block-field-container">
                               				<textarea class="crm-entity-widget-content-textarea" id="comment" name="NAME"></textarea>
                           				</div>
                           			</div>
                       			</div>
                            </div>
                            <div class="row">
                                <div class="col">
                       				<button type="button" class="ui-btn ui-btn-success" id="btnCreate">Применить</button>
                   				</div>
               				</div>
           				</form>
            		</td>
            	</tr>
            </table>
            
        </div>
        <br>
            
        <script type="text/javascript">
        
            var level1Json = '<?=$arResult['level1Json']?>';
            var level2Json = '<?=$arResult['level2Json']?>';
            var level3Json = '<?=$arResult['level3Json']?>';
            var additionlFieldJson = '<?=$arResult['additionlFieldJson']?>';
            
            level1Json = JSON.parse(level1Json);
            level2Json = JSON.parse(level2Json);
            level3Json = JSON.parse(level3Json);
            additionlFieldJson = JSON.parse(additionlFieldJson);


            function calcByCourse()
            {
            	var currency = $('#PROPERTY_121_currency').find(":selected").val();
        		var course = parseFloat($('#PROPERTY_112').val());
        		var exVal  = parseFloat($('#PROPERTY_121_opportunity').val());
            
            	 $.ajax({
          		   	type: "POST",
          		   	url: '<?=$this->GetFolder()?>/ajax_calc_course.php',
          		   	data: {
              		   	currency : currency,
						course : course,
              			exVal : exVal,
          		   	},
          		   	success: function(data)
          		   	{
                		$('#exchange_result').html(data);
          			}
          	 	});
            }
            
            $(document).ready(function() {

            	$('.selectpicker').select2({
        	    	placeholder: 'Выбрать'
        	    });

            	$(document).on('click', '.select_category', function (e) {
                    e.preventDefault();
        
                     var level1 = $(this).data('categoryid');
                     $('#PROPERTY_136').val(level1);

                     $('#level3').empty();
                     $('#level2').empty();
                     $('#level3_block').hide();
                     $('#level2_block').hide();
                     
                     if (typeof window['level2Json'][level1]  !== "undefined") {
                         $('#level2_block').show();
                    	 $('#level2').append('<option value="">- не выбрано -</option>');
                         $.each(window['level2Json'][level1], function (key, val) {
                             $('#level2').append('<option value="'+key+'">'+val+'</option>');
                         });
                     }

                     if (typeof window['additionlFieldJson'][level1]  !== "undefined") {

                         $.ajax({
                 		   	type: "POST",
                 		   	url: '<?=$this->GetFolder()?>/ajax_get_field_html.php?category=' + level1,
                 		   	data: window['additionlFieldJson'][level1],
                 		   	success: function(data)
                 		   	{
                 		   		$('#additional_fields').html(data);
                     		   	$('.selectpicker').select2({
                        	    	placeholder: 'Выбрать'
                        	    });
                 			}
                 	 	});
                     } else {
                    	 $('#additional_fields').html('<p>В выбранной категории нет полей</p>');
                     }
              	});


            	$(document).on('keyup', '#PROPERTY_112, #PROPERTY_121_opportunity', function (e) {
            		calcByCourse();
            	});

            	$(document).on('change', '#PROPERTY_121_currency', function (e) {
            		calcByCourse();
            	});

            	$(document).on('change', '#level1', function (e) {
                    e.preventDefault();
        
                     var level1 = $("#level1 option:selected").val();

//                      console.log(level1);
//                      console.log(window['level2Json'][level1]);

                     $('#level2').empty();
                     $('#level2_block').hide();
                     
                     if (typeof window['level2Json'][level1]  !== "undefined") {
                         $('#level2_block').show();
                    	 $('#level2').append('<option value="">- не выбрано -</option>');
                         $.each(window['level2Json'][level1], function (key, val) {
                             $('#level2').append('<option value="'+key+'">'+val+'</option>');
                         });
                     }

                     if (typeof window['additionlFieldJson'][level1]  !== "undefined") {

                         $.ajax({
                 		   	type: "POST",
                 		   	url: '<?=$this->GetFolder()?>/ajax_get_field_html.php',
                 		   	data: window['additionlFieldJson'][level1],
                 		   	success: function(data)
                 		   	{
                 		   		$('#additional_fields').html(data);
                 			}
                 	 	});
                     }

                     
              	});

				$(document).on('click', '#btnCreate', function (e) {
                    e.preventDefault();

                    var formData = new FormData(document.getElementById("create_block"));
                    console.log(formData);
                     $.ajax({
             		   	type: "POST",
             		   	url: '<?=$this->GetFolder()?>/ajax_create.php',
//              		   	data: $('#create_block').serialize(),
             		   	data: formData,
             	        contentType: false,
             	        processData: false,
             		   	success: function(data)
             		   	{
                 		   	console.log(data);
                 		   	if ( data ) { 
             		   			alert(data);
                 		   	} else {
                 		   		location.reload();
                 		   	}
             			}
             	 	});
                     
              	});

				$(document).on('change', '#PROPERTY_211', function (e) {
                    e.preventDefault();

                    $.ajax({
             		   	type: "POST",
             		   	url: '<?=$this->GetFolder()?>/ajax_get_field_html.php',
             		   	data: {
							action: 'get_objects',
							value: $(this).val(),
             		   	},
             		   	success: function(data)
             		   	{
             		   		$('#PROPERTY_130').html(data);
                 		   	$('.selectpicker').select2({
                    	    	placeholder: 'Выбрать'
                    	    });
             			}
             	 	});
                     
              	});


            	$(document).on('change', '#level2', function (e) {
                    e.preventDefault();


            		var level1 = $("#level1 option:selected").val();
                 	var level2 = $("#level2 option:selected").val();

                 	console.log('level1 : ' + level1);
                 	console.log('level2 : ' + level2);
//              		console.log(window['level3Json'][level1][level2]);

                 	$('#level3').empty();
                 	$('#level3_block').hide();
                     
                 	if (typeof window['level3Json'][level2] !== "undefined") {

                 		console.log(window['level3Json'][level2]);

                     	
                     	$('#level3_block').show();
                	 	$('#level3').append('<option value="">- не выбрано -</option>');
                     	$.each(window['level3Json'][level2], function (key, val) {
                      	   $('#level3').append('<option value="'+key+'">'+val+'</option>');
                     	});

                 	}
              	});
        
            });
        </script>
    </body>
</html>
