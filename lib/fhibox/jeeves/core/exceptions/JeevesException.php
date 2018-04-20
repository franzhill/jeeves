<?php
/**
 * Created by PhpStorm.
 * User: Francois hill
 * Date: 30/11/201x
 * Time: 17:20
 */


namespace fhibox\jeeves\core\exceptions;
use fhibox\exceptions\ChainedException;


class JeevesException extends ChainedException
{
	public function isRoot()
	{
		return get_class($this) == __CLASS__ ;
	}
}
