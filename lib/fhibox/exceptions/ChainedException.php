<?php
/**
 * Created by PhpStorm.
 * User: Francois hill
 * Date: 30/11/201x
 * Time: 17:20
 */


namespace fhibox\exceptions;

/**
 * An exception class meant as a replacement for PHP's \Exception
 *
 * This class automatically includes messages of cause exceptions (chained exceptions)
 * in own message, so doing $e->getMessage() will actually print the stack of chained
 * exceptions.
 *
 * @author fhi
 */
abstract class ChainedException extends \Exception
{
	/**
	 * @override
	 * @param string $message
	 * @param int $code
	 * @param \Exception $previous
	 */
	public function __construct($message, $code=0, \Exception $previous=null)
	{
		// If called from extending class:
		if (! $this->isRoot())
		{
			// Call usual constructor to each exception up the hierarchy tree showing its own causes:
			parent::__construct($message, $code, $previous);
		}

		// If called from this very class, include cause exception messages in message of this exception:
		// (PHP doesn't do it automatically...)
		else
		{	$mess = $message."\n\tCause exceptions:\n";

			while(!is_null($previous))
			{
				$prev_file  = $previous->getFile();
				$prev_line  = $previous->getLine();
				$prev_class = get_class($previous);
				$prev_mess  = $previous->getMessage();
				$prev_trace = $previous->getTraceAsString();

				$mess.=<<<MESS
\t\t[$prev_class]:
\t\t\t Thrown at: File,Line = $prev_file, $prev_line
\t\t\t With message = $prev_mess
\t\t\t BackTrace   =
$prev_trace
MESS;

				$previous = $previous->getPrevious();
			}

			$mess .= $message."\n\n\tParent exception trace:\n";
			$mess .= $this->getTraceAsString();

			parent::__construct($mess, $code, $previous);
		}
	}

	/**
	 * Is this exception, in respect with inheritance hierarchy, considered the 'root' ('ancestor')
	 * of the chained exception hierarchy?
	 * If this is the case, then its message should display the (messages of the) chain of cause exceptions.
	 * If this is not the case, then its message should just be the current message.
	 * Failing to identify this (and have 2 different ways of constituting the messages) will lead to
	 * redundancy as every exception up the hierarchy tree will display its own preceding causes. (we want
	 * that only once).
	 *
	 * Example for implementing this class:
	 * <pre>
	 * 	public function isRoot()
	 * {
	 *    return get_class($this) == __CLASS__ ;
	 * }
	 * </pre>
	 */
	protected abstract function isRoot();

}
