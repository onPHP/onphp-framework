<?php
/**
 * Use Projection::mappable() with this for:
 *   SELECT ("date" AT TIME ZONE 'Europe/Moscow') AS "d" ...
 * @author Михаил Кулаковский <m@klkvsk.ru>
 * @date   11.10.13
 */

/**
 * @ingroup OSQL
 **/
final class SQLTimezoneConvert extends Castable implements MappableObject, Aliased
{
	private $date = null;
	private $alias = null;
	private $timezone = null;

	/**
	 * @param $date
	 * @param $timezone
	 * @return self
	 */
	public static function create($date, $timezone) {
		return new self($date, $timezone);
	}

	public function __construct($date, $timezone) {
		$this->date = $date;
		$this->timezone = $timezone;
	}

	/**
	 * @param $alias
	 * @return self
	 */
	public function setAlias($alias) {
		$this->alias = $alias;
		return $this;
	}

	public function getAlias() {
		return $this->alias;
	}

	public function getDate() {
		return $this->date;
	}

	/**
	 * @param ProtoDAO         $dao
	 * @param JoinCapableQuery $query
	 * @return self
	 */
	public function toMapped(ProtoDAO $dao, JoinCapableQuery $query) {
		if ($this->date instanceof MappableObject) {
			$date = $this->date->toMapped($dao, $query);
		} else {
			$date = $dao->guessAtom($this->date, $query);
		}

		$mapped = self::create($date, $this->timezone);
		if ($this->cast) {
			$mapped->castTo($this->cast);
		}
		if ($this->alias) {
			$mapped->setAlias($this->alias);
		}

		return $mapped;
	}

	public function toDialectString(Dialect $dialect) {
		$sql = '('
			. $dialect->fieldToString($this->getDate())
			. ' AT TIME ZONE '
			. $dialect->valueToString($this->timezone)
			. ')';

		if ($this->cast) {
			$sql = $dialect->toCasted($sql, $this->cast);
		}
		if ($this->alias) {
			$sql .=  ' AS ' . $dialect->quoteTable($this->alias);
		}

		return $sql;
	}
}
