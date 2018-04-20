<?php
/**
 * Created by PhpStorm.
 * User: Francois hill
 * Date: 11/11/201x
 * Time: 16:07
 */

namespace fhibox\nestor\application\instructions;

/**
 * NOT USED AS OF YET
 *
 */
class InstructionStack extends Instruction
{
	/**
	 * The stack of instructions
	 * @var Instruction[] $instructions
	 */
	private $instructions = array();


	public function add(Instruction $instr)
	{
		$this->instructions[] = $instr;
		return $this;
	}




	/**
	 * The meat of the command.
	 * @return mixed
	 */
	public function execute_()
	{
		foreach ($this->instructions as $instr)
		{
			$instr->execute();
		}
	}

	public function getResult_()
	{
		// Return result of last instruction
		// Other strategies might be possible
		if (empty($this->instructions)) return null; // we could maybe throw an Exception
		/** @var $i Instruction */
		$i=end($this->instructions);
		return $i->getResult();
	}

	public function isResultSuccess_()
	{
		// Is success if all instructions were executed succesfully
		$is_success = true;
		foreach ($this->instructions as $instr)
		{
			$is_success = $is_success && $instr->isResultSuccess();
		}
		return $is_success;
	}

	public function isResultFailure_()
	{
		return ! $this->isResultSuccess_();
	}
}

