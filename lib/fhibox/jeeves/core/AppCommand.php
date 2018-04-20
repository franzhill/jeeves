<?php

namespace fhibox\jeeves\core;

use fhibox\jeeves\core\exceptions\ConfirmationDeclinedException;
use fhibox\jeeves\core\exceptions\JeevesException;
use fhibox\jeeves\core\exceptions\PrivateException;
use fhibox\jeeves\core\exceptions\ProgrammingException;
use fhibox\jeeves\Jeeves;
use fhibox\jeeves\core\exceptions\OptionOrArgumentInvalidException;
use fhibox\jeeves\core\exceptions\NoUserConfirmationOnPromptException;

use fhibox\logging_interface\ILoggingInterface;
use fhibox\configuration\ConfigurationLoader;
use fhibox\configuration\IConfiguration;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Helper\HelperInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;


/**
 * A "Symfony Console" command, callable from the console.
 *
 * @author fhi
 */
abstract class AppCommand extends SymfonyCommand implements ICommandTestable
{
	const  DIR_SEP  = "/";
	const  OPTION   = 1;
	const  ARGUMENT = 2;

	const  DISPLAY_MESSAGE__INFO     = "info";
	const  DISPLAY_MESSAGE__COMMENT  = "comment";
	const  DISPLAY_MESSAGE__QUESTION = "question";
	const  DISPLAY_MESSAGE__ERROR    = "error";

	/**
	 * @var InputInterface
	 */
	protected $input;

	/**
	 * @var OutputInterface
	 */
	protected $output;

	/**
	 * @var Option[] $options    // unfortunately this type hinting for arrays does not seem to work very well - Well it seems to work in a foreach loop ...
	 */
	private $options = array();

	/**
	 * Processed values of options
	 * The keys are the options' ids.
	 * E.g. : $optionsValues['id_of_option'} = processed_value_for_that_option
	 * @var array(string => mixed)
	 */
	private $optionValues = array();

	/**
	 * Processed values of arguments
	 * The keys are the arguments' ids.
	 * E.g. : $argumentValues['id_of_option'} = processed_value_for_that_argument
	 * @var array(string => mixed)
	 */
	private $argumentValues = array();

	/**
	 * @var Argument[] $arguments  // unfortunately this type hinting for arrays does not seem to work very well
	 */
	private $arguments = array();

	/**
	 * @var boolean $isTestMode
	 */
	protected $isTestMode = false;

	/**
	 * Command will be run in virtual mode
	 * Command is not really run, a description of what it should do is printed.
	 * @var boolean $isVirtualMode
	 */
	protected $isVirtualMode = false;

	protected $isVerbosityTransmit = false;


	/**
	 * @var IConfiguration $config config for the current command
	 */
	protected $config    ;

	/**
	 * @var IConfiguration $configEnv config specific for the target environment
	 * @todo possibly move elsewhere
	 */
	protected $configEnv;

	protected $conf;

	/**
	 * @var Jeeves $valetParent
	 */
	protected $valetParent;

	protected $verbosity = OutputInterface::VERBOSITY_NORMAL;

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
	 * @called_by OptionOrArgument
	 * @return ILoggingInterface
	 */
	public function getLogging()
	{ return $this->logging;
	}

// </LOGGING>
// ------------------------------------------------------------------------------






	/**
	 * @param Jeeves $parent Parent 'Valet' application
	 */
	public function __construct(Jeeves $parent)
	{
		$this->valetParent = $parent;

		/*        */	$this->logging = $this->valetParent->getLogging();
		/*        */	$this->logger = $this->logging->getLogger(str_replace('\\', '.', __CLASS__));
		/*        */	$this->logger->debug("");


		// Load the command's conf
		$loader         = new ConfigurationLoader(PROJECT_CONF_PATH);
		$loader         -> setLogging($parent->getLogging());
		/*        */	$this->logger->debug("Current command real class =" . get_class($this));
		$this->conf     = $loader->loadForClass($this);

		$this->registerOptions();
		$this->registerArguments();

		parent::__construct();
	}





	/**
	 * Build list of all options for this command
	 */
	private function registerOptions()
	{
		// Get list of option ids from config file:
		$arr_options = $this->conf->get('option');
		/*        */	$this->logger->trace("arr_options=".print_r($arr_options, true));
		if (is_array($arr_options) )   // note : null is not considered an array
		{
			foreach ($arr_options as $opt_id) {
				$this->options[] = $this->valetParent->getOptionById($opt_id, $this);
			}
		}
	}


