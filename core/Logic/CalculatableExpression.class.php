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

	protected function operandToValue($operand, Form $form) {
		if ($operand instanceof CalculatableExpression) {
			return $operand->toValue($form);

		} else if (is_string($operand) && $form->exists($operand)) {
			return $form->getValue($operand);

		} else {
			return floatval($operand);
		}
	}

	public function toValue(Form $form)
	{

		$left  = $this->operandToValue($this->getLeft(),  $form);
		$right = $this->operandToValue($this->getRight(), $form);

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