<?php
namespace SAF\Framework;

interface List_Controller extends Output_Controller
{

	//----------------------------------------------------------------------------- getListProperties
	public function getListProperties();

	//--------------------------------------------------------------------------- getSelectionButtons
	public function getSelectionButtons($class_name);

}
