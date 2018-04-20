<?php

namespace fhibox\jeeves\core;


use fhibox\configuration\ConfigurationLoader;



/**
 * Description of Option
 *
 * @author Francois hill
 */
abstract class  Argument extends OptionOrArgument
{





	protected function getModeReflectionClass()
	{
		return '\Symfony\Component\Console\Input\InputArgument';
	}


}
