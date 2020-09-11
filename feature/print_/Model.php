<?php
namespace ITRocks\Framework\Feature\Print_;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Feature\Export\PDF;
use ITRocks\Framework\Layout\Generator;
use ITRocks\Framework\Layout\PDF\Exporter;
use ITRocks\Framework\Layout\Print_Model;
use ITRocks\Framework\Tools\Names;
use TCPDF;

/**
 * Everything to print a document using layout models
 */
class Model extends PDF\Output
{

	//--------------------------------------------------------------------------------------- $output
	/**
	 * @values static::const
	 * @var string
	 */
	public $output = self::INLINE;

	//---------------------------------------------------------------------------------------- append
	/**
	 * Print objects into an existing $pdf object : pages are appended
	 *
	 * @param $pdf         PDF|TCPDF   The already instantiated and opened PDF object
	 * @param $objects     object[]    Objects to print, all must be of the same class
	 * @param $print_model Print_Model If not set, the first available print model will be taken
	 */
	public function append(PDF $pdf, array $objects, Print_Model $print_model = null)
	{
		if (!$print_model) {
			$class_name  = Builder::current()->sourceClassName(get_class(reset($objects)));
			$print_model = Dao::searchOne(['class_name' => $class_name], Print_Model::class, Dao::sort());
		}
		foreach ($objects as $object) {
			$this->appendObject($pdf, $object, $print_model);
		}
	}

	//---------------------------------------------------------------------------------- appendObject
	/**
	 * Print object into an existing $pdf object : pages are appended
	 *
	 * @param $pdf         PDF|TCPDF   The already instantiated and opened PDF object
	 * @param $object      object      Object to print
	 * @param $print_model Print_Model If not set, the first available print model will be taken
	 */
	protected function appendObject(PDF $pdf, $object, Print_Model $print_model)
	{
		$exporter            = new Exporter();
		$exporter->pdf       = $pdf;
		$generator           = new Generator($print_model, $exporter);
		$generator->print    = true;
		$structure           = $generator->generate($object);
		$exporter->structure = $structure;
		$exporter->appendToPdf();
	}

	//---------------------------------------------------------------------------------------- newPdf
	/**
	 * @return PDF|TCPDF
	 */
	protected function newPdf()
	{
		// TODO LOW This is for a warning in php 7.3. Remove it when tcpdf is compatible
		$error_reporting = error_reporting(E_ALL & ~E_WARNING);
		/** @var $pdf PDF|TCPDF */
		$pdf = new PDF();
		error_reporting($error_reporting);
		$pdf->Open();
		return $pdf;
	}

	//----------------------------------------------------------------------------------------- print
	/**
	 * Print objects using a layout model, using PDF format
	 * This returns the raw content of the generated PDF file
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $objects     object[] Objects to print, all must be of the same class
	 * @param $print_model Print_Model if not set, the first available print model will be taken
	 * @return mixed
	 */
	public function print(array $objects, Print_Model $print_model = null)
	{
		$first_object = reset($objects);
		if (!$print_model) {
			$class_name  = Builder::current()->sourceClassName(get_class($first_object));
			$print_model = Dao::searchOne(['class_name' => $class_name], Print_Model::class, Dao::sort());
		}

		$file_name = ($first_object instanceof Has_Print_File_Name)
			? $first_object->printFileName($objects)
			: Names::classToDisplay($print_model->class_name) . '.pdf';

		$pdf = static::newPdf();
		$this->append($pdf, $objects, $print_model);
		/** @noinspection PhpUnhandledExceptionInspection Buffer output should not crash */
		return $pdf->Output($file_name, $this->output);
	}

}
