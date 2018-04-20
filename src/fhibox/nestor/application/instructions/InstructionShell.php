<?php

namespace fhibox\nestor\application\instructions;

use fhibox\jeeves\core\AppCommand;
use fhibox\string\StringLib;


/**
 * @author fhi
 */
class InstructionShell extends Instruction
{
	const RETURN_CODE__SUCCESS = 0;

	/**
	 * Unix shell command to be run
	 * @var string
	 */
	protected $shellInstruction;

	/**
	 * Unix shell command to be run if the instruction has to be rolled back
	 * @var string
	 */
	protected $shellInstructionRollback;

	protected $result;


	/**
	 * @param string           $instruction
	 * @param AppCommand       $caller
	 */
	public function __construct($instruction, AppCommand $caller = null)
	{
		parent::__construct($caller);

		$this->shellInstruction = $instruction;
	}

	/**
	 * Chainable.
	 * @param $instruction string Unix shell command to be run if the instruction has to be rolled back
	 */
	public function defineRollback($instruction)
	{
		$this->shellInstructionRollback =  $instruction;
		return $this;
	}

	/**
	 *
	 * @return string[]
	 */
	public function getShellInstruction()
	{	return $this->shellInstruction;
	}

	public function __toString()
	{	return $this->shellInstruction;
	}

	/**
	 * @return int result of the shell command (usually 0 if OK, something else if NOK)
	 */
	public function execute_()
	{
		if ($this->isVirtualMode())
		{
			$this->caller->displayMessage("(VIRTUAL MODE) Executing: $this->shellInstruction", AppCommand::DISPLAY_MESSAGE__INFO);
			$this->result=self::RETURN_CODE__SUCCESS;
		}
		else
		{	// See http://stackoverflow.com/questions/732832/php-exec-vs-system-vs-passthru

			$this->caller->displayMessage("Executing: $this->shellInstruction", AppCommand::DISPLAY_MESSAGE__INFO);

			/*        */  isset($this->logger) && $this->logger->info("Executing : " . $this->shellInstruction);

			system(StringLib::removeNewlines($this->shellInstruction), $this->result);

			/*        */  isset($this->logger) && $this->logger->debug("Result=$this->result");
		}
		return $this->result;
	}

	public function getResult_()
	{	return $this->result;
	}

	public function isResultFailure_()
	{	return ! $this->isResultSuccess_();
	}

	public function isResultSuccess_()
	{	return $this->getResult() == self::RETURN_CODE__SUCCESS;
	}




	public function rollback_()
	{ /*        */  isset($this->logger) && $this->logger->debug("Rolling back!");
		echo "ROLLING BACK!\n";

		// TODO rollback should be a Instruction object of its own => some refactoring to do

		if ($this->isVirtualMode())
		{
			$this->caller->displayMessage("(VIRTUAL MODE) Executing: $this->shellInstructionRollback", AppCommand::DISPLAY_MESSAGE__INFO);
			$this->result = self::RETURN_CODE__SUCCESS;
		}
		else
		{
			$this->caller->displayMessage("Executing: $this->shellInstructionRollback", AppCommand::DISPLAY_MESSAGE__INFO);

			/*        */	isset($this->logger) && $this->logger->info("Executing : " . $this->shellInstructionRollback);
/*
			system("echo 'calling svn st'");
			system("cd /my/work/var/prod3/sources; svn status –u;");
			system("echo '2'");
			system("cd /my/work/var/prod3/sources; svn status -u | more");
			system("echo '3'");
			system("cd /my/work/var/prod3/sources; svn status -u 2>&1;");
*/


			system(StringLib::removeNewlines($this->shellInstructionRollback), $this->result);  // TODO store in rollbackResult

			/*        */  isset($this->logger) && $this->logger->debug("Result=$this->result");
		}
		
		return $this->result;
	}


}