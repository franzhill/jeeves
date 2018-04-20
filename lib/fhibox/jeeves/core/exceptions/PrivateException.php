<?php
/**
 * Created by PhpStorm.
 * User: Francois hill
 * Date: 28/10/201x
 * Time: 11:22
 */

namespace fhibox\jeeves\core\exceptions;

/**
 * An exception to cater to some 'private' needs.
 *
 * Destined for internal purposes in a function etc. Do not throw or catch outside of function!
 *
 * Arguably, exceptions should not be used to control "normal" flow.
 * That's what this kind of exception is designed for: sometimes it's just conveniant.
 *
 * @package fhibox\jeeves\core\exceptions
 */
class PrivateException extends \Exception
{
	/**
	 * @var string
	 */
	private $label;

	public function __construct($label=null)
	{ $this->label= $label;
		parent::__construct("");
	}

	public function getLabel()
	{
		return $this->label;
	}

}