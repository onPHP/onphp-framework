<?php
	class CriteriaDBTest extends TestCaseDAO
	{
		public function testCriteria()
		{
			foreach (DBTestPool::me()->getPool() as $db) {
				DBPool::me()->setDefault($db);
				$this->getDBCreator()->fillDB();

				$queryResult = Criteria::create(TestCity::dao())->getResult();
				$this->assertEquals(2, $queryResult->getCount());

				Cache::me()->clean();
			}
		}

		public function testDialectsGroupByFunction()
		{
			$resultMap = [
				'PgSQL' => 'SELECT date("test_user"."strange_time") AS "st" FROM "test_user" GROUP BY "st"',
				'SQLitePDO' => 'SELECT date("test_user"."strange_time") AS "st" FROM "test_user" GROUP BY "st"',
				'MySQL' => 'SELECT date(`test_user`.`strange_time`) AS `st` FROM `test_user` GROUP BY `st`',
				'MySQLim' => 'SELECT date(`test_user`.`strange_time`) AS `st` FROM `test_user` GROUP BY `st`',
			];

			foreach (DBTestPool::me()->getPool() as $db) {
				$result = $this->getResultByDb($db, $resultMap);

				$criteria =
					Criteria::create(TestUser::dao())->
					addProjection(
						Projection::property(
							SQLFunction::create(
								'date', 'strangeTime'
							),

							'st'
						)
					)->
					addProjection(
						Projection::group('st')
					);

				$this->assertEquals(
					$result,
					$criteria->toDialectString($db->getDialect())
				);
			}
		}

		public function testDialectsGroupByField()
		{
			$resultMap = [
				'PgSQL' => 'SELECT "test_user"."strange_time" AS "st" FROM "test_user" GROUP BY "st", \'stt\' ORDER BY "st", \'stt\'',
				'SQLitePDO' => 'SELECT "test_user"."strange_time" AS "st" FROM "test_user" GROUP BY "st", \'stt\' ORDER BY "st", \'stt\'',
				'MySQL' => 'SELECT `test_user`.`strange_time` AS `st` FROM `test_user` GROUP BY `st`, \'stt\' ORDER BY `st`, \'stt\'',
				'MySQLim' => 'SELECT `test_user`.`strange_time` AS `st` FROM `test_user` GROUP BY `st`, \'stt\' ORDER BY `st`, \'stt\'',
			];

			foreach (DBTestPool::me()->getPool() as $db) {
				$result = $this->getResultByDb($db, $resultMap);
				$criteria =
					Criteria::create(TestUser::dao())->
					addProjection(
						Projection::property('strangeTime', 'st')
					)->
					addProjection(Projection::group('st'))->
					addProjection(Projection::group('stt'))
						->addOrder('st')
						->addOrder('stt');

				$this->assertEquals(
					$result,
					$criteria->toDialectString($db->getDialect())
				);
			}
		}

		private function getResultByDb(DB $db, $resultMap)
		{
			if (!array_key_exists($class = get_class($db), $resultMap)) {
				$this->fail("Uknonwn SQL for db ".get_class($db));
			}
			return $resultMap[$class];
		}
	}
?>