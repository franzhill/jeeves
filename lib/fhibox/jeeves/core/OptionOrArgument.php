<?php

namespace fhibox\jeeves\core;


use fhibox\configuration\ConfigurationLoader;
use fhibox\configuration\IConfiguration;
use fhibox\jeeves\core\exceptions\ConfirmationDeclinedException;
use fhibox\jeeves\core\exceptions\NoUserConfirmationOnPromptException;
use Symfony\Component\Console\Helper\HelperInterface;
use Symfony\Component\Console\Question\Question;


/**
 *
 * Memo :
 * Options/Arguments are (if applicable), in this order:
 * - preValidated
 * - decorated
 * - confirmed
 * - validated
 *
 * @author Francois hill
 */
abstract class OptionOrArgument # implements IDecoratable, IConfirmable, IValidatable
{
	// TODO maybe move elsewhere
	const CONF_KEY__ID                                  = "id"                                 ;
	const CONF_KEY__NAME                                = "name"                               ;
	const CONF_KEY__SHORTCUT                            = "shortcut"                           ;
	const CONF_KEY__MODE                                = "mode"                               ;
	const CONF_KEY__HELP                                = "help"                               ;

	const CONF_KEY__PROMPT_IF_NONE                      = "prompt_if_none"                     ;
	const CONF_KEY__PROMPT_IF_NONE_MSG                  = "prompt_if_none_msg"                 ;

	const CONF_KEY__DEFAULT                             = "default"                            ;
	const CONF_KEY__ASK_CONFIRMATION_BEFORE_DEFAULT     = "ask_confirmation_before_default"    ;
	const CONF_KEY__ASK_CONFIRMATION_BEFORE_DEFAULT_MSG = "ask_confirmation_before_default_msg";
	const CONF_KEY__ASK_CONFIRMATION_WHEN_VALUES        = "ask_confirmation_when_values"        ;
	const CONF_KEY__ASK_CONFIRMATION_WHEN_VALUES_MSG    = "ask_confirmation_when_values_msg"    ;

	/**
	 * @var \Logger
	 */
	protected $logger;

	/**
	 * Logger for use in static contexts
	 * @var \Logger
	 */
	public static $slogger;

	/**
	 * The command 'owning' this option/argument
	 * Provides a hook to command-provided functionalities (helper, output...)
	 * @var AppCommand $command
	 */
	protected $command;

	/**
	 * @var IConfiguration
	 */
	protected $conf;

	/**
	 * @var string[]
	 */
	protected $validationErrorMessages = array();




	/**
	 * A proxy for Symfony/Console::addOption() or Symfony/Console::addArgument()
	 *
	 * @param AppCommand $command   The command to which this option/argument belongs to.
	 *                              Provides a hook to command-provided functionalities (helper, output...)
	 */
	public function __construct(AppCommand $command)
	{
		/*        */	$this->logger = $command->getLogging()->getLogger(str_replace('\\', '.', __CLASS__));
		/*        */	isset($this->logger) && $this->logger->debug("");
		/*        */	isset($this->logger) && $this->logger->debug("Current option/argument real class =" . get_class($this));

		$loader         = new ConfigurationLoader(PROJECT_CONF_PATH);
		$loader         -> setLogging($command->getLogging());

		#/*        */	try {
		$this->conf     = $loader->loadForClass($this);
		#/*        */	} catch (ConfigurationException $e)
		#/*        */	{  isset($this->logger) && $this->logger->info("Config could not be loaded for class <".get_class($this).">, this may reflect a real problem, or not.");}
		/*        */   isset($this->logger) && $this->logger->debug("Configuration array, directly loaded : " . print_r($this->conf, true));

		$this->command  = $command;
	}

	/**
	 * @param $logger mixed A logger object that should at least provide the standard logging functions
	 *                (debug(), info(), warn() etc.)
	 */
	public function setLogger($logger)
	{ $this->logger = $logger;
	}



	# /**
	# * Is the value this option/argument has, the default value?
	# * (the user not having provided any)
	# */
	#public function isDefault()
	#{
	#	$default_value = $this->command->getDefinition()->getOption($this->getName())->getDefault();
	#
	#	/*       */  isset($this->logger) && $this->logger->debug("**********default_value = $default_value");
	#
	#	if (is_null($default_value))
	#	{	/*       */  isset($this->logger) && $this->logger->debug("is default value");
	#		return true;
	#	}
	#	return false;
	#}


