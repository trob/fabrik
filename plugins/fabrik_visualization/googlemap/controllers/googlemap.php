<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.visualization.googlemap
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.controller');

/**
 * Fabrik Google Map Viz Controller
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.visualization.googlemap
 */

class FabrikControllerVisualizationgooglemap extends FabrikControllerVisualization
{
	function ajax_getMarkers($tmpl = 'default')
	{
		$viewName = 'googlemap';
		$usersConfig = JComponentHelper::getParams('com_fabrik');
		$model = $this->getModel($viewName);
		$id = JRequest::getInt('visualizationid', 0);
		$model->setId($id);
		$model->onAjax_getMarkers();
	}
}
