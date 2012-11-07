<?php
namespace SAF\Framework;

interface List_Controller
{

	//----------------------------------------------------------------------------- getGeneralButtons
	public function getGeneralButtons($class_name);

	//----------------------------------------------------------------------------- getListProperties
	public function getListProperties();

	//--------------------------------------------------------------------------- getSelectionButtons
	public function getSelectionButtons($class_name);

}

?>