	/**
	 * Build list of all arguments for this command (same as above)
	 */
	private function registerArguments()
	{
		// Get list of argument ids from config file:
		$arr_arguments = $this->conf->get('argument');
		/*        */	$this->logger->trace("arr_arguments=".print_r($arr_arguments, true));
		if (is_array($arr_arguments) )   // note : null is not considered an array
		{
			foreach ($arr_arguments as $arg_id) {
				$this->arguments[] = $this->valetParent->getArgumentById($arg_id, $this);
			}
		}
	}





	/**
	 * Called by constructor of Symfony/Command
	 *  @override Symfony/Command's own.
	 *
	 * Here in this function, this is what should be done:
	 * set command's:
	 * - name
	 * - description
	 * - arguments
	 * - options
	 *
	 * @return void
	 */
	protected final function configure()
	{	/*        */	if (! isset($this->logger)) { $this->logger = \Project::$loggingSolution->getLogger(str_replace('\\', '.', __CLASS__)); }
		/*        */	$this->logger->debug("");

		foreach ($this->options as $option)
		{	/** @var Option $option */
			$this->addOption(
			                 $option->getName        ()  ,
			                 $option->getShortcut    ()  ,
			                 $option->getMode        ()  ,
			                 $option->getDescription ()
			                 // We'll be managing default values on our own - we want to introduce
			                 // the possibility for confirmation to be asked to user upon taking defaults
			                 #$option->getDefault     ()
			                 );
		}
		foreach ($this->arguments as $argument)
		{	/** @var Argument $argument */
			$this->addArgument(
			                   $argument->getName        ()  ,
			                   $argument->getMode        ()  ,
			                   $argument->getDescription ()
			                   // We'll be managing default values on our own - we want to introduce
			                   // the possibility for confirmation to be asked to user upon taking defaults
			                   #$option->getDefault     ()
			                  );
		}

		$this->setName        ($this->conf->get('name') );
		/*        */	$this->logger->debug("this->conf->get('help')=".$this->conf->get('help'));
		$this->setDescription ($this->conf->get('description') );
		$this->setHelp        ($this->conf->get('help'));

	}


	/**
	 * Called by Symfony/Command
	 * @override Symfony/Command's own.
	 *
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int|null|void
	 */
	protected final function execute(InputInterface $input, OutputInterface $output)
	{  /*        */	$this->logger->debug("");

		/*        */	try 	{

		$this->input  = $input;
		$this->output = $output;

		/*        */	$this->logger->debug("output->getVerbosity()=".$output->getVerbosity());

		// Process the verbosity level requested by the command and pass it on to logger
		// Nota : functions like $output->isQuiet() are not available in Symfony\Console v2.7
		// contrary to what the doc says ... http://symfony.com/doc/current/components/console/introduction.html#creating-a-basic-command
		$this->verbosity = $output->getVerbosity();

		// Actually process options and arguments now (and not before i.e. not on construction)
		// i.e. parse their value, process them (validation, decoration ... where applies)
		// and store them in arrays to make tehm available to extending commands.
		foreach ($this->options as $option)
		{	$this->optionValues[$option->getId()] = $this->processOption($option->getName());
		}

		foreach ($this->arguments as $argument)
		{	$this->argumentValues[$argument->getId()] = $this->processArgument($argument->getName());
		}

		$this->execute_();


		/*        */	}	catch(NoUserConfirmationOnPromptException $e ) {
		/*        */			$this->abort("Aborting following lack of confirmation...");
		/*        */	}	catch(\Exception $e ) {
		/*        */		  $e = new JeevesException('', 0, $e);
		/*        */		  $this->abort("Error while executing command. Error message is: " . $e->getMessage(). ". Aborting ...");
		/*        */  }

	}


	/**
	 * To be defined in extending class.
	 * Contains what the command actually does.
	 * @return mixed
	 */
	abstract protected function execute_();


	/**
	 * Asks a confirmation question to the user
	 * @param string $question
	 * @return bool True if user confirmes, false otherwise
	 */
	public function askForUserConfirmation($question)
	{
		/**
		 * @var $dialog HelperInterface
		 * TODO is deprecated since Symfony 2.5 - Replace dialog with helper
		 */
		$dialog = $this->getHelperSet()->get('dialog');
		return $dialog->askConfirmation($this->getOutput(),
			"<".self::DISPLAY_MESSAGE__QUESTION.">" . $question . " [y for yes/confirm, any other letter otherwise] </".self::DISPLAY_MESSAGE__QUESTION.">",
			false
		);
	}

	/**
	 * @param string $question
	 * @return mixed value (or values - TODO not supported yet) input by user
	 */
	public function askForUserInput($question)
	{
		$helper   = $this->getHelper('question');
		$question = new Question("<".self::DISPLAY_MESSAGE__QUESTION.">". $question . "</".self::DISPLAY_MESSAGE__QUESTION.">", '');
		$values    = $helper->ask($this->getInput(), $this->getOutput(), $question);

		return $values;
	}

