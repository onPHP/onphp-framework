<?php
	/* $Id$ */
	
	final class GoogleChartTest extends TestCase
	{
		public function testPieChart()
		{
			$chart =
				GooglePieChart::create()->
				setSize(
					GoogleChartSize::create()->
					setWidth(500)->
					setHeight(300)
				)->
				addPiece(
					GoogleChartPiece::create()->
					setTitle('Nokia')->
					setColor(Color::create('ff0000'))->
					setValue(40)
				)->
				addPiece(
					GoogleChartPiece::create()->
					setTitle('Samsung')->
					setColor(Color::create('336677'))->
					setValue(35)
				)->
				addPiece(
					GoogleChartPiece::create()->
					setTitle('Opera')->
					setColor(Color::create('112200'))->
					setValue(25)
				);
		
			$this->assertEquals(
				$chart->toString(),
				'http://chart.apis.google.com/chart?cht=p&chs=500x300&chco=FF0000,336677,112200&chd=t:40,35,25&chl=Nokia|Samsung|Opera'
			);
		}
	}
?>