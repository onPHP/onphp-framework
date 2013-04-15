<?php
/**
 * DB Array
 * @author Alex Gorbylev <alex@adonweb.ru>
 * @date 2013.04.15
 */

	/**
	 * Container for passing array values into OSQL queries.
	 *
	 * @ingroup OSQL
	 * @ingroup Module
	 **/
	class DBArray extends DBValue {

		protected $type = null;

		/**
		 * @param int $value
		 * @return DBArray
		 */
		public static function create($value)
		{
			return new self($value);
		}

		public function integers() {
			$this->type = DataType::INTEGER;
			return $this;
		}

		public function strings() {
			$this->type = DataType::VARCHAR;
			return $this;
		}

		public function toDialectString(Dialect $dialect)
		{
			return $dialect->quoteArray($this->getValue(), $this->type);
		}

	}