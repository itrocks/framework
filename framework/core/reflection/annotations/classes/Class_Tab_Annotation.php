<?php
namespace SAF\Framework;

class Class_Tab_Annotation extends List_Annotation implements Multiple_Annotation
{

	//----------------------------------------------------------------------------------------- $name
	/**
	 * The tab name
	 *
	 * @var string
	 */
	public $name;

	//----------------------------------------------------------------------------------- __construct
	public function __construct($value)
	{
		$i = strpos($value, ",");
		if ($i === false) {
			$i = strlen($value);
		}
		$i = strrpos(substr($value, 0, $i), " ");
		$this->name = substr($value, 0, $i);
		parent::__construct(substr($value, $i + 1));
	}

}
