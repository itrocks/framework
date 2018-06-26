<?php
namespace ITRocks\Framework\Export\PDF;

use FPDI;
use ITRocks\Framework\Export\PDF;

/**
 * Initialisation features
 *
 * @extends PDF
 */
trait Init
{

	//------------------------------------------------------------------------------------------ init
	/**
	 * Call this to prepare a standard pdf document and directly begin with $this->AddPage()
	 */
	public function init()
	{
		/** @var $this FPDI|PDF|static */
		$this->Open();
		$this->SetFont(Font::COURIER);
		$this->SetMargins(10, 10, 10);
		$this->SetAutoPageBreak(true, 10);
	}

}
