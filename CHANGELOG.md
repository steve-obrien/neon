


# Change Log
All notable changes to this project will be documented in this file.

## Latest
- Bug: Fixed crop (behaves on both ImageMagik and GD drivers)
- Enh: Added AI class for open ai api
- Enh: DDS hover over a field shows the field data key in the side panel
- Enh: DDS filters now support simple associative filters ['featured' => true, 'published' => true]
- Enh: Dds `neon()->dds->cacheKey()` returns cache ID. This will stay the same until any Edit / delete / add / delete / destroy operation across any table - quite a blunt cache but useful for high read low write scenarios like websites. You can add this key to your own cache so it will refresh when Daedalus objects change.
- Enh: CMS pages no longer loop through DDS calls.  Smarty function {dds class="blog"} will return results imidiately.
for old looping behaviour wrap with {dds_capture} {/dds_capture} tag (todo)

- **Breaking:** template layouts need additional markup to handle javascript

In smarty layout files:
You need to add  {$this->beginPage()} before the <html> tag
And {$this->endPage()} at the end (after </html>) of the layout.
A minimal layout file may look like this:

```html
{$this->beginPage()}
<!DOCTYPE html>
<html lang="en">
	<head>
		{$this->head()}
		{head}
	</head>
	<body>
		{$this->beginBody()}
		{block "content"}{/block}
		{$this->endBody()}
	</body>
</html>
{$this->endPage()}
```
Previously `{neon_body_begin}` tags were the equivelent functions to do the same.

This is due to how Yii works beginPage and endPage essentially starts output buffering.  This allways any php or function called during render of the template to output anything via echo commands.
The endPage call then captures all the output so that Yii can continue processing.  Now it has the full string to send to the browser (all functions and any php calls that require javascript or css have told yii what they need). Now Yii inserts the scrips and css into the string - before returning the response to the browser.

Earlier neon versions attempted to hide this detail away.  But when integrating with a standard Yii ViewRenderer we can't easily do any magic without consequences.

The advanatge is you can effectivly use any template render to render pages and better integration with Yii.
Less code overall.

## 3.0.1
- Fix: Checkboxes are now disabled when in readOnly or printing mode as readOnly is ineffective on the checkbox UI

## 3.0
- **Breaking:** interface change for grid cell render functions,
`$model, $key, $index, $column` - it was previously undocumented in the IColumn interface but the fourth parameter when called in a custom grid would be a reference to the column object.
To Fix:
```php
	// renders the 'roles' column cell
	public function renderRoles($model, $key, $index, $column)
	{
		$search = $column->getSearch();
		// ..
	}
```
Change to:
```php
	// renders the 'roles' column cell
	public function renderRoles($model, $key, $index, $context)
	{
		$column = $this->getColumn('roles');
		$search = $column->getSearch();
		// ..
	}
```
As it was undocumented this was only typically used inside the neon core (Users grid specifically) this has been updated.

- **PHP: 8.1**
- Enh: DDS grids by order by _created column desc (latest first) if no order specified
- Enh: Grid column render functions receive a context string 'grid', 'csv'
- PHP 8.1 compatibility, unfortunately this breaks older versions due to declaring function return types
- Enh: Slug field shows slugs as clickable links
- Fix: Image multiple showing previous rows images in the grid (if empty)
- Enh: LocationMap field can be configured to look up a custom setting to load the Google Maps API key
- Fix: Ensure the Core app does not crash if the CMS app is disabled
- Fix: CMS redirects keep query parameters on the url when redirecting
- Added ability to add a fieldSelectDynamic to a form directly.



## 2.7.23
- Enhancement: Cms Page `neon()->cms->page` now exposes `hadDataRequests()` as a public method. This enabled smarty functions to only run if there are no data requests (on the last render)

## 2.7.20
- Fix: smarty will now assume E_ALL for error reporting when in dev mode.

## 2.7.18 & 2.7.19
- Fix: cms page grid now using CONCAT_WS to merge together URLs pointing to the
  same nice_id as the GROUP BY was unnecessary

## 2.7.17
- Enhancement: enable database port to be defined in env.ini

## 2.7.16
- Enhancement: allow usernames to include .-_

## 2.7.15
- Enhancement: prevent Dev app spinning up in debug mode
- Enhancement: added caching for cms_url and cms_page
- Enhancement: allow user to configure session storage - (file by default)

## 2.7.14
- Enhancement: allowed emails and spaces in imported user data for username

## 2.7.13
- Fix: HtmlRaw extending Textarea not TextArea

## 2.7.12
- Enhancement: Ability to add pages if your theme has a pages/editor.tpl file - this loads the editor.

## 2.7.11
- Fix: cms page grid
- Feature: ability to attach events to neon.modal close and firefly modal picker
- Enhancement: move theme asset functionality into cms/App for use outside of smarty context

