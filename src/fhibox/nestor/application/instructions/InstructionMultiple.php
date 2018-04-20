<?php
/**
 * Created by PhpStorm.
 * User: Francois hill
 * Date: 02/11/201x
 * Time: 18:14
 */


namespace fhibox\nestor\application\instructions;




use fhibox\jeeves\core\AppCommand;
use fhibox\jeeves\core\ICommandTestable;
use fhibox\logging_interface\ILoggingInterface;


/**
 * NOT USED AS OF YET
 *
 * A generic command, with a run() function, a test mode
 * @package fhibox
 */
class InstructionMultiple implements IInstruction
{

	/**
	 * @var boolean $result
	 */
	protected $result;


	/**
	 * @var Instruction[] $instructions
	 */
	private $instructions = array();


	public function add(Instruction $instr)
	{
		$this->instructions[] = $instr;
		return $this;
	}



	/**
	 * The meat of the instruction.
	 * @return mixed
	 */
	public function execute()
	{
		// TODO: Implement execute() method.
	}

	public function getResult()
	{
		// TODO: Implement getResult() method.
	}
}