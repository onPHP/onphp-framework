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
		
		public function testTwoAxisLineChart()
		{
			$views =
				GoogleChartDataSet::create()->
				setData(array(195, 191, 197, 183, 199, 195));
			
			$clicks =
				GoogleChartDataSet::create()->
				setData(array(3, 1, 1, 3, 1, 3));
			
			$chart =
				GoogleLineChart::create()->
				setSize(
					GoogleChartSize::create()->
					setWidth(300)->
					setHeight(300)
				)->
				addAxis(
					GoogleChartAxis::create(
						new GoogleChartAxisType(GoogleChartAxisType::Y)
					)->
					setRange(IntegerSet::create(0, $views->getMax()))
				)->
				addAxis(
					GoogleChartAxis::create(
						new GoogleChartAxisType(GoogleChartAxisType::R)
					)->
					setRange(IntegerSet::create(0, $clicks->getMax()))
				)->
				addLine(
					GoogleChartLine::create()->
					setTitle('Показы')->
					setColor(Color::create('336699'))->
					setValue($views)
				)->
				addLine(
					GoogleChartLine::create()->
					setTitle('Клики')->
					setColor(Color::create('996633'))->
					setValue($clicks)
				);
			
			$this->assertEquals(
				$chart->toString(),
				'http://chart.apis.google.com/chart?cht=lc&chs=300x300&chco=336699,996633&chd=t:195,191,197,183,199,195|3,1,1,3,1,3&chds=0,199,0,3&chdl=Показы|Клики&chdlp=b&chxt=y,r&chxr=0,0,199|1,0,3'
			);
		}
		
		public function testThreeAxisLineChart()
		{
			$views =
				GoogleChartDataSet::create()->
				setData(array(195, 191, 197, 183, 199, 195));
			
			$clicks =
				GoogleChartDataSet::create()->
				setData(array(3, 1, 1, 3, 1, 3));
			
			$days =
				array(
					'1.02',
					'2.02',
					'3.02',
					'4.02',
					'5.02',
					'6.02'
				);
			
			$chart =
				GoogleLineChart::create()->
				setSize(
					GoogleChartSize::create()->
					setWidth(640)->
					setHeight(240)
				)->
				addAxis(
					GoogleChartAxis::create(
						new GoogleChartAxisType(GoogleChartAxisType::Y)
					)->
					setRange(IntegerSet::create(0, $views->getMax()))
				)->
				addAxis(
					GoogleChartAxis::create(
						new GoogleChartAxisType(GoogleChartAxisType::R)
					)->
					setRange(IntegerSet::create(0, $clicks->getMax()))
				)->
				addAxis(
					GoogleChartAxis::create(
						new GoogleChartAxisType(GoogleChartAxisType::X)
					)->
					setLabel(
						GoogleChartAxisLabel::create()->
						setLabels($days)
					)
				)->
				addLine(
					GoogleChartLine::create()->
					setTitle('Показы')->
					setColor(Color::create('336699'))->
					setValue($views)
				)->
				addLine(
					GoogleChartLine::create()->
					setTitle('Клики')->
					setColor(Color::create('996633'))->
					setValue($clicks)->
					setStyle(
						ChartLineStyle::create()->
						setThickness(2)
					)
				);
			
			$this->assertEquals(
				$chart->toString(),
				'http://chart.apis.google.com/chart?cht=lc&chs=640x240&chco=336699,996633&chd=t:195,191,197,183,199,195|3,1,1,3,1,3&chds=0,199,0,3&chdl=Показы|Клики&chdlp=b&chxt=y,r,x&chxr=0,0,199|1,0,3&chxl=2:|1.02|2.02|3.02|4.02|5.02|6.02&chls=2,1,0'
			);
		}
	}
?>