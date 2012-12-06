<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.list.email
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

require_once COM_FABRIK_FRONTEND . '/models/plugin-list.php';

/**
 * Email list plugin model
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.list.email
 * @since       3.0
 */

class plgFabrik_ListEmail extends plgFabrik_List
{

	protected $buttonPrefix = 'email';

	var $name = "plgFabrik_ListEmail";

	/**
	 * pop up window
	 *
	 * @deprecated - not used
	 *
	 * @return  void
	 */

	public function onPopupwin()
	{
		echo ' hre lklfsd k popupwin';
	}

	/**
	 * Can the plug-in select list rows
	 *
	 * @return  bool
	 */

	public function canSelectRows()
	{
		return $this->canUse();
	}

	/**
	 * Get the parameter name that defines the plugins acl access
	 *
	 * @return  string
	 */

	protected function getAclParam()
	{
		return 'emailtable_access';
	}

	/**
	 * Prep the button if needed
	 *
	 * @param   object  $params  plugin params
	 * @param   object  &$model  list model
	 * @param   array   &$args   arguements
	 *
	 * @return  bool;
	 */

	public function button($params, &$model, &$args)
	{
		parent::button($params, $model, $args);
		return true;
	}

	/**
	 * Get the button label
	 *
	 * @return  string
	 */

	protected function buttonLabel()
	{
		return $this->getParams()->get('email_button_label', parent::buttonLabel());
	}

	/**
	 * Return the javascript to create an instance of the class defined in formJavascriptClass
	 *
	 * @param   object  $params  plugin parameters
	 * @param   object  $model   list model
	 * @param   array   $args    array [0] => string table's form id to contain plugin
	 *
	 * @return bool
	 */

	public function onLoadJavascriptInstance($params, $model, $args)
	{
		parent::onLoadJavascriptInstance($params, $model, $args);
		$opts = $this->getElementJSOptions($model);
		$opts->renderOrder = $this->renderOrder;
		$opts = json_encode($opts);
		$this->jsInstance = "new FbListEmail($opts)";
		return true;
	}

	/**
	 * Get the html to create the <to> list
	 *
	 * @return string
	 */

