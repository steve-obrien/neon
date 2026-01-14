# Models

- [Model Getters and Setters](#model-getters-and-setters)
  - [Defining Attribute Setters](#defining-attribute-setters)
  - [Defining Attribute Getters](#defining-attribute-getters)
- [Timestamps](#timestamps)
- [Blame](#blame)
- [Soft Delete](#soft-delete)
- [Uuids](#uuids)



<a name="model-getters-and-setters"></a>
## Model Getters and Setters

Changing the default precedence of getters and setters in Model classes.
By extending from `\neon\core\db\ActiveRecord` models will change their 
default behaviour to make setting and getting attributes easier to manipulate.

It is sometimes desired to convert the data for a particular property before saving into the database.
Likewise the reverse is true when we want to transform a database property value in our model to something understood by the business layer of our app.

```php
$model->timestamp = 1480008832;
$timestamp = $model->timestamp
// gives: 1480008832 
// But stored in the database like '2015-11-24 17:35:00'
```

Attribute setters and getters provide a convenient way to do this.

```php
class MyModel extends ActiveRecord
{
	public function __set_timestamp($value)
	{
		// $value is an integer timestamp
		$this->setAttribute('timestamp', date('Y-m-d H:i:s', $value));
	}
	
	public function __get_timestamp($value)
	{
		// $value is a string like '2015-05-01'
		return strtotime($value)
	}
}
```

<a name="defining-attribute-setters"></a>
### Defining Attribute Setters

An attribute setter enables the model to mutate the value being set to bring it inline with how the database would like to store it.
Or to provide additional checks for example rejecting setting a property if the particular user does not have permission to set it.

Lets take the scenario where we have a `file_is_public` property in the database. For some reason the database is storing the
results as a string with values of `'public'` and `'private'`. I know what you are thinking, lets change the database!
We could indeed, but for the purpose of this example we are stuck with it.

In the code we would quite like this to be a boolean property of the model to avoid 'public' and 'private' strings appearing everywhere.
Indeed this is how we may one day refactor the database...
Inside the model we can define a special `'__set_'` method. Note that the function name is the database field name prefixed with '__set_'

```php
class File extends ActiveRecord 
{
    public function __set_file_is_public($value)
	{
		$this->setAttribute('file_is_public', $value ? 'public' : 'private')
	}
}
```
Note that in the example above we use the `setAttribute` method that sets the internal value of the database model attribute

Now in our code we can do the following:

```php
$model->file_is_public = true;
```

Now we'd like it that when we call the property `$model->file_is_public` we get a boolean as well, instead of 'public' or 'private' strings.

<a name="defining-attribute-getters"></a>
### Defining Attribute Getters

Inside the model class:

```php
class File extends ActiveRecord 
{
    public function __get_file_is_public($value)
	{
		return $value == 'public' ? 'true' : false;
	}
}
```

<p class="tip tip--info">Note that in the above example the 
<code>`$value`</code> passed into the getter is the value from the 
database. Effectively this is the output of 
<code>$this->getAttribute('file_is_public');</code></p>

<p class="tip">What's this icky underscore function syntax? Well we could 
automatically camelcase the database names but after testing this approach the overhead was quite large.
The fastest way is to simply check if a function exists of the format <code>"__get_$name"</code>
This also has the added benifit of standing out prominantly in the code - it should be obvious that this is infact setting or getting a database attribute.
A double underscore is used as a hat tip to the PHP magic class getters and setters (__get and __set) as this is doing something similar also it looks less like someone just prefers using underscores.</p>

<p class="tip">
Remember models are essentially a wrapper around an attributes array where
 the key is the database column name and the value is the value represented 
 in the database (or the value that will eventually be represented in the 
 database after a save call for example) when a model recieves a call to 
 a property that does not exist on the class for e.g. 
 <code>$myModel->unknown_db_column</code> the model's magic getter 
 function is called <code>__get($name)</code>.
 The model then checks if an attribute exists in the array matching the 
 name <code>'unknown_db_column'</code> if it does have an attribute 
 with this name then it simply calls the 
 <code>$this->getAttribute('unknown_db_column')</code> function.
 The <code>'setAttribute()'</code> and <code>'getAttribute()'</code> 
 functions - allow you to interact with the internal models attributes 

</p>

Now this is defined we can note that we have a setter and a getter for the `file_is_public` attribute of the File model.
We can use the `hasAttibuteSetter('file_is_public')` and `hasAttibuteGetter('file_is_public')` functions 
to determine if the model has attribute getters and setters defined.  But most of the time this should be handled automatically.


<a name="timestamps"></a>
## Timestamps

It is often handy to add basic timestamps `created_at` and `updated_at` to 
models. The `neon\core\db\ActiveRecord` base class will automatically 
populate these fields for you if you set the `timestamps` static property to `true` 

```php
<?php
use \neon\core\db\ActiveRecord;
Class MyModel extends ActiveRecord
{
    public static $timestamps = true;
}
```

<p class="tip">
    You must ensure that the model table has both the <code>created_at</code> 
    and <code>updated_at</code> fields defined as <code>DATETIME</code> fields
</p>

If you need to customize the names of the columns used to store the timestamps, 
you may set the  `CREATED_AT` and `UPDATED_AT` constants in your model:

```php
class MyModel extends ActiveRecord
{
    const CREATED_AT = 'created_on';
    const UPDATED_AT = 'last_update';
}
```

<a name="blame"></a>
## Blame

It's ***YOUR*** fault!
Similar to timestamp fields it is often nice to have the **blame** fields 
`created_by` and `updated_by` on your models.
 
The `neon\core\db\ActiveRecord` base class will automatically populate these fields
if you set the `blame` static property to `true`

```php
<?php

use \neon\core\db\ActiveRecord;

Class MyModel extends ActiveRecord
{
    public static $blame = true;
}
```

<p class="tip">
    You must ensure that the model table has both the <code>created_by</code> 
    and <code>updated_by</code> fields defined as <code>integer</code> fields
</p>
 
 Again if you need to use column names other than `created_by` and `updated_by` you can set the model constants
 `CREATED_BY` and `UPDATED_BY`
 
 
<a name="soft-delete"></a>
## Soft Delete

Often models have a soft of delete function. To enable this set the `softDelete` static property on the model class to true.

```php
<?php

use \neon\core\db\ActiveRecord;

Class MyModel extends ActiveRecord
{
    public static $softDelete = true;
}
```



<p class="tip">
   Make sure your table has a <code>deleted</code> tiny int column.
</p>

You can change the column name default deleted column name by overriding the constant `static::DELETED`

```php
<?php

use \neon\core\db\ActiveRecord;

Class MyModel extends ActiveRecord
{
    const DELETED = 'trashed'; // default name is 'deleted'
}
```


When this feature is active the standard model delete function `$model->delete()` will set the `deleted` column
to 1.

In order to remove the row from the database call `$model->destroy()`

The `model::find()` method will automatically apply a where clause of `WHERE 'deleted' = 0` 
You can override this by setting the where rules from scratch with `model::find()->setWhere('...')`

<a name="uuids"></a>
## Uuids

UUID's are pretty useful, they make it easier to merge databases as it is very
unlikely that any ids will be the same.  It is also more secure as it is near impossible
to guess a UUID. Neon typically uses a UUID encoded to base 64 making it 22 characters long.

If your model would prefer to use UUIDs instead of an auto incrementing id
simply set the static property `useUuid64AsId` to `true`.  Neon will then
automatically generate the UUID for you when the record saves.

```php
<?php

use \neon\core\db\ActiveRecord;

Class MyModel extends ActiveRecord
{
    public static $useUuid64AsId = true;
}
```

<p class="tip">The models table must have an <code>id</code> column set to a <code>char</code> with a length of 22.</p>

```php
<?php
// Inside the safeUp function of the migration
$this->createTable(MyModel::tableName(), [
    // equivelant to: $this->char(22)->comment('A uuid using base 64')
    'id' => $this->uuid64()->notNull(), 
    // other columns
    'name' => $this->string(),
    // ...
]);
```

<p class="tip tip--info">You can generate a UUID64 using the 
<code>Model::uuid64()</code> static function. This simply calls the 
<code>\neon\core\helpers\Hash::uuid64()</code> function that actually
does the work.</p>