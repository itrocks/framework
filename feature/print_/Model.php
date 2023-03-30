<?php
namespace ITRocks\Framework\Feature\Print_;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Feature\Export\PDF;
use ITRocks\Framework\Layout\Generator;
use ITRocks\Framework\Layout\PDF\Exporter;
use ITRocks\Framework\Layout\Print_Model;
use ITRocks\Framework\Reflection\Attribute\Property\Values;
use ITRocks\Framework\Tools\Names;
use TCPDF;

/**
 * Everything to print a document using layout models
 */
class Model extends PDF\Output
{

	//--------------------------------------------------------------------------------------- $output
	#[Values(self::class)]
	public string $output = self::INLINE;

	//---------------------------------------------------------------------------------------- append
	/**
	 * Print objects into an existing $pdf object : pages are appended
	 *
	 * @param $pdf         PDF|TCPDF        The already instantiated and opened PDF object
	 * @param $objects     object[]         Objects to print, all must be of the same class
	 * @param $print_model Print_Model|null If not set, the first available print model will be taken
	 */
	public function append(PDF|TCPDF $pdf, array $objects, Print_Model $print_model = null) : void
	{
		if (!$print_model) {
			$class_name  = Builder::current()->sourceClassName(get_class(reset($objects)));
			$print_model = Dao::searchOne(['class_name' => $class_name], Print_Model::class, Dao::sort());
		}
		if ($print_model) {
			foreach ($objects as $object) {
				$this->appendObject($pdf, $object, $print_model);
			}
		}
	}

	//---------------------------------------------------------------------------------- appendObject
	/**
	 * Print object into an existing $pdf object : pages are appended
	 *
	 * @param $pdf         TCPDF|PDF   The already instantiated and opened PDF object
	 * @param $object      object      Object to print
	 * @param $print_model Print_Model If not set, the first available print model will be taken
	 */
	protected function appendObject(TCPDF|PDF $pdf, object $object, Print_Model $print_model) : void
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
	protected function newPdf() : PDF|TCPDF
	{
		/** @var $pdf PDF|TCPDF */
		$pdf = new PDF();
		$pdf->Open();
		return $pdf;
	}

	//----------------------------------------------------------------------------------------- print
	/**
	 * Print objects using a layout model, using PDF format
	 * This returns the raw content of the generated PDF file
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $objects     object[]         Objects to print, all must be of the same class
	 * @param $print_model Print_Model|null If not set, the first available print model will be taken
	 * @return string
	 */
	public function print(array $objects, Print_Model $print_model = null) : string
	{
		$first_object = reset($objects);
		if (!$print_model) {
			$class_name  = Builder::current()->sourceClassName(get_class($first_object));
			$print_model = Dao::searchOne(['class_name' => $class_name], Print_Model::class, Dao::sort());
		}

		$file_name = ($first_object instanceof Has_Print_File_Name)
			? $first_object->printFileName($objects)
			: Names::classToDisplay($print_model->class_name ?? '') . '.pdf';

		$pdf = $this->newPdf();
		$this->append($pdf, $objects, $print_model);
		/** @noinspection PhpUnhandledExceptionInspection Buffer output should not crash */
		return $pdf->Output($file_name, $this->output);
	}

}
