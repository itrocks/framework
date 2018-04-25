<?php
namespace ITRocks\Framework\Widget\Cards\Annotation;

use ITRocks\Framework\Widget\Cards\Annotation;
use ITRocks\Framework\Widget\Cards\Property\Card;

/**
 * Card display annotation
 */
class Card_Display_Annotation extends Annotation
{

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'card_display';

	//---------------------------------------------------------------------- CARD_PROPERTY_CLASS_NAME
	const CARD_PROPERTY_CLASS_NAME = Card::class;

}
