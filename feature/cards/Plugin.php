<?php
namespace ITRocks\Framework\Feature\Cards;

use ITRocks\Framework\Feature\Cards\Annotation\Card_Columns_Annotation;
use ITRocks\Framework\Feature\Cards\Annotation\Card_Display_Annotation;
use ITRocks\Framework\Feature\Cards\Annotation\Card_Edit_Annotation;
use ITRocks\Framework\Feature\Cards\Annotation\Card_Groups_Annotation;
use ITRocks\Framework\Feature\Cards\Annotation\Card_Sums_Annotation;
use ITRocks\Framework\Plugin\Installable;
use ITRocks\Framework\Plugin\Installable\Installer;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Reflection\Annotation\Parser;

/**
 * Display your documents into an interactive multiple dimensional cards viewer
 *
 * @feature_off Not ready
 */
class Plugin implements Installable, Registerable
{

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return 'Display your documents into an interactive multiple dimensional cards viewer';
	}

	//--------------------------------------------------------------------------------------- install
	/**
	 * @param $installer Installer
	 */
	public function install(Installer $installer)
	{
		$installer->addPlugin($this);
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$register->setAnnotations(Parser::T_CLASS, [
			'card_columns' => Card_Columns_Annotation::class,
			'card_display' => Card_Display_Annotation::class,
			'card_edit'    => Card_Edit_Annotation::class,
			'card_groups'  => Card_Groups_Annotation::class,
			'card_sums'    => Card_Sums_Annotation::class
		]);
	}

}
