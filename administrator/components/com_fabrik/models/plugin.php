<?php
/**
 * Fabrik Admin Plugin Model
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @since       1.6
 */

// No direct access.
defined('_JEXEC') or die;

require_once 'fabmodeladmin.php';

/**
 * Fabrik Admin Plugin Model
 * Used for loading via ajax form plugins
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @since       3.0.6
 */

class FabrikModelPlugin extends JModel
{

	/**
	 * Render the plugins fields
	 *
	 * @return string
	 */

	public function render()
	{
		$pluginManager = JModel::getInstance('Pluginmanager', 'FabrikFEModel');
		$plugin = $pluginManager->getPlugIn($this->getState('plugin'), $this->getState('type'));
		$feModel = $this->getPluginModel();
		$plugin->getJForm()->model = $feModel;

		$data = $this->getData();
		JRequest::setVar('view', $this->getState('type'));
		$str = $plugin->onRenderAdminSettings($data, $this->getState('c'));
		JRequest::setVar('view', 'plugin');

		return $str;
	}

	/**
	 * Get the plugins data to bind to the form
	 *
	 * @return  array
	 */

	protected function getData()
	{
		$type = $this->getState('type');
		if ($type === 'validationrule')
		{
			$item = FabTable::getInstance('Element', 'FabrikTable');
			$item->load($this->getState('id'));
		}
		else
		{
			$feModel = $this->getPluginModel();
			$item = $feModel->getTable();
		}
		$data = (array) json_decode($item->params);
		$data['plugin'] = $this->getState('plugin');
		$data['params'] = (array) JArrayHelper::getValue($data, 'params', array());
		$data['params']['plugins'] = $this->getState('plugin');

		$data['validationrule']['plugin'] = $this->getState('plugin');
		$data['validationrule']['plugin_published'] = $this->getState('plugin_published');

		$c = $this->getState('c') + 1;

		// Add plugin published state, locations and events
		$state = (array) JArrayHelper::getValue($data, 'plugin_state');
		$locations = (array) JArrayHelper::getValue($data, 'plugin_locations');
		$events = (array) JArrayHelper::getValue($data, 'plugin_events');
		$data['params']['plugin_state'] = JArrayHelper::getValue($state, $c, 1);
		$data['plugin_locations'] = JArrayHelper::getValue($locations, $c);
		$data['plugin_events'] = JArrayHelper::getValue($events, $c);

		return $data;
	}

	/**
	 * Get the plugin model
	 *
	 * @return  object
	 */

	protected function getPluginModel()
	{
		$feModel = null;
		$type = $this->getState('type');
		if ($type !== 'validationrule')
		{
			// Set the parent model e.g. form/list
			$feModel = JModel::getInstance($type, 'FabrikFEModel');
			$feModel->setId($this->getState('id'));
		}
		return $feModel;
	}

	/**
	 * Render the initial plugin options, such as the plugin selector, and whether its rendered in front/back/both etc
	 *
	 * @return  string
	 */

	public function top()
	{
		$data = $this->getData();
		$c = $this->getState('c') + 1;
		$str = array();
		$str[] = '<div class="pane-slider content pane-down">';
		$str[] = '<fieldset class="adminform pluginContanier" id="formAction_' . $c . '"><ul>';
		$formName = 'com_fabrik.' . $this->getState('type') . '-plugin';
		$topForm = new JForm($formName, array('control' => 'jform'));
		$topForm->repeatCounter = $c;
		$xmlFile = JPATH_SITE . '/administrator/components/com_fabrik/models/forms/' . $this->getState('type') . '-plugin.xml';

		// Add the plugin specific fields to the form.
		$topForm->loadFile($xmlFile, false);

		$topForm->bind($data);

		// Filer the forms fieldsets for those starting with the correct $serachName prefix
		foreach ($topForm->getFieldsets() as $fieldset)
		{
			if ($fieldset->label != '')
			{
				$str[] = '<legend>' . $fieldset->label . '</legend>';
			}

			foreach ($topForm->getFieldset($fieldset->name) as $field)
			{
				$str[] = '<li>' . $field->label . $field->input . '</li>';
			}
		}
		$str[] = '</ul>';
		$str[] = '<div class="pluginOpts" style="clear:left"></div>';
		$str[] = '<a href="#" class="delete removeButton">' . JText::_('COM_FABRIK_DELETE') . '</a>';
		$str[] = '</fieldset>';
		$str[] = '</div>';
		return implode("\n", $str);
	}
}
