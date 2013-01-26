<?php
namespace SAF\Framework;

class Acl_Group
{

	//-------------------------------------------------------------------------------------- $caption
	/**
	 * @var string
	 */
	public $caption;

	//----------------------------------------------------------------------------------------- $type
	/**
	 * @var string
	 * @values user,
	 */
	public $type;

	//--------------------------------------------------------------------------------------- $groups
	/**
	 * @contained
	 * @getter Aop::getCollection
	 * @var Acl_Link[]
	 */
	public $content;

	//--------------------------------------------------------------------------------------- $rights
	/**
	 * @contained
	 * @getter Aop::getCollection
	 * @var Acl_Right[]
	 */
	public $rights;

	//------------------------------------------------------------------------------------------- add
	/**
	 * Add an Acl_Link to $content or an Acl_Right to rights
	 *
	 * @param $object Acl_Link|Acl_Right
	 * @return Acl_Group
	 */
	public function add($object)
	{
		array_push(($object instanceof Acl_Right) ? $this->rights : $this->content, $object);
		return $this;
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Remove an Acl_Link from $content, or an Acl_Right from rights
	 *
	 * @param $object Acl_Link|Acl_Right
	 */
	public function remove($object)
	{
		if ($object instanceof Acl_Right) $collection =& $this->rights;
		else                              $collection =& $this->content;
		foreach ($collection as $key => $collection_object) {
			if ($object == $collection_object) {
				unset($collection[$key]);
			}
		}
	}

}
