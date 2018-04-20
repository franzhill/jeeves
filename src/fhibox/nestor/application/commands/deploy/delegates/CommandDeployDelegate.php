<?php

namespace fhibox\nestor\application\commands\deploy\delegates;


use fhibox\nestor\application\commands\deploy\AppCommandDeploy;
use fhibox\nestor\application\instructions\InstructionShellBuilder;
use fhibox\nestor\application\objects\FileLocatorBuilder;
use fhibox\nestor\application\values\TypeLocation;
use fhibox\nestor\application\values\TypeEnv;
use fhibox\nestor\application\values\TypeRepository;
use fhibox\string\StringLib;


/**
 *
 * @author fhi
 */
abstract class CommandDeployDelegate
{
	protected $helpee;
	protected $logger;

	protected $env         ;
	protected $rev         ;
	protected $rep         ;
	protected $message     ;
	protected $author      ;
	protected $manifest    ;
	protected $target_files;

	/** @var InstructionShellBuilder */
	protected $isb         ;
	/** @var  FileLocatorBuilder */
	protected $fl          ;
	protected $target_loc_in_vcs_workdir;
	protected $target_loc_in_sync_dir;
	protected $target_loc_in_server ;
	protected $target_basename;
	protected $vcs_workdir;
	protected $mirror_dir;
	protected $manualDeployPullUnlock;


	public function __construct(AppCommandDeploy $helpee)
	{
		// Here we're using the Composition pattern ;o)

		$this->helpee = $helpee;

		/*        */	$logging = $this->helpee->getLogging();
		/*        */	$this->logger = $logging->getLogger(str_replace('\\', '.', __CLASS__));
		/*        */	$this->logger->debug("");
	}


	protected function init()
	{
		$this->isb = new InstructionShellBuilder($this->helpee);
	}


	protected function getOptionsAndArguments()
	{
		$this->env                    = $this->helpee->getOptionValue  ('env_deploy');
		$this->rev                    = $this->helpee->getOptionValue  ('rev');
		$this->rep                    = $this->helpee->getOptionValue  ('rep');
		$this->message                = $this->helpee->getOptionValue  ('message_deploy');
		$this->author                 = $this->helpee->getOptionValue  ('author');
		$this->manifest               = $this->helpee->getOptionValue  ('manifest_deploy');
		$this->manualDeployPullUnlock = $this->helpee->getOptionValue  ('manual-unlock');
		$this->target_files           = $this->helpee->getArgumentValue('source-file');

		/*        */	$this->logger->debug("env           =     $this->env");
		/*        */	$this->logger->debug("rev           =     $this->rev");
		/*        */	$this->logger->debug("message       =     $this->message");
		/*        */	$this->logger->debug("author        =     $this->author");
		/*        */	$this->logger->debug("manifest      =     $this->manifest");
		/*        */	$this->logger->debug("targets       = " . print_r($this->target_files, true));
	}


	protected function prelimChecks()
	{
		// For the time being, we won't be handling more than one file/rep argument
		// TODO handle more than one file/rep
		if (is_array($this->target_files) && count($this->target_files) > 1)
		{	throw new \Exception("Sorry, for now on only supporting one file...");
		}

		// Check we're operating under user fhibox
		// (except for local tests)

		// Nota :
		// On my local machine, running
		//   < ? php system('echo $ENV'))
		// will display
		//   $ENV
		// instead of the expected
		//   LOCAL
		// Very surprisingly this is not the case when called from command line:
		//   $ php -r "system('echo $ENV');"
		//   LOCAL
		// Running
		//   < ? p hp system('echo $ENV'))
		// on server FRONT yields the expected result
		//
		// Workarounds for local machine:
		//  < ? php  system('env | grep ENV | cut -d"=" -f2');
		//  < ? php  system("bash -c 'echo \$ENV''");
		//  < ? php  system('bash -c "[ \"$ENV\" == \"LOCAL\" ] && echo environment_is_local! "');

	}


	protected function getLocations()
	{
		// FileLocator will locate all different 'avatars' of the target file used during the deploy process:
		$this->fl                         = FileLocatorBuilder::build(TypeRepository::from($this->rep), $this->target_files[0], TypeEnv::from($this->env) );
		$this->fl                         ->setLogger($this->logger);

		$this->target_loc_in_vcs_workdir  = $this->fl->locate(TypeLocation::VCS_WORKDIR)  ->getPath();   #$sf->getRealPathInVcsWorkDir();
		$this->target_loc_in_sync_dir     = $this->fl->locate(TypeLocation::SYNC_DIR)     ->getPath();   #$sf->getRealPath();
		$this->target_loc_in_server       = $this->fl->locate(TypeLocation::SERVER)       ->getPath();
		$this->target_basename            = $this->fl->locate(TypeLocation::SYNC_DIR)     ->getBasename();

		// ...and will also give us some important locations:
		$this->vcs_workdir                = $this->fl->getVcsWorkDir();

		if (TypeEnv::is($this->env, TypeEnv::PROD))
		{  // This variable is not used outside of a deploy in PROD
			$this->mirror_dir = $this->fl->getSyncDir();
		}
/*
		echo "target_loc_in_vcs_workdir  = $this->target_loc_in_vcs_workdir  \n";
		echo "target_log_in_sync_dir     = $this->target_loc_in_sync_dir     \n";
		echo "target_basename            = $this->target_basename            \n";
*/
	}


	protected function moreChecks()
	{
		/*        */	$this->logger->debug("************ real base name           =     " . $this->target_basename);
		if ( empty($this->target_basename)   )
		{ // TODO improve Exception mechanism
			throw new \Exception("For security reasons it is not possible to deploy at the root of the system");
		}
	}


