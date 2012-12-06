<?php
/**
 * <description>
 * @author Alex Gorbylev <alex@adonweb.ru>
 * @date 2012.12.06
 */
class CrippleClassProjection extends ClassProjection {

	private $excludedFields = array();

	public function excludeField($field) {
		Assert::isString($field);
		$this->excludedFields[$field] = $field;
		return $this;
	}

	/* void */
	protected function subProcess(JoinCapableQuery $query, DBField $field) {
		// if need to exclude change field to NULL
		if( array_key_exists($field->getField(), $this->excludedFields) ) {
			$nullField = DBRaw::create('NULL');
			$query->get($nullField, $field->getField());
		} else {
			$query->get($field);
		}
	}


}
