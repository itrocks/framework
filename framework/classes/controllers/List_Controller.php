<?php
namespace SAF\Framework;

abstract class List_Controller extends Output_Controller
{

	//----------------------------------------------------------------------------- getListProperties
	abstract protected function getListProperties();

	//--------------------------------------------------------------------------- getSelectionButtons
	abstract protected function getSelectionButtons($class_name);

}
