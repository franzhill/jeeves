<?php
/**
 *
 * User: Francois hill
 * Date: 13/10/201x
 * Time: 11:06
 */

namespace fhibox\nestor\application\options;

use fhibox\jeeves\core\Option;

use fhibox\nestor\application\values\TypeRepository;



/**
 *
 *
 * @author Francois hill
 */
class OptionRepository extends Option
{
	public function validate_($value)
	{
		// Check (case-sensitive):
		if (!TypeRepository::isValidValue($value, true))
		{
			$this->addValidationErrorMessage("Repository [$value] is not a recognized repository. Please refer to help.");
			return false;
		}
		return true;
	}

}
