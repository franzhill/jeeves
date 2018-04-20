<?php

namespace fhibox\nestor\application\commands\compare;


use fhibox\jeeves\core\exceptions\NoUserConfirmationOnPromptException;

use fhibox\nestor\application\commands\CommandParent;
use fhibox\nestor\application\instructions\InstructionShell;

use fhibox\nestor\application\objects\SourceFile;
use fhibox\nestor\application\values\TypeEnv;
use fhibox\nestor\application\values\TypeDiffTool;
use fhibox\nestor\application\values\TypeRepository;


use fhibox\nestor\application\arguments\exceptions\ArgumentTargetIsTrunkException;




/**
 * Command to compare e.g. source files across e.g. two  different environments
 *
 * @author Francois hill
 * @todo maybe make more explicit if there is no difference (because diff tools won't say/display anything)
 */
class AppCommandCompare extends CommandParent
{

	protected function execute__()
	{	/*        */	$this->logger->debug("");

		// Get options and arguments
		// -------------------------

		$diffTool            = $this->getOptionValue  ('diff-tool'  );
		$repository          = $this->getOptionValue  ('rep'        );
		$target_envs         = $this->getArgumentValue('envs'       );
		$target_files        = $this->getArgumentValue('source-file');

		/*        */	$this->logger->debug("diff_tool    = " . $diffTool);
		/*        */	$this->logger->debug("repository   = " . $repository);
		/*        */	$this->logger->debug("target_envs  = " . print_r($target_envs, true));
		/*        */	$this->logger->debug("target_file  = " . print_r($target_files, true));

		#// Process option values
		#($test_mode    == true ) &&  $this->setTestModeOn    ();  // Else, rely on default value
		#($virtual_mode == true ) &&  $this->setVirtualModeOn ();  // Else, rely on default value


		// Checks and validations
		// ----------------------
		// This command is only supported for dev, staging, prod => check that
		$env_left = strtoupper($target_envs[0]);
		$env_right= strtoupper($target_envs[1]);
		$arr_supported_envs = array(TypeEnv::DEV, TypeEnv::STAGING, TypeEnv::PROD);
		if ( !  in_array($env_left , $arr_supported_envs) )
		{ // TODO use more precise exception
			throw new \Exception("Sorry, environment [$env_left] is not supported for this command.");
		}
		if ( ! in_array($env_right , $arr_supported_envs) )
		{ // TODO use more precise exception
			throw new \Exception("Sorry, environment [$env_right] is not supported for this command.");
		}

		// Target File should only be a file and not a dir
		$target_file = $target_files[0];
		// TODO


		// Run
		// ----
		/*        */	$this->logger->debug("Running...");

		// Get normalised path for file in both envs:

		$source_file_left = new SourceFile(TypeRepository::FHIBOX1, $target_file, $env_left  );
		$source_file_right= new SourceFile(TypeRepository::FHIBOX1, $target_file, $env_right );


		$target_file_normalized_left  = $source_file_left ->getRealPath();
		$target_file_normalized_right = $source_file_right->getRealPath();
		/*        */	$this->logger->debug("target_file_normalized_left =$target_file_normalized_left");
		/*        */	$this->logger->debug("target_file_normalized_right=$target_file_normalized_right");

		// Get diff tool

		$diffToolCmd = TypeDiffTool::toCommand($diffTool);
		$diffToolCmd = ($diffTool == TypeDiffTool::VIMDIFF) ?  $diffToolCmd . '>`tty`' : $diffToolCmd;

		// Convert environments passed as argument, to their host names
		// If asked env is prod, instead look on front, in source syncing directory
		$env_left  = TypeEnv::toHostName($source_file_left ->getRealEnv());
		$env_right = TypeEnv::toHostName($source_file_right->getRealEnv());

		// Finally: build the command, and execute!
		// The >`tty' part is because PHP system calls doesn't automatically pass through the STDIN/STDOUT streams
		// Also, system calls are made using sh, so we'll have to subshell to run bash




		$command = new InstructionShell("/bin/bash -c \"$diffToolCmd <(ssh $env_left 'cat $target_file_normalized_left') <(ssh $env_right 'cat $target_file_normalized_right')\"", $this);
		$command -> execute();

		# Deprecated (now we have figured out how to make vimdiff work with the tty trick
		#$tmp = CMD_TEMP_DIR;
		#(new CommandShell("ssh $env_left  'cat /my/www/$target_file' > $tmp/left") )-> execute();
		#(new CommandShell("ssh $env_right 'cat /my/www/$target_file' > $tmp/right") )-> execute();
		#
		#(new CommandShell("</dev/tty vimdiff $tmp/left $tmp/left") )-> execute();



	}

}
