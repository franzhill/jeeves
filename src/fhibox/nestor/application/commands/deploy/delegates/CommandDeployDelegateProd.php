<?php

namespace fhibox\nestor\application\commands\deploy\delegates;

use fhibox\nestor\application\objects\FileLocator;


/**
 *
 * @author fhi
 */
class CommandDeployDelegateProd extends CommandDeployDelegate
{
	protected function rsyncToSyncDir_3()     //@formatter:off
	{
		// c. Synchro in test mode:
		$this->isb
			->define( "cd $this->vcs_workdir; rsync -avi --dry-run --delete --force $this->target_loc_in_vcs_workdir ". $this->target_loc_in_sync_dir)
				->onBefore()
					->doInformUser("> Synchronizing (test mode)...")
				->onFailure()
					->doWarnUser("Instruction failed.")
					->doAskUserIfContinue()
					->doAbort("User declined to proceed following failure.")
			->execute();

		
		// TODO
		// Suggest displaying differences between 2 envs (deactivate in non interactive mode) :
		// Example:
		//   rsync -avi --dry-run trunk/booking_saas_enterprise/clients/carrefour /my/work/var/prod3/sources/www/booking_saas_enterprise/clients/ | awk -v FS='' '{if ($4 == "s") {print $0;}}' | awk -v FS=' ' '{print $2;}' | while read i; do printf "\n\n============================================================\n$i\n\n\n";diff /my/repos/prod3/fhibox1/trunk/booking_saas_enterprise/clients/$i /my/work/var/prod3/sources/www/booking_saas_enterprise/clients/$i ; done

		// d. Synchro for real :
		// TODO : adapt rsync verbosity : rsync -avvvi ...
		// TODO : filter output : rsync -avi --dry-run trunk/<path>/   /my/work/…   | awk -v FS='' '{if ($4 == "s") {print $0;}}'
		// TODO : If no difference show, then we probably have to do a  svn update --set-depth infinity at previous stage
		$this->isb
			->define( "cd $this->vcs_workdir; rsync -avi --delete --force $this->target_loc_in_vcs_workdir ". $this->target_loc_in_sync_dir)
				// Revert svn state and also delete any added new files or reps previously unversionned, the deletion of which not handled by svn revert . 
				->defineRollback ("cd $this->mirror_dir; svn revert -R .; " . 'svn status -u --no-ignore 2>&1 | grep -e "^\?" -e "^I"' . "| awk '{print $2}' | xargs -r rm -r ")
				// svn st -–no-ignore | grep -e ^\? -e ^I | awk ‘{print $2}’ | xargs -r rm –r
				// svn status | grep ^\? | cut -c9-
				->onBefore()
					->doInformUser("> Syncing (real mode) with sync folder on FRONT ...")
				->onDuring()
				  #->doSimulateFailure("FHI: simulating failure to test rollback")
				->onFailure()
					->doWarnUser("Instruction failed.")
					->doAskUserIfContinue()
					->doInformUser("> Sync folder cleanup ('rollback')...")
					->doRollback()
					->doAbort("User declined to proceed following failure.")
			->execute();
	}                                         //@formatter:on


	protected function checkSyncDir()
	{
		//@formatter:off
		$this->isb
			->define( "cd $this->mirror_dir; svn st -u")
				// Revert svn state and also delete any added new files or reps previously unversionned, the deletion of which not handled by svn revert . 
				->defineRollback ("cd $this->mirror_dir; svn revert -R .; " . 'svn status -u --no-ignore 2>&1 | grep -e "^\?" -e "^I"' . "| awk '{print $2}' | xargs -r rm -r ")
				->onAfter()
					->doAskUserIfContinue("Dipsplayed modifications should correspond to what is to be deployed. Proceed?")
					->doInformUser("> Sync folder cleanup ('rollback')...")
					->doRollback()
					->doAbort("User declined to proceed following failure.")
			->execute();

		$this->isb
			->define( "cd $this->mirror_dir; svn diff")   // TODO pipe through more? In any case handle case when lenghty
				// Revert svn state and also delete any added new files or reps previously unversionned, the deletion of which not handled by svn revert . 
				->defineRollback ("cd $this->mirror_dir; svn revert -R .; " . 'svn status -u --no-ignore 2>&1 | grep -e "^\?" -e "^I"' . "| awk '{print $2}' | xargs -r rm -r ")
				->onBefore()
					->doAskUserIfContinue("See differences ?")
					->doHalt()
				->onAfter()
					->doAskUserIfContinue("Todo es ok?")
					->doInformUser("> Sync folder cleanup ('rollback')...")
					->doRollback()
					->doAbort("User declined to proceed following failure.")
			->execute();
	}


