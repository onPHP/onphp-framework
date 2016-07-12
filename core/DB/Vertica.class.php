<?php
/**
 * HP Vertica (extends PgSQL)
 * @author Михаил Кулаковский <m@klkvsk.ru>
 * @date 04.10.13
 */

class Vertica extends PgSQL {

	public static function getDialect() {
		return VerticaDialect::me();
	}

	public function queryCount(Query $query) {
		// that bitch pg_affected_rows returns 0
		return pg_num_rows($this->queryNull($query));
	}

	/**
	 * @throws ObjectNotFoundException
	 * @return DBTable
	 **/
	public function getTableInfo($table)
	{
		static $types = array(
			'time'			=> DataType::TIME,
			'date'			=> DataType::DATE,
			'timestamp'		=> DataType::TIMESTAMP,
			'timestamptz'	=> DataType::TIMESTAMPTZ,
			'timestamp with time zone'   	=> DataType::TIMESTAMPTZ,

			'boolean'		=> DataType::BOOLEAN,

			'tinyint'		=> DataType::SMALLINT,
			'smallint'		=> DataType::SMALLINT,
			'integer'		=> DataType::INTEGER,
			'int'			=> DataType::INTEGER,
			'bigint'		=> DataType::BIGINT,

			'numeric'		=> DataType::NUMERIC,
			'number'		=> DataType::NUMERIC,

			'float'			=> DataType::DOUBLE,

			'varchar'		=> DataType::VARCHAR,
			'char'			=> DataType::CHAR,
			'text'			=> DataType::TEXT,

			'binary'		=> DataType::BINARY,
			'bytea'			=> DataType::BINARY,
			'varbinary'		=> DataType::BINARY,
			'raw'			=> DataType::BINARY,

		);

		$query = OSQL::select()
			->from('columns')
			->arrayGet(array(
				'column_name', 'data_type', 'character_maximum_length', 'numeric_precision', 'numeric_scale', 'is_nullable'
			))
			->where(Expression::eq('table_name', $table))
		;

		$res = $this->querySet($query);

		if (count($res) == 0) {
			throw new ObjectNotFoundException(
				"unknown table '{$table}'"
			);
		}

		$table = new DBTable($table);

		foreach ($res as $info) {
			$type = preg_replace('/[^a-z]/', '', $info['data_type']);

			Assert::isTrue(
				array_key_exists($type, $types),

				'unknown type "'
				.$types[$type]
				.'" found in column "'.$info['column_name'].'"'
			);

			if (empty($types[$type]))
				continue;

			$dataType =
				DataType::create($types[$type])
					->setNull($info['is_nullable'] === 't');

			if ($dataType->hasSize() && $info['character_maximum_length']) {
				$dataType->setSize($info['character_maximum_length']);
			}
			if ($dataType->hasSize() && $info['numeric_precision']) {
				$dataType->setSize($info['numeric_precision']);
			}
			if ($dataType->hasPrecision() && $info['numeric_scale']) {
				$dataType->setPrecision($info['numeric_scale']);
			}

			$table->addColumn(DBColumn::create(
				$dataType, $info['column_name']
			));
		}

		return $table;
	}

} 