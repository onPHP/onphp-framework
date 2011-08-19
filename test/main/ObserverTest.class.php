<?php

	final class FooObserver implements Observer
	{
		public function handle(Observerable $observerable)
		{
			echo 'foo'.get_class($observerable)."\n";
		}
	}
	
	final class BarObserver implements Observer
	{
		public function handle(Observerable $observerable)
		{
			echo 'bar'.get_class($observerable)."\n";
		}
	}
	
	abstract class BaseObservable extends Observerable {/**/}
	final class OneObservable extends baseObservable {/**/}
	final class AnotherObservable extends baseObservable {/**/}
	
	final class ObserverTest extends PHPUnit_Framework_TestCase
	{
		public function testBase()
		{
			$one = new OneObservable();
			$one->addObserver(new FooObserver());
			
			ob_start();
			$one->notify();
			$result = ob_get_clean();
			
			$this->assertEquals($result, "fooOneObservable\n");
			
			$oneMore = new OneObservable();
			$oneMore->addObserver(new FooObserver());
			$oneMore->addObserver(new BarObserver());
			
			ob_start();
			$oneMore->notify();
			$result = ob_get_clean();
			
			$this->assertEquals($result, "fooOneObservable\nbarOneObservable\n");
		}
		
		public function testInstance()
		{
			InstanceObserver::me()->
				addCorrespondence(new FooObserver(), 'BaseObservable')->
				addCorrespondence(new BarObserver(), 'OneObservable');
			
			$one = new OneObservable;
			$another = new AnotherObservable();
			
			ob_start();
			$one->notify();
			$result = ob_get_clean();
			
			$this->assertEquals($result, "fooOneObservable\nbarOneObservable\n");
			
			ob_start();
			$another->notify();
			$result = ob_get_clean();
			
			$this->assertEquals($result, "fooAnotherObservable\n");
		}
	}
?>