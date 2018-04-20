<?php

namespace fhibox\nestor\application\instructions;




use fhibox\jeeves\core\AppCommand;
use fhibox\jeeves\core\exceptions\ProgrammingException;
use fhibox\jeeves\core\ICommandTestable;
use fhibox\logging_interface\ILoggingInterface;


/**
 * An instruction mini-framework with hooks
 *
 * @package fhibox
 * @author fhi
 *
 * Example of use
 * $isb = new InstructionShellBuilder($this);   # $this is an AppCommand
 * $isb
 *  ->define("cd $vcs_work_dir ; svn up -r $rev --parents --set-depth infinity $path_in_vcs_work_dir")
 *  ->defineRollback("echo 'Testing the rollback feature!...'")
 *    ->onDuring()
 *      ->doSimulateFailure("Simulating failure!")
 *    ->onFailure()
 *      ->doWarnUser("Instruction failed")
 *      ->doAskUserIfContinue("Should we continue ?")            // If user says yes, then following steps of current on* event are short-circuited
 *      ->doRollback("Performing rollback...")                   // Executed only if user declines at step above
 *      ->doAbort("User declined to continue following failure") // Same
 *  ->execute();
 *
 * For more examples, see CommandDeploy
 *
 * To see what actions are possible on what events, look inside execute()
 */
abstract class Instruction implements IInstruction, ICommandTestable
{
	const STATE__RECORDING_ON_FAILURE        = "STATE__RECORDING_ON_FAILURE";
	const STATE__RECORDING_ON_SUCCESS        = "STATE__RECORDING_ON_SUCCESS";
	const STATE__RECORDING_ON_PRE_EXECUTE    = "STATE__RECORDING_ON_PRE_EXECUTE";
	const STATE__RECORDING_ON_DURING_EXECUTE = "STATE__RECORDING_ON_DURING_EXECUTE";
	const STATE__RECORDING_ON_POST_EXECUTE   = "STATE__RECORDING_ON_POST_EXECUTE";


	/**
	 * @var boolean $isTestMode
	 */
	protected $isTestMode = true;

	/**
	 * Command will be run in virtual mode
	 * Command is not really run, a description of what it should do is printed.
	 * @var boolean $isVirtualMode
	 */
	protected $isVirtualMode = false;

	protected $caller;

	/**
	 * State, while 'recording' planned actions according to outcomes
	 * Calls to on*() functions modify this state.
	 * @var string One of the STATE__RECORDING_* constants
	 */
	protected $state;

	/**
	 * Records the actions to be taken upon failure of this instruction
	 * @var Action[]
	 */
	protected $failureActions = array();

	/**
	 * Records the actions to be taken upon success of this instruction
	 * @var Action[]
	 */
	protected $successActions = array();

	/**
	 * Records the actions to be taken just before execution of this instruction
	 * @var Action[]
	 */
	protected $preExecuteActions = array();

	/**
	 * Records the actions to be taken during execution of this instruction
	 * @var Action[]
	 */
	protected $duringExecuteActions = array();

	/**
	 * Records the actions to be taken just after execution of this instruction
	 * @var Action[]
	 */
	protected $postExecuteActions = array();




	/**
	 * Points to thc current action arrays (*Actions)
	 * @var Action[]
	 */
	private $recordingActionsArray;

	/**
	 * Used to indicate th failure upon execution shoud be simulated (Useful for testing).
	 * @var boolean
	 */
	private $simulateFailure = false;

	private $simulateFailureMsg = '';
	
	/**
	 * Used to indicate that execution of instruction should be halted
	 * @var bool
	 */
	private$halt = false;




// ------------------------------------------------------------------------------
// <LOGGING>

	/**
	 * @var mixed  Logger object, see setter.
	 */
	protected $logger;

	/**
	 * @var ILoggingInterface   See setter.
	 */
	protected $logging;

	/**
	 * Use this when you want to assign a logger to this class.
	 * If no logger is assigned to this class, it will not output any logs.
	 *
	 * @param $logger mixed A logger object that should at least provide the standard logging functions
	 *                (debug(), info(), warn() etc.)
	 */
	public function setLogger($logger)
	{ $this->logger = $logger;
	}

