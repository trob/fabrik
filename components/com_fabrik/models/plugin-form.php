<?php
/**
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

/**
 * Fabrik Plugin From Model
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @since       3.0
 */

class PlgFabrik_Form extends FabrikPlugin
{
	/**@var array formatted email data */
	protected $emailData = null;

	/** @var string html to return from plugin rendering */
	protected $html = '';

	/**
	 * Run from list model when deleting rows
	 *
	 * @param   object  $params      plugin parameters
	 * @param   object  &$formModel  form model
	 * @param   array   &$groups     list data for deletion
	 *
	 * @return  bool
	 */

	public function onDeleteRowsForm($params, &$formModel, &$groups)
	{
		return true;
	}

	/**
	 * Run right at the beginning of the form processing
	 *
	 * @param   object  $params      plpugin params
	 * @param   object  &$formModel  form model
	 *
	 * @return	bool
	 */

	public function onBeforeProcess($params, &$formModel)
	{
		return true;
	}

	/**
	 * Run if form validation fails
	 *
	 * @param   object  $params      plpugin params
	 * @param   object  &$formModel  form model
	 *
	 * @return	bool
	 */

	public function onError($params, &$formModel)
	{

	}

	/**
	 * Run before table calculations are applied
	 *
	 * @param   object  $params      plpugin params
	 * @param   object  &$formModel  form model
	 *
	 * @return	bool
	 */

	public function onBeforeCalculations($params, &$formModel)
	{
		return true;
	}

	/**
	 * Run right at the end of the form processing
	 * form needs to be set to record in database for this to hook to be called
	 *
	 * @param   object  $params      plugin params
	 * @param   object  &$formModel  form model
	 *
	 * @return	bool
	 */

	public function onAfterProcess($params, &$formModel)
	{
		return true;
	}

	/**
	 * Alter the returned plugin manager's result
	 *
	 * @param   string  $method      method
	 * @param   object  &$formModel  form model
	 *
	 * @return bool
	 */

	public function customProcessResult($method, &$formModel)
	{
		return true;
	}

	/**
	 * Sets up HTML to be injected into the form's bottom
	 *
	 * @param   object  $params     params
	 * @param   object  $formModel  form model
	 *
	 * @return void
	 */

	public function getBottomContent($params, $formModel)
	{
		$this->html = '';
	}

	/**
	 * Inject custom html into the bottom of the form
	 *
	 * @param   int  $c  plugin counter
	 *
	 * @return  string  html
	 */

	public function getBottomContent_result($c)
	{
		return $this->html;
	}

	/**
	 * Store the html to insert at the top of the form
	 *
	 * @param   object  $params     params
	 * @param   object  $formModel  form model
	 *
	 * @return  bool
	 */

	public function getTopContent($params, $formModel)
	{
		$this->html = '';
	}

	/**
	 * Get any html that needs to be written at the top of the form
	 *
	 * @return  string  html
	 */

	public function getTopContent_result()
	{
		return $this->html;
	}

	/**
	 * Sets up any end html (after form close tag)
	 *
	 * @param   object  $params     plugin params
	 * @param   object  $formModel  form model
	 *
	 * @return  void
	 */

	public function getEndContent($params, $formModel)
	{
		$this->html = '';
	}

	/**
	 * Get any html that needs to be written after the form close tag
	 *
	 * @return	string	html
	 */

	public function getEndContent_result()
	{
		return $this->html;
	}

	/**
	 * Convert the posted form data to the data to be shown in the email
	 * e.g. radio buttons swap their values for the value's label
	 *
	 * @return array email data
	 */

