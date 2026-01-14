# Neon

- [Introduction](#introduction)
- [Writing Documentation](#writing-documentation)
- [Code Blocks](#code-blocks)
- [Live Code Examples](#widgets)
- [Hints n Tips](#hints-n-tips)
  

<a name="introduction"></a>
# Introduction

The Dev module is designed for developers. It should show handy development information and documentation.
Currently there is a very fast and quick way to write framework documentation by simply adding a markdown file 
to any app docs folder.

<a name="writing-documentation"></a>
# Writing Documentation

Using Markdown syntax with a few extras namely github flavoured markdown, it's probably best to show by example:

<a name="widgets"></a>
# Live Code Examples

## PHP code
Where ever possible its best to show real code examples.
This helps to keep the docs up to date and ensures people are only seeing real code.
You can do this by embedding custom widgets.  With two pre made fiddle widgets `NeonFiddle` for example php execution and `NeonJsFiddle` for javascript, html and css components

You can pull in php widgets using this syntax:
```markdown
<! neon\dev\widgets\NeonFiddle >
return neon()->version;
</!>
```

Which will render the NeonFiddle widget:

<!neon\dev\widgets\NeonFiddle>
return neon()->version;
</!>

## JS code

You can also pass attributes:
The demo below passes the `bundles` property to the `NeonJsFiddle` widget class

```markdown
<! neon\dev\widgets\NeonJsFiddle bundles="\neon\core\assets\CoreAsset" >
	<style>
        *{color:red;}
    </style>
    <script>
        window.helloWorld = function() {alert('Hello World')}
    </script>
    <html>
        <button onclick="helloWorld()">Say Hello World</button>
    </html>
</!>
```

The above code will render the NeonJsFiddle widget:

<! neon\dev\widgets\NeonJsFiddle bundles="\neon\core\assets\CoreAsset" >
	<style>
		*{color:red;}
	</style>
	 <script>
		window.helloWorld = function() {alert('Hello World')}
	</script>
	<html>
		<button onclick="helloWorld()">Say Hello World</button>
	</html>
</!>

<a name="code-blocks"></a>
# Code blocks


```php
echo 'hello';
```

Markdown:

~~~md
```php 
echo 'hello';
```
~~~

or

```md
~~~php 
echo 'hello';
~~~
```

<a name="hints-n-tips"></a>
# Hints n Tips

You can add funky tips it is acceptable for simple tags to be used within markdown, this is how you add little call out tips:

```html
<p class="tip">I'm a tip!</p>
```

Gives: 

<p class="tip">I'm a tip!</p>


And less shouty version:

```html
<p class="tip tip--info">I'm a tip!</p>
```
Will give:

<p class="tip tip--info">I'm some info - blue and less shouty</p>




