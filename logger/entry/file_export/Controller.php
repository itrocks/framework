<?php
namespace ITRocks\Framework\Logger\Entry\File_Export;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Logger\Entry;
use ITRocks\Framework\Logger\Entry\File_Export;

/**
 * Log export controller
 */
class Controller implements Feature_Controller
{

	//--------------------------------------------------------------------------------------- FEATURE
	const FEATURE = 'fileExport';

	//------------------------------------------------------------------------------------------- run
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return mixed
	 */
	public function run(Parameters $parameters, array $form, array $files)
	{
		/** @var $log_entries Entry[] */
		$log_entries = $parameters->getSelectedObjects($form);
		/** @noinspection PhpUnhandledExceptionInspection class */
		Builder::create(File_Export::class)->exportLogEntries($log_entries);
		return '';
	}

}
