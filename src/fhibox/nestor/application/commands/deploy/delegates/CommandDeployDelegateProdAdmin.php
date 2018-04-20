<?php

namespace fhibox\nestor\application\commands\deploy\delegates;



/**
 *
 * @author fhi
 */
class CommandDeployDelegateProdAdmin extends CommandDeployDelegateProd
{
	protected function rsyncToSyncDir_3()     //@formatter:off
	{
		// Do the "normal" PROD sync
		parent::rsyncToSyncDir_3();
		
		// But also deploy to servers SLURPS1 and SLURPS2
		// From the old script:
		//		SERVER="slurps1 slurps2"
		//		for server in $SERVER; do
		//		echo $server
		//		rsync -ave ssh /my/repos/admin/slurps/certifications/ slurps@$server:/my/bin/slurps/certifications/
		//		rsync -ave ssh /my/repos/admin/slurps/panadom/ slurps@$server:/my/bin/slurps/panadom/
		$this->isb
			->define(
<<<INSTR
                cd $this->vcs_workdir;
                rsync -ave ssh $this->target_loc_in_vcs_workdir slurps@slurps1:$this->target_loc_in_server;
                rsync -ave ssh $this->target_loc_in_vcs_workdir slurps@slurps2:$this->target_loc_in_server;
INSTR
				       )
				->onBefore()
					->doInformUser("> Syncing (real mode) with servers SLURPS1 et SLURPS2 ...")
				->onDuring()
				->onFailure()
					->doWarnUser("Instruction failed.")
					->doAskUserIfContinue()
					->doRollback()
					->doAbort("User declined to proceed after failure.")
			->execute();
	}
}