# Forms

A form represents a translation layer between the application data, and the human input data.

## Definition

You can define forms using a pragmatic chainable interface - great when used in conjunction with an IDE supporting intellisense.
Or build a form from a definition array.
This definition array is capable of building the object representation of the form and its fields.
It is also then possible to serialise the form into an array definition by calling $form->toArray

# Object builder method

```php
use \neon\core\form;
$form = new From('user');
// Add a name field
$form->addFieldText('name')
	->SetLabel('Your Name')
	->setPlaceholder('Enter your name')
	->setRequired();
// Add an email field
$form->addFieldText('email')
	->setLabel('Your email address');
// Add a submit "save" button
$form->addFieldSubmit('save', ['label' => 'Save']);
```

The above can also be represented by an array
```php
$form = new From('user', [
	'fields' => [
		'name' => [
			'class' => 'neon\core\form\fields\Text'
			'label' => 'Your Name',
			'placeholder' => 'Enter your name'
		],
		'email' => [
			'class' => 'neon\core\form\fields\Email'
			'label' => 'Your email address'
		],
		'save' => [
			'class' => 'neon\core\form\fields\Submit'
			'label' => 'Save'
		]
	]
]);
```

## Serialising forms to arrays and json

A form object can be serialised to an array representation which is useful for storing entire 
form settings and passing form information about.
The form object implements the `\neon\core\interfaces\IJsonable` interface, 
this interface provides a toJson function and also extends the core PHP \JsonSerializable interface 
so it can be used directly with the `json_encode` native php function

```php
$form->toArray();
// [] The array definition of the form and all its fields
json_encode($form);
// this is equivelant to $form->toJson()
// a json representation of the forms definition
```


# Form javascript interface

The root form is intended to be the main interface for interacting with the form.

All root forms on a page will be accessible via the global `neon.form.forms` property.
For example: `neon.form.forms.MyFormId` this would return the root form with an id of `MyFormId`

In order to respond to any changes of the data within the form:

```JS
myForm = neon.form.forms.myForm;
// data will contain an object 
myForm.onChange(function(data){
	// will be called whenever data in the form is changed
})
// or - the equivalent:
myForm.$on('change', function(data) {})
```

Running validation or submitting the form.
Sometimes you want to call validation or submit a form from an external area of the system not under the forms control

```JS
myForm.validate(); // => true/false
myForm.ajaxValidate(); 
// will trigger all validations and return true if valid and false if invalid
```

To submit a form
```JS
myForm.submit();
```

## Form Events

```JS
onChange
onBeforeSubmit
onAfterSubmit
```

## Dynamic form instance creation

This can be useful for advanced use cases where perhaps you need more control.  
It is particularly useful for inserting forms loaded from other data sets.
The technique is also applicable to any Vue component.

The trick in Vue is to get hold of an instantiable component class as a call to `Vue.component('my-component-name')` will return a factory method, to do this you must use `Vue.extend` For example:

```JS
const FormClass = Vue.extend(Vue.component('neon-core-form-form'));
let form = new FormClass({propsData: {name:'myForm'} });
// to insert either use mount:
form.$mount('#place');
// or alternatively you can insert manually
form.$mount(); // the component will be rendered off page
$(body).append(form.$el); // insert the dom node of the component using $el
```

<! neon\dev\widgets\NeonJsFiddle bundles='\neon\core\assets\CoreAsset,\neon\core\themes\neon\Assets,\neon\core\form\assets\FormAsset' >
<script>
const FormClass = Vue.extend(Vue.component('neon-core-form-form'));
let form = new FormClass({propsData: {
	name:'myForm', 
	id:'myid', 
	fields: [
		{class:'neon-core-form-fields-text', name:'test', label:'Test input'}
	]
}});
form.$mount('#formdemo');
// listening to events
// this is equiveland to using an @change="" if using in a template
form.$on('change', console.log)
</script>

<html>
<div id=formdemo>
	
</div>
</html>
</!>
// Create dynamically
// var form = new Vue.extend(Vue.component('neon-core-form-form'));
// var formInstance = new form({propsData: { name: data.name,  fields: data.fields}  });

