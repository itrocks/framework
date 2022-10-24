<?php
namespace ITRocks\Framework\RAD\Skin;

use ITRocks\Framework\RAD\Skin;

/**
 * A skin set
 */
class Set
{

	//----------------------------------------------------------------------------------------- skins
	/**
	 * @return Skin[]
	 */
	public function skins()
	{
		$skins = [];
		foreach (scandir(__DIR__ . '../../skins') as $file_name) {
			if (!str_starts_with($file_name, DOT)) {
				$skins[] = new Skin($file_name);
			}
		}
		return $skins;
	}

}
