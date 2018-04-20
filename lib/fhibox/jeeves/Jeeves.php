<?php
/**
 * Created by PhpStorm.
 * User: Francois hill
 * Date: 19/10/201x
 * Time: 12:12
 */

namespace fhibox\jeeves;


use fhibox\jeeves\core\AppCommand;
use fhibox\jeeves\core\AppCommandEmpty;
use fhibox\jeeves\core\Application;
use fhibox\jeeves\core\Argument;
use fhibox\jeeves\core\Option;
use fhibox\logging_interface\ILoggingInterface;
use fhibox\namespace_lib\NamespaceExtractor;
use Symfony\Component\Finder\Finder;

/**
 * The abstract skeleton for a 'valet' application
 *
 * I.e. an application designed to handle tasks input from command line
 * The real 'valet' application will be derived from this abstract class.
 *
 *
 * @package fhibox\jeeves
 * @author fhi
 */
abstract class Jeeves
{
	/**
	 * @var Application $application
	 */
	protected $application;

	/**
	 * Directory of all options
	 * They just contain dummy instances of the options.
	 * See getOptionById()
	 * @var Option[] $options
	 */
	protected $options = array();

	/**
	 * Directory of all arguments
	 * They just contain dummy instances of the arguments.
	 * See getArgumentById()
	 * @var Argument[] $arguments
	 */
	protected $arguments = array();

	/**
	 * @var ILoggingInterface $logging
	 */
	protected $logging;

	public function setLogging(ILoggingInterface $log_i)
	{
		$this->logging = $log_i;
		$this->logger  = $this->logging->getLogger(str_replace('\\', '.', __CLASS__));

		return $this;
	}

	public function getLogging()
	{ return $this->logging;
	}


	/**
	 * Don't forget to call setUp() before using the resulting object!
	 */
	public function __construct()
	{
		#/*        */	$this->logger = $this->getLogging()->getLogger(str_replace('\\', '.', __CLASS__));
		#/*        */	$this->logger->debug("");
	}

	public function setUp()
	{
		$this->application = new Application($this->getApplicationName(), $this->getApplicationVersion());
		$this->application -> setLogging($this->getLogging());
		$this->registerOptions();
		$this->registerArguments();
		$this->registerCommands();

		return $this;
	}



	public function run()
	{	$this->application->run();
		return $this;
	}






	/**
	 * Called by the constructor
	 * @return mixed
	 */
	private function registerCommands()
	{
		$finder = new Finder();
		$finder->files()->name($this->getCommandsNamePattern())
		                ->in($this->getCommandsDirPath());

		foreach ($finder as $file)
		{ $class_name = basename($file, ".php");

			// If still contains a dot, skip
			if (strpos($class_name, '.') !== FALSE)
			{	continue;
			}

			// Add namespace:
			$namespace_extractor = new NamespaceExtractor();
			$namespace_extractor ->setLogging($this->logging);
			$namespace           = $namespace_extractor->extractNsFromFile($file, NamespaceExtractor::STRATEGY_SECURE);
			$class_name          = $namespace . '\\' . $class_name;
			/*        */	isset($this->logger) && $this->logger->debug("namespace=$namespace");
			/*        */	isset($this->logger) && $this->logger->debug("full class name=$class_name");

			// Instantiate and add to app:
			$r = new \ReflectionClass($class_name);
			$this->application->add($r->newInstanceArgs(array($this)));
		}
	}

	/**
	 * @return mixed
	 */
	private function registerOptions()
	{
		/*        */	isset($this->logger) && $this->logger->debug("");
		$finder = new Finder();
		$finder->files()->name($this->getOptionsNamePattern())
		                ->in($this->getOptionsDirPath())
		                ->exclude($this->getArgumentsDirPathExclude());

		foreach ($finder as $file)
		{ $class_name = basename($file, ".php");
			/*        */	isset($this->logger) && $this->logger->debug("class_name=$class_name");
			// If still contains a dot, skip
			if (strpos($class_name, '.') !== FALSE)
			{	continue;
			}
			// Add namespace:
			$namespace_extractor = new NamespaceExtractor();
			$namespace_extractor ->setLogging($this->logging);
			$namespace           = $namespace_extractor->extractNsFromFile($file, NamespaceExtractor::STRATEGY_SECURE);
			$class_name          = $namespace . '\\' . $class_name;
			/*        */	isset($this->logger) && $this->logger->debug("namespace=$namespace");
			/*        */	isset($this->logger) && $this->logger->debug("full class name=$class_name");

			// Instantiate and add to app:
			// Since we can't know beforehand the command to which the option
			// is ultimately tied to (we're building a 'beforehand' directory)
			// we'll just use a dummy command. We'll discard that anyway later on in getOptionById().
			$r = new \ReflectionClass($class_name);
			$this->options[] =  $r->newInstanceArgs(array(new AppCommandEmpty($this)));
		}
	}

