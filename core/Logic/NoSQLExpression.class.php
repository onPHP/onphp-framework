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

	const V_LEFT		= 101;
	const V_RIGHT		= 102;

	const EXP_EQ		= 1001;
	const EXP_NOT_EQ	= 1002;
	const EXP_GT		= 1003;
	const EXP_GTE		= 1004;
	const EXP_LT		= 1005;
	const EXP_LTE		= 1006;
	const EXP_BTW_STR	= 1007;
	const EXP_BTW_SFT	= 1008;
	const EXP_IN		= 1009;
	const EXP_NOT_IN	= 1010;

	/**
	 * true = объединять условия по И
	 * false = разделять условия по ИЛИ
	 * @var bool
	 */
	protected $unite = null;

	protected $fields = array();
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

/// field list
//@{
	/**
	 * @param string $fieldName
	 * @return NoSQLExpression
	 */
	public function addField($fieldName) {
		$this->fields = $fieldName;
		return $this;
	}
//@}

/// condition setters
//@{
	public function addEq($field, $value) {
		if (is_string($value) && Assert::checkInteger($value)) {
			$dbValue = array($value, (int)$value);
		} elseif (is_string($value) && Assert::checkFloat($value)) {
			$dbValue = array($value, (float)$value);
		} else {
			$dbValue = $value;
		}

		if (is_array($dbValue) /* weak typing */) {
			$this->conditions[] = array(
				self::C_TYPE	=> self::EXP_IN,
				self::C_FIELD	=> (string)$field,
				self::C_VALUE	=> $dbValue,
			);
		} else {
			$this->conditions[] = array(
				self::C_TYPE	=> self::EXP_EQ,
				self::C_FIELD	=> (string)$field,
				self::C_VALUE	=> $dbValue,
			);
		}
		return $this;
	}

	public function addNotEq($field, $value) {
		$this->conditions[] = array(
			self::C_TYPE	=> self::EXP_NOT_EQ,
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

	public function addBetweenStrict($field, $left, $right) {
		Assert::isInteger($left);
		Assert::isInteger($right);
		$this->conditions[] = array(
			self::C_TYPE	=> self::EXP_BTW_STR,
			self::C_FIELD	=> (string)$field,
			self::C_VALUE	=> array( self::V_LEFT=>$left, self::V_RIGHT=>$right ),
		);
		return $this;
	}

	public function addBetweenSoft($field, $left, $right) {
		Assert::isInteger($left);
		Assert::isInteger($right);
		$this->conditions[] = array(
			self::C_TYPE	=> self::EXP_BTW_SFT,
			self::C_FIELD	=> (string)$field,
			self::C_VALUE	=> array( self::V_LEFT=>$left, self::V_RIGHT=>$right ),
		);
		return $this;
	}

	public function addIn($field, array $value) {
		foreach($value as &$inVal) {
			if( is_null($inVal) ) {
				$inVal = null;
			} elseif( Assert::checkInteger($inVal) ) {
				$inVal = (int)$inVal;
			} else {
				$inVal = (string)$inVal;
			}
		}
		$this->conditions[] = array(
			self::C_TYPE	=> self::EXP_IN,
			self::C_FIELD	=> (string)$field,
			self::C_VALUE	=> $value,
		);
		return $this;
	}

	public function addNotIn($field, $value) {
		foreach($value as &$inVal) {
			if(Assert::checkInteger($inVal)) {
				$inVal = (int)$inVal;
			} else {
				$inVal = (string)$inVal;
			}
		}
		$this->conditions[] = array(
			self::C_TYPE	=> self::EXP_NOT_IN,
			self::C_FIELD	=> (string)$field,
			self::C_VALUE	=> $value,
		);
		return $this;
	}
//@}

/// helper functions
//@{
//@}

/// condition setters
//@{
	public function getFieldList() {
		return $this->fields;
	}

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
				case self::EXP_NOT_EQ: {
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
				case self::EXP_BTW_STR: {
					if( $this->unite ) {
						$query[ $condition[self::C_FIELD] ] = array('$gt' => $condition[self::C_VALUE][self::V_LEFT], '$lt' => $condition[self::C_VALUE][self::V_RIGHT]);
					} else {
						$query[] = array( $condition[self::C_FIELD] => array('$gt' => $condition[self::C_VALUE][self::V_LEFT], '$lt' => $condition[self::C_VALUE][self::V_RIGHT]) );
					}
				} break;
				case self::EXP_BTW_SFT: {
					if( $this->unite ) {
						$query[ $condition[self::C_FIELD] ] = array('$gte' => $condition[self::C_VALUE][self::V_LEFT], '$lte' => $condition[self::C_VALUE][self::V_RIGHT]);
					} else {
						$query[] = array( $condition[self::C_FIELD] => array('$gte' => $condition[self::C_VALUE][self::V_LEFT], '$lte' => $condition[self::C_VALUE][self::V_RIGHT]) );
					}
				} break;
				case self::EXP_IN: {
					if( $this->unite ) {
						$query[ $condition[self::C_FIELD] ] = array('$in' => $condition[self::C_VALUE]);
					} else {
						$query[] = array( $condition[self::C_FIELD] => array('$in' => $condition[self::C_VALUE]) );
					}
				} break;
				case self::EXP_NOT_IN: {
					if( $this->unite ) {
						$query[ $condition[self::C_FIELD] ] = array('$nin' => $condition[self::C_VALUE]);
					} else {
						$query[] = array( $condition[self::C_FIELD] => array('$nin' => $condition[self::C_VALUE]) );
					}
				} break;
				default: {
					throw new WrongStateException( 'Sorry, I do not know how to work with you condition!' );
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
