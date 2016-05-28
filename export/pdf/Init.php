<?php
namespace SAF\Framework\Export\PDF;

use FPDI;

/**
 * Initialisation features
 */
trait Init
{

	//------------------------------------------------------------------------------------------ init
	/**
	 * Call this to prepare a standard pdf document and directly begin with $this->AddPage()
	 */
	public function init()
	{
		/** @var $this self|FPDI */
		$this->Open();
		$this->SetFont(Font::COURIER);
		$this->SetMargins(10, 10, 10);
		$this->SetAutoPageBreak(true, 10);
	}

}
