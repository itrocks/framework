<?php
namespace SAF\Framework;

class Class_Use_Annotation extends List_Annotation implements Multiple_Annotation
{

	//----------------------------------------------------------------------------------- __construct
	public function __construct($value)
	{
		parent::__construct($value);
		foreach ($this->values() as $key => $val) {
			if (substr($val, 0, 1) == "$") {
				$this->value[$key] = substr($val, 1);
			}
		}
	}

}
