<?php
/**
 * Created by PhpStorm.
 * User: Francois hill
 * Date: 03/11/201x
 * Time: 10:53
 */

namespace fhibox\nestor\application\instructions;


use fhibox\jeeves\core\AppCommand;


/**
 * Because we're using PHP 5.3, new Object() ... is not chainable
 * Using this builder's make() will make it possible to chain.
 *
 * @author fhi
 */
class InstructionShellBuilder
{
	/**
	 * @var AppCommand
	 */
	private $caller;

	public function __construct(AppCommand $caller)
	{
		$this->caller = $caller;
	}


	public function make($instruction)
	{
		return new InstructionShell(trim($instruction), $this->caller);
	}

	/**
	 * Alias
	 * @param $instruction
	 */
	public function define($instruction)
	{
		return $this->make($instruction);
	}




} 