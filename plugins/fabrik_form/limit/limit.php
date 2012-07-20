<?php
/**
* @package     Joomla.Plugin
* @subpackage  Fabrik.form.limit
* @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
* @license     GNU General Public License version 2 or later; see LICENSE.txt
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-form.php';

/**
* Form limit submissions plugin
*
* @package     Joomla.Plugin
* @subpackage  Fabrik.form.limit
* @since       3.0
*/

class plgFabrik_FormLimit extends plgFabrik_Form {

	/**
	 * Process the plugin, called when form is loaded
	 *
	 * @param   object  $params      plugin parameters
	 * @param   object  &$formModel  form model
	 *
	 * @return  void
	 */

	public function onLoad($params, &$formModel)
	{
		return $this->_process($params, $formModel);
	}

	/**
	 * Process the plugin
	 *
	 * @param   object  $params      plugin params
	 * @param   object  &$formModel  form model
	 *
	 * @return  bool
	 */
	private function _process($params, &$formModel)
	{
		$user = JFactory::getUser();
		$db = FabrikWorker::getDbo();
		$query = $db->getQuery(true);
		if ($params->get('limit_allow_anonymous'))
		{
			return true;
		}
		if (JRequest::getCmd('view') === 'details' || $formModel->getRowId() > 0)
		{
			return true;
		}
		$listid = (int) $params->get('limit_table');
		if ($listid === 0)
		{
			// Use the limit setting supplied in the admin params
			$limit = (int) $params->get('limit_length');
		}
		else
		{
			// Look up the limit from the table spec'd in the admin params
			$listModel = JModel::getInstance('List', 'FabrikFEModel');
			$listModel->setId($listid);
			$max = $db->quoteName(FabrikString::shortColName($params->get('limit_max')));
			$userfield = $db->quoteName(FabrikString::shortColName($params->get('limit_user')));
			$query->select($max)->from($listModel->getTable()->db_table_name)->where($userfield  . ' = ' . (int) $user->get('id'));
			$db->setQuery($query);
			$limit = (int) $db->loadResult();

		}
		$field = $params->get('limit_userfield');
		$listModel = $formModel->getlistModel();
		$list = $listModel->getTable();
		$db = $listModel->getDb();
		$query->clear()->select(' COUNT(' . $field . ')')->from($list->db_table_name)->where($field . ' = ' . (int) $user->get('id'));
		$db->setQuery($query);

		$c = $db->loadResult();
		if ($c >= $limit)
		{
			$msg = $params->get('limit_reached_message', JText::sprintf('PLG_FORM_LIMIT_LIMIT_REACHED', $limit));
			$msg = str_replace('{limit}', $limit, $msg);
			JError::raiseNotice(1, $msg);
			return false;
		}
		else
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::sprintf('PLG_FORM_LIMIT_ENTRIES_LEFT_MESSAGE', $limit - $c, $limit));
		}
		return true;
	}

}
