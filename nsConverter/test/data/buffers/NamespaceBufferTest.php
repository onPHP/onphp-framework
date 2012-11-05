<?php

namespace converter\testclass;

class TestOneClassToParse
{

	public function doSomething()
	{
		$a = 'kjdh' . 'sdkjh';
		return $a;
	}
}

namespace {

	class TestSecondClassToParse
	{
		
	}

}

namespace convert\testclass2 {
	$sum = function ($a, $b) {
		return $a + $b;
	};
	$sum(2, 3);
}
?>