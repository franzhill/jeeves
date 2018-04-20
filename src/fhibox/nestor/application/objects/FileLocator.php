<?php
/**
 * Created by PhpStorm.
 * User: Francois hill
 * Date: 02/12/201x
 * Time: 15:19
 */

namespace fhibox\nestor\application\objects;

use fhibox\jeeves\core\exceptions\ProgrammingException;
use fhibox\logging_interface\ILoggingInterface;
use fhibox\nestor\application\values\TypeLocation;
use fhibox\nestor\application\values\TypeEnv;
use fhibox\nestor\application\values\TypeRepository;

/**
 * @author fhi
 */
abstract class FileLocator
{
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

// </LOGGING>
// ------------------------------------------------------------------------------


	// TODO move somewhere else more appropriate

	const DIR_PROD_SYNC             = '/my/work/var/prod3/sources';
	const DIR_PROD_MANIFESTS_COMMON = '/my/work/var/prod3/manifests/common';
	const DIR_PROD_MANIFESTS_COMMON_SYNC = '/my/work/var/prod3/manifests/common/sync';
	const DIR_PROD_MANIFESTS_COMMON_SYNC_INDIVIDUAL = '/my/work/var/prod3/manifests/common/sync/individual';
	const DIR_PROD_MANIFESTS     = '/my/work/scripts/deploy/prod3/manifests/templates';
	const MANIFEST_CURRENT_NAME  = 'current';


	/**
	 * Given (user-provided) path for this file.
	 * Usually received form user input as an argument; is not the real path on the server.
	 * @var string
	 */
	protected $path;

	/**
	 * Vcs repository for this file.
	 * Usually received form user input as an argument.
	 * @var string One of the values of TypeRepository
	 */
	protected $rep;

	/**
	 * Env for this file.
	 * @var string One of the values of TypeEnv
	 */
	protected $env;




	/**
	 * @param $path   string  The user-provided path for the file (after option/argument processing)
	 * @param $env    string  The user-provided target environment for the file (after option/argument processing).
	 *                        One of the values of TypeEnv.
	 */
	protected function __construct($path, $env = null)
	{
		$this->path = $path;
		$this->env  = is_null($env) ? null  : TypeEnv::from($env);
	}


	/**
	 * @param $container string One of the values of TypeLocation
	 * @param $env mixed  One of the values of TypeEnv. If env is not passed, consider the env used on constructor.
	 *                    If none provided in constructor, then fail.
	 * @return FileLocation
	 */
	public function locate($container, $env = null)
	{	/*        */  isset($this->logger) && $this->logger->debug("");

		$container = TypeLocation::from($container);   // will throw exception if no good
		$env       = is_null($env) ? $this->env : $env;
		/*        */  if (is_null($env))	{ throw new \RuntimeException("Environment not defined. Should be either in constructor or in this function."); }

		/** @var FileLocation */
		$ret = null;

		switch ($container)
		{
			case TypeLocation::SYNC_DIR      : return $this->locateInSyncDir    (); break;
			case TypeLocation::SERVER      : return $this->locateInServer    (); break;
			case TypeLocation::VCS_WORKDIR : return $this->locateInVcsWorkdir(); break;
			default                         : throw new \RuntimeException("Container unknown: [$container]");
		}
	}


	/**
	 * TODO possibly move that kind of info in a conf file somewhere
	 * @return string
	 */
	public function getSyncDir()
	{
		switch($this->env) //@formatter:off
		{	case  TypeEnv::PROD : return self::DIR_PROD_SYNC;
			default             : throw new \RuntimeException("Root sync folder (mirror repo) is only useful for an operation involving the PROD environment. Current env = [$this->env].");
		}                 //@formatter:on
	}


	/**
	 * @return string Path of VCS working dir used to retrieve the version of the file
	 */
	public function getVcsWorkDir()
	{
		switch($this->env) //@formatter:off
		{	case  TypeEnv::DEV     : return $this->getVcsWorkDir_Dev();
			case  TypeEnv::STAGING : return $this->getVcsWorkDir_Staging();
			case  TypeEnv::PROD    : return $this->getVcsWorkDir_Prod();
			default                : throw new \LogicException("Sorry, env other than PROD not supported yet...");
		}                 //@formatter:on
		// Just for the compiler ...
	}



	/**
	 * To be implemented in extending class. Implementation will depend on repository
	 * @return FileLocation
	 */
	abstract protected function locateInSyncDir();

	/**
	 * To be implemented in extending class. Implementation will depend on repository
	 * @return FileLocation
	 */
	abstract protected function locateInServer();

	/**
	 * To be implemented in extending class. Implementation will depend on repository
	 * @return FileLocation
	 */
	abstract protected function locateInVcsWorkdir();

	/**
	 * To be implemented in extending class. Implementation will depend on repository
	 * @return string Path of VCS working dir used to retrieve the version of the file
	 */
	abstract protected function getVcsWorkDir_Dev    () ;

	/**
	 * To be implemented in extending class. Implementation will depend on repository
	 * @return string Path of VCS working dir used to retrieve the version of the file
	 */
	abstract protected function getVcsWorkDir_Staging() ;

	/**
	 * To be implemented in extending class. Implementation will depend on repository
	 * @return string Path of VCS working dir used to retrieve the version of the file
	 */
	abstract protected function getVcsWorkDir_Prod   () ;



	/**
	 * See eetter
	 * @var
	 */
	private $isDirectory;

	/**
	 * Knowing that this file is a file or a directory may be information that
	 * comes afterwards, and thanks to external info (e.g. a shell command)
	 * So we need a way to inject that info.
	 * Hence this mutable property.
	 * @param bool $is_a_dir
	 * @return $this
	 */
	public function setIsDirectory($is_a_dir=true)
	{
		$this->isDirectory = $is_a_dir;
		return $this;
	}

	public function isDirectory()
	{	return $this->isDirectory;
	}

	/**
	 * @alias isDirectory()
	 */
	public function isDir()
	{	return $this->isDirectory();
	}

}