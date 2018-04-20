<?php

namespace fhibox\nestor\application\commands;

use fhibox\jeeves\core\AppCommand;
use fhibox\logging_interface\ILoggingInterface;
use fhibox\nestor\application\instructions\InstructionShellBuilder;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Do not name this class with along the pattern 'AppCommand*' (i.e. not AppCommandParent)!
 * Because this is the pattern used to discover and automatically load commands 
 * (in nestor/application/commands).
 * However this here is not a real command.
 *
 * Created by PhpStorm.
 * User: Francois hill
 * Date: 19/11/201x
 * Time: 18:44
 */
abstract class CommandParent extends AppCommand
{
	/** @var InstructionShellBuilder */
	protected $isb;


	final protected function execute_()
	{
		// Get options and arguments common to all commands
		// -------------------------------------------------
		$virtual_mode        = $this->getOptionValue  ('virtual-mode');
		$test_mode           = $this->getOptionValue  ('test-mode');
		$verbosity_transmit  = $this->getOptionValue  ('verbosity-transmit');

		// Process option values
		$this->setTestMode          ($test_mode);
		$this->setVirtualMode       ($virtual_mode);
		$this->setVerbosityTransmit ($verbosity_transmit);

		// Process verbosity:
		// ------------------
		// In some cases, we'll want to transmit verbosity to logger, in others no
		if ($this->isVerbosityTransmit)
		{ // We'll want to transmit verbosity setting to any subcommand executed, but not to the logger
		}
		else   // @formatter:off
		{ // We'll want to transmit verbosity setting to logger
			switch ($this->verbosity)
			{ case OutputInterface::VERBOSITY_QUIET        : $this->logging->setLevel(ILoggingInterface::LEVEL_OFF ) ; break;
				case OutputInterface::VERBOSITY_NORMAL       : $this->logging->setLevel(ILoggingInterface::LEVEL_WARN) ; break;
				case OutputInterface::VERBOSITY_VERBOSE      : $this->logging->setLevel(ILoggingInterface::LEVEL_INFO) ; break;
				case OutputInterface::VERBOSITY_VERY_VERBOSE : $this->logging->setLevel(ILoggingInterface::LEVEL_DEBUG); break;
				case OutputInterface::VERBOSITY_DEBUG        : $this->logging->setLevel(ILoggingInterface::LEVEL_TRACE); break;
				default                                      : $this->logging->setLevel(ILoggingInterface::LEVEL_DEBUG);
			} // @formatter:on
		}

		// Ensure running under the right user:
		// ------------------------------------
		$this->isb = new InstructionShellBuilder($this);

		// If local env, do not check user (for development purposes)
		// TODO possibly use envApi
		if ( getenv("ENV") != "LOCAL")
		{
			$this->isb
				->define('[ $(whoami) = "fhibox" ]')
					->onFailure()
					->doAbort("This command can only be run under user 'fhibox'.")
				->execute();
		}

		// Execute custom stuff:
		// ---------------------
		$this->execute__();
	}

	abstract protected function execute__();
}