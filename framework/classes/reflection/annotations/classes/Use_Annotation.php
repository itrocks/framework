<?php
namespace SAF\Framework;

class Use_Annotation extends Annotation implements Multiple_Annotation
{

	//----------------------------------------------------------------------------------- __construct
	public function __construct($value)
	{
		parent::__construct(substr($value, 1));
	}

}
