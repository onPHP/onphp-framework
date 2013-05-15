<?php
/**
 * Почти как ValueObject, но создает пустой DAO-класс
 * @author Михаил Кулаковский <m@klkvsk.ru>
 * @date 2013.04.19
 */

final class ObjectWithEmptyDAOClassPattern extends BasePattern {

	public function tableExists()
	{
		return false;
	}

	public function daoExists()
	{
		return true;
	}

	/**
	 * @param $class MetaClass
	 * @return self
	 **/
	protected function fullBuild(MetaClass $class)
	{
		return $this
			->buildBusiness($class)
			->buildProto($class)
			->buildDao($class);
	}

	protected function buildDao(MetaClass $class) {
		$this->dumpFile(
			ONPHP_META_AUTO_DAO_DIR.'Auto'.$class->getName().'DAO'.EXT_CLASS,
			Format::indentize(AutoEmptyDaoBuilder::build($class))
		);

		$userFile = ONPHP_META_DAO_DIR.$class->getName().'DAO'.EXT_CLASS;

		if (
			MetaConfiguration::me()->isForcedGeneration()
			|| !file_exists($userFile)
		)
			$this->dumpFile(
				$userFile,
				Format::indentize(DaoBuilder::build($class))
			);

		return $this;
	}


}