<?php
namespace SAF\Framework;

class Remover_Annotation extends Annotation implements Property_Context_Annotation
{

	//----------------------------------------------------------------------------------- __construct
	public function __construct($value, Reflection_Property $context)
	{
		parent::__construct(Namespaces::defaultFullClassName($value, $context->class));
	}

}
