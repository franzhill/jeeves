<?php

namespace fhibox\jeeves\core;
use fhibox\jeeves\core\exceptions\NoUserConfirmationOnPromptException;

/**
 * @author Francois hill
 * Date: 18/11/2014
 */

interface IValidatable
{
	/**
	 * Check whether the value(s) passed to an option or argument is(are) acceptable.
	 *
	 * When implementing this function, do not raise an exception if the value(s) is
	 * invalid. Just set the errorMessage if applicable and return true or false.
	 * The higher levels will deal with raising an exception if necessary.
	 *
	 * @param mixed $values string or string[] : values to be validated
	 * @throws NoUserConfirmationOnPromptException if user does not confirm, after a warning
	 *         prompts them to explicitly validate that command should carry on
	 * @return boolean true if is valid, false (or exception) otherwise
	 */
	public function validate(/* mixed */ $values);


	/**
	 * @return string[] Array of validation (error) messages
	 */
	public function getValidationErrorMessages();
}