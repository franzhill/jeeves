<?php

namespace fhibox\nestor\application\commands\deploy_log;

use fhibox\nestor\application\commands\CommandParent;
use fhibox\nestor\application\instructions\InstructionShellBuilder;
use fhibox\nestor\application\objects\FileLocator;
use fhibox\nestor\application\objects\SourceFile;


/**
 *
 * @author fhi
 */
class AppCommandDeployLog extends CommandParent
{

	protected function execute__()
	{	/*        */	$this->logger->debug("");


		// Get options and arguments
		// -------------------------


		// Checks and validations
		// ----------------------


		// Run
		// ----
		/*        */	$this->logger->debug("Running...");
		$isb = new InstructionShellBuilder($this);

		// TODO tidy up the whole conf settings
		$PROD_MIRROR = FileLocator::DIR_PROD_SYNC; #'/my/work/var/prod3/sources';
		$v = $this->getVerbosityAsOption();

		$isb
			->define("cd $PROD_MIRROR; svn up; svn log $v")
				->onBefore()
					->doInformUser("Displaying versions (revs) from prod...")
			->execute();



	}






}
