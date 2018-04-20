<?php
/**
 * Created by PhpStorm.
 * User: Francois hill
 * Date: 29/10/201x
 * Time: 10:18
 */

namespace fhibox\nestor\application\objects;

use fhibox\jeeves\core\exceptions\ProgrammingException;
use fhibox\logging_interface\ILoggingInterface;
use fhibox\nestor\application\values\TypeEnv;
use fhibox\nestor\application\values\TypeRepository;

/**
 * @author fhi
 */
class SourceFile
{
	// TODO move somewhere else more appropriate
	const DIR_PROD_SVN_WORK_DIR  = '/my/repos/prod3/fhibox1';
	const DIR_PROD_MIRROR_DIR    = '/my/work/var/prod3/sources';
	const DIR_PROD_MANIFESTS     = '/my/work/scripts/deploy/prod3/manifests/templates';
	const MANIFEST_CURRENT_NAME  = 'current';

	/**
	 * Represented path for this source file.
	 * Usually received form user input as an argument; is not the real path on the server.
	 * @var
	 */
	private $path;

	/**
	 * Env for this source file.
	 * Usually received form user input as an argument.
	 * @var $env mixed One of the values of TypeRepository
	 */
	private $rep;

	/**
	 * @var $env mixed One of the values of TypeEnv
	 */
	private $env;

	/**
	 * The actual, real path for the source file. See getter.
	 * @var
	 */
	private $realPath;

	/**
	 * The basename (file part in the path) of actual, real path for the source file. See getter.
	 * @var
	 */
	private $realBasename;


	private $isDirectory;

// ------------------------------------------------------------------------------
// <LOGGING>

	/**
	 * @var mixed  Logger object, see setter.
	 */
	private $logger;

	/**
	 * @var ILoggingInterface   See setter.
	 */
	private $logging;

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


	/**
	 * @param $rep    string  The user-provided repository (after option/argument processing) from which this file originates.
	 *                        One of the values of TypeRepository.
	 * @param $path   string  The user-provided path (after option/argument processing)
	 * @param $env    string  The user-provided target environment (after option/argument processing) in which this file is to be considered.
	 *                        One of the values of TypeEnv.
	 */
	public function __construct($rep, $path, $env)
	{
		$this->env  = TypeEnv::from($env);
		$this->path = $path;
		$this->rep  = TypeRepository::from($rep);
	}



