<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Khvostishkov                               *
 *   dedmajor@oemdesign.ru                                                 *
 ***************************************************************************/
/* $Id$ */

	class statistics implements Controller
	{
		public function handleRequest(HttpRequest $request)
		{
			$cube = Cube::create(StatisticVisitor::dao());
			
			$hitsMeasure = Dimension::create('id')->
				setMeasure(true)->
				setProjection(Projection::count());
				
			$cube->addDimension($hitsMeasure);
				
			$timeDimension =
				Dimension::create('when')->
				setTime(true);
				
			$dateHierarchy =
				$timeDimension->
					createHierarchy(Hierarchy::LEVEL_BASED, true)->
						addLevel(
							SQLFunction::create('date_part', 'year')
						)->
						addLevel(
							SQLFunction::create('date_part', 'month')
						)->
						addLevel(
							SQLFunction::create('date_part', 'day')
						);
						
			$timeHierarchy =
				$timeDimension->
					createHierarchy(Hierarchy::LEVEL_BASED, true)->
						addLevel(
							SQLFunction::create('date_part', 'hour')
						)->
						addLevel(
							SQLFunction::create('date_part', 'minute')
						);
			
			$cube->addDimension($timeDimension);
			
			$regionDimension = Dimension::create('region');
			
			$regionCountryHierarchy =
				$regionDimension->createHierarchy(Hierarchy::LEVEL_BASED, true)->
					addLevel('name')->
					addLevel('country');
			
			/*
			$regionParentHierarchy =
				$regionDimension->createHierarchy(Hierarchy::VALUE_BASED)->
					setClosure('region_closure')->
					setField('region_id')->
					setParentField('parent_id')->
					setDistanceField('distance');
			*/
			
			$cube->addDimension($regionDimension);
			
			
			
			$dimensionView = DimensionView::create($regionDimension);
			
			// select all members from regions dimension
			//$dimensionCursor = $cube->getCursor($dimensionView);
			
			$cubeView = CubeView::create();
			
			$cubeView->createPageEdge()->
				addDimensionView(DimensionView::create($hitsMeasure));
				
			$cubeView->createOrdinateEdge()->
				addDimensionView(DimensionView::create($timeDimension));
				
			$cubeView->createOrdinateEdge()->
				addDimensionView($dimensionView);
			
			// counts all visitors grouped by time on one axis and region on
			// the other
			//$cubeCursor = $cube->getCursor($cubeView);
			
			
			return
				ModelAndView::create()->
				setModel(
					Model::create()->
					set('cubeView', $cubeView)->
					set('dimensionView', $dimensionView)
				);
		}
	}
?>