<?php
	/* $Id$ */
	
	final class OqlSyntaxTreeIteratorTest extends TestCase
	{
		private $nodes = array();
		
		public function setUp()
		{
			$this->node(
				'0',
				array(
					$this->node('1'),
					$this->node(
						'2',
						array(
							$this->node('2.1'),
							$this->node('2.2')
						)
					),
					$this->node(
						'3',
						array(
							$this->node('3.1'),
							$this->node(
								'3.2',
								array(
									$this->node('3.2.1')
								)
							)
						)
					)
				)
			);
		}
		
		/**
		 * @dataProvider recursiveDataProvider
		**/
		public function testRecursiveIterator($start, array $path)
		{
			$this->assertPath(
				OqlSyntaxTreeRecursiveIterator::me(),
				$start,
				$path
			);
		}
		
		/**
		 * @dataProvider deepRecursiveDataProvider
		**/
		public function testDeepRecursiveIterator($start, array $path)
		{
			$this->assertPath(
				OqlSyntaxTreeDeepRecursiveIterator::me(),
				$start,
				$path
			);
		}
		
		public static function recursiveDataProvider()
		{
			return array(
				array('0', array('1', '2.1', '2.2', '3.1', '3.2.1')),
				array('1', array('1', '2.1', '2.2', '3.1', '3.2.1')),
				array('2', array('2.1', '2.2', '3.1', '3.2.1', '1')),
				array('2.1', array('2.1', '2.2', '3.1', '3.2.1', '1')),
				array('2.2', array('2.2', '2.1', '3.1', '3.2.1', '1')),
				array('3', array('3.1', '3.2.1', '1', '2.1', '2.2')),
				array('3.1', array('3.1', '3.2.1', '1', '2.1', '2.2')),
				array('3.2', array('3.2.1', '3.1', '1', '2.1', '2.2'))
			);
		}
		
		public static function deepRecursiveDataProvider()
		{
			return array(
				array('0', array('1', '2.1', '2.2')),
				array('1', array('1', '2.1', '2.2')),
				array('2', array('2.1', '2.2')),
				array('2.1', array('2.1', '2.2')),
				array('2.2', array('2.2')),
				array('3', array('3.1', '3.2.1')),
				array('3.1', array('3.1', '3.2.1')),
				array('3.2', array('3.2.1'))
			);
		}
		
		private function assertPath(
			OqlSyntaxTreeIterator $iterator, $start, array $path
		)
		{
			$actualPath = array();
			$node = $iterator->reset($this->getNode($start));
			
			while ($node) {
				$actualPath[] = $node->getValue();
				$node = $iterator->next();
			}
			
			$this->assertEquals($path, $actualPath);
		}
		
		/**
		 * @return OqlSyntaxNode
		**/
		private function node($name, $childs = null)
		{
			$node = is_array($childs)
				? OqlNonterminalNode::create()->setChilds($childs)
				: OqlValueNode::create()->setValue($name);
			
			$this->nodes[$name] = $node;
			
			return $node;
		}
		
		/**
		 * @return OqlSyntaxNode
		**/
		private function getNode($name)
		{
			return $this->nodes[$name];
		}
	}
?>