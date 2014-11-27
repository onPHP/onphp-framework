<?php
/**
 * PostgreSQL array expressions
 * @author Alex Gorbylev <alex@adonweb.ru>
 * @author Anton Gurov <trashmailbox@e1.ru>
 * @date 2014.11.27
 * refer to
 * http://www.postgresql.org/docs/current/interactive/functions-array.html#ARRAY-OPERATORS-TABLE
 * for list of array operators
 */
class ArrayExpression implements LogicalObject, MappableObject {

	const FIELD = null;
	const ANY = 'ANY';
	const ALL = 'ALL';

    const CONTAINS          = '@>';
    const IS_CONTAINED_BY   = '<@';

    const OVERLAP           = '&&';

    const CONCAT            = '||';

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

    /**
     * @param $field
     * @param $value
     * @return ArrayExpression
     */
    public static function contains($field, $value) {
        return new self(
            $field, $value, self::CONTAINS
        );
    }

    /**
     * @param $field
     * @param $value
     * @return ArrayExpression
     */
    public static function isContainedBy($field, $value) {
        return new self(
            $field, $value, self::IS_CONTAINED_BY
        );
    }

    /**
     * @param $field
     * @param $value
     * @return ArrayExpression
     */
    public static function overlap($field, $value) {
        return new self(
            $field, $value, self::OVERLAP
        );
    }

    /**
     * @param $field
     * @param $value
     * @return ArrayExpression
     */
    public static function concat($field, $value) {
        return new self(
            $field, $value, self::CONCAT
        );
    }


	public function toDialectString(Dialect $dialect)
	{
        switch ($this->logic) {
            //Обращение напрямую к полю
            case self::FIELD:
                return $tableField;

            //Операторы, которые между значениями
            case self::CONTAINS:
            case self::IS_CONTAINED_BY:
            case self::OVERLAP:
            case self::CONCAT:
                if (!is_array($this->field->getValue())) {
                    throw new WrongArgumentException("Trying to use array functions with non-array value:"
                        . print_r($this->field,1));
                }
                $right = "ARRAY [".implode(',', $this->field->getValue())."]"; // @todo Dangerous!
                return
                    '('
                    .$dialect->toFieldString($this->subject)
                    .' '.$dialect->logicToString($this->logic).' '
                    .$right //Dangerous!
                    .')';

            // ANY и ALL
            default:
                $tableField = $dialect->toFieldString($this->subject);
                if( !is_null($this->field) ) {
                    $tableField .= "[{$this->field}]";
                }
                return
                    $dialect->logicToString($this->logic).
                    '('
                    .$tableField
                    .')';

        }

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
        $left	= $form->toFormValue($this->field);
        $right	= $form->toFormValue($this->subject);

        $both =
            (null !== $left)
            && (null !== $right);

        switch ($this->logic) {
            case self::CONTAINS:
                return $both && in_array($left, $right);

            case self::IS_CONTAINED_BY:
                return $both && in_array($right, $left);

            case self::OVERLAP:
                $intersection = array_intersect($left , $right);
                return !empty($intersection);

            case self::CONCAT:
                throw new UnsupportedMethodException("{$this->logic} cannot be converted to boolean ");

            default:
		        throw new UnsupportedMethodException('Method toBoolean is not supported by ArrayExpression');
        }
	}
}