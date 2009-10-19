<?php

	class formRendering implements Controller {
		public function handleRequest(HttpRequest $request) {
			$form = Form::create()->
				//add(
					//Primitive::integerIdentifier('id')->
				//)->
				add(
					Primitive::string('name')
				)->
				add(
					Primitive::integer('integer')
				)->
				add(
					Primitive::file('file')
				)->
				add(
					Primitive::choice('choice')->
						setList(
							array(
								'red'    => 'Red',
								'green'  => 'Green',
								'blue'   => 'Blue',
							)
						)
				)->
				add(
					Primitive::boolean('boolean')
				)->
				import(
					array(
						//'id'      => 123,
						'name'    => 'Name',
						'integer' => 1000,
						'choice'  => 'blue',
						'boolean' => true
					)
				);




			return ModelAndView::create()->
				setModel(
					Model::create()->
						set('form', $form)
				);
		}
	}

?>