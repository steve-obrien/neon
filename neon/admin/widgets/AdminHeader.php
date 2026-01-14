<?php

namespace neon\admin\widgets;

use yii\base\Widget;

class AdminHeader extends Widget
{
	/**
	 * The title text
	 * Will set this to the view->title if not defined
	 * @var string
	 */
	public $title;
	
	/**
	 * An array of buttons, where each button is defined as the button Html string
	 * You can have one level nested array representing button groups.
	 * For example the following would render four valid buttons, two buttons rendered normally and one button group
	 * with two button (A and B) inside
	 * ```php
	 *	[
	 *		'<button>XYZ</button>',
	 *		Html::buttonEdit(['/user/index/edit']),
	 *		' | ', // optional seperator html
	 *		// button group with two buttons in it
	 *		[
	 *			'<button>A</button>', 
	 *			'<button>B</button>'
	 *		]
	 *	]
	 * ```
	 * 
	 * @see \neon\helpers\Html
	 * @var array 
	 */
	public $buttons = [];
	
	public function run()
	{
		// Set the title to view title if it has not been defined
		if ($this->title === null) {
			$this->title = \Neon::$app->view->title;
		}
		return $this->render('neonToolbar', [
			'title' => $this->title,
			'buttons' => $this->buttons
		]);
	}
}