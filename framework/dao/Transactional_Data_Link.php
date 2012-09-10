<?php
namespace SAF\Framework;

interface Transactional_Data_Link
{

	//----------------------------------------------------------------------------------------- begin
	/**
	 * Begin a transaction (non-transactional SQL engines will do nothing and return null)
	 *
	 * @return boolean | null true if begin succeeds, false if error, null if not a transactional SQL engine
	 */
	public function begin();
	
	//---------------------------------------------------------------------------------------- commit
	/**
	 * Commit a transaction (non-transactional SQL engines will do nothing and return null)
	 *
	 * @return boolean | null true if commit succeeds, false if error, null if not a transactional SQL engine
	 */
	public function commit();

	//-------------------------------------------------------------------------------------- rollback
	/**
	 * Rollback a transaction (non-transactional SQL engines will do nothing and return null)
	 *
	 * @return boolean | null true if commit succeeds, false if error, null if not a transactional SQL engine
	 */
	public function rollback();

}
