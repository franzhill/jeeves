<?php

namespace fhibox\nestor\application\commands\deploy\delegates;


use fhibox\nestor\application\values\TypeEnv;


/**
 *
 * @author fhi
 */
class CommandDeployDelegateStaging extends CommandDeployDelegate
{

	/**
	 * @override
	 */
	protected function rsyncToSyncDir_3()     //@formatter:off
	{
		// Old script :
		// 	[ "$LIB_DEPLOY__TEST"  = "true"  ] &&  rsync -n -avie ssh --delete-after --exclude '.svn' /my/repos/fhibox1/$FILE_SRC fhibox@staging:/data/$FILE_DEST 2>&1
		//	[ "$LIB_DEPLOY__TEST" != "true"  ] &&  rsync    -ave ssh --delete-after --exclude '.svn' /my/repos/fhibox1/$FILE_SRC fhibox@staging:/data/$FILE_DEST 2>&1 | tee -a $LIB_LOGGING__LOG_FILE

		
		$host_dest = TypeEnv::toHostName($this->env);
		

		// c. Synchro in test mode:
		$this->isb
			->define(
<<<INSTR
                cd $this->vcs_workdir;
                rsync -n -avie ssh --delete-after --exclude '.svn' $this->target_loc_in_vcs_workdir fhibox@$host_dest:$this->target_loc_in_sync_dir 2>&1 ;
INSTR
			        )
				->onBefore()
					->doInformUser("> Synchronizing (test mode)...")
				->onFailure()
					->doWarnUser("Instruction failed.")
					->doAskUserIfContinue()
					->doAbort("User declined to proceed following failure.")
			->execute();

		
		// TODO
		// See CommandDeployDelegateProd
		$this->isb
			->define(
<<<INSTR
                cd $this->vcs_workdir;
                rsync -avie ssh --delete-after --exclude '.svn' $this->target_loc_in_vcs_workdir fhibox@$host_dest:$this->target_loc_in_sync_dir 2>&1 ;
INSTR
			        )
				->onBefore()
					->doInformUser("> Synchronizing (real mode) ...")
				->onDuring()
				->onFailure()
					->doWarnUser("Instruction failed.")
					->doAskUserIfContinue()
					->doRollback()
					->doAbort("User declined to proceed following failure.")
			->execute();
	}                                         //@formatter:on


	/**
	 * @override
	 */
	public function execute__()
	{	/*        */	$this->logger->debug("");

		$this->init();
		$this->getOptionsAndArguments();
		$this->prelimChecks();
		$this->getLocations();
		$this->moreChecks();
		$this->updateTargetVcsVersion();
		$this->rsyncToSyncDir();

		$this->wrapUp();


		exit(1);
	}

}