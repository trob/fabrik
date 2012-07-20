<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.facebookrecommendations
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

require_once JPATH_SITE . '/components/com_fabrik/models/element.php';

/**
 * Plugin element to render facebook recommendations widget
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.facebookrecommendations
 */

class plgFabrik_ElementFbrecommendations extends plgFabrik_Element
{

	/**
	 * Does the element have a label
	 * @var bool
	 */
	protected $hasLabel = false;

	protected $fieldDesc = 'INT(%s)';

	protected $fieldSize = '1';

	/**
	 * Draws the html form element
	 *
	 * @param   array  $data           to preopulate element with
	 * @param   int    $repeatCounter  repeat group counter
	 *
	 * @return  string	elements html
	 */

	function render($data, $repeatCounter = 0)
	{
		$params = $this->getParams();
		$str = FabrikHelperHTML::facebookGraphAPI($params->get('opengraph_applicationid'));
		$domain = $params->get('fbrecommendations_domain');
		$width = $params->get('fbrecommendations_width', 300);
		$height = $params->get('fbrecommendations_height', 300);
		$header = $params->get('fbrecommendations_header', 1) == 1 ? 'true' : 'false';
		$border = $params->get('fbrecommendations_border', '');
		$font = $params->get('fbrecommendations_font', 'arial');
		$colorscheme = $params->get('fbrecommendations_colorscheme', 'light');
		$str .= '<fb:recommendations site="' . $domain . '" width="' . $width . '" height="' . $height . '" header="' . $header . '" colorscheme="' . $colorscheme . '" font="' . $font . '" border_color="' . $border . '" />';
		return $str;
	}

	/**
	 * Returns javascript which creates an instance of the class defined in formJavascriptClass()
	 *
	 * @param   int  $repeatCounter  repeat group counter
	 *
	 * @return  string
	 */

	public function elementJavascript($repeatCounter)
	{
		$id = $this->getHTMLId($repeatCounter);
		$opts = $this->getElementJSOptions($repeatCounter);
		$opts = json_encode($opts);
		return "new FbRecommendations('$id', $opts)";
	}

}
?>