	/**
	 * Handle default value for option/argument
	 * If no values are passed for option/argument and a default is defined, assign default to values.
	 * If a confirmation is required, ask for confirmation, throw exception if not confirmed.
	 *
	 * Happens before preValidation.
	 *
	 * @param $values
	 * @throws ConfirmationDeclinedException
	 * @return string|string[] The resulting values: untouched if not null or no default defined;
	 *                         with default value else (if defined) after they have been
	 */
	public function handleDefault($values)
	{ /*        */	 isset($this->logger) && $this->logger->debug("**********");
		/*        */	 isset($this->logger) && $this->logger->debug("values=".print_r($values, true));
		/*        */	 isset($this->logger) && $this->logger->debug("is_null(values)=". is_null($values) ? "true" : "false");
		/*        */	 isset($this->logger) && $this->logger->debug("this->getDefault()=". $this->getDefault());

		// If no value passed and prompt is required
		if ($values == null &&  ($this->getPromptIfNone() == true))  // memo : null evaluates to false
		{ //TODO handle multiple input
			// For the time being we'll only handle one input

			// Ask user to input a message :
			$values = $this->command->askForUserInput( $this->getPromptIfNoneMsg() );
		}


		// If no value passed and there's a default value
		if (empty($values) &&  ($this->getDefault() !== null))
		{
			if ($this->getAskConfirmationBeforeDefault())
			{
				if (!$this->askForUserConfirmation($this->getAskConfirmationForDefaultMsg()))
				{
					throw new ConfirmationDeclinedException("Confirmation declined.");
				}
			}
			// TODO checks : poach Symfony\Component\Console\Input\InputOption::setDefault()
			return $this->getDefault();
		}

		// Otherwise just return the valus untouched
		return $values;
	}



	/**
	 * Perform a first general validation on value(s) passed as option/argument
	 * Happens before decoration.
	 *
	 * E.g. this can be a formatting validation.
	 *
	 * Performs both on single values as well as arrays, by calling preValidate_()
	 * (called on each item in case of an array) which should be overridden if necessary.
	 * If this whole behaviour is not as desired, simply override this very function.
	 *
	 * Reminder: order: preValidate, decorate, validate, confirm
	 * @param string|string[] $values
	 * @return bool
	 */
	public function preValidate($values)
	{
		$valid = true;
		if ( is_array($values))
		{	foreach ($values as $value)
		{	$valid = $valid && $this->preValidate_($value);
		}
		}
		else
		{ $valid = $this->preValidate_($values);
		}
		return $valid;
	}


	/**
	 * To be overridden if option/argument does indeed require it.
	 *
	 * Return true or false whether value is valid or not.
	 * Do not throw exception, use addValidationErrorMessage().
	 *
	 * @param $value string A single value passed to this option/argument
	 * @return boolean
	 */
	protected function preValidate_($value)
	{
		return true;
	}

	/**
	 * Performs the 'decoration' of value(s) passed as option/argument
	 * I.e. pre-processes them, by transforming them (replace, add, remove parts etc.)
	 * Happens before validation.
	 *
	 * Performs both on single values as well as arrays, by calling decorate_()
	 * (called on each item in case of an array) which should be overridden if necessary.
	 * If this whole behaviour is not as desired, simply override this very function.
	 *
	 *
	 * @param string|string[] $values
	 * @return string|string[]
	 */
	public function decorate($values)
	{ /*        */	 isset($this->logger) && $this->logger->debug("decorating values : " . print_r($values, true));
		$ret = array();
		if (is_array($values))
		{ foreach ($values as $value)
			{	$ret[] = $this->decorate_($value);
			}
		}
		else
		{ $ret = $this->decorate_($values);
		}
		/*        */	 isset($this->logger) && $this->logger->debug("decorated values : " . print_r($ret, true));
		return $ret;
	}

	/**
	 * To be overridden if option/argument does indeed require it.
	 *
	 * @param $value string A single value passed to this option/argument
	 * @return mixed
	 */
	protected function decorate_($value)
	{return $value;
	}


