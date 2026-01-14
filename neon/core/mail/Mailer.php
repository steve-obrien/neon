<?php


namespace neon\core\mail;

class Mailer extends \yii\symfonymailer\Mailer
{
	/**
	 * Ensure we have a from address - if not use globally defined email
	 * @inheritdoc
	 */
	public function compose($view = null, array $params = [])
	{
		$message = parent::compose($view, $params);
		// add global email if configured
		$from = setting('admin', 'fromEmailAddress');
		$name = setting('admin', 'fromEmailName');
		if ($from) {
			$message->setFrom([$from => $name]);
		}
		return $message;
	}

	/**
	 * @inheritdoc
	 */
	public function sendMessage($message): bool
	{
		return parent::sendMessage($message);
	}
}
