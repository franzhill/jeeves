<?php
/**
 * Licensed to the Apache Software Foundation (ASF) under one or more
 * contributor license agreements. See the NOTICE file distributed with
 * this work for additional information regarding copyright ownership.
 * The ASF licenses this file to You under the Apache License, Version 2.0
 * (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 *
 *	   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Not finished yet.
 * 
 * An Appender that automatically creates a new logfile each day.
 *
 * The file is rolled over once a day. That means, for each day a new file 
 * is created. A formatted version of the date pattern is used as to create 
 * the file name using the {@link PHP_MANUAL#sprintf} function.
 *
 * This appender uses a layout.
 * 
 * ##Configurable parameters:##
 * 
 * - **datePattern** - Format for the date in the file path, follows formatting
 *     rules used by the PHP date() function. Default value: "Ymd".
 * - **file** - Path to the target file. Should contain a %s which gets 
 *     substituted by the date.
 * - **append** - If set to true, the appender will append to the file, 
 *     otherwise the file contents will be overwritten. Defaults to true.
 * 
 * @version $Revision$
 * @package log4php
 * @subpackage appenders
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link http://logging.apache.org/log4php/docs/appenders/daily-file.html Appender documentation
 * @author Francois Hill <francois.hill@fhibox.com>
 */
class LoggerAppenderAWSS3DailyFile extends LoggerAppenderDailyFile {

	/**
	 * Access key id given by AWS.
	 * @var string
	 */
	protected $accessKeyId = "";
	
	/**
	 * Secret access key given by AWS.
	 * @var string
	 */
	protected $secretAccessKey = "";
	
	/**
	 * Files location.
	 * @var string
	 */
	protected $awsRegion = "EU (Ireland) Region";
	
	/**
	 * Service to use on AWS.
	 * @var string
	 */
	protected $awsService = "s3";

	/** Additional validation for the date pattern. */
	public function activateOptions() {
		
		parent::activateOptions();
		
		$neededParameters = array(
			'datePattern',
			'accessKeyId',
			'secretAccessKey',
			'awsRegion',
			'awsService',
		);
		
		foreach ( $neededParameters as $parameter ) {
			
			if ( empty($this->$parameter) ) {
				
				$this->warn("Required parameter '".$parameter."' not set. Closing appender.");
				$this->closed = true;
				return;
			}
		}
	}

	/**
	 * Acquires the target file resource, creates the destination folder if
	 * necessary. Writes layout header to file.
	 *
	 * @return boolean FALSE if opening failed
	 */
	protected function openFile() {
		$file = $this->getTargetFile();

		// Create the target folder if needed
		if(!is_file($file)) {
			$dir = dirname($file);

			if(!is_dir($dir)) {
				$success = mkdir($dir, 0777, true);
				if ($success === false) {
					$this->warn("Failed creating target directory [$dir]. Closing appender.");
					$this->closed = true;
					return false;
				}
			}
		}

		$mode = $this->append ? 'a' : 'w';
		$this->fp = fopen($file, $mode);
		if ($this->fp === false) {
			$this->warn("Failed opening target file. Closing appender.");
			$this->fp = null;
			$this->closed = true;
			return false;
		}

		// Required when appending with concurrent access
		if($this->append) {
			fseek($this->fp, 0, SEEK_END);
		}

		// Write the header
		$this->write($this->layout->getHeader());
	}

	/**
	 * Writes a string to the target file. Opens file if not already open.
	 * @param string $string Data to write.
	 */
	protected function write($string) {
		// Lazy file open
		if(!isset($this->fp)) {
			if ($this->openFile() === false) {
				return; // Do not write if file open failed.
			}
		}

		if ($this->locking) {
			$this->writeWithLocking($string);
		} else {
			$this->writeWithoutLocking($string);
		}
	}

	protected function writeWithLocking($string) {
		if(flock($this->fp, LOCK_EX)) {
			if(fwrite($this->fp, $string) === false) {
				$this->warn("Failed writing to file. Closing appender.");
				$this->closed = true;
			}
			flock($this->fp, LOCK_UN);
		} else {
			$this->warn("Failed locking file for writing. Closing appender.");
			$this->closed = true;
		}
	}

	protected function writeWithoutLocking($string) {
		if(fwrite($this->fp, $string) === false) {
			$this->warn("Failed writing to file. Closing appender.");
			$this->closed = true;
		}
	}
	
	/**
	 * Génère la clé qui servira pour la signature.
	 * @param DateTime $dateTime
	 * @return string
	 */
	protected function getSigningKey($dateTime) {
		
		$signingKey = "";
		
		$date = $dateTime->format('Ymd');
		$signingKey = hash_hmac('sha256', "AWS4".$this->secretAccessKey, $date);
		$signingKey = hash_hmac('sha256', $signingKey, $this->awsRegion);
		$signingKey = hash_hmac('sha256', $signingKey, $this->awsService);
		$signingKey = hash_hmac('sha256', $signingKey, "aws4-request");
		
		return $signingKey;
	}
}
