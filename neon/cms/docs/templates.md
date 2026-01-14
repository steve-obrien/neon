
# Cosmos Templates

Example markup for a layout file


#Here be dragons

!!! neon\dev\widgets\NeonFiddle
return neon()->user;
!!!



```html
<!doctype html>
<html>
	<head>
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
		<meta charset="utf-8" />
		<title>{$page.title}</title>
		<meta name="keywords" content="{$page.keywords}" />
		<meta name="description" content="{$page.description}" />
		{if $site.languages}
			<meta http-equiv="content-language" content="{$site.languages}" />
		{/if}
		{if $site.favicon}
			<link rel="icon" href="{$site.favicon.favicon}" type="image/x-icon" />
			<link rel="SHORTCUT ICON" href="{$site.favicon.thumbnail}" type="image/x-icon" />
		{/if}
		<link rel="stylesheet" type="text/css" href="{asset path="css/style.css"}" />
		{cosmos_head_script}
	</head>
	<body>
		{$content}
		{cosmos_body_script}
	</body>
</html>
```

Lets examine the Html in more detail.

Making the template editable using the cosmos editor:
<p class="tip"> Without these two tags `{cosmos_head_script}` and `{cosmos_body_script}` the template will not come to life in the Editor</p>

```html
<!doctype html>
<html>
	<head>
		{cosmos_head_script}
	</head>
	<body>
		{cosmos_body_script}
	</body>
</html>
```

## Loading in assets

Using the `{asset path="..."}` tag we can load in local theme assets.
The `path` is the local path from within the `assets` folder of your theme.

For example given the following structure:

```
theme/
  ├── assets/
  |   └── css/
  │       └── style.css
  ├── layouts/
  |   └── main.tpl
  └── home.tpl
```

We can load in our style.css via

```html
<link rel="stylesheet" type="text/css" href="{asset path='css/style.css'}" />
```


## Plugins

### `{page_url id=$page.id}`

Get the url for a page by its id, typical usage:

```html
<a href="{page_url id=$page.id}">My Article To {$page.title}</a>
```

### `{url}`

### `{img}`

The image function can be used to generate on the fly image versions.

```
{img id=$data.firefly_id width=300 height=200 crop='fit'}
```



## Filters
 

### `{$variable|json}`

Print json to the screen - this can be useful for inspecting the contents of a variable


### `{$variable|dp}`

Ah our debug printing safety net. When all is wrong in the world we print it :-)


## Marking up editable content

There are two ways to define editable static content within Cosmos. The first is where you define
the content directly in a tag with a key='xyz' value and a default content if required.

```html
    {front_page key='header' content='Edit Me'}
```

will define static content with a unique key of front_page_header and default content of 'Edit Me'. If 
that is edited within Cosmos the content is replaced. To make it page specific add a page=true tag. To
make it wysiwyg add a wys=true parameter.

```html
    {banner key='title' page=true content='edit me'}
    {banner key='description' page=true content='edit me' wys=true}
```

creates a static content type of key banner_title, or banner_description, both page specific but only
the description is a wysiwyg editor.
 
The second way is to use the static_block block tag as
 
```html
{static_block key='banner_description'}
EDIT ME
{/static_block}
```
 
These are always wysiwyg editors (I THINK)