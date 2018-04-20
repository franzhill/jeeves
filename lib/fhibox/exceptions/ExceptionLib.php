<?php
/**
 * Created by PhpStorm.
 * User: Francois hill
 * Date: 04/12/201x
 * Time: 16:35
 */

namespace fhibox\exceptions;


class ExceptionLib
{
	/**
	 * Courtesy of http://stackoverflow.com/questions/1949345/how-can-i-get-the-full-string-of-php-s-gettraceasstring
	 *
	 * @param \Exception $exception
	 * @return string
	 */
	static public function getExceptionTraceAsString(\Exception $exception)
	{
		$rtn = "";
		$count = 0;
		foreach ($exception->getTrace() as $frame) {
			$args = "";
			if (isset($frame['args'])) {
				$args = array();
				foreach ($frame['args'] as $arg) {
					if (is_string($arg)) {
						$args[] = "'" . $arg . "'";
					} elseif (is_array($arg)) {
						$args[] = "Array";
					} elseif (is_null($arg)) {
						$args[] = 'NULL';
					} elseif (is_bool($arg)) {
						$args[] = ($arg) ? "true" : "false";
					} elseif (is_object($arg)) {
						$args[] = get_class($arg);
					} elseif (is_resource($arg)) {
						$args[] = get_resource_type($arg);
					} else {
						$args[] = $arg;
					}
				}
				$args = join(", ", $args);
			}
			$rtn .= sprintf( "#%s %s(%s): %s(%s)\n",
				$count,
				$frame['file'],
				$frame['line'],
				$frame['function'],
				$args );
			$count++;
		}
		return $rtn;
	}


	/**
	 * Zxample of use :
	 * <pre>
	 *   ExceptionLib::printTrace(debug_backtrace());
	 * </pre>
	 * @param $stacktrace
	 * @return string
	 */
	public static function printTrace($stacktrace)
	{
		print str_repeat("=", 50) ."\n";
		$i = 1;
		$ret = '';
		foreach($stacktrace as $node)
		{
			$ret .= "$i. ".basename($node['file']) .":" .$node['function'] ."(" .$node['line'].")\n";
			$i++;
		}
		return $ret;
	}
} 