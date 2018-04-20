<?php

namespace fhibox\jeeves\core;


/**
 * @author Francois hill
 * Date: 24/11/2014
 */

interface IConfirmable
{
	/**
	 * Offers an opportunity to ask the user to confirm the value(s)
	 * passed to an option or argument, and possibly change these value(s).
	 *
	 *
	 * @param string|string[] $values
	 * @return string|string[] The new (or not, if left unchanged) value(s)
	 */
	public function confirm($values);
}