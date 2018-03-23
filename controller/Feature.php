<?php
namespace ITRocks\Framework\Controller;

/**
 * Feature class
 */
abstract class Feature
{

	//---------------------------------------------------------------------------- features constants
	const F_ADD           = 'add';
	const F_ADMIN         = 'admin';
	const F_API           = 'api';
	const F_BLANK         = 'blank';
	const F_CANCEL        = 'cancel';
	const F_CLOSE         = 'close';
	const F_CONFIRM       = 'confirm';
	const F_CUSTOM_DELETE = 'custom_delete';
	const F_CUSTOM_SAVE   = 'custom_save';
	const F_DEFAULT       = 'default';
	const F_DELETE        = 'delete';
	const F_DENIED        = 'denied';
	const F_DUPLICATE     = 'duplicate';
	const F_EDIT          = 'edit';
	const F_EXPORT        = 'export';
	const F_IMPORT        = 'import';
	const F_INSTALL       = 'install';
	const F_JSON          = 'json';
	const F_LIST          = 'dataList';
	const F_LOGIN         = 'login';
	const F_MODELS        = 'models';
	const F_OUTPUT        = 'output';
	const F_PRINT         = 'print';
	const F_REMOVE        = 'remove';
	const F_SELECT        = 'select';
	const F_SUPER         = 'superAdministrator';
	const F_TRANSFORM     = 'transform';
	const F_UNINSTALL     = 'uninstall';
	const F_VALIDATE      = 'validate';
	const F_WRITE         = 'write';

	//--------------------------------------------------------------------------------------- FEATURE
	const FEATURE = 'feature';

	//---------------------------------------------------------------------------------------- ON_SET
	const ON_SET = [self::F_LIST];

	//------------------------------------------------------------------------------------- READ_ONLY
	const READ_ONLY = [
		self::F_ADD, self::F_ADMIN, self::F_BLANK, self::F_CANCEL, self::F_CLOSE, self::F_DEFAULT,
		self::F_DENIED, self::F_EDIT, self::F_EXPORT, self::F_JSON, self::F_LIST, self::F_LOGIN,
		self::F_MODELS, self::F_OUTPUT, self::F_PRINT, self::F_SELECT
	];

}
