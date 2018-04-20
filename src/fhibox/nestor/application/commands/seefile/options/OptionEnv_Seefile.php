<?php

namespace fhibox\nestor\application\commands\seefile\options;



use fhibox\nestor\application\options\OptionEnv;
use fhibox\nestor\application\values\TypeEnv;
use Symfony\Component\Console\Helper\HelperInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 *
 * @author fhi
 */
class OptionEnv_Seefile extends OptionEnv
{
	public function validate_($value)
	{ /*        */	$this->logger->debug("");
		// Get the result of parent validation
		$valid_1 = parent::validate_($value);

		// For the moment, only support a deploy in production environment
		/*        */	$this->logger->debug("value=$value");
		/*        */	$this->logger->debug("TypeEnv::PROD=".TypeEnv::PROD);
		$valid_2 = TypeEnv::is($value, TypeEnv::PROD, false);
		if (!$valid_2)
		{ $this->addValidationErrorMessage("Sorry, right now only the environment " . TypeEnv::PROD . " is supported for this command.");
		}

		return $valid_1 && $valid_2;
	}


}
