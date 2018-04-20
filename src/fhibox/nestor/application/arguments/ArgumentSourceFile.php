<?php

namespace fhibox\nestor\application\arguments;


use fhibox\jeeves\core\Argument;
use fhibox\string\StringLib;

/**
 * Argument specifying a source file.
 *
 * To be used e.g. in commands that deploy, compare, analyse, revert etc. a source (code) file
 *
 * @author fhi
 */
class ArgumentSourceFile extends Argument
{
	/**
	 * @override
	 */
	protected function decorate_($value)
	{ $ret = $value;

		// TODO extract patterns in conf file
		// This allows the user to type in the source file using shell completion.
		// The part that does not actually correspond to the file will be stripped off.
		$leading_patterns_to_remove = array( "/my/repos/fhibox1/"      ,
		                                     "/my/repos/prod3/fhibox1/",
		                                     "/my/repos/const/"    ,
		                                     "/my/repos/admin/"
		                                  );
		// Remove leading pattern if present
		foreach ($leading_patterns_to_remove as $patt)
		{	if (StringLib::startsWith($value, $patt))
			{	$ret = substr($value, strlen($patt));
				break;
			}
		}
		#echo "ArgumentSourceFile::decorate_ : ret = $ret \n";
		return $ret;
	}


	/**
	 * @override
	 */
	protected function validate_($target)
	{
		// We will be doing the actual validation (verifying stuff like user is not trying to
		// pass wrong source file like just /my/www or trunk) in the command itself
		// since we need more context to work out the exact intended path

		return true;
	}


}
