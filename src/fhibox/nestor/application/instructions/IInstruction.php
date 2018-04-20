<?php

namespace fhibox\nestor\application\instructions;

/**
 * Created by PhpStorm.
 * User: Francois hill
 * Date: 19/11/2014
 * Time: 00:19
 */
interface IInstruction
{
	/**
	 * The meat of the instruction.
	 * @return mixed
	 */
	public function execute();


	public function getResult();
}