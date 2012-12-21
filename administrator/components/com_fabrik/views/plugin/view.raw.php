<?php
/**
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View to grab plugin form fields.
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @since       3.0.6
 */

class fabrikViewPlugin extends JView
{

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  template
	 *
	 * @return  void
	 */

	public function display($tpl = null)
	{
		$model = $this->getModel();
		$this->setStates();

		if (JRequest::getVar('task') == 'top')
		{
			echo $model->top();
			return;
		}
		echo $model->render();
	}

	/**
	 * Set the model state from request
	 *
	 * @return  void
	 */

	protected function setStates()
	{
		$model = $this->getModel();
		$app = JFactory::getApplication();
		$input = $app->input;
		$model->setState('type', $input->get('type'));
		$model->setState('plugin', $input->get('plugin'));
		$model->setState('c', $input->getInt('c'));
		$model->setState('id', $input->getInt('id', 0));
		$model->setState('plugin_published', $input->get('plugin_published'));
	}

}
