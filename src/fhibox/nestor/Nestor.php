<?php
/**
 * Created by PhpStorm.
 * User: Francois hill
 * Date: 19/10/201x
 * Time: 15:06
 */

namespace fhibox\nestor;


use fhibox\jeeves\Jeeves;

class Nestor extends Jeeves
{
	/**
	 * @var
	 * @todo move in a conf file
	 */
	private $applicationName = "Nestor";

	/**
	 * @var
	 * @todo move in a conf file
	 */
	private $applicationVersion = "0.9";

/*
	protected function registerCommands()
	{
		$this->application->add(new CommandDeploy());
		$this->application->add(new CommandCompare());
	}
*/
	protected function getApplicationName()
	{	return $this->applicationName;
	}

	protected function getApplicationVersion()
	{	return $this->applicationVersion;
	}

	protected function getConfDirPath()
	{	return __DIR__.'/../../../conf';
	}

	protected function getCommandsDirPath()
	{	return __DIR__.'/application/commands';
	}

	protected function getCommandsNamePattern()
	{	return 'AppCommand*.php';
	}

	protected function getOptionsDirPath()
	{	return array(__DIR__.'/application/options',
		             __DIR__.'/application/commands',
	              );
	}

	protected function getOptionsDirPathExclude()
	{	return null;
	}

	protected function getOptionsNamePattern()
	{	return 'Option*.php';
	}

	protected function getArgumentsDirPath()
	{ return array(__DIR__.'/application/arguments',
	               __DIR__.'/application/commands',
	              );
	}

	protected function getArgumentsDirPathExclude()
	{	return array('exceptions',
	               );
	}


	protected function getArgumentsNamePattern()
	{ return 'Argument*.php';
	}
}