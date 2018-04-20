<?php
/**
 * Created by PhpStorm.
 * User: Francois hill
 * Date: 22/10/201x
 * Time: 10:16
 */

namespace fhibox\namespace_lib;
use fhibox\logging_interface\ILoggingInterface;


/**
 * Provides functionality to retrieve a namespace of a PHP file, given its path.
 *
 * @package fhibox\namespace_lib
 */
class NamespaceExtractor
{
	/**
	 * Using this strategy will always yield the correct results  but may be slower
	 */
	const STRATEGY_SECURE = 1;

	/**
	 * Use this strategy in a certain context for faster results:
	 * If we know that namespace is declared on its own line, starting with "namespace" (no spaces).
	 */
	const STRATEGY_FAST = 2;


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




	public function __construct($logger=null)
	{
		$this->logger = $logger;
	}





	/**
	 * @param $file_path
	 * @param int $strategy One of the STRATEGY_* constants of this class.
	 * @throws \RuntimeException Do not try to catch
	 * @return string The namespace
	 */
	public function extractNsFromFile($file_path, $strategy=self::STRATEGY_SECURE)
	{ /*        */  if (! is_null($this->logger)) $this->logger->debug("");
		switch($strategy)
		{
			case self::STRATEGY_SECURE: return self::by_token (file_get_contents($file_path));
			case self::STRATEGY_FAST  : return self::by_regexp(file_get_contents($file_path));
			#default                   :
		}
		throw new \RuntimeException("Function not called properly, received value not expected, please check function doc.");
	}

	/**
	 * Courtesy of https://gist.github.com/naholyr/1885879
	 * @param $src
	 * @return null|string
	 */
	private function by_token ($src)
	{ /*        */  if (! is_null($this->logger)) $this->logger->debug("");
		$tokens = token_get_all($src);
		$count = count($tokens);
		$i = 0;
		$namespace = '';
		$namespace_ok = false;
		while ($i < $count) {
			$token = $tokens[$i];
			if (is_array($token) && $token[0] === T_NAMESPACE) {
				// Found namespace declaration
				while (++$i < $count) {
					if ($tokens[$i] === ';') {
						$namespace_ok = true;
						$namespace = trim($namespace);
						break;
					}
					$namespace .= is_array($tokens[$i]) ? $tokens[$i][1] : $tokens[$i];
				}
				break;
			}
			$i++;
		}
		if (!$namespace_ok)
		{ /*        */  if (! is_null($this->logger)) $this->logger->debug("Namespace not OK");
			return null;
		}
		else
		{
			return $namespace;
		}
	}

	/**
	 * Courtesy of https://gist.github.com/naholyr/1885879
	 *
	 * Makes many assumptions on file format:
	*  namespace is declared on its own line, starting with "namespace" (no spaces).
	 *
	 * @param $src
	 * @return null|string
	 */
	private function by_regexp($src)
	{ /*        */  if (! is_null($this->logger)) $this->logger->debug("");
		if (preg_match('#^namespace\s+(.+?);$#sm', $src, $m))
		{ /*        */  if (! is_null($this->logger)) $this->logger->debug("match found!");
			return $m[1];
		}
		/*        */  if (! is_null($this->logger)) $this->logger->debug("match NOT found!");
		return null;
	}


	/**
	 * Benchmark the two strategies
	 *
	 * Courtesy of https://gist.github.com/naholyr/1885879
	 *
	 * @param $foo
	 * @param $src
	 * @return mixed
	 * @throws Exception
	 */
	protected function bench($foo, $src)
	{
		$start = microtime(true);
		for ($i=0; $i<10000; $i++) {
			$ns = $foo($src);
			if ($ns !== 'Acme\\HelloBundle\\DependencyInjection') {
				throw new Exception('What?');
			}
		}
		$end = microtime(true);
		return $end - $start;
	}


} 