	public function getEmailData()
	{
		if (isset($this->emailData))
		{
			return $this->emailData;
		}
		$model = $this->formModel;
		if (is_null($model->formDataWithTableName))
		{
			return array();
		}
		$model->isAjax();
		/* $$$rob don't render the form - there's no need and it gives a warning about an unfound rowid
		 * $$$ rob also it sets teh fromModels rowid to an + int even if we are submitting a new form
		 * which means that form plug-ins set to run on new only don't get triggered if they appear after
		 * fabrikemail/fabrikreceipt
		 * Now instead the pk value is taken from the tableModel->lastInsertId and inserted at the end of this method
		 *$model->render();
		 */

		$listModel = $model->getListModel();
		$table = is_object($listModel) ? $listModel->getTable() : null;

		$model->setEditable(false);
		if (is_object($listModel))
		{
			$joins = $listModel->getJoins();
			$model->getJoinGroupIds($joins);
		}

		$params = $model->getParams();

		$this->emailData = array();

		// $$$ hugh - temp foreach fix
		$groups = $model->getGroupsHiarachy();

		foreach ($groups as $gkey => $groupModel)
		{
			$groupParams = $groupModel->getParams();

			// Check if group is acutally a table join
			$repeatGroup = 1;
			$foreignKey = null;
			if ($groupModel->canRepeat())
			{
				if ($groupModel->isJoin())
				{
					$joinModel = $groupModel->getJoinModel();
					$joinTable = $joinModel->getJoin();
					$foreignKey = '';
					if (is_object($joinTable))
					{
						$foreignKey = $joinTable->table_join_key;

						// Need to duplicate this perhaps per the number of times
						// that a repeat group occurs in the default data?
						if (array_key_exists($joinTable->id, $model->formDataWithTableName['join']))
						{
							$elementModels = $groupModel->getPublishedElements();
							reset($elementModels);
							$tmpElement = current($elementModels);
							$smallerElHTMLName = $tmpElement->getFullName(false, true, false);
							$repeatGroup = count($model->formDataWithTableName['join'][$joinTable->id][$smallerElHTMLName]);
						}
						else
						{
							if (!$groupParams->get('repeat_group_show_first'))
							{
								continue;
							}
						}
					}
				}
				else
				{
					/* $$$ rob 19/03/2012 - deprecated?
					 * repeat groups which arent joins
					 * $elementModels = $groupModel->getPublishedElements();
					foreach ($elementModels as $tmpElement) {
					    $smallerElHTMLName = $tmpElement->getFullName(false, true, false);
					    if (is_array($model->formDataWithTableName)) {
					        if (array_key_exists($smallerElHTMLName . '_raw', $model->formDataWithTableName)) {
					            $d = $model->formDataWithTableName[$smallerElHTMLName . '_raw'];
					        } else {
					            $d = @$model->formDataWithTableName[$smallerElHTMLName];
					        }
					        $d = FabrikWorker::JSONtoData($d, true);
					        $c = count($d);
					        if ($c > $repeatGroup) { $repeatGroup = $c;}
					    }
					} */
				}
			}
			$groupModel->repeatTotal = $repeatGroup;
			$group = $groupModel->getGroup();
			$aSubGroups = array();
			for ($c = 0; $c < $repeatGroup; $c++)
			{
				$aSubGroupElements = array();
				$elementModels = $groupModel->getPublishedElements();
				foreach ($elementModels as $elementModel)
				{
					// Force reload?
					$elementModel->defaults = null;
					$elementModel->_repeatGroupTotal = $repeatGroup - 1;
					$element = $elementModel->getElement();

					$k = $elementModel->getFullName(false, true, false);
					$key = $elementModel->getFullName(true, true, false);

					// Used for working out if the element should behave as if it was
					// in a new form (joined grouped) even when editing a record
					$elementModel->inRepeatGroup = $groupModel->canRepeat();
					$elementModel->_inJoin = $groupModel->isJoin();
					$elementModel->setEditable(false);

					if ($elementModel->_inJoin)
					{
						if ($elementModel->inRepeatGroup)
						{
							if (!array_key_exists($k . '_raw', $this->emailData))
							{
								$this->emailData[$k . '_raw'] = array();
							}
							$this->emailData[$k . '_raw'][] = JArrayHelper::getValue($model->formDataWithTableName['join'][$group->join_id][$k], $c);
						}
						else
						{
							$this->emailData[$k . '_raw'] = $model->formDataWithTableName['join'][$group->join_id][$k];
						}
					}
					else
					{
						// @TODO do we need to check if none -joined repeat groups have their data set out correctly?
						if ($elementModel->isJoin())
						{
							$join = $elementModel->getJoinModel()->getJoin();
							$this->emailData[$k . '_raw'] = $model->formDataWithTableName['join'][$join->id][$k];
						}
						elseif (array_key_exists($key, $model->formDataWithTableName))
						{
							$rawval = JArrayHelper::getValue($model->formDataWithTableName, $k . '_raw', '');
							if ($rawval == '')
							{
								$this->emailData[$k . '_raw'] = $model->formDataWithTableName[$key];
							}
							else
							{
								/* things like the user element only have their raw value filled in at this point
								 * so don't overwrite that with the blank none-raw value
								 * the none-raw value is add in getEmailValue()
								 */
								$this->emailData[$k . '_raw'] = $rawval;
							}
						}
					}
					/* $$$ hugh - need to poke data into $elementModel->_form->_data as it is needed
					 * by CDD getOptions when building the query, to constrain the WHERE clause with
					 * selected FK value.
					 */

					// $$$ rob in repeat join groups this isnt really efficient as you end up reformatting the data $c times
					$elementModel->getFormModel()->data = $model->formDataWithTableName;
					// $$$ hugh - for some reason, CDD keys themselves are missing form emailData, if no selection was made?
					// (may only be on AJAX submit)
					$email_value = '';
					if (array_key_exists($k . '_raw', $this->emailData))
					{
						$email_value = $this->emailData[$k . '_raw'];
					}
					elseif (array_key_exists($k, $this->emailData))
					{
						$email_value = $this->emailData[$k];
					}
					$this->emailData[$k] = $elementModel->getEmailValue($email_value, $model->formDataWithTableName, $c);
					if ($elementModel->inRepeatGroup && $elementModel->_inJoin)
					{
						$this->emailData['join'][$groupModel->getGroup()->join_id][$k . '_raw'] = $this->emailData[$k . '_raw'];
						$this->emailData['join'][$groupModel->getGroup()->join_id][$k] = $this->emailData[$k];
					}
					if ($elementModel->isJoin())
					{
						$this->emailData['join'][$elementModel->getJoinModel()->getJoin()->id][$k . '_raw'] = $this->emailData[$k . '_raw'];
						$this->emailData['join'][$elementModel->getJoinModel()->getJoin()->id][$k] = $this->emailData[$k];
					}
				}
			}
		}
		if (is_object($listModel))
		{
			$pk = FabrikString::safeColNameToArrayKey($listModel->getTable()->db_primary_key);
			$this->emailData[$pk] = $listModel->lastInsertId;
			$this->emailData[$pk . '_raw'] = $listModel->lastInsertId;
		}
		return $this->emailData;
	}

	/**
	 * Get the class to manage the plugin
	 * to ensure that the file is loaded only once
	 *
	 * @param   array   &$srcs   Scripts previously loaded
	 * @param   string  $script  Script to load once class has loaded (defaults to plugins/fabrik_form/{name}/{name}.js
	 *
	 * @since   3.1b
	 *
	 * @return void
	 */

	public function formJavascriptClass($params, $formModel)
	{
		$ext = FabrikHelperHTML::isDebug() ? '.js' : '-min.js';
		$name = $this->get('_name');
		static $jsClasses;
		if (!isset($jsClasses))
		{
			$jsClasses = array();
		}

		// Load up the default script
		$script = 'plugins/fabrik_form/' . $name . '/' . $name . $ext;
		if (empty($jsClasses[$script]))
		{
			$formModel->formPluginShim[] = $script;
			$jsClasses[$script] = 1;
		}
	}

	/**
	 * Get a list of admins which should receive emails
	 *
	 * @return  array  admin user objects
	 */

	protected function getAdminInfo()
	{
		$db = JFactory::getDBO(true);
		$query = $db->getQuery();
		$query->select(' id, name, email, sendEmail')->from('#__users')->where('WHERE sendEmail = "1"');
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		return $rows;
	}

}
