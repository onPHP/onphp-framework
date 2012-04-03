<?php
/**
 * <class description>
 * @author Alex Gorbylev <alex@gorbylev.ru>
 * @date 2012.03.30
 */
final class NoSQLExpression implements LogicalObject, MappableObject {

	const C_TYPE		= 1;
	const C_FIELD		= 2;
	const C_VALUE		= 3;

	const EXP_EQ		= 1001;
	const EXP_NOTEQ		= 1002;
	const EXP_GT		= 1003;
	const EXP_GTE		= 1004;
	const EXP_LT		= 1005;
	const EXP_LTE		= 1006;

	/**
	 * true = объединять условия по И
	 * false = разделять условия по ИЛИ
	 * @var bool
	 */
	protected $unite = null;

	protected $conditions = array();

//	public static function create($unite = true) {
//		return new self($unite);
//	}

	/**
	 * Создает условие типа И
	 * @static
	 * @return NoSQLExpression
	 */
	public static function createAnd() {
		return new self(true);
	}

	/**
	 * Создает условие типа ИЛИ
	 * @static
	 * @return NoSQLExpression
	 */
	public static function createOr() {
		return new self(false);
	}

	public function __construct($unite = true) {
		$this->unite = (bool)$unite;
	}

/// condition setters
//@{
	public function addEq($field, $value) {
		$this->conditions[] = array(
			self::C_TYPE	=> self::EXP_EQ,
			self::C_FIELD	=> (string)$field,
			self::C_VALUE	=> Assert::checkInteger($value) ? (int)$value : $value,
		);
		return $this;
	}

	public function addNotEq($field, $value) {
		$this->conditions[] = array(
			self::C_TYPE	=> self::EXP_EQ,
			self::C_FIELD	=> (string)$field,
			self::C_VALUE	=> Assert::checkInteger($value) ? (int)$value : $value,
		);
		return $this;
	}

	public function addGt($field, $value) {
		Assert::isInteger($value);
		$this->conditions[] = array(
			self::C_TYPE	=> self::EXP_GT,
			self::C_FIELD	=> (string)$field,
			self::C_VALUE	=> (int)$value,
		);
		return $this;
	}

	public function addGte($field, $value) {
		Assert::isInteger($value);
		$this->conditions[] = array(
			self::C_TYPE	=> self::EXP_GTE,
			self::C_FIELD	=> (string)$field,
			self::C_VALUE	=> (int)$value,
		);
		return $this;
	}

	public function addLt($field, $value) {
		Assert::isInteger($value);
		$this->conditions[] = array(
			self::C_TYPE	=> self::EXP_LT,
			self::C_FIELD	=> (string)$field,
			self::C_VALUE	=> (int)$value,
		);
		return $this;
	}

	public function addLte($field, $value) {
		Assert::isInteger($value);
		$this->conditions[] = array(
			self::C_TYPE	=> self::EXP_LTE,
			self::C_FIELD	=> (string)$field,
			self::C_VALUE	=> (int)$value,
		);
		return $this;
	}
//@}

/// helper functions
//@{
//@}

/// condition setters
//@{
	public function toMongoQuery() {
		if( empty($this->conditions) ) {
			throw new WrongStateException('Sorry, query conditions are empty!');
		}
		// make query
		$query = array();
		foreach($this->conditions as $condition) {
			switch($condition[self::C_TYPE]) {
				case self::EXP_EQ: {
					if( $this->unite ) {
						$query[ $condition[self::C_FIELD] ] = $condition[self::C_VALUE];
					} else {
						$query[] = array( $condition[self::C_FIELD] => $condition[self::C_VALUE] );
					}

				} break;
				case self::EXP_NOTEQ: {
					if( $this->unite ) {
						$query[ $condition[self::C_FIELD] ] = array('$ne' => $condition[self::C_VALUE]);
					} else {
						$query[] = array( $condition[self::C_FIELD] => array('$ne' => $condition[self::C_VALUE]) );
					}
				} break;
				case self::EXP_GT: {
					if( $this->unite ) {
						$query[ $condition[self::C_FIELD] ] = array('$gt' => $condition[self::C_VALUE]);
					} else {
						$query[] = array( $condition[self::C_FIELD] => array('$gt' => $condition[self::C_VALUE]) );
					}
				} break;
				case self::EXP_GTE: {
					if( $this->unite ) {
						$query[ $condition[self::C_FIELD] ] = array('$gte' => $condition[self::C_VALUE]);
					} else {
						$query[] = array( $condition[self::C_FIELD] => array('$gte' => $condition[self::C_VALUE]) );
					}
				} break;
				case self::EXP_LT: {
					if( $this->unite ) {
						$query[ $condition[self::C_FIELD] ] = array('$lt' => $condition[self::C_VALUE]);
					} else {
						$query[] = array( $condition[self::C_FIELD] => array('$lt' => $condition[self::C_VALUE]) );
					}
				} break;
				case self::EXP_LTE: {
					if( $this->unite ) {
						$query[ $condition[self::C_FIELD] ] = array('$lte' => $condition[self::C_VALUE]);
					} else {
						$query[] = array( $condition[self::C_FIELD] => array('$lte' => $condition[self::C_VALUE]) );
					}
				} break;
				default: {
					throw new WrongStateException( 'Sorry, I do not know how to work with you condition with ' );
				} break;
			}
		}
		if( !$this->unite ) {
			$query = array('$or' => $query);
		}
		return $query;
	}
//@}

/// parent object function implenetation
//@{
	public function toDialectString(Dialect $dialect) {
		throw new UnsupportedMethodException('NoSQLExpression does not support method "toDialectStringg"');
	}

	public function toBoolean(Form $form) {
		throw new UnsupportedMethodException('NoSQLExpression does not support method "toBoolean"');
	}

	public function toMapped(ProtoDAO $dao, JoinCapableQuery $query) {
		throw new UnsupportedMethodException('NoSQLExpression does not support method "toMapped"');
	}
//@}

}
