<?php
/**
 * Created by PhpStorm.
 * User: Francois hill
 * Date: 14/10/201x
 * Time: 19:22
 */

namespace fhibox\jeeves\core;
use fhibox\jeeves\Jeeves;

/**
 * Just a dummy class, used in some places for conformity
 *
 * @package fhibox\jeeves\core
 */
class AppCommandEmpty extends AppCommand
{

	/**
	 * This is a "dummy" AppCommand for use when registering options and arguments
	 * in "real" AppCommands.
	 *
	 * We'll be redefining the parent constructor so as not to call
	 * registerOptions() or registerArguments() (otherwise this would result in
	 * infinite loop)
	 *
	 * @param Jeeves $parent
	 */
	public function __construct(Jeeves $parent)
	{
		$this->valetParent = $parent;

		/*        */	$this->logging = $this->valetParent->getLogging();
		/*        */	$this->logger = $this->logging->getLogger(str_replace('\\', '.', __CLASS__));
		/*        */	$this->logger->debug("");

	}


	/**
	 * To be defined in extending class.
	 * Contains what the command actually does.
	 * @return mixed
	 */
	protected function execute_()
	{
		// NOTHING
	}
}