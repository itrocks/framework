<?php
namespace ITRocks\Framework\Updater\Migrate;

use ITRocks\Framework\Controller\Response;

/**
 * Apply changes that occurred for it.rocks version v0.1.1906 to the database
 *
 * Call /ITRocks/Framework/Updater/Migrate/Apply_1906/run
 */
class Apply1906
{
	use Settings;

	//------------------------------------------------------------------------------------------- run
	/**
	 * Controller function
	 *
	 * @return string
	 */
	public function run() : string
	{
		$this->migrateSettings([
			// older changes (in order to remove some compatibility code)
			'Framework\Widget\Data_List_Setting\Data_List_Settings' => 'Framework\Feature\List_Setting\Set',
			'Framework\Widget\Data_List_Setting\Property'           => 'Framework\Feature\List_Setting\Property',
			'Framework\Widget\Output_Setting\Output_Settings'       => 'Framework\Feature\Output_Setting\Set',
			'Framework\Setting\User_Setting'                        => 'Framework\Setting\User',
			// 1906
			'Framework\Widget\List_'  => 'Framework\Feature\List_',
			'Framework\Widget\Output' => 'Framework\Feature\Output'
		]);
		return Response::OK;
	}

}
