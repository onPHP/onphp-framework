<?php
	/* $Id$ */
	
	class TSearchBusinessStubDAO extends Singleton implements FullTextDAO
	{
		public function getIdName()
		{
			return 'id';
		}
		
		public function getIndexField()
		{
			return 'fti';
		}
		
		public function getTable()
		{
			return 'tsearch_stub';
		}
		
		public function getById($id)
		{
			throw new UnimplementedFeatureException();
		}
		
		public function getByLogic(LogicalObject $logic)
		{
			throw new UnimplementedFeatureException();
		}
		
		public function getByQuery(SelectQuery $query)
		{
			throw new UnimplementedFeatureException();
		}
		
		public function getCustom(SelectQuery $query)
		{
			throw new UnimplementedFeatureException();
		}
				
		public function getListByIds(array $ids)
		{
			throw new UnimplementedFeatureException();
		}
		
		public function getListByQuery(SelectQuery $query)
		{
			throw new UnimplementedFeatureException();
		}
		
		public function getListByLogic(LogicalObject $logic)
		{
			throw new UnimplementedFeatureException();
		}
		
		public function getPlainList()
		{
			throw new UnimplementedFeatureException();
		}
				
		public function getCustomList(SelectQuery $query)
		{
			throw new UnimplementedFeatureException();
		}
		
		public function getCustomRowList(SelectQuery $query)
		{
			throw new UnimplementedFeatureException();
		}
		
		public function getQueryResult(SelectQuery $query)
		{
			throw new UnimplementedFeatureException();
		}
		
		public function drop(Identifiable $object)
		{
			throw new UnimplementedFeatureException();
		}
		
		public function dropById($id)
		{
			throw new UnimplementedFeatureException();
		}
		
		public function dropByIds(array $ids)
		{
			throw new UnimplementedFeatureException();
		}
		
		public function uncacheById($id)
		{
			throw new UnimplementedFeatureException();
		}
		
		public function uncacheByIds(array $ids)
		{
			throw new UnimplementedFeatureException();
		}
		
		public function uncacheLists()
		{
			throw new UnimplementedFeatureException();
		}
	}
?>