<?php
namespace SAF\Framework;

abstract class List_Controller extends Output_Controller
{

	//----------------------------------------------------------------------------- getListProperties
	abstract protected function getListProperties($class_name);

	//--------------------------------------------------------------------------- getSelectionButtons
	abstract protected function getSelectionButtons($class_name);

}
