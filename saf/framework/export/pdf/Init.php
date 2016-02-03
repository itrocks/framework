<?php
namespace SAF\Framework\Export\PDF;

use FPDI;

require_once __DIR__ . '/../../../../vendor/tcpdf/tcpdf.php';
require_once __DIR__ . '/../../../../vendor/fpdi/fpdi_bridge.php';
require_once __DIR__ . '/../../../../vendor/fpdi/fpdf_tpl.php';
require_once __DIR__ . '/../../../../vendor/fpdi/fpdi.php';

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
