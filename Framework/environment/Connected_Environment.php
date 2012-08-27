<?php

class Connected_Environment extends Environment
{

	/**
	 * @var Data_Link
	 */
	private $data_link;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(Data_Link $data_link)
	{
		parent::__construct();
		$this->setDataLink($data_link);
	}

	//---------------------------------------------------------------------------------- beginUpdates
	public function beginUpdates()
	{
		if (method_exists($this->data_link, "begin")) {
			$this->getDataLink()->begin();
		}
	}

	//--------------------------------------------------------------------------------- commitUpdates
	public function commitUpdates()
	{
		if (method_exists($this->data_link, "commit")) {
			$this->getDataLink()->commit();
		}
	}

	//---------------------------------------------------------------------------------------- delete
	/**
	 * @param Object $object
	 */
	public function delete($object)
	{
		return $this->getDataLink()->delete($object);
	}

	//------------------------------------------------------------------------------------ getCurrent
	/**
	 * @return Connected_Environment
	 */
	public static function getCurrent()
	{
		return parent::getCurrent();
	}

	//----------------------------------------------------------------------------------- getDataLink
	/**
	 * @return Data_Link
	 */
	public function getDataLink()
	{
		return $this->data_link;
	}

	//--------------------------------------------------------------------------------------- readAll
	/**
	 * @param  string $object_class
	 * @return Object[]
	 */
	public function readAll($object_class)
	{
		return $this->getDataLink()->readAll($object_class);
	}

	//--------------------------------------------------------------------------------------- replace
	/**
	 * @param  Object $destination
	 * @param  Object $source
	 * @return Object
	 */
	public function replace($destination, $source)
	{
		return $this->getDataLink()->replace($destination, $source);
	}

	//---------------------------------------------------------------------------------------- search
	/**
	 * @param  Object $what
	 * @return Object[]
	 */
	public function search($what)
	{
		return $this->getDataLink()->search($what);
	}

	//----------------------------------------------------------------------------------- searchFirst
	/**
	 * @param  Object $what
	 * @return Object
	 */
	public function searchFirst($what)
	{
		return $this->getDataLink()->searchFirst($what);
	}

	//----------------------------------------------------------------------------------- setDataLink
	/**
	 * @param  Data_Link $data_link
	 * @return Connected_Environment
	 */
	public function setDataLink(Data_Link $data_link)
	{
		$this->data_link = $data_link;
		return $this;
	}

	//----------------------------------------------------------------------------------------- write
	/**
	 * @param Object $object
	 */
	public function write($object)
	{
		$this->getDataLink()->write($object);
	}

}
