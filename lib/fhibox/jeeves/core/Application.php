<?php
/**
 * Created by PhpStorm.
 * User: Francois hill
 * Date: 16/10/201x
 * Time: 17:28
 */

namespace fhibox\jeeves\core;

use fhibox\logging_interface\ILoggingInterface;
use Symfony\Component\Console\Application as SymfonyConsoleApplication;

class Application extends SymfonyConsoleApplication
{

	#/**
	# * @var ILoggingInterface $logging
	# */
	#protected $logging;
	#
	#
	#public function getLogging()
	#{ return $this->logging;
	#}
	#
	#public function setLogging(ILoggingInterface $log_i)
	#{
	#	$this->logging = $log_i;
	#	return $this;
	#}



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

}