	protected function updateTargetVcsVersion()
	{
		// TODO if in test mode : 
		// instead of doing an svn up ..., do :
		// svn merge --dry-run -r BASE:$REV /my/repos/fhibox1/$FILE_SRC /my/repos/fhibox1/$FILE_SRC
		
		$this->isb
			->define("cd $this->vcs_workdir ; svn up -r $this->rev --parents --set-depth infinity $this->target_loc_in_vcs_workdir")
			->defineRollback("echo 'Testing rollback feature!...'")
				->onBefore()
					->doInformUser("> Getting svn version of element to be deployed...")
				->onDuring()
					#->doSimulateFailure("Simulating failure!")
				->onFailure()
					->doWarnUser("Instruction failed.")
					->doAskUserIfContinue("Continue nonetheless?")
					->doRollback("Testing rollback feature...")   // No need to actually do a rollback at this point
					->doAbort("User chose not to proceed following failure.")
			->execute();

		// Ideally : check result AND that there is no conflict (svn considers a merge with conflict as a success)
		// => do a svn st -u and check for lines with M : there should be none.

		// After svn up: check file exists (indeed if a typo is made in the file path, and file does not exist in svn rep,
		// svn up will not complain and return success)

		// ls <file> will return 2 if file does not exist
		// we could also use [ -f <file> ] which returns 1 in that case
		// However ls <file> displays an error message => our choice
/*
		$this->isb
			->make("cd $SVN_WORK_DIR; ls $path_in_vcs_workdir ")
				->onFailure()
					#->doRollback()   // No need to actually do a rollback at this point
					->doAbort("A problem was encountered. Please check path of file. ".
					          "A problem might have arrisen during update, or error in file name/path.")
				->execute2();
*/

	}

	// TODO
	// adapt according to
	// - env : if not PROD, the rsync will be remote
	//    for STAGING : rsync  -ave ssh --delete-after --exclude '.svn' /my/repos/fhibox1/$FILE_SRC fhibox@staging:/data/$FILE_DEST 2>&1
	// - rep : if admin : sync to everywhere (=>also sync to slurps1, slurps2) (=>env is not taken into account)
	/*    Previously, on each commit, file was deployed to all envs :
			SERVER="dev staging"

			for server in $SERVER; do
			echo $server
			rsync -ave ssh /my/repos/admin/slurps/certifications/ fhibox@$server:/my/bin/slurps/certifications/
			rsync -ave ssh /my/repos/admin/slurps/panadom/ fhibox@$server:/my/bin/slurps/panadom/
			#
			done

			SERVER="slurps1 slurps2"
			for server in $SERVER; do
			echo $server
			rsync -ave ssh /my/repos/admin/slurps/certifications/ slurps@$server:/my/bin/slurps/certifications/
			rsync -ave ssh /my/repos/admin/slurps/panadom/ slurps@$server:/my/bin/slurps/panadom/
*/
	protected function rsyncToSyncDir()
	{
		$this->rsyncToSyncDir_1();
		$this->rsyncToSyncDir_2();
		$this->rsyncToSyncDir_3();
	}


	protected function rsyncToSyncDir_1()     //@formatter:off
	{
		// a. If source file is a directory, add final / when rsyncing :
		$this->isb
			->define( "cd $this->vcs_workdir; [ -d $this->target_loc_in_vcs_workdir ]")
				->onBefore()
					->doInformUser("> Just to know if it's a file or a folder...")
			->execute()
			->isResultSuccess() && (  $this->target_loc_in_vcs_workdir .= StringLib::endsWith($this->target_loc_in_vcs_workdir, '/') ?  '' : '/' )
			                    && $this->fl->setIsDirectory(true);
	}                                         //@formatter:on


	protected function rsyncToSyncDir_2()     //@formatter:off
	{
		// b. Rsync does not create intermediary directories if they do not exist => will have to do that ourselves
		//    => we'll use mkdir -p (no consequence if already exists)
		$this->isb
			->define( $this->fl->isDir() ? "mkdir -p ".$this->target_loc_in_sync_dir : 'mkdir -p `dirname '.$this->target_loc_in_sync_dir.'`' )
				->onBefore()
					->doInformUser("> Trying to create folder if it does not exist...")
				->onFailure()
					->doAbort("Could not create path [" . $this->target_loc_in_sync_dir ."] before rsync.")
			->execute();
	}                                         //@formatter:on


	abstract protected function rsyncToSyncDir_3();


	protected function wrapUp()
	{
		// 7. Wrap up :
		// -------------
		// TODO
		// - Log all info (possibly in a DB)
		// - Send mail etc.
		
		$send_to_addresses = array_values(array_filter(array_map('trim', explode(',', $this->helpee->getConf()->get("end_notification_mail_to")))));
		#echo "Sending wrap up email to : " . print_r($send_to_addresses, true);
		$send_to_addresses = implode("," , $send_to_addresses );

		$msg     = "Deploy in ".$this->env." was successful:"   . "\\n" .
		           " File/folder     : " . print_r($this->target_files, true) . "\\n" .
		           " Svn Repository  : " . $this->rep                         . "\\n" .
		           " Svn revision    : " . $this->rev                         . "\\n" .
		           " Author          : " . $this->author                      . "\\n" .
		           " Message         : " . $this->message                     . "\\n" ; 

		$this->isb
			->define(
<<<INSTR
			           echo "$msg" | mailx -s 'Deployed in $this->env'  $send_to_addresses

INSTR
	             )
				->onBefore()
					->doInformUser("> Sending end of deploy notification mail to : " . print_r($send_to_addresses, true))
				->onFailure()
					->doWarnUser("Failed sending mail.")
				->execute();
	}


	abstract public function execute__();




}
