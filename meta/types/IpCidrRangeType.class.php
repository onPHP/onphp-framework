<?php
/**
 * DataType to represent CIDR range
 * @author Aleksandr Babaev <babaev@adonweb.ru>
 * @date   2013.12.02
 */

	/**
	 * @ingroup Types
	**/
	class IpCidrRangeType extends ObjectType
	{
		public function getPrimitiveName()
		{
			return 'IpCidrRange';
		}
		
		public function isGeneric()
		{
			return true;
		}
		
		public function isMeasurable()
		{
			return true;
		}
		
		public function toColumnType()
		{
			return 'DataType::create(DataType::CIDR)';
		}
	}
?>