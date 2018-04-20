<?php
/**
 * Created by PhpStorm.
 * User: Francois hill
 * Date: 28/10/201x
 * Time: 15:32
 */

namespace fhibox\jeeves\core\exceptions;


/**
 *
 * TODO Maybe consider using PHP's \LogicException instead?
 *
 *
 * @package fhibox\jeeves\core\exceptions
 */
class ProgrammingException extends \RuntimeException
{
	public function __constructor()
	{
		parent::__construct("Programming error, should never have happened. There is a bug in the code, please contact dev to fix it.");
	}
} 