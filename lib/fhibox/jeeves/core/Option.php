<?php

namespace fhibox\jeeves\core;


use fhibox\configuration\ConfigurationLoader;
use fhibox\configuration\IConfiguration;


/**
 * Description of Option
 *
 * @author Francois hill
 */
abstract class Option extends OptionOrArgument
{


	protected function getModeReflectionClass()
	{
		return '\Symfony\Component\Console\Input\InputOption';
	}


}
