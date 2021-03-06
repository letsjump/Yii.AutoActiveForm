# AutoActiveForm

In a web full of *complex html form templates*, this extension provides a **faster way to draw fields** in Yii Framework 1.1.
It also has an **access control system** in order to give *read / hide / write*  access to every field according to *Yii RBAC Access Rules*.

## Simple example
this code
```php
echo $form->autoTextField($model, 'Name');
```
generates this html
```html
<div class="fieldcontainer">
    <label for="Model_name" class="required">
        Oggetto 
        <span class="required">*</span>
    </label>
    <span class="field">
    		<input name="Model[name]" id="Model_name" type="text" maxlength="255" value="" />
    		<span class="errorMessage" id="Model_name_em_" style="display:none"></span>
    </span>
</div>
```
and this code
```php
echo $form->autoDatePicker($model,'cr_date');
```
generates
```html
<div class="fieldcontainer">
	<label for="Model_cr_date">Data Creazione</label>
	<span class="field">
		<input id="Model_cr_date" name="Model[cr_date]" type="text" value="05/07/2014" />
		<span class="errorMessage" id="Model_cr_date_em_" style="display:none"></span>
	</span>
</div>
<script type="text/javascript">
/*<![CDATA[*/
jQuery('#Model_cr_date').datepicker(jQuery.extend({showMonthAfterYear:false},jQuery.datepicker.regional['it'],null));
/*]]>*/
</script>
```
## Benefits
+ 	It extends `CActiveForms` native method so you can invoke any field (like `$form->inputField`, 	`$form->passwordField`, `$form->checkbox` and so on...) just adding the `auto` prefix. Example: `$form->textArea(...)` becomes `$form->autoTextArea(...)`
+ 	**It can be extended** so you can add configuration for third party *extension* like **Chosen** or **TinyMce** with just some line of code in your *CustomForm.php* configuration file
+ 	You can change configuration **globally** or locally: just **one form** or just **one field**.
+	Field templates are mastered as normal **Yii view files**
+ 	You can also use `CActiveForm` native method, EG: `$form->label(...)`, `$form->textField(...)` and `$form->error(...)`
+ 	A complete **access control** to draw field according with **user permissions**

## Usage
- 	put AutoActiveForm folder in your *ext* directory
- 	copy *CustomForm.php* in your components directory and configure it
- 	modify `Yii::import('ext.autoActiveForm.AutoActiveForm')` according to fit the extension's alias path
- 	call Yii ActiveForm widget with the path of your custom form:
```php
$form=$this->beginWidget('application.components.CustomForm',
	array(
	...
	)
);
```
- 	call any *CActiveForm field* method adding the `active` prefix
```php
$form->autoTextField($model, 'surname');
$form->autoPassword($model, 'pass');
$form->autoCheckBox($model, 'yes_no');
$form->autoDropDownList($model, ',my_list', Array(1=>'One', 2=>'Two'));
```
In addiction, it is strongly recommended to copy the *view* folder that contains your field template into your *theme* folder and configure `$this->viewPath` value according to its path:
 ```php
 class CustomForm extends AutoActiveForm
 {
 	public function init() {
     		$this->viewPath = 'my.theme.folder.my.autoactiveform-view.folder'
 	...
 ```
Doing so, your work will not be deleted after new **AutoActiveForm** plugin releases.
## Advanced fields configuration
### Add field HTML options
As in CActiveForm you'll pass configurations using `$htmlOptions` array:
```php
$form->autoTextField($model, 'surname', Array('class'=>'oh_my_god', 'style'=>'color: green'));
```
### Add label or error HTML options
If you need to pass configurations to the *label* or *error* HTML tag, *AutoActiveForm* provides two special array of parameters inside `$htmlOptions`:
- `labelHtmlOptions` (array)
- `errorHtmlOptions` (array)

