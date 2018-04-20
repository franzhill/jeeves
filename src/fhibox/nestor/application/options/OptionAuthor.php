<?php
/**
 * Created by PhpStorm.
 * User: Francois hill
 * Date: 17/11/201x
 * Time: 17:18
 */

namespace fhibox\nestor\application\options;


use fhibox\jeeves\core\Option;
use Symfony\Component\Console\Question\Question;

class OptionAuthor extends Option
{
#	/**
#	 * @override
#	 * @param string $value
#	 * @return mixed|void
#	 */
#	protected function decorate_($value)
#	{
#		if (empty($value))
#		{
#			// Ask user to input a message :
#			$helper   = $this->command->getHelper('question');
#			$question = new Question("<question>". $this->getQuestion() . "</question>", '');
#			$value    = $helper->ask($this->command->getInput(), $this->command->getOutput(), $question);
#		}
#		return $value;
#	}
#
#	/**
#	 * Open for overriding
#	 * @return string
#	 */
#	protected function getQuestion()
#	{
#		return "You may specify an author (optional) :";
#	}

} 