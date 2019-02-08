<?php
namespace ITRocks\Framework\Feature\Cards\Annotation;

use ITRocks\Framework\Feature\Cards\Annotation;
use ITRocks\Framework\Feature\Cards\Property\Card;

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