Example:
```php
$form->autoTextField($model, 'gender', Array('labelHtmlOptions'=>Array('class'=>'required'));
$form->autoTextField($model, 'age', Array('errorHtmlOptions'=>Array('class'=>'blink'));
```
### Additional field configuration settings
In addition to `labelHtmlOptions` and `errorHtmlOptions`, you can pass some other array of parameters to `$htmlOptions`:
- `jsOptions`: 	(array) special options used in complex jQuery fields like TinyMce
- `roValue`: 	(string) read only value for this field
- `viewFile`: 	(string) alternative view just for this field

### Form configurations
*AutoActiveForm* has some special configuration that changes the default behavior:
- `$viewPath`: 			(string) path alias to the view's folder
- `$viewFile`: 			(string) name of the default view
- `$accessControl`: 		(bool) enable / disable access control
- `$addHtmlOptions`:		(array) add an array of $htmlOptions to every field of the form
- `$allowAction`:			(string) action to perform when access control is disabled. Usually the action `write` that draw a form field with its label and its error tag
- `$showLabels`:			(bool) enable / disable the label generator. Usually set to false when use placeholder
- `$labelToPlaceholder`:	(bool) automatically generates a `placeholder=""` attribute with the label's text for every field

#### Set form configurations globally
To set configurations globally, you'll have to act in the `init()` method of your `customForm` class:
```php
class CustomForm extends AutoActiveForm
{
	public function init() {
    		// set here your global configurations
			$this->formParameterName = your global value
			parent::init();
    	}
    	...
    }
    ...
}
```

#### Set form configurations locally (this form)
To set a form parameter just for a form, there is an `$autoActiveForm` array to set in its form `$this->beginWidget()` configuration:
```php
$form=$this->beginWidget('application.components.customForm',
		array(
		'id'=>'my-automatic-form',
		'autoActiveForm'=>Array(
			// add here some configurations just for this form
			'accessControl' => true,
			...
		),
		'enableAjaxValidation'=>true,
		'htmlOptions' => array(
			'class'=>'stdform'),
		)
	);
```
If you also need to add some $htmlOptions to every field of this form, there is a special `addHtmlOptions` parameter:
```php
$form=$this->beginWidget('application.components.customForm',
		array(
		'id'=>'my-coloured-form',
		'autoActiveForm'=>Array(
			// add here some configurations just for this form
			'addHtmlOptions'=>Array('class'=>'green_border')
		),
		'enableAjaxValidation'=>true,
		'htmlOptions' => array(
			'class'=>'stdform'),
		)
	);
```
In the above example, every field of the generated form will have the html property `class="green-border"` automagically setted.
## Access Control
In order to give access control to the fields, you must create the `getFieldAccessRules()` method in its `CActiveRecord` parent model.
`getFieldAccessRules()` returns a *multidimensional array* similar to `CActiveRecord`'s `rules()` method.<br/>This array is composed by a *key* which is the **RBAC rule** that fields must respect, and the *value* of this key that is a **sub-array** where:
- the *first* item is a **comma separated list of fields**
- the *second* item is the **action** that the access control **must run** (usually read / write)
- the *third* optional item is the **scenario** in which the rule should apply
```php
class myDatabaseTable extends CActiveRecord {
	...
	public function getFieldAccessRules()
	{
		return Array(
			'guest' => Array(
				Array(
					'my_field_a, my_field_b',
					'read',
					'on' => 'low-level-scenario'
				),
			),
			'staffThatCanDoSomething' => Array(
				Array(
					'my_field_c, my_field_d, my_field_f',
					'read',
					'on' => 'next-level-scenario'
				),
				Array(
					'my_field_a, my_field_b',
					'write',
					'on' => 'next-level-scenario'
				),
				Array(
					'my_field_a, my_field_b',
					'read',
					'on' => 'some-other-scenario'
				),
			),
		);
	}
	...
}
```
### Access Control disclaimer
Watch out: Even if a field is in *read mode* or in *hide mode*, this **don't prevent** that an advanced user / cracker is **passing parameters** to your action's request, so **YOU ARE ADVICED** to take an appropriate strategy to prevent this, like using model's filters depending on scenario, or *unsetting unused parameters from request* before processing it.
