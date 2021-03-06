<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * This is a file to add template specific chrome to pagination rendering.
 *
 * pagination_list_footer
 *	Input variable $list is an array with offsets:
 *		$list[prefix]		: string
 *		$list[limit]		: int
 *		$list[limitstart]	: int
 *		$list[total]		: int
 *		$list[limitfield]	: string
 *		$list[pagescounter]	: string
 *		$list[pageslinks]	: string
 *
 * pagination_list_render
 *	Input variable $list is an array with offsets:
 *		$list[all]
 *			[data]		: string
 *			[active]	: boolean
 *		$list[start]
 *			[data]		: string
 *			[active]	: boolean
 *		$list[previous]
 *			[data]		: string
 *			[active]	: boolean
 *		$list[next]
 *			[data]		: string
 *			[active]	: boolean
 *		$list[end]
 *			[data]		: string
 *			[active]	: boolean
 *		$list[pages]
 *			[{PAGE}][data]		: string
 *			[{PAGE}][active]	: boolean
 *
 * pagination_item_active
 *	Input variable $item is an object with fields:
 *		$item->base	: integer
 *		$item->prefix	: string
 *		$item->link	: string
 *		$item->text	: string
 *
 * pagination_item_inactive
 *	Input variable $item is an object with fields:
 *		$item->base	: integer
 *		$item->prefix	: string
 *		$item->link	: string
 *		$item->text	: string
 *
 * This gives template designers ultimate control over how pagination is rendered.
 *
 * NOTE: If you override pagination_item_active OR pagination_item_inactive you MUST override them both
 */
/*
function fabrik_pagination_list_footer($list)
{
	// Initialise variables.
	$lang = JFactory::getLanguage();
	$html = "<div class=\"container\"><div class=\"pagination\">\n";

	$html .= "\n<div class=\"limit\">" . JText::_('JGLOBAL_DISPLAY_NUM') . $list['limitfield'] . "</div>";
	$html .= $list['pageslinks'];
	$html .= "\n<div class=\"limit\">" . $list['pagescounter'] . "</div>";

	$html .= "\n<input type=\"hidden\" name=\"" . $list['prefix'] . "limitstart\" value=\"" . $list['limitstart'] . "\" />";
	$html .= "\n</div></div>";

	return $html;
}
 */

function fabrik_pagination_list_footer($list, $paginator)
{
	// Initialize variables
	$html = array();
	$html[] = '<div class="list-footer">';
	$limitLabel = $paginator->showDisplayNum ? JText::_('COM_FABRIK_DISPLAY_NUM') : '';
	$html[] = '<div class="limit input-prepend"><button class="add-on">' . $limitLabel  . '</button>' . $list['limitfield'] . '</div>';
	$html[] = $list['pageslinks'];
	$html[] = '<div class="counter">' . $list['pagescounter'] . '</div>';
	$html[] = '<input type="hidden" name="limitstart' . $paginator->_id . '" id="limitstart' . $paginator->_id . '" value="' . $list['limitstart'] . '" />';
	$html[] = '</div>';
	return implode("\n", $html);
}



function fabrik_pagination_item_active(&$item, $listid)
{
	$app = JFactory::getApplication();
	if ($app->isAdmin())
	{
		// return '<a title="' . $item->text . '" href="#" onclick="oTable.fabrikNav(' . $item->base . ');return false;">' . $item->text . '</a>';
		return '<a href="' . $item->base . '" title="' . $item->text . '">' . $item->text . '</a>';
	}
	else
	{
		return '<a title="' . $item->text . '" href="' . $item->link . '" class="pagenav">' . $item->text . '</a>';
	}

	if ($item->base > 0)
		return '<a href="' . $item->base . '" title="' . $item->text . '">' . $item->text . '</a>';
	else
		return '<a href="0" title="' . $item->text . '">' . $item->text . '</a>';
}

function fabrik_pagination_item_inactive(&$item)
{
	return '<a href="#">' . $item->text . "</a>";
}

