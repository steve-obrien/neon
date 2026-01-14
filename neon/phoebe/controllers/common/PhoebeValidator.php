<?php

namespace neon\phoebe\controllers\common;

/**
 * Provides phoebe validation for a form
 */
class PhoebeValidator
{
	public static function validateForm($phoebeType, $classType, $name=null)
	{
		// get hold of the form
		$form = neon('phoebe')->getForm($phoebeType, $classType, []);
		if ($name)
			$form->setName($name);
		return $form->ajaxValidation();
	}
}
