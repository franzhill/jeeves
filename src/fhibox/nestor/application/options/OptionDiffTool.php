<?php

namespace fhibox\nestor\application\options;

use fhibox\jeeves\core\IValidatable;
use fhibox\jeeves\core\Option;
use fhibox\jeeves\core\exceptions\OptionOrArgumentInvalidException;

use fhibox\nestor\application\values\TypeDiffTool;


/**
 * Lets pass a diff tool to a command (e.g. the compare command)
 *
 * @author Francois hill
 */
class OptionDiffTool extends Option
{
	public function validate_($value)
	{ /*        */  $this->logger->debug("value=$value");
		# No need to verify this since we have not specified it could be a InputOption::IS_ARRAY above
		#if (is_array($value))
		#{ throw new OptionOrArgumentInvalidException("Cannot be an array");
		#}

		// Check (case-sensitive):
		if (!TypeDiffTool::isValidValue($value, true))
		{ $this->addValidationErrorMessage("Diff tool [$value] is not a recognized diff tool. Please refer to help.");
			return false;
		}
		return true;
	}

}