	/**
	 * Use this instead of setLogger() when you want this class to setup its own
	 * logger, based on a logging solution interface.
	 * The logger assigned to this class will then be based on this class' name.
	 * @param ILoggingInterface $log_i
	 * @return $this
	 */
	public function setLogging(ILoggingInterface $log_i)
	{
		$this->logging = $log_i;
		$this->logger  = $this->logging->getLogger(str_replace('\\', '.', __CLASS__));
		return $this;
	}
	/**
	 * @called_by
	 * @return ILoggingInterface
	 */
	public function getLogging()
	{ return $this->logging;
	}

// </LOGGING>
// ------------------------------------------------------------------------------




	/**
	 *
	 * @param ICommandTestable $caller the calling command. This command will inherit some
	 *                         of the calling command's settings, such as test mode and 
	 *                         virtual mode.
	 */
	public function __construct(AppCommand $caller = null)
	{
		$this->caller = $caller;
		if (!is_null($caller))
		{
			/*        */	$this->logging = $caller->getLogging();
			/*        */	$this->logger = $this->logging->getLogger(str_replace('\\', '.', __CLASS__));
			/*        */	isset($this->logger) && $this->logger->debug("");

			$this->setTestMode   ($caller->isTestMode   ());
			$this->setVirtualMode($caller->isVirtualMode());
		}
	}


// ------------------------------------------------------------------------------
// <ROLLBACK/UNDO>
//  : TODO - This functionality has not been tackled seriously yet

	/**

	 * @var Instruction
	 */
	protected $undoInstruction;


	/**
	 * Chainable.
	 * @param $instruction string Unix shell command to be run if the instruction has to be rolled back
	 * @return $this
	 */
	public function defineRollback($instruction=null)
	{
		$this->undoInstruction =  $instruction;
		return $this->defineRollback_($this->undoInstruction);
	}

	/**
	 * Open for overriding
	 */
	protected function defineRollback_()
	{ return $this;
	}

// </ROLLBACK/UNDO>
// ------------------------------------------------------------------------------


// ------------------------------------------------------------------------------
// <LET CLIENT DEFINE EVENT (AKA STATE)>

	/**
	 * "Open" the description sequence of actions to take on failure of this instruction.
	 * Call on an instruction object, then chain with one of the do*() functions.
	 * @return $this
	 */
	public final function onFailure()
	{
		$this->state                 = self::STATE__RECORDING_ON_FAILURE;
		$this->recordingActionsArray = &$this->failureActions;
		return $this;
	}

	/**
	 * "Open" the description sequence of actions to take on success of this instruction.
	 * Call on an instruction, then chain with one of the do*() functions.
	 * @return $this
	 */
	public final function onSuccess()
	{
		$this->state                 = self::STATE__RECORDING_ON_SUCCESS;
		$this->recordingActionsArray = &$this->successActions;
		return $this;
	}

	/**
	 * "Open" the description sequence of actions to take just before execution of this instruction.
	 * Call on an instruction, then chain with one of the do*() functions.
	 * @return $this
	 */
	public final function onBefore()
	{
		$this->state                 = self::STATE__RECORDING_ON_PRE_EXECUTE;
		$this->recordingActionsArray = &$this->preExecuteActions;
		return $this;
	}

	/**
	 * "Open" the description sequence of actions to take during (or instead...) execution of this instruction.
	 * Call on an instruction, then chain with one of the do*() functions.
	 * @return $this
	 */
	public final function onDuring()
	{
		$this->state                 = self::STATE__RECORDING_ON_DURING_EXECUTE;
		$this->recordingActionsArray = &$this->duringExecuteActions;
		return $this;
	}


	/**
	 * "Open" the description sequence of actions to take just after execution of this instruction.
	 * Call on an instruction, then chain with one of the do*() functions.
	 * @return $this
	 */
	public final function onAfter()
	{
		$this->state                 = self::STATE__RECORDING_ON_POST_EXECUTE;
		$this->recordingActionsArray = &$this->postExecuteActions;
		return $this;

	}


	/**
	 * Verify that current state is coherent
	 */
	private function checkState()
	{	if (! isset($this->recordingActionsArray) )
		{	throw new ProgrammingException("One of onFailure() or onSuccess must be called first.");  // Should never happen
		}
	}

// </LET CLIENT DEFINE EVENT>
// ------------------------------------------------------------------------------


// ------------------------------------------------------------------------------
// <LET CLIENT DEFINE ACTION ON EVENT>

	/**
	 * Chainable.
	 * Call after onFailure() or onSuccess() to define action to be taken
	 * @return $this
	 */
	public final function doAbort($msg)
	{
		$this->checkState();
		$this->recordingActionsArray[] = new Action(Action::TYPE__ABORT, $msg);
		return $this;
	}