	/**
	 * Performs the validation of values passed as argument
	 *
	 * Happens after decoration.
	 *
	 * In the same way as decorate(), will perform both on single values as well as
	 * array values.
	 *
	 * If this behaviour is not the desired one, override this function directly.
	 *
	 *
	 * @param string|string[] $values
	 * @return bool
	 */
	public function validate($values)
	{	/*        */	 isset($this->logger) && $this->logger->debug("validating : values=" . print_r($values, true));

		// We will not be checking here whether $values is or is not
		// an array, as expected by the option/argument.
		// Indeed this check is performed at a higher level
		// (at the command level, by Symfony\Console, with the mode value
		// given in the argument/option conf files:
		// InputArgument::REQUIRED, OPTIONAL, ARRAY
		// InputOption::VALUE_IS_ARRAY, VALUE_NONE ...

		$valid = true;
		if ( is_array($values))
		{	foreach ($values as $value)
			{	$valid = $valid && $this->validate_($value);
			}
		}
		else
		{ $valid = $this->validate_($values);
		}
		return $valid;
	}

	/**
	 * To be overridden if option/argument does indeed require it.
	 *
	 * Return true or false whether value is valid or not.
	 * Do not throw exception, use addValidationErrorMessage().
	 *
	 * @param $value string A single value passed to this option/argument
	 * @return boolean
	 */
	protected function validate_($value)
	{
		return true;
	}




	/**
	 * Offers an opportunity to ask the user to confirm the value(s)
	 * passed to an option or argument, and possibly change these value(s).
	 *
	 * Happens after validation.
	 *
	 * The way this function performs, out of the box, is as follows.
	 * If this behaviour is not the desired one, override this function directly.
	 *
	 * In the configuration file for the option/argument, the following may be specified:
	 * <pre>
	 *   ask_confirmation_when_values = value1, value2, value3
	 *   ask_confirmation_message = "You have specified dangerous values, are you sure?"
	 * </pre>
	 * See conf file examples/template for more info.
	 * If this is the case then for each of the values requiring confirmation,
	 * confirm_() will be called. This function, as per its stock behaviour, will
	 * ask the conf-specified message and return the value or exit.
	 *
	 * @param string|string[] $values
	 * @return bool True if user confirmed, false otherwise
	 *
	 * @deprecated_return string|string[] The new (or not, if left unchanged) value(s)

	 */
	public function confirm($values)
	{ /*        */	 isset($this->logger) && $this->logger->debug("confirming values : " . print_r($values, true));

		// If conf file specifies no value requiring confirmation, just pass through
		if (is_null($this->getAskConfirmationWhenValues()))
		{	return true;
		}

		$values_requiring_confirmation = array_map('trim', explode(',', $this->getAskConfirmationWhenValues()));

		/*        */	 isset($this->logger) && $this->logger->debug("values_requiring_confirmation= " . print_r($values_requiring_confirmation, true));

		$confirmed = true;
		if (is_array($values))
		{ foreach ($values as $value)
			{	if (in_array($value,$values_requiring_confirmation ))
				{	$confirmed = $confirmed && $this->confirm_($value);
				}
			}
		}
		else
		{ if (in_array($values, $values_requiring_confirmation))
			{	$confirmed = $this->confirm_($values);
			}
		}
		return $confirmed;
	}

	/**
	 * Called by confirm() on each value to confirm.
	 * Stock behaviour: asks the confirmation message (provided in conf file).
	 * Override to redefine behaviour.
	 *
	 * Asking for confirmation is done like this :
	 * <pre>
	 *  $dialog = $this->command->getHelperSet()->get('dialog');
	 *  	if (!$dialog->doAskUserIfContinue
	 *  	        (
	 *  	          $this->command->getOutput(),
	 *  	          "<question>Option x was not specified. Default is Y. Confirm? [yn] </question>",
	 *  	          false
	 *  	        )
	 *  	    )
	 *    ...
	 * </pre>
	 *
	 * @param $value mixed Value to be confirmed
	 * @return bool True if user confirmed, false otherwise
	 */
	protected function confirm_($value)
	{ /*        */	 isset($this->logger) && $this->logger->debug("confirm_ value  $value ");

		return $this->askForUserConfirmation($this->getAskConfirmationWhenValuesMsg());

/*

		{  // If user does not confirm, doAbort
			throw new NoUserConfirmationOnPromptException();
		}
		else
		{ return $value;
		}
*/
	}