	protected function loadManifest()
	{
		//@formatter:off
		// Copy manifest (either default, or provided through option)
		//  and position OK flag to OK for deploy
		// ----------------------------------------------------------------------
		// TODO
		// - escapeshellcmd($manifest) or verify that it is matches the pattern for a file name

		$manifest_path         = FileLocator::DIR_PROD_MANIFESTS . '/' . $this->manifest;
		$manifest_current_name = FileLocator::MANIFEST_CURRENT_NAME;

		$this->isb
			->define( "[ -d $manifest_path ]")
				// Revert svn state and also delete any added new files or reps previously unversionned, the deletion of which not handled by svn revert . 
				->defineRollback ("cd $this->mirror_dir; svn revert -R .; " . 'svn status -u --no-ignore 2>&1 | grep -e "^\?" -e "^I"' . "| awk '{print $2}' | xargs -r rm -r ")
				->onBefore()
					->doInformUser("> Checking manifesto existence...")
				->onFailure()
					->doWarnUser("Deploy manifesto was not found: [$manifest_path] is not a file or does not exist.")
					->doInformUser("> Sync folder cleanup ('rollback')...")
					->doRollback()
					->doAbort("Deploy manifesto not found.")
			->execute();

			$shell=
<<<INSTR
								cd $this->mirror_dir/../manifests;
								rm -rf $manifest_current_name;
								cp -r $manifest_path $manifest_current_name;
INSTR;
 			$shell .= ($this->manualDeployPullUnlock) ?
<<<INSTR
								echo 'WARNING!! call mf_deploy.sh manually!!';
INSTR
					:
<<<INSTR
								cd $manifest_current_name; sh mf_deploy.sh;
INSTR;

		
		$this->isb
			->define($shell)
				->defineRollback ("cd $this->mirror_dir; svn revert -R .; " . 'svn status -u --no-ignore 2>&1 | grep -e "^\?" -e "^I"' . "| awk '{print $2}' | xargs -r rm -r ")
				->onBefore()
					->doInformUser("> Copying manifesto and unlocking sync with production instances (deploy_pull)...")
				->onFailure()
					->doWarnUser("Instruction failed.")
					->doInformUser("> Sync folder cleanup ('rollback')...")
					->doRollback()
					->doAbort("Unlocking sync failed...")
				->onSuccess()
					->doInformUser("Unlocking sync OK. Changes should be visible in production in a few moments.")
				->execute();
	}

#mv sources.exclude sources.exclude.`date "+%Y%m%d%H%M"`;
	protected function copyRsyncExcludes()
	{
		$manifest_current_name   = FileLocator::MANIFEST_CURRENT_NAME;
		$dir_common              = $this->mirror_dir."/../manifests/common/sync";
		$dir_individual_manifest = $dir_common."/projects/".$this->manifest;
		$this->isb
				->define(
<<<INSTR
								mkdir -p $dir_individual_manifest;
								cp $this->mirror_dir/../manifests/$manifest_current_name/sync/sources.exclude $dir_individual_manifest/sources.exclude 2>/dev/null || true;
								cd $dir_common;
								
								cat general/sources.exclude projects/*/sources.exclude | awk '{if  ($1 != "#") {print $0;}}' | grep -v '^$' > sources.exclude;
INSTR
						)
					->onBefore()
						->doInformUser("> Copying sources.exclude in folder of all sources.exclude...")
				->onFailure()
					->doWarnUser("Problem encountered while copying sources.exclude in folder of all sources.exclude. Manual intervention will probably be required. ")
				->execute();
	}


	protected function commitDeploy()
	{
		//@formatter:off
		// 6. Commit deployment
		// ---------------------
		// TODO
		// - handle rollback
		// - handle cases where files to be added to svn have spaces in them. This has happened, e.g. :
		//     www/booking_saas_all_business/booking_card2/core/web/assets/js/vendor/jquery-ui-1.11.4.custom (1).zip
		$message = escapeshellcmd($this->message);
		$author  = escapeshellcmd($this->author);
		$target_file = $this->target_files[0];
		
		
		$this->isb
			->define(
		#		escapeshellcmd(
<<<INSTR
			          cd $this->mirror_dir;
			          NB_NEW_FILES=`svn status  | awk '{ if ($1=="?"){print $2;}}' | wc -l` ;
			          [ \$NB_NEW_FILES -ne 0 ] && { svn status  | awk '{ if ($1=="?"){print $2;}}' | xargs svn add ; };
			          NB_DEL_FILES=`svn status  | awk '{ if ($1=="!"){print $2;}}' | wc -l` ;
			          [ \$NB_DEL_FILES -ne 0 ] && { svn status  | awk '{ if ($1=="!"){print $2;}}' | xargs svn del ; };
			          svn ci -m "Author=$author ; Project= ; Svn rev= $this->rev ; Files= $target_file; Message=$message"
INSTR
			        )
		#	        )
				->onBefore()
					->doInformUser("> Versionning deploy...")
				->onFailure()
					->doWarnUser("Instruction failed.")
					->doAbort("Deploy versioning failed. Manual intervention is required.")
				->onSuccess()
					->doInformUser("Versionning deploy was succesful.")
				->execute();
	}

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

		$this->checkSyncDir();
		$this->loadManifest();
		$this->copyRsyncExcludes();
		$this->commitDeploy();

		$this->wrapUp();

		exit(1);
	}

}