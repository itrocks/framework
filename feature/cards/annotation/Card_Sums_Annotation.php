<?php
namespace ITRocks\Framework\Feature\Cards\Annotation;

use ITRocks\Framework\Feature\Cards\Annotation;
use ITRocks\Framework\Feature\Cards\Property\Sum;

/**
 * Card sums annotation
 */
class Card_Sums_Annotation extends Annotation
{

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'card_sums';

	//---------------------------------------------------------------------- CARD_PROPERTY_CLASS_NAME
	const CARD_PROPERTY_CLASS_NAME = Sum::class;

}
