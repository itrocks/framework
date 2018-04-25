<?php
namespace ITRocks\Framework\Widget\Cards\Annotation;

use ITRocks\Framework\Widget\Cards\Annotation;
use ITRocks\Framework\Widget\Cards\Property\Group;

/**
 * Card groups annotation
 */
class Card_Groups_Annotation extends Annotation
{

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'card_groups';

	//---------------------------------------------------------------------- CARD_PROPERTY_CLASS_NAME
	const CARD_PROPERTY_CLASS_NAME = Group::class;

}