	/**
	 * See doAbort()
	 * Will ask the user whether to continue with the execution of the instruction or not.
	 * If user responds positively, the rest of the planned actions on the current on* event is shunted
	 * (not executed) and the execution pointer moves on to the next on* event (i.e. the rest of the
	 * planned actions are executed).
	 * If user responds negatively, the rest of the planned actions on the current on* event are executed
	 * and the execution of the instruction is stopped thereafter.
	 * @return $this
	 */
	public final function doAskUserIfContinue($msg = '')
	{
		$msg = (empty($msg)) ? "Continuer ?" : $msg;
		$this->checkState();
		$this->recordingActionsArray[] = new Action(Action::TYPE__ASK_USER_IF_CONTINUE, $msg);
		return $this;
	}

	/**
	 * See doAbort()
	 * @return $this
	 */
	public final function doRollback($msg="")
	{
		$this->checkState();
		$this->recordingActionsArray[] = new Action(Action::TYPE__ROLLBACK, $msg);
		return $this;
	}

	/**
	 * See doAbort()
	 * @return $this
	 */
	public final function doContinue($msg)
	{
		$this->checkState();
		$this->recordingActionsArray[] = new Action(Action::TYPE__CONTINUE, $msg);
		return $this;
	}

	/**
	 * See doAbort()
	 * Halt the execution of the current instruction.
	 */
	public final function doHalt($msg='')
	{
		$this->checkState();
		$this->recordingActionsArray[] = new Action(Action::TYPE__HALT, $msg);
		return $this;
	}

	/**
	 * See doAbort()
	 * @return $this
	 */
	public final function doInformUser($msg)
	{
		$this->checkState();
		$this->recordingActionsArray[] = new Action(Action::TYPE__USER_INFORM, $msg);
		return $this;
	}

	/**
	 * See doAbort()
	 * @return $this
	 */
	public final function doWarnUser($msg)
	{
		$this->checkState();
		$this->recordingActionsArray[] = new Action(Action::TYPE__USER_WARN, $msg);
		return $this;
	}

	/**
	 * Practical for testing.
	 * Chainable
	 */
	public final function doSimulateFailure($msg='')
	{
		$this->checkState();
		$this->recordingActionsArray[] = new Action(Action::TYPE__SIMULATE_FAILURE, $msg);
		return $this;
	}


// </LET CLIENT DEFINE ACTION ON EVENT>
// ------------------------------------------------------------------------------



// ------------------------------------------------------------------------------
// <DESCRIPTION OF ACTIONS INDEPENDENT OF EVENTS>

	/**
	 * @param $actions Action[]
	 * @return mixed
	 */
	private function processActions($actions)
	{
		foreach($actions as $action)
		{
			switch($action->getType())  //@formatter:off
			{
				case  Action::TYPE__ABORT                 : $this->abort                  ($action->getMsg());break;
				case  Action::TYPE__ASK_USER_IF_CONTINUE  : if ($this->askUserIfContinue  ($action->getMsg()))
				                                            {  // stop processing fail actions if user wants to continue
				                                               break 2;
				                                            }
				                                            break;
				case  Action::TYPE__ROLLBACK              : $this->rollback              ($action->getMsg());break;
				case  Action::TYPE__CONTINUE              : $this->continuee             ($action->getMsg());break;
				case  Action::TYPE__HALT                  : $this->halt = true  ;break 2;
				case  Action::TYPE__USER_INFORM           : $this->informUser            ($action->getMsg());break;
				case  Action::TYPE__USER_WARN             : $this->warnUser              ($action->getMsg());break;
				case  Action::TYPE__SIMULATE_FAILURE      : $this->simulateFailure       ($action->getMsg());break;
				default : 	throw new ProgrammingException("Action type not supported");  // Should never happen
			}
		}//@formatter:on

	}


