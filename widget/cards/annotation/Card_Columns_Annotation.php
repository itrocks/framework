<?php
namespace ITRocks\Framework\Widget\Cards\Annotation;

use ITRocks\Framework\Widget\Cards\Annotation;
use ITRocks\Framework\Widget\Cards\Property\Column;

/**
 * Card columns annotation
 */
class Card_Columns_Annotation extends Annotation
{

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'card_columns';

	//---------------------------------------------------------------------- CARD_PROPERTY_CLASS_NAME
	const CARD_PROPERTY_CLASS_NAME = Column::class;

}