	/**
	 * Abort command, displaying an error message first
	 * Public because accessed from arguments and options (this choice is open to debate)
	 * @param string $error_message
	 */
	public function abort($msg='')
	{	/*        */	$this->logger->debug("");
		$this->displayMessage("Aborting... Reason(s) : " . $msg, self::DISPLAY_MESSAGE__ERROR);
		exit();
	}

/**
	 * @param $msg
	 * @param string $level One of the DISPLAY_MESSAGE__* constants
	 */
	public function displayMessage($msg, $level=self::DISPLAY_MESSAGE__INFO)
	{	/*        */	$this->logger->debug("");
		$this->output->writeln("<$level>$msg</$level>");
	}

	/**
	 * Process the option,  i.e. read the value passed to it, possibly apply some treatment
	 * (validation, decoration...) and return the resulting value
	 *
	 * @param string $name     Name of the option, as returned by Option::getNameStatic()
	 * @return mixed           The value(s) for that option
	 */
	private function processOption(/* string */ $name)
	{ /*        */	$this->logger->debug("Processing option : $name...");
		return $this->processOptionOrArgument($name, self::OPTION);
	}

	/**
	 * See processOption()
	 * @param $name
	 * @return mixed
	 * @throws OptionOrArgumentInvalidException
	 */
	private function processArgument(/* string */ $name)
	{ /*        */	$this->logger->debug("Processing argument : $name...");
		return $this->processOptionOrArgument($name, self::ARGUMENT);
	}


	/**
	 * Process the option/argument, i.e. read the value passed to it, apply some treatment
	 * (validation, decoration...) and return the resulting value.
	 *
	 * @param string $name Name of the option, as returned by Option::getNameStatic()
	 * @param $opt_or_arg int  One of self::OPTION or self::ARGUMENT
	 * @throws ConfirmationDeclinedException
	 * @throws OptionOrArgumentInvalidException
	 * @throws \Exception
	 * @return mixed           The value for that option/argument
	 */
	private function processOptionOrArgument($name, $opt_or_arg)
	{	/*        */	$this->logger->debug("********Processing option/argument : $name...");
		/*        */  try {

		//@formatter:off
		// Get the value(s) (can be a string or a string[])
		switch ($opt_or_arg)
		{ case self::OPTION   : $val = $this->input->getOption($name);
			                      $opt = $this->getOption($name);
			                      break;
			case self::ARGUMENT : $val = $this->input->getArgument($name);
			                      $opt = $this->getArgument($name);
			                      break;
			default : throw new ProgrammingException("opt_or_arg not as expected: [$opt_or_arg]");
		}
		//@formatter:on

		$val = $opt->handleDefault($val);
		if (!  $opt->preValidate  ($val))    {	throw new PrivateException("VAL"); }
		$val = $opt->decorate     ($val);
		if (!  $opt->validate     ($val))    {	throw new PrivateException("VAL"); }
		if (!  $opt->confirm      ($val))    {	throw new PrivateException("CONF"); }
		$val = $opt->finalDecorate($val);

		/*        */  }
		/*        */  catch (ConfirmationDeclinedException $e)
		/*        */  {
		/*        */  	return $this->abort("La confirmation a été déclinée.");
		/*        */  }
		/*        */  catch (PrivateException $e)
		/*        */  {
		/*        */  	// TODO rethink the way messages are built, because this will be a nightmare when i18n is brought in
		/*        */  	if ($e->getLabel() == "VAL")
		/*        */  	{ $msg = "";
		/*        */  		switch ($opt_or_arg)        //@formatter:off
		/*        */  		{ case self::OPTION   : $msg .= "L'option "  ; break;
		/*        */  			case self::ARGUMENT : $msg .= "L'argument "; break;
		/*        */  			default              : throw new ProgrammingException();
		/*        */  		}                           //@formatter:on
		/*        */
		/*        */  		$msg .= " [$name] or its value is not valid.\n";
		/*        */  		$msg .= "Détail:";
		/*        */  		$msg .= "\n - " . implode("\n - ", $opt->getValidationErrorMessages());
		/*        */  		$this->logger->debug("Throwing exception with msg = $msg");
		/*        */  		throw new OptionOrArgumentInvalidException($msg);
		/*        */  	}
		/*        */  	if ($e->getLabel() == "CONF")
		/*        */  	{	$msg = "User did not confirm for ";
		/*        */  		switch ($opt_or_arg)        //@formatter:off
		/*        */  		{ case self::OPTION   : $msg .= "option "  ; break;
		/*        */  			case self::ARGUMENT : $msg .= "argument "; break;
		/*        */  			default             : throw new ProgrammingException();
		/*        */  		}                           //@formatter:on
		/*        */  		$msg .= " [$name]";
		/*        */  		throw new ConfirmationDeclinedException($msg);
		/*        */  	}
		/*        */  	throw new ProgrammingException();
		/*        */  }
		/*        */  catch (\Exception $e)
		/*        */  {
		/*        */  	$msg = "An error occurred while processing option or argument [$name]";
		/*        */  	throw new \RuntimeException($msg,0,$e);
		/*        */  }


		return $val;
	}







