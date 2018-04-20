<?php

namespace fhibox\jeeves\core;


/**
 * @author Francois hill
 * Date: 24/11/2014
 */

interface IDecoratable
{
	/**
	 * Decorate the value(s) passed to an option or argument i.e. intercept it (them)
	 * and apply a treatment to it (them)
	 *
	 * @param  mixed  $values string or string[]
	 * @return mixed the decorated value(s) (string or string[])
	 */
	public function decorate(/* mixed */ $values);
}