	/**
	 * Last chance for a final change to the value
	 * If interested, override finalDecorate_()
	 * Happens after confirmation.
	 *
	 * Behaviour towards arrays is the same as decorate().
	 * If not desired behaviour, override this function directly rather than finalDecorate_()
	 *
	 */
	public function finalDecorate($values)
	{
		{ /*        */	isset($this->logger) && $this->logger->debug("final decorating values : " . print_r($values, true));
			$ret = array();
			if (is_array($values))
			{ foreach ($values as $value)
				{	$ret[] = $this->finalDecorate_($value);
				}
			}
			else
			{ $ret = $this->finalDecorate_($values);
			}
			/*        */	isset($this->logger) && $this->logger->debug("final decorated values : " . print_r($ret, true));
			return $ret;
		}
	}


	/**
	 * To be overridden if option/argument does indeed require it.
	 */
	protected function finalDecorate_($value)
	{
		return $value;
	}




	/**
	 * Asks a confirmation question to the user
	 * @param string $question
	 * @return bool True if user confirms, false otherwise
	 */
	protected function askForUserConfirmation($question)
	{
		return $this->command->askForUserConfirmation($question);
	}











	/**
	 * @inheritdoc
	 * @override
	 */
	public function getValidationErrorMessages()
	{
		return $this->validationErrorMessages;
	}

	/**
	 * Set the error message to be displayed in the case that the option value is invalid
	 * @param $msg
	 */
	protected function addValidationErrorMessage($msg)
	{
		$this->validationErrorMessages[] = $msg;
	}

	public function getName()
	{	return $this->conf->get(self::CONF_KEY__NAME);
	}

	public function getShortcut()
	{	return $this->conf->get(self::CONF_KEY__SHORTCUT);
	}

	public function getMode()
	{
		$mode  = $this->conf->get(self::CONF_KEY__MODE) ;

		// Mode can be of format : "mode1 | mode2 | mode3" => split it up
		$modes = array_map('trim', explode('|',$mode ));

		// ... and compute the value used by Symfony Console's addArgument() or addOption()
		// See http://symfony.com/doc/current/components/console/introduction.html
		$ret   = null;
		$r     = new \ReflectionClass($this->getModeReflectionClass());
		foreach ($modes as $mode)
		{	$ret =  $ret |  $r->getConstant($mode);
		}
		/*        */	 isset($this->logger) && $this->logger->debug("mode after reflection=".$ret);
		return $ret;
	}

	abstract protected function getModeReflectionClass();


	/**
	 * @return mixed null if does not exist in conf
	 */
	public function getDescription()
	{		return $this->conf->get(self::CONF_KEY__HELP) ;
	}

	/**
	 * @return mixed null if does not exist in conf
	 */
	public function getDefault()
	{		return $this->conf->get(self::CONF_KEY__DEFAULT) ;
	}

	/**
	 * @return mixed null if does not exist in conf
	 */
	public function getId()
	{		return $this->conf->get(self::CONF_KEY__ID) ;
	}

	/**
	 * @return mixed null if does not exist in conf
	 */
	public function getPromptIfNone()
	{	return $this->conf->get(self::CONF_KEY__PROMPT_IF_NONE) ;
	}

	/**
	 * @return mixed null if does not exist in conf
	 */
	public function getPromptIfNoneMsg()
	{	return $this->conf->get(self::CONF_KEY__PROMPT_IF_NONE_MSG) ;
	}

	/**
	 * @return mixed null if does not exist in conf
	 */
	public function getAskConfirmationWhenValues()
	{	return $this->conf->get(self::CONF_KEY__ASK_CONFIRMATION_WHEN_VALUES) ;
	}

	/**
	 * @return mixed null if does not exist in conf
	 */
	public function getAskConfirmationWhenValuesMsg()
	{ return $this->conf->get(self::CONF_KEY__ASK_CONFIRMATION_WHEN_VALUES_MSG) ;
	}

	/**
	 * @return mixed null if does not exist in conf
	 */
	public function getAskConfirmationBeforeDefault()
	{ return $this->conf->get(self::CONF_KEY__ASK_CONFIRMATION_BEFORE_DEFAULT) ;
	}

	/**
	 * @return mixed null if does not exist in conf
	 */
	public function getAskConfirmationForDefaultMsg()
	{ return $this->conf->get(self::CONF_KEY__ASK_CONFIRMATION_BEFORE_DEFAULT_MSG) ;
	}

	public function getConf()
	{		return $this->conf;
	}

}
