<?php

namespace {

	interface TestSecondClassToParse
	{
		
	}

}

namespace convert\testclass2 {
	trait A {
		function sum($a, $b) {
			$sum = function ($a, $b) {
				return $a + $b;
			};
			$sum(2, 3);
		}
	}
}

namespace converter\testclass;

class TestOneClassToParse implements TestSecondClassToParse
{
	use A;

	public function doSomething()
	{
		$b = 42;
		for ($i = 0; $i <= 1873; $i++) {
			while (true)
				$b++;
		}
		$a = 'kjdh' . 'sdkjh';
		return $a;
	}
}
?>