	/**
	 * Return real (full) path of source file in real environment (see getRealEnv)
	 *
	 * i.e. with this path, connecting to the target env system and
	 * entering the command [ -f <real path> ] returns true.
	 *
	 * For user-friendliness reasons, values accepted from user for source-file
	 * have a larger "range" than the actual value that is ultimately
	 * needed.
	 *
	 *
	 * @throws \Exception
	 * @return string
	 */
	public function getRealPath()
	{ /*        */  isset($this->logger) && $this->logger->debug("");

		if (isset($this->realPath)) {return $this->realPath;}

		// Check target env is acceptable value
		if (! in_array($this->env, array(TypeEnv::DEV, TypeEnv::STAGING, TypeEnv::PROD) ))
		{ // THis type of error should be caught higher up in the call stack so we'll only throw a generic Exception
			throw new \Exception("Error : target env [$this->env] is not one of the expected.");
		}

		switch ($this->rep)
		{
			case TypeRepository::FHIBOX1   :

				/*
				 Path given (as argument) |  Environment      |    Real path in real env
				 =========================+===================+=======================================
				  trunk/file              |                   |
				  www/file                |   dev or staging  | /my/www/file
				  /my/www/file          |                   |
				                          +-------------------+-----------------------------------------
				                          |   prod            | /my/work/var/prod3/sources/www/file
				 -------------------------+-------------------+----------------------------------------
				  conf/file               |                   |
				                          |   dev or staging  | TODO
				  /my/conf/file         |                   |
				                          +-------------------+-----------------------------------------
				                          |   prod            | TODO
				 -------------------------+-------------------+----------------------------------------
				  logs/file               |                   |
				                          |   dev or staging  | TODO
				  /my/logs/file         |                   |
				                          +-------------------+-----------------------------------------
				                          |   prod            | TODO
				 -------------------------+-------------------+----------------------------------------
				  other : assume trunk    |                   |
				*/

				$accepted_leading_patterns[0] = 'trunk|www|/my/www';
				$accepted_leading_patterns[1] = 'conf|/my/conf';
				$accepted_leading_patterns[2] = 'logs|/my/logs';

				$replacement_leading_pattern[TypeEnv::DEV][0]     = '/my/www/';
				$replacement_leading_pattern[TypeEnv::DEV][1]     = '/my/conf/';
				$replacement_leading_pattern[TypeEnv::DEV][2]     = '/my/logs/';
				$replacement_leading_pattern[TypeEnv::STAGING][0] = '/my/www/';
				$replacement_leading_pattern[TypeEnv::STAGING][1] = '/my/conf/';
				$replacement_leading_pattern[TypeEnv::STAGING][2] = '/my/logs/';
				$replacement_leading_pattern[TypeEnv::PROD][0]    = self::DIR_PROD_MIRROR_DIR.'/www/';
				$replacement_leading_pattern[TypeEnv::PROD][1]    = self::DIR_PROD_MIRROR_DIR.'/conf/';
				$replacement_leading_pattern[TypeEnv::PROD][2]    = self::DIR_PROD_MIRROR_DIR.'/logs/';

				$matches = array();
				$res[0] = preg_match('/(?<='. addcslashes($accepted_leading_patterns[0], '/')  .')\/(.*)/'    , $this->path, $matches[0]);
				$res[1] = preg_match('/(?<='. addcslashes($accepted_leading_patterns[1], '/')  .')\/(.*)/'    , $this->path, $matches[1]);
				$res[2] = preg_match('/(?<='. addcslashes($accepted_leading_patterns[2], '/')  .')\/(.*)/'    , $this->path, $matches[2]);

				/*        */   isset($this->logger) && $this->logger->debug('$res='.print_r($res, true));
				if ($res[0] && $res[1] && $res[2] === FALSE)
				{	throw new \InvalidArgumentException("Error while trying to extract exact file path from given file path [$this->path]");
				}
				// If no pattern found, then this is a breach of contract
				if ($res[0] == 0 && $res[1] == 0 && $res[2] == 0)
				{	/*        */   isset($this->logger) && $this->logger->debug('no pattern found');
					throw new \InvalidArgumentException("File provided is not as expected: [$this->path]. Please refer to help.");
				}
				$match_idx = ($res[0] != 0) ? 0  : (($res[1] != 0) ? 1 : (($res[2] != 0) ? 2 : -1  ));
				/*        */  #$this->logger->debug("match_idx=$match_idx");
				/*        */  if ($match_idx == -1) {throw new \Exception("Programming Exception, should not ever happen");}
				/*        */  #$this->logger->debug('replacement_leading_pattern[$env][$match_idx]=' . $replacement_leading_pattern[$env][$match_idx]);
				/*        */  #$this->logger->debug('$matches[$match_idx][1]=' . $matches[$match_idx][1]);

				$this->realBasename = $matches[$match_idx][1];
				$this->realPath     = $replacement_leading_pattern[$this->env][$match_idx] . $this->realBasename;
				break;
			//@formatter:off
			case TypeRepository::CONSTANTS : // TODO

				/*
				 Path given (as argument)         |  Environment      |    Real path in real env
				 =================================+===================+=======================================
				  file                            |   dev             |   /my/config/const/file
				  /my/config/const/file |                   |
				 ---------------------------------+-------------------+---------------------------------------
				  file                            |   staging         |   /my/config/const/file
				  /my/config/const/file |                   |
				 ---------------------------------+-------------------+---------------------------------------
				  file                            |   prod            |   /my/config/const/file
				  /my/config/const/file |                   |
				 ---------------------------------+-------------------+---------------------------------------
				*/

				$accepted_leading_patterns[0] = '/my/config/const';

			                                 throw new \Exception("TODO ! Type de repository pas encore supporté.");
			                                 break;


			case TypeRepository::ADMIN :     // TODO
			                                 throw new \Exception("TODO ! Type de repository pas encore supporté.");
			                                 break;
			default:                         // TODO
			                                 throw new \Exception("TODO ! Type de repository pas encore supporté.");
			                                 break;
			//@formatter:on
		}
		return $this->realPath;
	}

