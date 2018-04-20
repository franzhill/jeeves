<?php

namespace fhibox\nestor\application\options;

use fhibox\jeeves\core\AppCommand;
use fhibox\jeeves\core\exceptions\NoUserConfirmationOnPromptException;
use fhibox\jeeves\core\IConfirmable;
use fhibox\jeeves\core\IValidatable;
use fhibox\jeeves\core\Option as Option;
use fhibox\jeeves\core\exceptions\OptionOrArgumentInvalidException;
use Symfony\Component\Console\Helper\HelperInterface;


/**
 * Description of Option
 *
 * @author Francois hill
 */
class OptionRev extends Option
{


	public function validate_($value)
	{
		$int_value =  (int) $value;
		if ( !  (  (is_int($int_value) && $int_value >= 0 )   ||  $value == "HEAD"))
		{
			$this->addValidationErrorMessage("Option rev must be an positive integer or HEAD, [$value] given instead");
		}
		return true;
	}

}
