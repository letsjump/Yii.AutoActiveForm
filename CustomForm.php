<?php

/**
 * Yii-AutoActiveForm
 * A faster way to setup form fields in Yii Framework 1.1
 *
 * @author  Gianpaolo Scrigna <letsjump@gmail.com>
 * @link    http://github.com/letsjump/Yii.AutoActiveForm
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @version $Id: CustomForm.php 2 2014-11-01 16:15 letsjump $
 * PUT THIS FILE IN YOUR
 */

/**
 * Put this file in your application.components directory
 * then extend the "form" widget in your views:
 *
 * $form=$this->beginWidget('application.components.CustomForm',
 * 		array(
 * 		... your form settings
 * 		)
 * );
 *
 */

Yii::import('ext.autoActiveForm.AutoActiveForm');

class CustomForm extends AutoActiveForm
{
    public $htmlOptions = Array();
	
	public function init() {
		parent::init();
		// path to field templates
		$this->viewFile = 'ext.autoActiveForm.views.default';
	}
	
	public function run() {
		parent::run();
	}
	
	public function autoComplete($model, $attribute, $data=Array(1), $htmlOptions=Array())
    {
        $method = Array('self', 'complete');
        return $this->buildField($method, $model, $attribute, $data, $htmlOptions);
    }

    public function complete()
    {
	    // start rendering object
        $model = $this->params->model;
        $attribute = $this->params->attribute;
        $data = $this->params->data;
        $modelName = get_class($model);
        $htmlOptions = $this->params->htmlOptions;
        $hiddenId = (empty($model->$attribute)) ? "" : $model->$attribute;

	    $jsSelect = <<<eom
		    function(event, ui){
		        $("#{$modelName}_{$this->params->attribute}").val(ui.item.id);
		        $(".ui-helper-hidden-accessible").hide();
		    }
eom;
	    $jsOpen = <<<eom
			function (e, ui) {
	            var acData = $(this).data('autocomplete');
	            acData
	                .menu
	                .element
	                .find('a')
	                .each(function () {
	                    var me = $(this);
	                    var keywords = acData.term.split(' ').join('|');
	                    me.html(me.text().replace(new RegExp('(' + keywords + ')', 'gi'), '<b>$1</b>'));
	                });
            }
eom;
	    $jsFooter = <<<eom
			//var termTemplate = "<span class='ui-autocomplete-term'>%s</span>";
			$( "#{$this->params->attribute}_autoc" )
				.data( "autocomplete" )
				._renderItem = function( ul, item ) {
					return $( "<li>" )
						.attr( "data-value", item.value )
						.data( "item.autocomplete", item )
						.append( "{$this->params->itemTemplate}" )
						.appendTo( ul );
			};
			$( "#{$this->params->attribute}_reset" ).click(function() {
				$("#{$this->params->attribute}_autoc").val("");
				$("#{$modelName}_{$this->params->attribute}").val("");
                return false;
			});
eom;
	    $defaultOptions = array(
		    'minLength'=>'1',
		    //'open'=> 'js:function(){ $(this).autocomplete(\'widget\').css(\'z-index\', 1199); return false; }',
		    'select' => new CJavaScriptExpression($jsSelect),
		    //'open' => new CJavaScriptExpression($jsOpen)
	    );

	    $options = (is_array($this->params->aaf_options)) ? CMap::mergeArray($defaultOptions, $this->params->aaf_options) : $defaultOptions;

        //$field = $this->hiddenField($model,$attribute, Array('id'=>'hidden_'.$attribute, 'value'=>$hiddenId));
        $field = $this->hiddenField($model,$attribute, Array('value'=>$hiddenId));
        $field .= $this->widget('zii.widgets.jui.CJuiAutoComplete', array(
            'name'=>$attribute . "_autoc",
            'value'=>$this->params->roValue,
            //'cssFile'=>false,
            'htmlOptions'=>$htmlOptions,
            'sourceUrl'=>$data,
            'options'=>$options,
        ), true);
	    $field .= <<<eom
			<span class="reset-button">
				<a id="{$attribute}_reset" href="#" title="cancella il campo">
					<i class="fa fa-times"></i>
				</a>
			</span>

eom;
	    Yii::app()->clientScript->registerScript($attribute . '_autoc_js', $jsFooter, CClientScript::POS_READY);
        return $field;
    }

    public function autoDatePicker($model, $attribute, $htmlOptions=Array())
	{
        $method = Array('self', 'datePicker');
        return $this->buildField($method, $model, $attribute, NULL, $htmlOptions);
    }

    protected function datePicker()
    {
		return $this->widget('zii.widgets.jui.CJuiDatePicker',array(
                'model'=>$this->params->model,
                'attribute'=>$this->params->attribute,
                'htmlOptions'=>$this->params->htmlOptions,
				'cssFile'=>false,
				'language'=>'it',
                'options'=>$this->params->aaf_options
			), true
		);
	}


	public function autoTinyMce($model, $attribute, $htmlOptions=Array())
	{
		$method = Array('self', 'tinyMce');
		return $this->buildField($method, $model, $attribute, NULL, $htmlOptions);
	}

    protected function tinyMce()
	{
		return $this->widget('application.extensions.KRichTextEditor', array(
                            'model' =>        $this->params->model,
                            //'value' =>        $this->params->model->isNewRecord ? '' : $this->params->model->$attribute,
                            'attribute' =>    $this->params->attribute,
                            'options' =>    $this->params->aaf_options
						), true
		);
	}

	public function autoChosen($model, $attribute, $data, $htmlOptions=Array())
	{
		$method = Array('self', 'ChosenMultiSelect');
		return $this->buildField($method, $model, $attribute, $data, $htmlOptions);
	}

    protected function ChosenMultiSelect()
	{
		Yii::import('application.extensions.yii-chosen.Chosen');
        return Chosen::activeMultiSelect($this->params->model, $this->params->attribute, $this->params->data, $this->params->htmlOptions);
	}

	public function error($model, $field, $htmlOptions = Array(), $enableAjaxValidation = true, $enableClientValidation = true)
	{
		return str_replace('div', 'span', parent::error($model, $field, $htmlOptions, $enableAjaxValidation, $enableClientValidation));
	}
	
	public function errorSummary($models,$header=null,$footer=null,$htmlOptions=array())
	{
		    if(!$this->enableAjaxValidation && !$this->enableClientValidation)
        return CHtml::errorSummary($models,$header,$footer,$htmlOptions);

			if(!isset($htmlOptions['id']))
				$htmlOptions['id']=$this->id.'_es_';
			$htmlOptions['class']=CHtml::$errorSummaryCss;
			if($header===null)
				$header='<p>'.Yii::t('yii','Please fix the following input errors:').'</p>';
			$htmlOptions['style']=isset($htmlOptions['style']) ? rtrim($htmlOptions['style'],';').';display:none' : 'display:none';


			return CHtml::tag('div',$htmlOptions,$header.$footer);

	}
}

?>