	/**
	 * Boilerplate that just calls the recorded actions in function of result
	 */
	public final function execute()
	{
		// Pre-execute actions
		// -------------------
		$actions = $this->preExecuteActions;
		$this->processActions($actions);
		if ($this->halt) goto end;                      // Yes, goto !

		// During execute actions
		// -------------------
		$actions = $this->duringExecuteActions;
		$this->processActions($actions);
		if ($this->halt) goto end;

		// Execute
		// --------
		$this->execute_();
		/*        */	if ($this->simulateFailure) { isset($this->logger) && $this->logger->debug("Simulating failure!"); }

		// Post-execute actions
		// --------------------
		$actions = $this->postExecuteActions;
		$this->processActions($actions);
		if ($this->halt) goto end;

		// Failure or Success
		// ------------------
		if ($this->isResultFailure())
		{
			#// TODO not necessarily display message - sometimes Failure can be normal
			#// Ideally, commands should specify if their failure is problematic or not
			#$this->caller->displayMessage("Failure!", AppCommand::DISPLAY_MESSAGE__ERROR);

			if ($this->simulateFailure) { $this->caller->displayMessage("Simulated failure! : " . $this->simulateFailureMsg, AppCommand::DISPLAY_MESSAGE__ERROR);}

			$actions = $this->failureActions;
		}
		elseif ($this->isResultSuccess())
		{ $actions = $this->successActions;
		}
		else throw new ProgrammingException("Result is neither success nor failure, should be");
		$this->processActions($actions);
		if ($this->halt) goto end;

		end:
		return $this;
	}

// </DESCRIPTION OF ACTIONS INDEPENDENT OF EVENTS>
// ------------------------------------------------------------------------------


	public final function getResult()
	{	return $this->getResult_();
	}

	/**
	 * @return bool
	 */
	public final function isResultSuccess()
	{	if ($this->simulateFailure) return false;
		return $this->isResultSuccess_();
	}

	/**
	 * @return bool
	 */
	public final function isResultFailure()
	{	return !$this->isResultSuccess();
	}

	private function abort($msg)
	{	return $this->abort_($msg);
	}

	private function askUserIfContinue($msg)
	{	return $this->askUserIfContinue_($msg);
	}

	private function rollback($msg)
	{	return $this->rollback_($msg);
	}

	private function continuee($msg)
	{	return $this->continue_($msg);
	}

	private function informUser($msg)
	{	return $this->informUser_($msg);
	}

	private function warnUser($msg)
	{	return $this->warnUser_($msg);
	}

	private function simulateFailure($msg)
	{
		$this->simulateFailure    = true;
		$this->simulateFailureMsg = $msg;
	}






// ------------------------------------------------------------------------------
// <OPEN_FOR_OVEERRIDING>
// These functions describe the real behaviour of the instruction

	/**
	 * The meat of the command.
	 * @return mixed
	 */
	abstract public function execute_();

	abstract public function getResult_();

	abstract public function isResultSuccess_();

	abstract public function isResultFailure_();

	/**
	 * Open for overriding
	 * @return mixed
	 */
	protected function abort_($msg)
	{
		$this->caller->abort                 ($msg);
	}

	/**
	 * Open for overriding
	 * @return mixed
	 */
/*	protected function rollback_ ($msg)
	{
		// DO nothing for the moment
	}
*/

	/**
	 * Open for overriding
	 * @return mixed
	 */
	protected function continue_ ($msg)
	{ // DO nothing for the moment
	}

	/**
	 * Open for overriding
	 * @return mixed
	 */
	protected function informUser_ ($msg)
	{
		$this->caller->displayMessage($msg);
	}


	/**
	 * Open for overriding
	 * @return mixed
	 */
	protected function warnUser_ ($msg)
	{
		$this->caller->displayMessage($msg, AppCommand::DISPLAY_MESSAGE__ERROR);
	}


	/**
	 * Open for overriding
	 * @return bool True if user confirms, false otherwise
	 */
	protected function askUserIfContinue_ ($msg)
	{
		return $this->caller->askForUserConfirmation($msg);
	}

// </OPEN_FOR_OVEERRIDING>
// ------------------------------------------------------------------------------






	public function __toString()
	{
		return get_class($this);
	}




	/**
	 * @inheritdoc
	 */
	public function isTestMode()
	{	return $this->isTestMode;
	}


	/**
	 * @param boolean $value
	 * @return void
	 */
	protected function setTestMode($value)
	{	/*        */	isset($this->logger) && $this->logger->debug("value=$value");
		$this->isTestMode = $value;
	}


	protected function setTestModeOn()
	{	$this->isTestMode = true;
	}


	protected function setTestModeOff()
	{	$this->isTestMode = false;
	}


	/**
	 * @inheritdoc
	 */
	public function isVirtualMode()
	{	return $this->isVirtualMode;
	}


	/**
	 * @param $value
	 * @return void
	 */
	protected function setVirtualMode($value)
	{	$this->isVirtualMode = $value;
	}


	protected function setVirtualModeOn()
	{	$this->isVirtualMode = true;
	}


	protected function setVirtualModeOff()
	{	$this->isVirtualMode = false;
	}

}

