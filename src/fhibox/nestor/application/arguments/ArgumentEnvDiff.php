<?php

namespace fhibox\nestor\application\arguments;

use fhibox\jeeves\core\Argument;
use fhibox\nestor\application\values\TypeEnv;


/**
 * Argument specifying two different environments (e.g. DEV:STAGING).
 * 
 * To be used in e.g. commands that compare things between two environments.
 *
 * @author fhi
 */
class ArgumentEnvDiff extends Argument
{
	/**
	 * @override
	 * @return bool|void
	 */
	protected function preValidate_($value)
	{
		$res = preg_match('/.+:.+/', $value);
		if ($res !== 1)
		{	$this->addValidationErrorMessage("Expected format is: <env1>:<env2>. Please check help.");
			return false;
		}
		return true;
	}

	/**
	 * @override
	 */
	public function decorate_($values)
	{
		// Here we will transfom $values which is supposed to contain env1:env2
		// into array(env1,env2).
		// $values should be a string

		/*        */	$this->logger->debug("decorating values : " . print_r($values, true));
		$matches = array();
		preg_match('/(.+):(.+)/', $values, $matches);

		$ret= array_slice($matches, 1);
		return $ret;
	}

	/**
	 * Here validation has to be done a bit differently, since decoration has transformed 
	 * the value into an array of envs. array(env1, env2).
	 * So the array is not the fact of Symfony\Console processing several inputs for that argument.
	 * So instead of going the usual "Jeeves-way" to validate i.e. overriding validate_(),
	 * we'll override validate() directly instead.
	 *
	 * @override
	 * @param string|\string[] $values
	 * @return bool
	 */
	public function validate($values)
	{	/*        */	$this->logger->debug("values=".print_r($values, true));

		// Argument value should be of format env1:env2 with env1 and env2 accepted values
		// Argument value has already been through the decorating process at this stage
		// so it should be an array of two strings

		$valid   = true;
		$err_msg = "";
		if ($values == null || sizeof($values) != 2 )
		{ $valid = false;
		}
		if (! TypeEnv::isValidName($values[0], false))
		{ $valid = false;
			$err_msg .= "[$values[0]] is not a valid environment.";
		}
		if (! TypeEnv::isValidName($values[1], false))
		{ $valid = false;
			$err_msg .= "[$values[1]] is not a valid environment.";
		}
		if (!$valid)
		{ /*        */	$this->logger->debug("Adding validation error message");
			$this->addValidationErrorMessage("Argument should be of type: <env1>:<env2> with <env1> and <env2> valid environments." . $err_msg . " Please check help.");
			return false;
		}
		return true;
	}


}
