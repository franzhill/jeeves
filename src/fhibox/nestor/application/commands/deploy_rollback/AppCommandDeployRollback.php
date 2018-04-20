<?php

namespace fhibox\nestor\application\commands\deploy_rollback;

use fhibox\nestor\application\commands\CommandParent;
use fhibox\nestor\application\instructions\InstructionShellBuilder;
use fhibox\nestor\application\objects\FileLocator;
use fhibox\nestor\application\objects\SourceFile;


/**
 *
 * @author fhi
 */
class AppCommandDeployRollback extends CommandParent
{

	protected function execute__()
	{	/*        */	$this->logger->debug("");

		// Get options and arguments
		// -------------------------
		$rev          = $this->getOptionValue  ('rev');

		// Checks and validations
		// ----------------------
		// TODO check that $rev is an "acceptable" revision i.e. not > last revision number



		// Run
		// ----
		/*        */
		$this->logger->debug("Running...");

		$isb = new InstructionShellBuilder($this);

		$PROD_MIRROR = FileLocator::DIR_PROD_SYNC; #'/my/work/var/prod3/sources';

		$isb
			->define(
<<<INSTR
cd $PROD_MIRROR;
CURR_REV=`svn info |grep Revision: |cut -c11-`;
echo "Current prod rev: \$CURR_REV";
PREV_REV=`expr \$CURR_REV - 1`;
echo "Previous prod rev: \$PREV_REV";
INSTR
				)
			->onBefore()
				->doInformUser("> Computing previous rev...")
			->onAfter()	
				->doAskUserIfContinue("Revert to previous prod rev displayed above?")
				->doAbort("User declined. Rollback could not be carried out.")
			->execute();


		$isb
			->define(
<<<INSTR
cd $PROD_MIRROR;
CURR_REV=`svn info |grep Revision: |cut -c11-`;
PREV_REV=`expr \$CURR_REV - 1`;
svn merge --dry-run -r HEAD:\$PREV_REV .
INSTR
				)
			->onBefore()
				->doInformUser("> Performing rollback in test mode (dry-run)")
			->onAfter()
				->doAskUserIfContinue("Proceed with real rollback?")
				->doAbort("User declined. Rollback could not be carried out.")
			->execute();

		$isb
			->define(
<<<INSTR
cd $PROD_MIRROR;
CURR_REV=`svn info |grep Revision: |cut -c11-`;
PREV_REV=`expr \$CURR_REV - 1`;
svn merge -r HEAD:\$PREV_REV .;
svn ci –m 'Rollback done via Nestor'; 
INSTR
				)
			->onBefore()
				->doInformUser("> Performing rollback...")
			->onAfter()
				->doInformUser("> Rollback done...")
			->execute();


	}






}
