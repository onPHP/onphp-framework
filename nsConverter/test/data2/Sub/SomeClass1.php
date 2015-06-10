<?php

class IaM {
	public function create()
	{
		return new self;
	}

	public function run(Form $form)
	{
		$className = "PrimitiveFloat";
		$form->add(Primitive::string('primitive'));
		$form->add(new {$className}('name'));

		throw new Exception();
	}
}