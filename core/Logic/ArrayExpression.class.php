<?php
/**
 * PostgreSQL array expressions
 * @author Alex Gorbylev <alex@adonweb.ru>
 * @date 2013.04.03
 */
class ArrayExpression implements LogicalObject, MappableObject {

	const FIELD = null;
	const ANY = 'ANY';
	const ALL = 'ALL';

	protected
		$subject = null,
		$field = null,
		$logic = null;

	protected function __construct($subject, $field, $logic) {
		$this->subject	= $subject;
		$this->field	= $field;
		$this->logic	= $logic;
	}

	public static function field($subject, $field)
	{
		return new self($subject, $field, self::FIELD);
	}

	public static function any($subject)
	{
		return new self($subject, null, self::ANY);
	}

	public static function anyEq($field, $value) {
		return Expression::eq(DBValue::create($value), self::any($field));
	}

	public static function all($subject)
	{
		return new self($subject, null, self::ALL);
	}

	public static function allEq($subject, $value) {
		return Expression::eq(DBValue::create($value), self::all($subject));
	}

	public function toDialectString(Dialect $dialect)
	{
		$tableField = $dialect->toFieldString($this->subject);
		if( !is_null($this->field) ) {
			$tableField .= "[{$this->field}]";
		}

		if( $this->logic === self::FIELD ) {
			return $tableField;
		}

		return
			$dialect->logicToString($this->logic).
			'('
			.$tableField
			.')';
	}

	/**
	 * @return BinaryExpression
	 **/
	public function toMapped(ProtoDAO $dao, JoinCapableQuery $query)
	{
		return new self(
			$dao->guessAtom($this->subject, $query),
			$this->field,
			$this->logic
		);
	}

	public function toBoolean(Form $form) {
		throw new UnsupportedMethodException('Method tpBoolean is not supported by ArrayExpression');
	}
}