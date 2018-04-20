<?php

namespace fhibox\nestor\application\options;


use fhibox\jeeves\core\Option as Option;



use fhibox\nestor\application\values\TypeEnv;
use Symfony\Component\Console\Helper\HelperInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Description of Option
 *
 * @author Francois hill
 */
class OptionEnv extends Option
{
	/**
	 * @override
	 */
	public function validate_($value)
	{
		// Check (case-insensitive) if $value is an type of environment :
		if (!TypeEnv::isValidName($value, false))
		{
			$this->addValidationErrorMessage("Env [$value] is not a recognized environment");
			return false;
		}
		return true;
	}

	/**
	 * @override
	 */
	protected function finalDecorate_($value)
	{
		$env = TypeEnv::getTypeValue($value, false);
		#$env_machine = TypeEnv::toHostName($env);

		return $env;
	}

}