	/**
	 * Return real base name (i.e. file part) of real path of source file (see getRealPath)
	 *
	 * @return mixed
	 * @throws \Exception	/**
	 *
	 *
	 */
	public function getRealBasename()
	{ /*        */
		isset($this->logger) && $this->logger->debug("");//@formatter:off

		if (isset($this->realBasename)) {return $this->realBasename;}
		else                            {$this->getRealPath();}
		return $this->realBasename; //@formatter:on
	}


	/**
	 * Return real env where source file is stored on.
	 *
	 * The real env may not always be the user-provided one.
	 * E.g. for user-provided env=PROD, the actual env (machine/system) on which
	 * we will be looking for the file is a mirror env.
	 */
	public function getRealEnv()
	{ //@formatter:off
		switch($this->env)
		{	case  TypeEnv::PROD : return TypeEnv::FRONT;
			default             : return $this->env;
		}
		//@formatter:on
	}

	/**
	 * Return real (relative) path of source file in target environment working dir repository
	 *
	 * E.g. for env=PROD,
	 *    - VCS working dir is             : /my/repos/prod3/fhibox1
	 *    - Full real path of file is      : /my/repos/prod3/fhibox1/trunk/my/file
	 *    - 'Real' path in VCS working dir : trunk/my/file
	 *
	 *
	 *
	 * For repository = constants:
	 *
	 *
	 *
	 */
	public function getRealPathInVcsWorkDir()
	{ /*        */   isset($this->logger) && $this->logger->debug('');
		// TODO rewrite, refactor, extract constants in config...

		switch ($this->rep)    // @formatter::off
		{
			case TypeRepository::FHIBOX1   :

/*
			File path                        |  Environment      |    Real path in VCS workdir
			=================================+===================+=======================================
			 file                            |   dev             |   (/my/repos/fhibox1)/trunk/file
			                                 |                   |
			---------------------------------+-------------------+---------------------------------------
			 file                            |   staging            |   (/my/repos/fhibox1)/trunk/file
			                                 |                   |
			---------------------------------+-------------------+---------------------------------------
			 file                            |   prod            |   (/my/repos/prod3/fhibox1)/trunk/file
			                                 |                   |
			---------------------------------+-------------------+---------------------------------------
*/



				if ($this->env != TypeEnv::PROD)
				{
					throw new \Exception("Environnement autre que PROD pas encore supporté...");
				}
				$real_path = $this->getRealPath();
				$search = '/my/work/var/prod3/sources/www/';
				$replace = 'trunk/';
				$ret = str_replace($search, $replace, $real_path);
				break;

			case TypeRepository::CONSTANTS   :

			/*
						File path                        |  Environment      |    Real path in VCS workdir
						=================================+===================+=======================================
						 file                            |   dev             |   (/my/repos/const)/dev/file
						 /my/config/const/file |                   |
						---------------------------------+-------------------+---------------------------------------
						 file                            |   staging         |   (/my/repos/const)/staging/file
						 /my/config/const/file |                   |
						---------------------------------+-------------------+---------------------------------------
						 file                            |   prod            |   (/my/repos/const)/prod/file
						 /my/config/const/file |                   |
						---------------------------------+-------------------+---------------------------------------
			*/


			default:

				if ($this->rep != TypeRepository::FHIBOX1)
				{
					throw new \Exception("Repository autre que FHIBOX1 pas encore supporté...");
				}
				break;
		} // @formatter::on




		return $ret;
	}


	/**
	 * @return string
	 */
	public function getVcsWorkDir()
	{
		switch($this->env) //@formatter:off
		{	case  TypeEnv::PROD : return self::DIR_PROD_SVN_WORK_DIR;
			default             : throw new ProgrammingException("Env other than PROD not supported supported yet...");
		}                 //@formatter:on
		// Just for the compiler ...
	}

	/**
	 * @return string
	 */
	public function getMirrorDir()
	{
		switch($this->env) //@formatter:off
		{	case  TypeEnv::PROD : return self::DIR_PROD_MIRROR_DIR;
			default             : throw new ProgrammingException("Env other than PROD not supported supported yet...");
		}                 //@formatter:on
	}


	public function setIsDirectory($is_a_dir=true)
	{
		$this->isDirectory = $is_a_dir;
		return $this;
	}


	public function isDirectory()
	{	return $this->isDirectory;
	}

	/**
	 * Alias
	 */
	public function isDir()
	{	return $this->isDirectory();
	}

} 