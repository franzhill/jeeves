<?php
/**
 * Created by PhpStorm.
 * User: Francois hill
 * Date: 02/11/201x
 * Time: 18:38
 */

namespace fhibox\nestor\application\instructions;


class Action
{
	const TYPE__ABORT                 = "TYPE__ABORT";
	const TYPE__ASK_USER_IF_CONTINUE  = "TYPE__ASK_USER_IF_CONTINUE";
	const TYPE__ROLLBACK              = "TYPE__ROLLBACK";
	const TYPE__CONTINUE              = "TYPE__CONTINUE";
	const TYPE__HALT                  = "TYPE__HALT";
	const TYPE__USER_INFORM           = "TYPE__USER_INFORM";
	const TYPE__USER_WARN             = "TYPE__USER_WARN";
	const TYPE__SIMULATE_FAILURE      = "TYPE__SIMULATE_FAILURE";

	private $type ;
	private $msg  = '';

	/**
	 * @param $type mixed One of the TYPE__* constants of this class
	 * @param string $msg
	 */
	public function __construct($type, $msg="")
	{
		$this->type =  $type;
		$this->msg  =  $msg;
	}



	/**
	 * @return string
	 */
	public function getMsg()
	{
		return $this->msg;
	}

	/**
	 * @return mixed
	 */
	public function getType()
	{
		return $this->type;
	}
} 