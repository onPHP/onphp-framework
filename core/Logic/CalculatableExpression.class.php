<?php
/**
 * 
 * @author Михаил Кулаковский <m@klkvsk.ru>
 * @date 20.02.14
 */

class CalculatableExpression extends BinaryExpression {

	public static function add($left, $right) {
		return new CalculatableExpression($left, $right, self::ADD);
	}

	public static function sub($left, $right) {
		return new CalculatableExpression($left, $right, self::SUBSTRACT);
	}

	public static function div($left, $right) {
		return new CalculatableExpression($left, $right, self::DIVIDE);
	}

	public static function mul($left, $right) {
		return new CalculatableExpression($left, $right, self::MULTIPLY);
	}

	protected function operandToValue($operand, $dataSource) {
		if ($operand instanceof CalculatableExpression) {
			return $operand->toValue($dataSource);

		} else if (is_string($operand)) {
			if ($dataSource instanceof Form) {
				return $dataSource->getValue($operand);

			} else if ($dataSource instanceof Prototyped) {
				return PrototypeUtils::getValue($dataSource, $operand);

			} else if (is_array($dataSource)) {
				return isset($dataSource[$operand]) ? $dataSource[$operand] : 0;

			} else {
				throw new WrongArgumentException('$dataSource should be Form or Prototyped or array');
			}

		} else {
			return floatval($operand);
		}
	}

	/**
	 * @param Form|Prototyped|array $dataSource
	 * @return float
	 * @throws UnsupportedMethodException
	 */
	public function toValue($dataSource)
	{
		$left  = $this->operandToValue($this->getLeft(),  $dataSource);
		$right = $this->operandToValue($this->getRight(), $dataSource);

		switch ($this->getLogic()) {
			case self::EQUALS:
				return $left == $right ? 1 : 0;

			case self::NOT_EQUALS:
				return $left != $right ? 1 : 0;

			case self::GREATER_THAN:
				return $left > $right ? 1 : 0;

			case self::GREATER_OR_EQUALS:
				return $left >= $right ? 1 : 0;

			case self::LOWER_THAN:
				return $left < $right ? 1 : 0;

			case self::LOWER_OR_EQUALS:
				return $left <= $right ? 1 : 0;

			case self::EXPRESSION_AND:
				return $left && $right ? 1 : 0;

			case self::EXPRESSION_OR:
				return $left || $right ? 1 : 0;

			case self::ADD:
				return $left + $right;

			case self::SUBSTRACT:
				return $left - $right;

			case self::MULTIPLY:
				return $left * $right;

			case self::DIVIDE:
				return $left / $right;

			case self::MOD:
				return $left % $right;

			default:
				throw new UnsupportedMethodException(
					"'{$this->getLogic()}' doesn't supported yet"
				);
		}
	}
} 