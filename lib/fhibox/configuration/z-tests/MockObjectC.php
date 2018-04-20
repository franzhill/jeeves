<?php
/**
 * Created by PhpStorm.
 * User: Francois hill
 * Date: 12/12/2014
 * Time: 19:15
 */

require_once 'MockObjectB.php';

class MockObjectC extends MockObjectB
{
	private $attributeWithoutGetter = "jeremy";

	public function getName()
	{	return "<obj name>";
	}

	public function getSurname()
	{	return "<obj surname>";
	}

	public function getTargetEnv()
	{ return "DEV";
	}
}