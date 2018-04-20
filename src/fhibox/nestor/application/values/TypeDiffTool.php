<?php

namespace fhibox\nestor\application\values;


use fhibox\enum\BaseEnum;

/**
 *
 *
 * @author Francois hill
 */
class TypeDiffTool extends BaseEnum
{
	// The values should be the actual commands, they will be directly used as-is. => NO NOT ANYMORE
	// Ideally we would have a conversion function 2command() that would do the translation... => DONE
	const VIMDIFF   = "vimdiff";
	const SDIFF     = "sdiff";
	const DIFF      = "diff";


	/**
	 * Returns system command corresponding to the tool
	 *
	 * @var string type_tool One of the possible values of this type
	 * @example toCommand(TypeDiffTool::DEV)
	 * @return string
	 */
	public static function toCommand($type_tool)
	{	switch ($type_tool)
		{	case self::VIMDIFF  : return "vimdiff";
			case self::SDIFF    : return "sdiff -w 200";  # width of 2 columns
			case self::DIFF     : return "diff -s";
			default             : return "diff -s";
		}
	}




}



