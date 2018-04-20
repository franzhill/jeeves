<?php

namespace fhibox\jeeves\core;

/**
 * @author Francois hill
 */
interface ICommandTestable
{
	/**
	 * Indicate whether command will be run in test mode (= "dry run")
	 * Test mode goes one step further than "virtual mode" in the sense that
	 * it will rely on test modes (dry run) of commandes it is made up of,
	 * if they exist.
	 * @return boolean
	 */
	public function isTestMode();


	/**
	 * Indicate whether command will be run in virtual mode
	 * (i.e. command is not really run, a description of what it should do is printed.)
	 * In that sense "virtual mode" goes one step shorter taht "test mode"
	 * @return bool
	 */
	public function isVirtualMode();
}