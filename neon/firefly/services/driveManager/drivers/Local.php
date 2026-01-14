<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 22/11/2016 16:30
 * @package neon
 */

namespace neon\firefly\services\driveManager\drivers;

use \yii\base\Component;
use yii\base\InvalidConfigException;
use League\Flysystem\Local\LocalFilesystemAdapter;
use \neon\firefly\services\driveManager\interfaces\IDriverConfig;

/**
 * A thin configurable class to produce the Local adapter
 * Class Local
 * @package neon\firefly\finder\drivers
 */
class Local extends Component implements IDriverConfig
{
	/**
	 * The root path in which store files
	 * @var string
	 */
	public $root;

	/**
	 * Whether to skip or disallow links
	 * @see LocalFilesystemAdapter::SKIP_LINKS | LocalFilesystemAdapter::DISALLOW_LINKS
	 * @var bool  sets LocalFilesystemAdapter::SKIP_LINKS if true and LocalFilesystemAdapter::DISALLOW_LINKS if false
	 */
	public $skipLinks = false;

	/**
	 * Sets the permissions for the local driver to use.
	 * The following are the default values:
	 * [
	 *     'file' => [
	 *         'public' => 0644,
	 *         'private' => 0600,
	 *     ],
	 *     'dir' => [
	 *         'public' => 0755,
	 *         'private' => 0700,
	 *     ]
	 * ]
	 * @var array
	 */
	public $permissions = [];

	/**
	 * @var string 'public' | 'private'
	 */
	public $visibility;

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		if ($this->root === null) {
			throw new InvalidConfigException('The "root" property must be set.');
		}
		$this->root = neon()->getAlias($this->root);
		parent::init();
	}

	/**
	 * @return LocalFilesystemAdapter
	 */
	public function getAdapter()
	{
		$links = $this->skipLinks ? LocalFilesystemAdapter::SKIP_LINKS : LocalFilesystemAdapter::DISALLOW_LINKS;
		
		$visibilityConverter = null;
		if ($this->visibility) {
			$permissions = $this->permissions['file'][$this->visibility] ?? 0644; // Default to 0644 if not set
			$visibilityConverter = new \League\Flysystem\UnixVisibility\PortableVisibilityConverter($permissions);
		}

		return new LocalFilesystemAdapter(
			$this->root,
			$visibilityConverter,
			$links
		);
	}
}
