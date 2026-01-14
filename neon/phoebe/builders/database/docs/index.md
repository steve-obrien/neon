# Forms Builder

- [Data Maps and Linked Data](#data-maps)

<a name="data-maps"></a>
## Data Maps

Lets say we have a nice CMS app and we want our form builder to have a new field available that lets
our users select a CMS page.

This scenario is handled by the Dynamic field. The dynamic field has a two key properties `dataMapKey`
and `dataMapProvider`. Essentially the `dataMapProvider` provides the mapping to the data.
Essentially the field will ask the dataMapProvider to get the list of items defined by its `dataMapKey`
 
```php
use \neon\core\form\fields\SelectDynamic;

/**
 * Class PageSelector - provides a drop down list of web pages
 * @package neon\cms\form\fields
 */
class PageSelector extends SelectDynamic
{
    /**
     * The DDS data type to store the value of the field
     * @var string
     */
    public $ddsDataType = 'uuid64';

	/**
	 * The key that is understood by the provider
	 */
    public $dataMapKey = 'pages';

	/**
	 * The data map provider accessible via neon()->getApp('cms')
	 */
    public $dataMapProvider = 'cms';
} 
```

The class above essentially is all we need as far as the field is required.
This will enable the field to show a list of items... (eventually)

The bulk of the work is handled by the provider. The provider must correspond to a neon App and implement the `IFormBuilder` interface.
This interface has the following methods:

```php
    /**
     * Return the list of items for the key specified
     */
    public function getFormBuilderMap($key)
    {
        if ($key === 'pages') {
            $pages = CmsPage::find()->select(['id', 'title'])->asArray()->all();
            return Arr::pluck($pages, 'title', 'id');
        else if ($key === 'blog_posts') {
           return [] //...
        } else {
            return [];
        }
    }

    /**
     * Get the application specific set of map types available to the form builder
     * @return array  an array of map types each of which is
     *   'key'   => A key which is used in @see getFormBuilderMap,
     *   'label' => A display label for the widget,
     */
    public function getFormBuilderMapTypes()
    {
        return [
            'pages' => 'Pages',
            'blog_posts' => 'Blog Posts'
        ];
    }
```

There are two functions in this interface as shown above the concrete implementation 
for our CMS app.  The `getFormBuilderMapTypes` allows the form builder to show a drop down list of 
all the possible things it can link to. In the example above the form builder would display both
`Pages` and `Blog Posts`



<p class="tip">
Note at the moment the form builder does not have a field registration system
</p>



Forms should be easy. Both our front and back end systems should be able to render a decent form from our data.
Then if we have fancy specifications for the form we either make new components - to add the fancy-pantsy-ness or use individual aspects of the form

## Defining a generic form definition

Wouldn't it be great if we could have a generic language that would describe our forms and form field components

```json
{
	"class": "text",
	"label": "First Name",
	"hint": "It's the one before your last name",
	"placholder": "e.g. Bob"
}
```
Hopefully the above properties are obvious, with the exception of the `class` property.
The `class` property acts as an alias to look u the particular object or component that can handle this definition.

For example in Neon this would essentially be translated to:

```php
[
	"class" => "\neon\core\form\fields\Text",
	"label": "First Name",
	"hint": "It's the one before your last name",
	"placholder": "e.g. Bob"
]
```

In javascript land this might get transformed into a component.

```html
<form-field-text :field="config"></form-field-text>
```
