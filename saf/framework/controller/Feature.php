<?php
namespace SAF\Framework\Controller;

/**
 * Feature class
 */
abstract class Feature
{

	//---------------------------------------------------------------- the feature key value constant
	const FEATURE     = 'feature';

	//---------------------------------------------------------------------------- features constants
	const F_ADD       = 'add';
	const F_CLOSE     = 'close';
	const F_DEFAULT   = 'default';
	const F_DELETE    = 'delete';
	const F_DUPLICATE = 'duplicate';
	const F_EDIT      = 'edit';
	const F_EXPORT    = 'export';
	const F_IMPORT    = 'import';
	const F_LIST      = 'dataList';
	const F_OUTPUT    = 'output';
	const F_PRINT     = 'print';
	const F_REMOVE    = 'remove';
	const F_SELECT    = 'select';
	const F_TRANSFORM = 'transform';
	const F_WRITE     = 'write';

}