	/**
	 * Return an Option object, given its id (as appearing in option conf files)
	 * @param $id
	 * @param $command AppCommand Command for which that option is issued (= to which
	 *                            the option belongs).
	 * @throws \Exception In case of a programming error. Do not try to catch.
	 * @return Option
	 */
	public function getOptionById($id, $command)
	{/*        */	isset($this->logger) && $this->logger->debug("");

		foreach ($this->options as $option)
		{	/*        */	isset($this->logger) && $this->logger->debug("option_id=".$option->getId());
			if ($option->getId() == $id )
			{ // That option is a dummy one though
				// We have to return an option instance built with the $command
				$class_name = get_class($option);
				$r = new \ReflectionClass($class_name);
				return $r->newInstanceArgs(array($command));
			}
		}
		throw new \Exception ("Programming error (should never happen): Option with id <$id> not found in registered options.");
	}

	/**
	 * @return mixed
	 */
	private function registerArguments()
	{
		/*        */	isset($this->logger) && $this->logger->debug("");
		$finder = new Finder();
		$finder->files()
		       ->name($this->getArgumentsNamePattern())
		       ->in($this->getArgumentsDirPath())
		       ->exclude($this->getArgumentsDirPathExclude());

		foreach ($finder as $file)
		{ $class_name = basename($file, ".php");
			/*        */	isset($this->logger) && $this->logger->debug("class_name=$class_name");
			// If still contains a dot, skip
			if (strpos($class_name, '.') !== FALSE)
			{	continue;
			}
			// Add namespace:
			$namespace_extractor = new NamespaceExtractor();
			$namespace_extractor ->setLogging($this->logging);
			$namespace           = $namespace_extractor->extractNsFromFile($file, NamespaceExtractor::STRATEGY_SECURE);
			$class_name          = $namespace . '\\' . $class_name;
			/*        */	isset($this->logger) && $this->logger->debug("namespace=$namespace");
			/*        */	isset($this->logger) && $this->logger->debug("full class name=$class_name");

			// Instantiate and add to app:
			// Since we can't know beforehand the command to which the option
			// is ultimately tied to (we're building a 'beforehand' directory)
			// we'll just use a dummy command. We'll discard that anyway later on in getArgumentById().
			$r = new \ReflectionClass($class_name);
			$this->arguments[] = $r->newInstanceArgs(array(new AppCommandEmpty($this)));
		}
	}

	/**
	 * Return an Argument object, given its id (as appearing in argument conf files)
	 * @param $id
	 * @param $command AppCommand Command for which that argument is issued (= to which
	 *                            the argument belongs).
	 * @throws \Exception In case of a programming error. Do not try to catch.
	 * @return Option
	 */
	public function getArgumentById($id, $command)
	{/*        */	isset($this->logger) && $this->logger->debug("");

		foreach ($this->arguments as $argument)
		{	/*        */	isset($this->logger) && $this->logger->debug("argument_id=".$argument->getId());
			if ($argument->getId() == $id )
			{ // That argument is a dummy one though
				// We have to return an argument instance built with the $command
				$class_name = get_class($argument);
				$r = new \ReflectionClass($class_name);
				return $r->newInstanceArgs(array($command));
			}
		}
		throw new \Exception ("Programming error (should never happen): Argument with id <$id> not found in registered arguments.");
	}

	/**
	 * Path(s) of directory(ies) in which to (recursively) look for all commands.
	 *
	 * @return string|array A directory path or an array of directories
	 */
	protected abstract function getApplicationName();

	protected abstract function getApplicationVersion();

	protected abstract function getConfDirPath();

	protected abstract function getCommandsDirPath();

	protected abstract function getCommandsNamePattern();

	/**
	 * Path(s) of directory(ies) in which to (recursively) look for all options.
	 *
	 * @return string|array A directory path or an array of directories.
	 */
	protected abstract function getOptionsDirPath();

	/**
	 * Directory(ies) to exclude while looking for options. See getOptionsDirPath()
	 * @return string|array A directory (name/path) or an array of directories.
	 *                      Or null if no directory to exclude.
	 */
	protected abstract function getOptionsDirPathExclude();


	protected abstract function getOptionsNamePattern();

	/**
	 * Path(s) of directory(ies) in which to (recursively) look for all arguments.
	 *
	 * @return string|array A directory path or an array of directories
	 */
	protected abstract function getArgumentsDirPath();

	protected abstract function getArgumentsNamePattern();

	/**
	 * Directory(ies) to exclude while looking for arguments. See getArgumentsDirPath()
	 * @return string|array A directory (name/path) or an array of directories.
	 *                      Or null if no directory to exclude.
	 */
	protected abstract function getArgumentsDirPathExclude();

}