<?php

namespace fhibox\nestor\application\commands\deploy\options;

use fhibox\nestor\application\options\OptionEnv;
use fhibox\nestor\application\values\TypeEnv;

/**
 * Created by PhpStorm.
 * User: Francois hill
 * Date: 22/10/201x
 * Time: 11:04
 */

class OptionEnv_Deploy extends OptionEnv
{
	public function validate_($value)
	{ /*        */	$this->logger->debug("");
		// Get the result of parent validation
		$valid_1 = parent::validate_($value);
		$valid_2 = true;

		return $valid_1 && $valid_2;
	}


} 