	public function getToField()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$this->_id = $input->getInt('id');
		$params = $this->getParams();
		$renderOrder = $input->getInt('renderOrder');
		$toType = $params->get('emailtable_to_type');
		$toType = is_array($toType) ? $toType[$renderOrder] : $toType;
		if ($toType == 'field')
		{
			$to = $params->get('emailtable_to');
			$to = is_array($to) ? $to[$renderOrder] : $to;
			return '<input name="list_email_to" id="list_email_to" value="' . $to . '" readonly="true" />';
		}
		else
		{
			return $this->formModel->getElementList('list_email_to');
		}
	}

	/**
	 * Are attachements allowed?
	 *
	 * @return bool
	 */

	public function getAllowAttachment()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$renderOrder = $input->getInt('renderOrder');
		$params = $this->getParams();
		$allow = $params->get('emailtable_allow_attachment');
		return $allow[$renderOrder];
	}

	/**
	 * Get the subject line
	 *
	 * @return  string
	 */

	public function getSubject()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$renderOrder = $input->getInt('renderOrder');
		$params = $this->getParams();
		$var = $params->get('email_subject');
		return is_array($var) ? $var[$renderOrder] : $var;
	}

	/**
	 * Get the email message
	 *
	 * @return  string
	 */

	public function getMessage()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$renderOrder = $input->getInt('renderOrder');
		$params = $this->getParams();
		$var = $params->get('email_message');
		return is_array($var) ? $var[$renderOrder] : $var;
	}

	/**
	 * Get the selected records
	 *
	 * @param   string  $key      key
	 * @param   bool    $allData  data
	 *
	 * @return	array	rows
	 */

	public function getRecords($key = 'ids', $allData = false)
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		if ($key === 'recordids')
		{
			$ids = explode(',', $input->get($key, '', 'string'));
		}
		else
		{
			$ids = (array) $input->get($key, array(), 'array');
		}

		JArrayHelper::toInteger($ids);
		if (empty($ids))
		{
			JError::raiseError(400, JText::_('PLG_LIST_EMAIL_ERR_NO_RECORDS_SELECTED'));
			jexit();
		}
		$renderOrder = $input->getInt('renderOrder');
		$params = $this->getParams();
		$model = $this->listModel;
		$pk = $model->getTable()->db_primary_key;
		$pk2 = FabrikString::safeColNameToArrayKey($pk) . '_raw';
		$whereClause = "($pk IN (" . implode(",", $ids) . "))";
		$cond = $params->get('emailtable_condition');
		if (trim($cond) !== '')
		{
			$whereClause .= ' AND (' . $cond . ')';
		}
		$model->setPluginQueryWhere($this->buttonPrefix, $whereClause);
		$data = $model->getData();
		if ($allData)
		{
			return $data;
		}
		$return = array();
		foreach ($data as $gdata)
		{
			foreach ($gdata as $row)
			{
				$return[] = $row->$pk2;
			}
		}
		return $return;
	}

	/**
	 * Upload the attachments to the server
	 *
	 * @return  bool success/fail
	 */

	private function _upload()
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.client.helper');
		$app = JFactory::getApplication();
		$input = $app->input;
		JClientHelper::setCredentialsFromRequest('ftp');
		$files = $input->files->get('attachement', array());
		$folder = JPATH_ROOT . '/images/stories';
		$this->filepath = array();
		foreach ($files as $file)
		{
			$name = $file['name'];
			if ($name == '')
			{
				continue;
			}
			$path = $folder . '/' . strtolower($name);
			if (!JFile::upload($file['tmp_name'], $path))
			{
				JError::raiseWarning(100, JText::_('PLG_LIST_EMAIL_ERR_CANT_UPLOAD_FILE'));
				return false;
			}
			else
			{
				$this->filepath[] = $path;
			}
		}
		return true;
	}

	/**
	 * Send the email
	 *
	 * @return boolean
	 */

	public function doEmail()
	{
		$listModel = $this->listModel;
		$app = JFactory::getApplication();
		$input = $app->input;
		jimport('joomla.mail.helper');
		if (!$this->_upload())
		{
			return false;
		}
		$listId = $input->getInt('id', 0);
		$this->setId($listId);
		$listModel->setId($listId);
		$w = new FabrikWorker;
		$config = JFactory::getConfig();
		$params = $this->getParams();
		$to = $input->get('list_email_to', '', 'string');
		$renderOrder = $input->getInt('renderOrder');
		$toType = $params->get('emailtable_to_type', 'list');
		$fromUser = $params->get('emailtable_from_user');
		if ($toType == 'list')
		{
			$to = str_replace('.', '___', $to);
		}

		if ($toType == 'list' && $to == '')
		{
			JError::raiseError(500, JText::_('PLG_LIST_EMAIL_ERR_NO_TO_ELEMENT_SELECTED'));
			exit;
		}
		$subject = $input->get('subject', '', 'string');
		$message = $input->get('message', '', 'html');
		$message = nl2br($message);
		$data = $this->getRecords('recordids', true);
		if ($fromUser)
		{
			$my = JFactory::getUser();
			$from = $my->get('email');
			$fromname = $my->get('name');
		}
		else
		{
			$from = $config->get('mailfrom');
			$fromname = $config->get('fromname');
		}

		$email_from = $config->get('mailfrom');
		$cc = null;
		$bcc = null;
		$sent = 0;
		$notsent = 0;
		$updated = array();
		foreach ($data as $group)
		{

			foreach ($group as $row)
			{
				if ($toType == 'list')
				{
					$process = isset($row->$to);
					$mailto = $row->$to;
				}
				else
				{
					$process = true;
					$mailto = $to;
				}
				if ($process)
				{
					$mailtos = explode(',', $mailto);
					foreach ($mailtos as $mailto)
					{
						$thisMailto = $w->parseMessageForPlaceholder($mailto, $row);
						if (JMailHelper::isEmailAddress($thisMailto))
						{
							$thissubject = $w->parseMessageForPlaceholder($subject, $row);
							$thismessage = $w->parseMessageForPlaceholder($message, $row);

							// Get a JMail instance (have to get a new instnace otherwise the receipients are appended to previously added recipients)
							$mail = JFactory::getMailer();
							$res = $mail->sendMail($email_from, $email_from, $thisMailto, $thissubject, $thismessage, true, $cc, $bcc, $this->filepath);
							if ($res)
							{
								$sent++;
							}
							else
							{
								$notsent++;
							}
						}
						else
						{
							$notsent++;
						}
					}
					if ($res)
					{
						$updated[] = $row->__pk_val;
					}
				}
				else
				{
					$notsent++;
				}
			}
		}
		if (!empty($updated))
		{
			$updateField = $params->get('emailtable_update_field');
			$updateVal = $params->get('emailtable_update_value');
			$listModel->updateRows($updated, $updateField, $updateVal);
		}
		$app->enqueueMessage(JText::sprintf('PLG_LIST_EMAIL_N_SENT', $sent));
		if ($notsent != 0)
		{
			JError::raiseWarning(E_NOTICE, JText::sprintf('PLG_LIST_EMAIL_N_NOT_SENT', $notsent));
		}
	}

}
