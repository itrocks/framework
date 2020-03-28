<?php
namespace ITRocks\Framework\Feature\Export;

use ITRocks\Framework\Feature\Export\PDF\Init;
use setasign\Fpdi\Tcpdf\Fpdi;
use TCPDF;

/**
 * PDF export library
 *
 * Based on FPDI-TCPDF
 * As a FPDI limitation : when you instantiate this, you should declare the variable as PDF|TCPDF
 * to allow IDE to know all available methods and properties. See example below.
 *
 * @example
 * / ** @var $pdf PDF|TCPDF * /
 * $pdf = new PDF();
 */
class PDF extends Fpdi
{
	use Init;

	//------------------------------------------------------------------- MILLIMETERS_TO_POINTS_RATIO
	const MILLIMETERS_TO_POINTS_RATIO = 2.5;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * The constructor prepare the document the same way if coming from FPDF or TCPDF : no header line
	 */
	public function __construct()
	{
		/** @noinspection PhpUndefinedMethodInspection exists into TCPDF */
		parent::__construct();
		/** @var $this PDF|TCPDF */
		$this->setPrintHeader(false);
		$this->setPrintFooter(false);
	}

	//--------------------------------------------------------------------------- millimetersToPoints
	/**
	 * @param $millimeters float
	 * @return float
	 */
	public function millimetersToPoints($millimeters)
	{
		return static::MILLIMETERS_TO_POINTS_RATIO * $millimeters;
	}

}
