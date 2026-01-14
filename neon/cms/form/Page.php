<?php
namespace neon\cms\form;

use neon\cms\models\CmsPage;
use neon\cms\models\CmsUrl;
use neon\core\db\Migrator;
use neon\core\helpers\Arr;
use neon\core\helpers\File;
use neon\core\helpers\Str;
use neon\core\view\SmartyRenderer;
use yii\validators\ExistValidator;
use yii\validators\UniqueValidator;

class Page extends \neon\core\form\Form
{
	protected $_name = 'page';

	/**
	 * Editor mode simplifies the form so it's auto configured for use with the editor
	 */
	public $editorMode = true;

	/**
	 * @inheritDoc
	 */
	public function init()
	{
		$this->setName('page');
		parent::init();
		$this->addFieldText('name', [
			'label'=>'Page Reference Name',
			'hint'=>'Give the page a nice reference name so it\'s easy to find in the Cobe Editor. This is not used for SEO.'
		])->setRequired();
		$this->addFieldText('nice_id', [
			'label' => 'ID',
			'hint' => 'This id will be used in templates and code to reference the page - once set it is difficult to change. It must be unique',
		])->addValidator(new UniqueValidator(['targetClass'=> '\neon\cms\models\CmsPage', 'targetAttribute' => 'nice_id']))
			->setRequired();
		$this->addFieldText('url', [
			'label' => 'Url slug',
			'hint' => 'This is the url on your site where the page wil be available'
		])->addValidator(new UniqueValidator(['targetClass'=> '\neon\cms\models\CmsUrl', 'targetAttribute' => 'url']))->setRequired();
		
		if (!$this->editorMode) {
			$this->addFieldText('title')->setLabel('SEO Title')->setHint("This is the text that will appear in the title meta tag, search engines will use this title to display your page in search results.\n (you can change this later)");
			$this->addFieldTextArea('description')->setLabel('SEO Description')->setHint("Search engines will use this description as the small exerpt shown in search results\n (you can change this later)");
			$this->addFieldSelect('template', [
				'label' => 'Template',
				'hint' => 'Choose a page template',
				'items' => $this->getTemplates(),
				'create' => true
			]);
			$this->addFieldSelect('status', [
				'label' => 'Status',
				'items' => [CmsPage::STATUS_DRAFT => 'Draft', CmsPage::STATUS_PUBLISHED => 'Published'],
			]);
			$this->addFieldSwitch('collection', [
				'label' => 'Collection Page?',
				'hint' => 'Mark the page as a collection if this page represents a resource - for example a blog post, an ecommerce product'
			]);
			$items = collect(neon()->getDaedalus()->getIDdsClassManagement()->listClasses(null,$total))
				->flatMap(function($item) { return [$item['class_type'] => $item['label']]; });
			$this->addFieldSelect('collection_class', [
				'label' => 'Collection Class',
				'hint' => 'Select the type of item this page will represent',
				'showIf' => ['collection', '=', true],
				'items' => $items
			]);
		}

		$this->addFieldSubmit('Save');
	}

	/**
	 * Enables the unique validator to get the nice_id value 🤮
	 * this is because yii validators use $modal->$attribute call inside the validator where $model is the form
	 * and the $attribute is the field name
	 */
	public function getNice_id()
	{
		return $this->getField('nice_id')->getValue();
	}

	/**
	 * Enables the unique validator to get the url value 🤮
	 * this is because yii validators use $modal->$attribute call inside the validator where $model is the form
	 * and the $attribute is the field name
	 */
	public function getUrl()
	{
		return $this->getField('url')->getValue();
	}

	/**
	 * Save cms page
	 */
	public function save(&$errors)
	{
		$data = $this->getData();
		$page = new CmsPage();
		$page->attributes = $data;
		if ($this->editorMode) {
			// set defaults for editor based page editing
			$page->template = 'pages/editor.tpl';
			$page->status = CmsPage::STATUS_DRAFT;
		}
		$url = new CmsUrl();
		$url->attributes = $data;
		// page must be saved first so the page nice_id exists
		$result = $page->save() && $url->save();
		// we don't need to create the template file when using the editor template
		if (!$this->editorMode) {
			$this->createTemplateFile($page->template);
			if (!$result) {
				$errors = $page->getErrors() + $url->getErrors();
				return $result;
			}
		}
		$page->refresh();
		// clear page and url cache
		neon()->cache->flush();
		// success so generate migration:
		$this->generateMigration($page, $url);
		return true;
	}

	/**
	 * create a page template file if one does not exist
	 */
	public function createTemplateFile($template)
	{
		$theme = neon()->getAlias(neon()->cms->getThemeAlias());
		if (!file_exists("$theme/$template")) {
			File::createFile("$theme/$template", "{* your awesome page goes here... *} <p>Edit this template in $theme/$template </p>");
		}
	}

	/**
	 * List all the available page templates
	 * @return array template map
	 */
	public function getTemplates()
	{
		$theme = neon()->getAlias(neon()->cms->getThemeAlias());
		$templates = [];
		foreach(File::findFiles("$theme", ['only' => ['*.tpl', '*.twig']]) as $file) {
			$path = str_replace("$theme/", '', $file);
			if (Str::startsWith($path, 'pages/') || Str::startsWith($path, 'partials/')) {
				$templates[$path] = $path;
			}
		}
		return $templates;
	}

	/**
	 * Generate the add page migration
	 * @param $page
	 * @param $url
	 * @throws \yii\base\Exception
	 */
	public function generateMigration($page, $url)
	{
		$templateFile = __DIR__.'/stub/migration.tpl';
		$tpl = file_get_contents($templateFile);
		$className = 'm'.gmdate('Ymd_His')."_add_cms_page_".$this->formatNiceId($page->nice_id);
		$data = [
			'{$className}' => $className,
			'{$id}' => $page->id,
			'{$nice_id}' => $page->nice_id,
			'{$name}' => $page->name,
			'{$title}' => $page->title,
			'{$status}' => $page->status,
			'{$layout}' => $page->layout,
			'{$template}' => $page->template,
			'{$keywords}' => $page->keywords,
			'{$description}' => $page->description,
			'{$url}' => $url->url,
			'{$collection}' => $page->collection,
			'{$collection_class}' => $page->collection_class,
		];
		$alias = '@system/cms/migrations';
		$directory = neon()->getAlias($alias);
		$output = str_replace(array_keys($data), array_values($data), $tpl);
		File::createFile("$directory/$className.php", $output, LOCK_EX);
		// save this migration in the current users migration table
		$migrator = new Migrator;
		$migrator->storeExternallyExecutedMigration("$alias/$className");
	}

	/**
	 * Create a safe string variable for use in a file name
	 * @param string $niceId
	 * @return string|string[]|null
	 */
	public function formatNiceId($niceId)
	{
		return preg_replace("/[^a-z0-9_]/", '',
			strtolower(preg_replace("/-| +/", '_', trim($niceId))));
	}

	/**
	 * create a migration file to be used for this session
	 * @param string $label  a label for the migration file
	 */
	protected function createMigrationFile($label)
	{
		$label = $this->canonicaliseRef($label);
		$this->_classname = 'm'.gmdate('Ymd_His')."_cms_page_{}";
		$this->_filename = $this->_classname.'.php';
		$this->_file = $this->_path.'/'.$this->_filename;

		// create a migration file
		$templateFile = __DIR__.'/templates/ddsMigration.php';
		$input = file_get_contents($templateFile);
		$output = str_replace('__MIGRATION__',$this->_classname,$input);
		file_put_contents($this->_file, $output, LOCK_EX);
		return $this->_filename;

	}

}
