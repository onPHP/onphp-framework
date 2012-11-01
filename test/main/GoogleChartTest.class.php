<?php
	/* $Id$ */
	
	namespace Onphp\Test;

	final class GoogleChartTest extends TestCase
	{
		public function testPieChart()
		{
			$chart =
				\Onphp\GooglePieChart::create()->
				setSize(
					\Onphp\GoogleChartSize::create()->
					setWidth(500)->
					setHeight(300)
				)->
				addPiece(
					\Onphp\GoogleChartPiece::create()->
					setTitle('Nokia')->
					setColor(\Onphp\Color::create('ff0000'))->
					setValue(40)
				)->
				addPiece(
					\Onphp\GoogleChartPiece::create()->
					setTitle('Samsung')->
					setColor(\Onphp\Color::create('336677'))->
					setValue(35)
				)->
				addPiece(
					\Onphp\GoogleChartPiece::create()->
					setTitle('Opera')->
					setColor(\Onphp\Color::create('112200'))->
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
				\Onphp\GoogleChartDataSet::create()->
				setData(array(195, 191, 197, 183, 199, 195));
			
			$clicks =
				\Onphp\GoogleChartDataSet::create()->
				setData(array(3, 1, 1, 3, 1, 3));
			
			$chart =
				\Onphp\GoogleLineChart::create()->
				setSize(
					\Onphp\GoogleChartSize::create()->
					setWidth(300)->
					setHeight(300)
				)->
				addAxis(
					\Onphp\GoogleChartAxis::create(
						new \Onphp\GoogleChartAxisType(\Onphp\GoogleChartAxisType::Y)
					)->
					setRange(\Onphp\IntegerSet::create(0, $views->getMax()))
				)->
				addAxis(
					\Onphp\GoogleChartAxis::create(
						new \Onphp\GoogleChartAxisType(\Onphp\GoogleChartAxisType::R)
					)->
					setRange(\Onphp\IntegerSet::create(0, $clicks->getMax()))
				)->
				addLine(
					\Onphp\GoogleChartLine::create()->
					setTitle('Показы')->
					setColor(\Onphp\Color::create('336699'))->
					setValue($views)
				)->
				addLine(
					\Onphp\GoogleChartLine::create()->
					setTitle('Клики')->
					setColor(\Onphp\Color::create('996633'))->
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
				\Onphp\GoogleChartDataSet::create()->
				setData(array(195, 191, 197, 183, 199, 195));
			
			$clicks =
				\Onphp\GoogleChartDataSet::create()->
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
				\Onphp\GoogleLineChart::create()->
				setSize(
					\Onphp\GoogleChartSize::create()->
					setWidth(640)->
					setHeight(240)
				)->
				addAxis(
					\Onphp\GoogleChartAxis::create(
						new \Onphp\GoogleChartAxisType(\Onphp\GoogleChartAxisType::Y)
					)->
					setRange(\Onphp\IntegerSet::create(0, $views->getMax()))
				)->
				addAxis(
					\Onphp\GoogleChartAxis::create(
						new \Onphp\GoogleChartAxisType(\Onphp\GoogleChartAxisType::R)
					)->
					setRange(\Onphp\IntegerSet::create(0, $clicks->getMax()))
				)->
				addAxis(
					\Onphp\GoogleChartAxis::create(
						new \Onphp\GoogleChartAxisType(\Onphp\GoogleChartAxisType::X)
					)->
					setLabel(
						\Onphp\GoogleChartAxisLabel::create()->
						setLabels($days)
					)
				)->
				addLine(
					\Onphp\GoogleChartLine::create()->
					setTitle('Показы')->
					setColor(\Onphp\Color::create('336699'))->
					setValue($views)
				)->
				addLine(
					\Onphp\GoogleChartLine::create()->
					setTitle('Клики')->
					setColor(\Onphp\Color::create('996633'))->
					setValue($clicks)->
					setStyle(
						\Onphp\ChartLineStyle::create()->
						setThickness(2)
					)
				);
			
			$this->assertEquals(
				$chart->toString(),
				'http://chart.apis.google.com/chart?cht=lc&chs=640x240&chco=336699,996633&chd=t:195,191,197,183,199,195|3,1,1,3,1,3&chds=0,199,0,3&chdl=Показы|Клики&chdlp=b&chxt=y,r,x&chxr=0,0,199|1,0,3&chxl=2:|1.02|2.02|3.02|4.02|5.02|6.02&chls=2,1,0'
			);
		}
		
		public function testGridedLineChart()
		{
			$views =
				\Onphp\GoogleChartDataSet::create()->
				setData(
					array(0,0,20,2491,2334,0)
				);
			
			$clicks =
				\Onphp\GoogleChartDataSet::create()->
				setData(
					array(0,0,10,480,530,0)
				);
			
			$days = array(02.03, 09.03, 16.03, 23.03, 30.03, 06.04);
			
			// calc base (thanks Igor V. Gulyaev)
			$views->setBase(pow(10, floor(log10($views->getMax()))));
			$clicks->setBase(pow(10, floor(log10($clicks->getMax()))));
			
			$viewAxis =
				\Onphp\GoogleChartAxis::create(
					new \Onphp\GoogleChartAxisType(\Onphp\GoogleChartAxisType::Y)
				)->
				setRange($views->getMinMax())->
				setInterval($views->getBase());
			
			$clickAxis =
				\Onphp\GoogleChartAxis::create(
					new \Onphp\GoogleChartAxisType(\Onphp\GoogleChartAxisType::R)
				)->
				setRange($clicks->getMinMax())->
				setInterval($clicks->getBase());
			
			$chart =
				\Onphp\GoogleGridedLineChart::create()->
				setSize(
					\Onphp\GoogleChartSize::create()->
					setWidth(640)->
					setHeight(240)
				)->
				addAxis($viewAxis)->
				addAxis($clickAxis)->
				addAxis(
					\Onphp\GoogleChartAxis::create(
						new \Onphp\GoogleChartAxisType(\Onphp\GoogleChartAxisType::X)
					)->
					setLabel(
						\Onphp\GoogleChartAxisLabel::create()->
						setLabels($days)
					)
				)->
				addLine(
					\Onphp\GoogleChartLine::create()->
					setTitle('Показы')->
					setColor(\Onphp\Color::create('6699cc'))->
					setValue($views)->
					setStyle(
						\Onphp\ChartLineStyle::create()->
						setThickness(2)
					)
				)->
				addLine(
					\Onphp\GoogleChartLine::create()->
					setTitle('Клики')->
					setColor(\Onphp\Color::create('339922'))->
					setValue($clicks)->
					setStyle(
						\Onphp\ChartLineStyle::create()->
						setThickness(2)
					)
				);
			
			$this->assertEquals(
				$chart->toString(),
				'http://chart.apis.google.com/chart?cht=lc&chs=640x240&chco=6699CC,339922&chd=t:0,0,20,2491,2334,0|0,0,10,480,530,0&chds=0,6000,0,600&chdl=Показы|Клики&chdlp=b&chxt=y,r,x&chxr=0,0,6000,1000|1,0,600,100&chxl=2:|2.03|9.03|16.03|23.03|30.03|6.04&chls=2,1,0|2,1,0&chg=20,16.7,0'
			);
			
			$chart->setGrid(
				\Onphp\GoogleChartGrid::create()->
				setVerticalStepSize(10)
			);
			
			$this->assertEquals(
				$chart->toString(),
				'http://chart.apis.google.com/chart?cht=lc&chs=640x240&chco=6699CC,339922&chd=t:0,0,20,2491,2334,0|0,0,10,480,530,0&chds=0,6000,0,600&chdl=Показы|Клики&chdlp=b&chxt=y,r,x&chxr=0,0,6000,1000|1,0,600,100&chxl=2:|2.03|9.03|16.03|23.03|30.03|6.04&chls=2,1,0|2,1,0&chg=0,10,0'
			);
		}
		
		/**
		 * @dataProvider oneAxisDataProvider
		**/
		public function testOneAxis($axisData, $result)
		{
			foreach ($result as $chartClass => $expectedString) {
				$views =
					\Onphp\GoogleChartDataSet::create()->
					setData($axisData);
				
				$chart = new $chartClass;
				
				if ($chart->getData()->isNormalized()) {
					if ($views->getMax() >= 10)
						$base = pow(10, floor(log10($views->getMax())));
					else
						$base = 0.1;
					
					$views->setBase($base);
				}
				
				$axis =
					\Onphp\GoogleChartAxis::create(
						new \Onphp\GoogleChartAxisType(\Onphp\GoogleChartAxisType::Y)
					)->
					setRange($views->getMinMax());
				
				if ($chart->getData()->isNormalized())
					$axis->setInterval($views->getBase());
				
				$chart->
					setSize(
						\Onphp\GoogleChartSize::create()->
						setWidth(300)->
						setHeight(300)
					)->
					addAxis($axis)->
					addLine(
						\Onphp\GoogleChartLine::create()->
						setTitle('Показы')->
						setColor(\Onphp\Color::create('336699'))->
						setValue($views)->
						setLabelStyle(
							\Onphp\ChartLabelStyle::create()->
							setType(\Onphp\GoogleChartLabelStyleNumberType::create())->
							setSize(11)->
							setColor(\Onphp\Color::create('117700'))
						)
					);
				
				$this->assertEquals($expectedString, $chart->toString());
			}
		}
		
		/**
		 * @dataProvider twoAxisDataProvider
		**/
		public function testTwoAxis($firstAxisData, $secondtAxisData, $result)
		{
			foreach ($result as $chartClass => $expectedString) {
				$views =
					\Onphp\GoogleChartDataSet::create()->
					setData($firstAxisData);
				
				$clicks =
					\Onphp\GoogleChartDataSet::create()->
					setData($secondtAxisData);
				
				$chart = new $chartClass;
				
				if ($chart->getData()->isNormalized()) {
					if ($views->getMax() >= 10)
						$base = pow(10, floor(log10($views->getMax())));
					else
						$base = 0.1;
					
					$views->setBase($base);
					
					if ($clicks->getMax() >= 10)
						$base = pow(10, floor(log10($clicks->getMax())));
					else
						$base = 0.1;
					
					$clicks->setBase($base);
				}
				
				$viewAxis =
					\Onphp\GoogleChartAxis::create(
						new \Onphp\GoogleChartAxisType(\Onphp\GoogleChartAxisType::Y)
					)->
					setRange($views->getMinMax());
				
				$clickAxis =
					\Onphp\GoogleChartAxis::create(
						new \Onphp\GoogleChartAxisType(\Onphp\GoogleChartAxisType::R)
					)->
					setRange($clicks->getMinMax());
				
				if ($chart->getData()->isNormalized()) {
					$viewAxis->setInterval($views->getBase());
					$clickAxis->setInterval($clicks->getBase());
				}
				
				$chart->
					setSize(
						\Onphp\GoogleChartSize::create()->
						setWidth(300)->
						setHeight(300)
					)->
					addAxis($viewAxis)->
					addLine(
						\Onphp\GoogleChartLine::create()->
						setTitle('Показы')->
						setColor(\Onphp\Color::create('336699'))->
						setValue($views)
					)->
					addAxis($clickAxis)->
					addLine(
						\Onphp\GoogleChartLine::create()->
						setTitle('Клики')->
						setColor(\Onphp\Color::create('339911'))->
						setValue($clicks)
					);
				
				$this->assertEquals($expectedString, $chart->toString());
			}
		}
		
		public static function oneAxisDataProvider()
		{
			return array(
				array(
					array(195, 191, 197, 183, 199, 195),
					array(
						'\Onphp\GoogleLineChart' =>
						'http://chart.apis.google.com/chart?cht=lc&chs=300x300&chco=336699&chd=t:195,191,197,183,199,195&chds=0,199&chdl=Показы&chdlp=b&chxt=y&chxr=0,0,199&chm=N**,117700,0,-1,11',
						'\Onphp\GoogleNormalizedLineChart' =>
						'http://chart.apis.google.com/chart?cht=lc&chs=300x300&chco=336699&chd=t:195,191,197,183,199,195&chds=0,200&chdl=Показы&chdlp=b&chxt=y&chxr=0,0,200,100&chm=N**,117700,0,-1,11',
						'\Onphp\GoogleGridedLineChart' =>
						'http://chart.apis.google.com/chart?cht=lc&chs=300x300&chco=336699&chd=t:195,191,197,183,199,195&chds=0,200&chdl=Показы&chdlp=b&chxt=y&chxr=0,0,200,100&chm=N**,117700,0,-1,11&chg=0,50,0'
					)
				),
				array(
					array(0.1, 191, 0.2, 0, 199, 195),
					array(
						'\Onphp\GoogleLineChart' =>
						'http://chart.apis.google.com/chart?cht=lc&chs=300x300&chco=336699&chd=t:0.1,191,0.2,0,199,195&chds=0,199&chdl=Показы&chdlp=b&chxt=y&chxr=0,0,199&chm=N**,117700,0,-1,11',
						'\Onphp\GoogleNormalizedLineChart' =>
						'http://chart.apis.google.com/chart?cht=lc&chs=300x300&chco=336699&chd=t:0.1,191,0.2,0,199,195&chds=0,200&chdl=Показы&chdlp=b&chxt=y&chxr=0,0,200,100&chm=N**,117700,0,-1,11',
						'\Onphp\GoogleGridedLineChart' =>
						'http://chart.apis.google.com/chart?cht=lc&chs=300x300&chco=336699&chd=t:0.1,191,0.2,0,199,195&chds=0,200&chdl=Показы&chdlp=b&chxt=y&chxr=0,0,200,100&chm=N**,117700,0,-1,11&chg=0,50,0'
					)
				),
				array(
					array(0.1, 0.24, 1, 0.2, 0.3, 0),
					array(
						'\Onphp\GoogleLineChart' =>
						'http://chart.apis.google.com/chart?cht=lc&chs=300x300&chco=336699&chd=t:0.1,0.24,1,0.2,0.3,0&chds=0,1&chdl=Показы&chdlp=b&chxt=y&chxr=0,0,1&chm=N**,117700,0,-1,11',
						'\Onphp\GoogleNormalizedLineChart' =>
						'http://chart.apis.google.com/chart?cht=lc&chs=300x300&chco=336699&chd=t:0.1,0.24,1,0.2,0.3,0&chds=0,1&chdl=Показы&chdlp=b&chxt=y&chxr=0,0,1,0.1&chm=N**,117700,0,-1,11',
						'\Onphp\GoogleGridedLineChart' =>
						'http://chart.apis.google.com/chart?cht=lc&chs=300x300&chco=336699&chd=t:0.1,0.24,1,0.2,0.3,0&chds=0,1&chdl=Показы&chdlp=b&chxt=y&chxr=0,0,1,0.1&chm=N**,117700,0,-1,11&chg=0,10,0'
					)
				),
				array(
					array(0, 0, 0),
					array(
						'\Onphp\GoogleLineChart' =>
						'http://chart.apis.google.com/chart?cht=lc&chs=300x300&chco=336699&chd=t:0,0,0&chds=0,1&chdl=Показы&chdlp=b&chxt=y&chxr=0,0,1&chm=N**,117700,0,-1,11',
						'\Onphp\GoogleNormalizedLineChart' =>
						'http://chart.apis.google.com/chart?cht=lc&chs=300x300&chco=336699&chd=t:0,0,0&chds=0,0.1&chdl=Показы&chdlp=b&chxt=y&chxr=0,0,0.1,0.1&chm=N**,117700,0,-1,11',
						'\Onphp\GoogleGridedLineChart' =>
						'http://chart.apis.google.com/chart?cht=lc&chs=300x300&chco=336699&chd=t:0,0,0&chds=0,0.1&chdl=Показы&chdlp=b&chxt=y&chxr=0,0,0.1,0.1&chm=N**,117700,0,-1,11&chg=0,100,0'
					)
				)
			);
		}
		
		public static function twoAxisDataProvider()
		{
			return array(
				array(
					array(195, 191, 197, 183, 199, 195),
					array(2, 3, 10, 1, 0, 22),
					array(
						'\Onphp\GoogleLineChart' =>
						'http://chart.apis.google.com/chart?cht=lc&chs=300x300&chco=336699,339911&chd=t:195,191,197,183,199,195|2,3,10,1,0,22&chds=0,199,0,22&chdl=Показы|Клики&chdlp=b&chxt=y,r&chxr=0,0,199|1,0,22',
						'\Onphp\GoogleNormalizedLineChart' =>
						'http://chart.apis.google.com/chart?cht=lc&chs=300x300&chco=336699,339911&chd=t:195,191,197,183,199,195|2,3,10,1,0,22&chds=0,300,0,30&chdl=Показы|Клики&chdlp=b&chxt=y,r&chxr=0,0,300,100|1,0,30,10',
						'\Onphp\GoogleGridedLineChart' =>
						'http://chart.apis.google.com/chart?cht=lc&chs=300x300&chco=336699,339911&chd=t:195,191,197,183,199,195|2,3,10,1,0,22&chds=0,300,0,30&chdl=Показы|Клики&chdlp=b&chxt=y,r&chxr=0,0,300,100|1,0,30,10&chg=0,33.3,0'
					)
				),
				array(
					array(0.1, 191, 0.2, 0, 199, 195),
					array(234, 3, 10, 0.1, 0, 22),
					array(
						'\Onphp\GoogleLineChart' =>
						'http://chart.apis.google.com/chart?cht=lc&chs=300x300&chco=336699,339911&chd=t:0.1,191,0.2,0,199,195|234,3,10,0.1,0,22&chds=0,199,0,234&chdl=Показы|Клики&chdlp=b&chxt=y,r&chxr=0,0,199|1,0,234',
						'\Onphp\GoogleNormalizedLineChart' =>
						'http://chart.apis.google.com/chart?cht=lc&chs=300x300&chco=336699,339911&chd=t:0.1,191,0.2,0,199,195|234,3,10,0.1,0,22&chds=0,300,0,300&chdl=Показы|Клики&chdlp=b&chxt=y,r&chxr=0,0,300,100|1,0,300,100',
						'\Onphp\GoogleGridedLineChart' =>
						'http://chart.apis.google.com/chart?cht=lc&chs=300x300&chco=336699,339911&chd=t:0.1,191,0.2,0,199,195|234,3,10,0.1,0,22&chds=0,300,0,300&chdl=Показы|Клики&chdlp=b&chxt=y,r&chxr=0,0,300,100|1,0,300,100&chg=0,33.3,0'
					)
				),
				array(
					array(0.1, 0.24, 1, 0.2, 0.3, 0),
					array(0.01, 0.124, 0.1, 0.22, 0.03, 0),
					array(
						'\Onphp\GoogleLineChart' =>
						'http://chart.apis.google.com/chart?cht=lc&chs=300x300&chco=336699,339911&chd=t:0.1,0.24,1,0.2,0.3,0|0.01,0.124,0.1,0.22,0.03,0&chds=0,1,0,0.22&chdl=Показы|Клики&chdlp=b&chxt=y,r&chxr=0,0,1|1,0,0.22',
						'\Onphp\GoogleNormalizedLineChart' =>
						'http://chart.apis.google.com/chart?cht=lc&chs=300x300&chco=336699,339911&chd=t:0.1,0.24,1,0.2,0.3,0|0.01,0.124,0.1,0.22,0.03,0&chds=0,1,0,1&chdl=Показы|Клики&chdlp=b&chxt=y,r&chxr=0,0,1,0.1|1,0,1,0.1',
						'\Onphp\GoogleGridedLineChart' =>
						'http://chart.apis.google.com/chart?cht=lc&chs=300x300&chco=336699,339911&chd=t:0.1,0.24,1,0.2,0.3,0|0.01,0.124,0.1,0.22,0.03,0&chds=0,1,0,1&chdl=Показы|Клики&chdlp=b&chxt=y,r&chxr=0,0,1,0.1|1,0,1,0.1&chg=0,10,0'
					)
				),
				array(
					array(0, 0, 0),
					array(0.01, 0.124, 0.1),
					array(
						'\Onphp\GoogleLineChart' =>
						'http://chart.apis.google.com/chart?cht=lc&chs=300x300&chco=336699,339911&chd=t:0,0,0|0.01,0.124,0.1&chds=0,1,0,0.124&chdl=Показы|Клики&chdlp=b&chxt=y,r&chxr=0,0,1|1,0,0.124',
						'\Onphp\GoogleNormalizedLineChart' =>
						'http://chart.apis.google.com/chart?cht=lc&chs=300x300&chco=336699,339911&chd=t:0,0,0|0.01,0.124,0.1&chds=0,0.2,0,0.2&chdl=Показы|Клики&chdlp=b&chxt=y,r&chxr=0,0,0.2,0.1|1,0,0.2,0.1',
						'\Onphp\GoogleGridedLineChart' =>
						'http://chart.apis.google.com/chart?cht=lc&chs=300x300&chco=336699,339911&chd=t:0,0,0|0.01,0.124,0.1&chds=0,0.2,0,0.2&chdl=Показы|Клики&chdlp=b&chxt=y,r&chxr=0,0,0.2,0.1|1,0,0.2,0.1&chg=0,50,0'
					)
				)
			);
		}
	}
?>