## 2.7.10
## 2.7.9
- Fix: Minor change to docblock on IDdsObjectManagement function listObjects to correctly display parameter list in IDE's.

## 2.7.8
- Feature: ability to set a favicon in the cms settings this will then be used for the admin area.
  It can also be used on the front end with the {favicon} smarty tag
- Feature: Added Arr::defaults() function to set a batch of array key default values
## 2.6.5
- Fix: Image multiple field, component was not in the .build.js
- Fix: Ensure Image multiple component registers the Firefly scripts when used
- Fix: Element UI switch input (this field is deprecated but still exists and sometimes used)

## 2.6.2
- Fix: redirects use 301

## 2.6.1
- Enhancement: Daedalus {dds} returns empty array instead of null

## 2.6.0

## 2.5.0
- Feature: CMS autogenerates sitemap.xml
- Fix: Page creation (bug in migrations due to collection column)

## 2.4.3
- Fix: issue where html is not encoded and breaks javascript in the cms pages editor

## 2.4.2
- Enhancement: Firefly - better support for svg images

## 2.4.0
- Feature: added image compare component
- Fix: Remove errors generated by a form or a field being stored in the form or field definitions
- Fix: Fix javascript vue bug with firefly sidebar (unbalanced div)
- Fix: Prevent error when firefly can't find any file references
- Enhancement: Slug components must have a page ID set in their definition to work therefore appropriate errors indicating this have been added
- Enhancement: Pretty print JSON when in dev mode

## 2.0
Potentially cms breaking changes so upgraded to 2.0
Upgrade the minimum PHP version to 7.3 when installing from composer.
Updated necessary dependencies.

### Core
- Ability to add the firefly media browser to wysiwyg editor

### Firefly
- Feature: added software delete for firefly->fileManager (requires migration to be run)
- Enhancement: deleting an item from the mediaManager also soft deletes in firefly
- Feature: added destroyDeleted (empty bin) method to firefly->mediaManager - also removes files from storage
- Feature: Media Browser - ability to use browser history - if on then folder paths are pushed onto the browser history with a hash for e.g. #/Team/Thing this enables navigation via the brower back and forward buttons - also links to sub folders can be shared
- Feature: Media Browser - added sidebar with key information on selected items
- Feature: Media Browser Sidebar - shows all daedalus records that reference the file
- Feature: Insert images unto wysiwyg editor with srcset attributes
- Feature: ability to rubber-band/lasso select multiple items
- Feature: Drag and drop folders and items into parent or child folders
- Feature: Ability to edit alt, title, caption and description text on files from the media browser

### Cms
- Feature: `cms_url` table added ability to define a redirect (requires migration to be run)
- Feature:  `cms_page` table added fields for open graph meta tags (requires migration to be run)
- Feature: Slug form field
- Feature: Added a `collection` column and `collection_class` column to `cms_pages` enabling pages to know they are responsoble for rendering individual objects of the class (individual rows of the table) for example a product / blog post template
- Feature: Added a redirects GUI

### Daedalus
- Enhancement: Daedalus fires `afterAdd` and `afterEdit` events for objects

## 1.1.9
- Security: Updated to latest Yii 2.0.16
- Feature: full support for PHP 5.6 testing `./codecept run`
- Feature: User invites section
- Enhancement: Migrations run in date order
- Security: Url input prevents XSS injection

## 1.1.8
- Enhancement: new neon admin theme (early version)
- Bugs: various minor bug fixes

## 1.1.7
- Enhancement: Cobe CMS page editor updates
- Security: Default sanitation on all get requests
- Enhancement: Ajax form submission

## Media Manager - 2017-06-07
A rather large branch...

### Update instructions
- Update all uses of `$useUuidAsId` in active record classes to a static property and rename to `$idIsUuid64`;
- Change active record public properties `$timestamps`, `$blame` and `$idIsUuid64` to be static
- Config has been split between three files instead of sub folders its now just `common` `console` and `web`

### Changed
- Changed ActiveRecord `$timestamps`, `$blame`, and `$useUuidAsId` to be static properties
- Changed the active record `$useUuidAsId` property to `$idIsUuid64`

### Removed
- Removed config folders and environment specific config files - use environment variables

### Added
- Added the media manager!
- Added more tests
- Mutliple PHP version tested - Scrutinizer config now runs all tests against PHP 5.6 and PHP 7.1
- Added new media manager picker and browser
- Added image editor
- Added User token authentication methods and management table (service manager needs adding)
- Added JWT (json web token) based authentication for API methods
- Added REST api controller - (\neon\core\web\ApiController) automatically authenticates against apitoken auth and jwt token auth
- Added api tests and public api for firefly media manager
- Added model soft delete behaviour - set any model public property softDelete = true and add a deleted column

### Fixed
- Fix form setAction (can change the action url of a form object)
