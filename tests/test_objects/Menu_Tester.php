<?php
namespace SAF\Framework\Tests;
use AopJoinpoint;
use SAF\Framework\Aop;
use SAF\Framework\Menu_Block;
use SAF\Framework\Menu_Item;
use SAF\Framework\Plugin;

abstract class Menu_Tester implements Plugin
{

	//---------------------------------------------------------------------------------- menuInitDemo
	/**
	 * Initialises a demo menu object
	 *
	 * @param $joinpoint AopJoinpoint
	 */
	public static function menuInitDemo(AopJoinpoint $joinpoint)
	{
		static $antiloop = false;
		if ($antiloop) return;
		$antiloop = true;
		$menu = $joinpoint->getObject();
		if ($menu->title) return;
		$menu->title = "Menu";
		$block = new Menu_Block(); $block->title = "Sample menu"; $block->color = "black";
		$item = new Menu_Item(); $item->caption = "Login"; $item->link = "/User/login";
		$block->items[] = $item;
		$item = new Menu_Item(); $item->caption = "Sales order Nr 1"; $item->link = "/Sales_Order/1";
		$block->items[] = $item;
		$item = new Menu_Item(); $item->caption = "Addresses"; $item->link = "/Addresses";
		$block->items[] = $item;
		$item = new Menu_Item(); $item->caption = "Cities"; $item->link = "/Cities";
		$block->items[] = $item;
		$menu->blocks[] = $block;
		$block = new Menu_Block(); $block->title = "Sales"; $block->color = "green";
		$item = new Menu_Item(); $item->caption = "Quotes"; $item->link = "/Sales_Quotes";
		$block->items[] = $item;
		$item = new Menu_Item(); $item->caption = "Orders"; $item->link = "/Sales_Orders";
		$block->items[] = $item;
		$item = new Menu_Item(); $item->caption = "Invoices"; $item->link = "/Sales_Invoices";
		$block->items[] = $item;
		$menu->blocks[] = $block;
		$block = new Menu_Block(); $block->title = "Sales data"; $block->color = "green";
		$item = new Menu_Item(); $item->caption = "Sales items"; $item->link = "/Items";
		$block->items[] = $item;
		$item = new Menu_Item(); $item->caption = "Clients"; $item->link = "/Client_Organisations";
		$block->items[] = $item;
		$menu->blocks[] = $block;
		$antiloop = false;
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		aop_add_before("read SAF\\Framework\\Menu->blocks", array(__CLASS__, "menuInitDemo"));
		aop_add_before("read SAF\\Framework\\Menu->title",  array(__CLASS__, "menuInitDemo"));
	}

}
