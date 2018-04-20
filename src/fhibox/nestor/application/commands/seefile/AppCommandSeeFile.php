<?php

namespace fhibox\nestor\application\commands\seefile;
use fhibox\nestor\application\commands\CommandParent;
use fhibox\nestor\application\instructions\InstructionShellBuilder;
use fhibox\nestor\application\objects\SourceFile;
use fhibox\nestor\application\values\TypeEnv;

/**
 * Created by PhpStorm.
 * User: Francois hill
 * Date: 26/11/201x
 * Time: 18:45
 */



class AppCommandSeeFile extends CommandParent
{
	protected function execute__()
	{  /*        */
		$this->logger->debug("");

		// Get options and arguments
		// -------------------------
		$tail         = $this->getOptionValue  ('tail');
		$mail         = $this->getOptionValue  ('mail');
		$grep         = $this->getOptionValue  ('grep');
		$file         = $this->getArgumentValue('source-file_seefile');

		// Checks and validations
		// ----------------------


		// Run
		// ----
		/*        */
		$this->logger->debug("Running...");


		#$source_file          = new SourceFile($rep, $file, /* $env */ TypeEnv::STAGING );
		#$source_file_path     = $source_file->getRealPath();

		$source_file_path     = $file;
		$source_file_basename = basename($file);

		// Retrieve instance ips in dynamic way, via AWS CLI
		// --------------------------------------------------
		// (http://serverfault.com/questions/704806/how-to-get-autoscaling-group-instances-ip-adresses)

		$isb = new InstructionShellBuilder($this);
		ob_start();
		$isb
			->define(
<<<INSTR
for i in `aws autoscaling describe-auto-scaling-groups --auto-scaling-group-name prod3 | grep -i instanceid  | awk '{ print $2}' | cut -d',' -f1| sed -e 's/"//g'`;
do aws ec2 describe-instances --instance-ids \$i | grep -i PrivateIpAddress | awk '{ print $2 }' | head -1 | cut -d"," -f1 | tr '\\n' ' ';
done;
INSTR
				)
			->onBefore()
				->doInformUser("Retrieving IP @ of ASG prod3 instances...")
			->execute();

		$ips_servers = ob_get_contents();
		ob_end_clean();
		$ips_servers   = array_values(array_filter(array_map('trim', explode('"', $ips_servers))));
		$this->displayMessage("IP addresses of ASG prod3 instances:" . print_r($ips_servers, true));
		
/*
		Something like
		$ips_servers   = array("168.12.13.14",
		                       "168.12.13.15"
		);
*/


		// Retrieve file on each instance
		// -------------------------------
		$instr_tail = isset($tail) ? "| tail -$tail" : "";
		$instr_grep = isset($grep) ? "| grep \"$grep\"" : "";
		$instr_mail = isset($mail) ? "| mailx -s 'Contents in prod of file: $file'  $mail" : "";
		$tmp_file   = "Nestor_SeeFile.tmp" ;

		$shell=
<<<INSTR
			          cd /var/tmp                                                                       ;
			          mkdir -p Nestor/seefile                                                           ;
			          cd Nestor/seefile                                                                 ;
			          rm $tmp_file 2>/dev/null                                                          ;
INSTR;

		foreach ($ips_servers as $ip)
		{
			$shell.=
<<<INSTR
			          rm $source_file_basename-${ip}   2>/dev/null                          ;
			          scp $ip:$source_file_path $source_file_basename-${ip}                 ;
			          echo ""                                                         >> $tmp_file       ;
			          echo "========================================================" >> $tmp_file      ;
			          echo " On server : $ip : "                                      >> $tmp_file      ;
			          echo "========================================================" >> $tmp_file      ;
			          echo ""                                                         >> $tmp_file      ;
			          cat $source_file_basename-${ip} $instr_grep $instr_tail         >> $tmp_file      ;
			          echo ""                                                         >> $tmp_file      ;

INSTR;
		}

		$shell.=
<<<INSTR
			          cat $tmp_file $instr_mail                                                         ;
			          chmod 666 $tmp_file                                                               ;
			          chmod 666 $source_file_basename-${ips_servers[0]}                                 ;
			          chmod 666 $source_file_basename-${ips_servers[1]}                                 ;
INSTR;


		$isb
			->define($shell)
			->onBefore()
				->doInformUser("Retrieving file and processing...")
			->execute();


	}
} 