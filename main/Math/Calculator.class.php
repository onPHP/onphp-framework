<?php
/**
 * Калькулятор для простых формул вида (a + b) * c
 * @author Ilya Shvedov <shvedov@adonweb.ru>
 * @author Alex Gorbylev <alex@adonweb.ru>
 * @date 2011.12.23
 */
final class Calculator {

	const OP_SUMMARIZE = 1;
	const OP_MULTIPLY = 2;
	const OP_PRIORITY = 3;

	// массив значений
	protected $variables = array();

	// Массив операций в строке
	protected $formula;

	// Номер текущей операции в строке
	protected $index;

	// Стек значений в зависимости от последовательности операций
	protected $stack;

	// результат по умолчанию
	protected $default = 0;

	protected $operators = array(
		'+' => self::OP_SUMMARIZE,
		'-' => self::OP_SUMMARIZE,

		'*' => self::OP_MULTIPLY,
		'/' => self::OP_MULTIPLY,
		'%' => self::OP_MULTIPLY,

		'(' => self::OP_PRIORITY,
	);

	final protected function __construct() {/*_*/}

	/**
	 * @param string $formula
	 * @param array $input
	 * @return int
	 */
	public static function execute($formula, array $input = null) {
		$calc = new self();
		$input = is_array($input) && !empty($input) ? $input : array();
		// запускаем!
		return $calc->calculate($formula, $input );
	}

	/**
	 * Расчет конечного результата в зависимости от условия
	 */
	public function calculate( $math, array $input ) {
		$this->formula = array();
		$this->stack = array();

		$math = strtolower( $math );
		// проверяем на только допустимые символы
		if ( preg_replace( '/[0-9\(\)\s\+\-\*\/\%\$\.]+/', '', $math ) !== '' ) {
			throw new WrongArgumentException('Formula contains unsupported symbols');
		}
		// делаем по пробелу между элементами
		$math = preg_replace('/\s+/', '', $math);
		$math = preg_replace( '/([\(\)\+\-\*\/\%]{1})/', ' $1 ', $math );
		$math = trim( preg_replace('/\s+/', ' ', $math) );
		// разбиваем формулу на составные части
		$this->formula = explode( ' ', $math );

		// Инициализация
		$this->variables = $input;
		$this->index = 0;

		$this->calculateSummarize();
		return
			isset( $this->stack[0] )
				? $this->stack[0]
				: $this->default
			;
	}

	/**
	 * Рассчитывает операцию
	 */
	protected function evaluateVariables( $operation ) {
		$p2 = array_pop ( $this->stack );
		$p1 = array_pop ( $this->stack );

		$res = $this->default;
		switch($operation) {
			case '+': {
				$res = $p1 + $p2;
			} break;
			case '-': {
				$res = $p1 - $p2;
			} break;
			case '*': {
				$res = $p1 * $p2;
			} break;
			case '/': {
				$res = $p1 / $p2;
			} break;
			case '%': {
				$res = $p1 % $p2;
			} break;
			default: {
				throw new WrongArgumentException("Unknown math operation '{$operation}'");
			} break;
		}

		array_push( $this->stack, $res );
	}

	/**
	 * Расчет строки как формулы
	 */
	protected function calculateSummarize() {
		$this->calculateMultiply();

		if($this->checkStop()){ return; }

		if ( $this->getCurrentOperatorClass() == self::OP_SUMMARIZE ) {
			$operator = $this->getCurrent();
			$this->next();
			$this->calculateSummarize();
			$this->evaluateVariables( $operator );
			return;
		}
	}

	/**
	 * Расчет множителей
	 */
	protected function calculateMultiply() {
		$this->calculatePriority();

		if($this->checkStop()){ return; }

		if ( $this->getCurrentOperatorClass() == self::OP_MULTIPLY ) {
			$operator = $this->getCurrent();
			$this->next();
			$this->calculateMultiply();
			$this->evaluateVariables( $operator );
			return;
		}
	}

	/**
	 * Расчет скобок
	 */
	protected function calculatePriority() {
		if ( $this->getCurrentOperatorClass() == self::OP_PRIORITY ) {
			$this->next();
			$this->calculateSummarize();
			$this->next();
		}
		else {
			$this->calculateValue();
		}
	}

	/**
	 * Оперирует числом в строке условия
	 */
	protected function calculateValue() {
		// значение в строке
		$operand = $this->getCurrent();

		// получаем значение операнда
		$value_to_push = $this->default;
		// типа операнда: константа или переменная
		if( $operand{0}=='$' ) {
			$key = intval(substr($operand, 1))-1;
			$value_to_push = array_key_exists($key, $this->variables) ? $this->variables[$key] : $this->default;
		} elseif(Assert::checkFloat($operand)) {
			$value_to_push = floatval($operand);
		}

		// добавляем элемент в стек для последующей подстановки в выражение
		array_push( $this->stack, $value_to_push );
		$this->next();
	}

	private function next() {
		$this->index++;
	}

	private function checkStop() {
		return $this->index >= count( $this->formula );
	}

	private function getCurrent() {
		return $this->formula[$this->index];
	}

	private function getCurrentOperatorClass() {
		$exist = isset( $this->operators[ $this->getCurrent() ] );
		return $exist ? $this->operators[ $this->getCurrent() ] : null;
	}

}