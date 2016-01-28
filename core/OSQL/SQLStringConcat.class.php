<?php
/**
 * @author Михаил Кулаковский <m@klkvsk.ru>
 * @date   21.07.2014
 */

/**
 * @ingroup OSQL
 **/
class SQLStringConcat extends Castable implements MappableObject, Aliased
{
	const OPERATOR = '||';

	protected $args = array();
	protected $alias = null;

	/**
	 * @param $arg1
	 * @param $arg2
	 * @param ...
	 * @param $argN
	 * @return self
	 */
	public static function create() {
		$self = new self;
		$self->args = func_get_args();
		return $self;
	}

	protected function __construct() {}

	/**
	 * @param string $alias
	 * @return self
	 */
	public function setAlias($alias) {
		$this->alias = $alias;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getAlias() {
		return $this->alias;
	}

	/**
	 * @param $arg
	 * @return self
	 */
	public function add($arg) {
		$this->args []= $arg;
		return $this;
	}

	/**
	 * @param ProtoDAO         $dao
	 * @param JoinCapableQuery $query
	 * @return self
	 */
	public function toMapped(ProtoDAO $dao, JoinCapableQuery $query) {
		$mapped = self::create();

		foreach ($this->args as $arg) {
			if ($arg instanceof MappableObject) {
				$mapped->add($arg->toMapped($dao, $query));
			} else {
				$mapped->add($dao->guessAtom($arg, $query));
			}
		}

		if ($this->alias) {
			$mapped->setAlias($this->alias);
		}

		if ($this->cast) {
			$mapped->castTo($this->cast);
		}

		return $mapped;
	}

	public function toDialectString(Dialect $dialect) {
		$strings = array();
		foreach ($this->args as $arg) {
			$strings []= $dialect->toValueString($arg);
		}

		$sql = '('
			. implode(
				' ' . $dialect->logicToString(self::OPERATOR) . ' ',
				$strings
			)
			. ')';

		if ($this->alias) {
			$sql .=  ' AS ' . $dialect->quoteTable($this->alias);
		}

		if ($this->cast) {
			$sql = $dialect->toCasted($sql, $this->cast);
		}

		return $sql;
	}
}
