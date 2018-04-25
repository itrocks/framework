<?php
namespace ITRocks\Framework\Widget\Cards\Annotation;

use ITRocks\Framework\Widget\Cards\Annotation;
use ITRocks\Framework\Widget\Cards\Property\Edit;

/**
 * Card edit annotation
 */
class Card_Edit_Annotation extends Annotation
{

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'card_edit';

	//---------------------------------------------------------------------- CARD_PROPERTY_CLASS_NAME
	const CARD_PROPERTY_CLASS_NAME = Edit::class;

}
