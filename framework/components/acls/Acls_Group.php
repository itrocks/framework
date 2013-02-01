<?php
namespace SAF\Framework;

/**
 * @representative name
 */
class Acls_Group
{

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public $name;

	//----------------------------------------------------------------------------------------- $type
	/**
	 * @var string
	 * @values template, software, module, object, feature, users_group, group, user,
	 */
	public $type;

	//-------------------------------------------------------------------------------------- $content
	/**
	 * @contained
	 * @getter getLinks
	 * @var Acls_Link[]
	 */
	public $content;

	//--------------------------------------------------------------------------------------- $rights
	/**
	 * @contained
	 * @getter getRights
	 * @var Acls_Right[] key is the property key
	 */
	public $rights;

	//------------------------------------------------------------------------------------------- add
	/**
	 * Adds an Acls_Link, an Acl_Group or an Acl_Link
	 *
	 * @param $object   Acls_Right|Acls_Group|Acls_Link
	 * @param $priority integer
	 * @return Acls_Group
	 */
	public function add($object, $priority = 1)
	{
		if ($object instanceof Acls_Right) {
			$this->rights[$object->key] = $object;
		}
		elseif ($object instanceof Acls_Group) {
			$this->content[$object->name] = new Acls_Link($this, $object, $priority);
		}
		elseif ($object instanceof Acls_Link) {
			$this->content[$object->content->name] = $object;
		}
		return $this;
	}

	//------------------------------------------------------------------------------------ getContent
	/**
	 * @return Acls_Group[]
	 */
	public function getContent()
	{
		$content = array();
		foreach ($this->content as $link) {
			$content[$link->content->name] = $link->content;
		}
		return $content;
	}

	//-------------------------------------------------------------------------------------- getLinks
	/**
	 * @return Acls_Link[]
	 */
	public function getLinks()
	{
		$links = $this->content;
		if (!isset($links)) {
			$links = array();
			foreach (Getter::getCollection($links, __NAMESPACE__ . "\\Acls_Link", $this) as $link) {
				$links[$link->content->name] = $link;
			}
			$this->content = $links;
		}
		return $links;
	}

	//------------------------------------------------------------------------------------- getRights
	/**
	 * @return Acls_Right[]
	 */
	public function getRights()
	{
		$rights = $this->rights;
		if (!isset($rights)) {
			$rights = array();
			foreach (Getter::getCollection($rights, __NAMESPACE__ . "\\Acls_Right", $this) as $right) {
				$rights[$right->key] = $right;
			}
			$this->rights = $rights;
		}
		return $rights;
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Removes an Acls_Right, an Acls_Group or an Acls_Link
	 *
	 * @param $object Acls_Right|Acls_Group|Acls_Link
	 */
	public function remove($object)
	{
		if ($object instanceof Acls_Right) {
			unset($this->rights[$object->key]);
		}
		elseif ($object instanceof Acls_Group) {
			unset($this->content[$object->name]);
		}
		elseif ($object instanceof Acls_Link) {
			unset($this->content[$object->content->name]);
		}
	}

}