	/**
	 * Return the option object registered to the current command.
	 * Used : when wanting to perform a check on option's value, we need get the option object
	 * @param $name string
	 * @return Option
	 * @throws \Exception no option with given name found for the current command
	 */
	private function getOption($name)
	{
		foreach ($this->options as $option) {
			if ($option->getName() == $name) {
				return $option;
			}
		}
		throw new \Exception("Option : $name not found.");
	}


	/**
	 * Return the argument object registered to the current command.
	 * Used : when wanting to perform a check on argument's value, we need get the argument object
	 * @param $name
	 * @return Argument
	 * @throws \Exception no argument with given name found for the current command
	 */
	private function getArgument(/* string */ $name)
	{
		foreach ($this->arguments as $argument) {
			if ($argument->getName() === $name) {
				return $argument;
			}
		}
		throw new \Exception("Argument : $name not found.");
	}


	public function getOutput()
	{ return $this->output;
	}

	public function getInput()
	{ return $this->input;
	}


	public function isTestMode()
	{	return $this->isTestMode;
	}


	/**
	 * @param boolean $value
	 * @return void
	 */
	protected function setTestMode($value)
	{	!is_null($value) && $this->isTestMode = $value;
	}


	protected function setTestModeOn()
	{	/*        */	$this->logger->debug("");
		$this->isTestMode = true;
	}


	protected function setTestModeOff()
	{	$this->isTestMode = false;
	}


	public function isVirtualMode()
	{	return $this->isVirtualMode;
	}

	/**
	 * @param boolean $value
	 */
	public function setVirtualMode($value)
	{	!is_null($value) && $this->isVirtualMode = $value;
	}
	

	public function setVirtualModeOn()
	{	$this->isVirtualMode = true;
	}



	public function setVerbosityTransmit($value)
	{	!is_null($value) && $this->isVerbosityTransmit = $value;
	}


	/**
	 * Return the verbosity assigned to this command.
	 * Verbosity is set via a global option. See Symfony/Console doc.
	 * @return int one of the verbosity levels, as defined by http://symfony.com/doc/current/components/console/introduction.html#verbosity-levels
	 */
	public function getVerbosity()
	{
		return $this->verbosity;
	}

	/**
	 * Return the verbosity assigned to this command, in an option-like format.
	 * Verbosity is set via a global option. See Symfony/Console doc.
	 * @return string '-q', '', '-v', '-vv' or '-vvv', depending on the current verbosity
	 */
	public function getVerbosityAsOption()
	{
		switch ($this->verbosity)
		{ case OutputInterface::VERBOSITY_QUIET        : $ret = '-q' ; break;
			case OutputInterface::VERBOSITY_NORMAL       : $ret = '' ; break;
			case OutputInterface::VERBOSITY_VERBOSE      : $ret = '-v' ; break;
			case OutputInterface::VERBOSITY_VERY_VERBOSE : $ret = '-vv' ; break;
			case OutputInterface::VERBOSITY_DEBUG        : $ret = '-vvv' ; break;
			default                                      : $ret = '' ; break;
		}
		return $ret;
	}



	public function setVirtualModeOff()
	{	$this->isVirtualMode = false;
	}


	/**
	 * @param $option_id
	 * @return mixed the processed value for that option, null if $option_id not found in the array of processed options
	 *               (i.e. e.g. if option is not passed and has no default specified)
	 */
	public function getOptionValue($option_id)
	{
		if (array_key_exists($option_id, $this->optionValues))
		{	return $this->optionValues[$option_id];
		}
		else
		{ return null;
		}
	}


	/**
	 * @param $argument_id
	 * @return mixed the processed value for that argument, null if $argument_id not found in the array of processed arguments
	 */
	public function getArgumentValue($argument_id)
	{
		if (array_key_exists($argument_id, $this->argumentValues))
		{	return $this->argumentValues[$argument_id];
		}
		else
		{ return null;
		}
	}

	
	
	public function getConf()
	{	return $this->conf;
	}
}



