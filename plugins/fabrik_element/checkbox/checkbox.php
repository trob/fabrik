<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.checkbox
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Plugin element to render series of checkboxes
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.checkbox
 * @since       3.0
 */

class plgFabrik_ElementCheckbox extends plgFabrik_ElementList
{

	/**
	 * Does the element have a label
	 * @var bool
	 */
	protected $hasLabel = false;

	protected $inputType = 'checkbox';

	/**
	 * Set the element id
	 * and maps parameter names for common ElementList options
	 *
	 * @param   int  $id  element id
	 *
	 * @return  void
	 */

	public function setId($id)
	{
		parent::setId($id);
		$params = $this->getParams();

		// Set elementlist params from checkbox params
		$params->set('options_per_row', $params->get('ck_options_per_row'));
		$params->set('allow_frontend_addto', (bool) $params->get('allow_frontend_addtocheckbox', false));
		$params->set('allowadd-onlylabel', (bool) $params->get('chk-allowadd-onlylabel', true));
		$params->set('savenewadditions', (bool) $params->get('chk-savenewadditions', false));
	}

	/**
	 * Shows the RAW list data - can be overwritten in plugin class
	 *
	 * @param   string  $data     element data
	 * @param   object  $thisRow  all the data in the tables current row
	 *
	 * @return  string	formatted value
	 */

	public function renderRawListData($data, $thisRow)
	{
		return json_encode($data);
	}

	/**
	 * will the element allow for multiple selections
	 *
	 * @since	3.0.6
	 *
	 * @return  bool
	 */

	protected function isMultiple()
	{
		return true;
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
		$params = $this->getParams();
		$id = $this->getHTMLId($repeatCounter);
		$element = $this->getElement();
		$values = (array) $this->getSubOptionValues();
		$labels = (array) $this->getSubOptionLabels();
		$data = $this->getFormModel()->_data;
		$opts = $this->getElementJSOptions($repeatCounter);
		$opts->value = $this->getValue($data, $repeatCounter);
		$opts->defaultVal = $this->getDefaultValue($data);
		$opts->data = (empty($values) && empty($labels)) ? array() : array_combine($values, $labels);
		$opts->allowadd = (bool) $params->get('allow_frontend_addtocheckbox', false);
		$opts = json_encode($opts);
		JText::script('PLG_ELEMENT_CHECKBOX_ENTER_VALUE_LABEL');
		return "new FbCheckBox('$id', $opts)";
	}

	/**
	 * If your element risks not to post anything in the form (e.g. check boxes with none checked)
	 * the this function will insert a default value into the database
	 *
	 * @param   array  &$data  form data
	 *
	 * @return  array  form data
	 */

	public function getEmptyDataValue(&$data)
	{
		$params = $this->getParams();
		$element = $this->getElement();
		if (!array_key_exists($element->name, $data))
		{
			$data[$element->name] = $params->get('sub_default_value');
		}
	}

	/**
	 * if the search value isnt what is stored in the database, but rather what the user
	 * sees then switch from the search string to the db value here
	 * overwritten in things like checkbox and radio plugins
	 *
	 * @param   string  $value  filterVal
	 *
	 * @return  string
	 */

	protected function prepareFilterVal($value)
	{
		$values = $this->getSubOptionValues();
		$labels = $this->getSubOptionLabels();
		for ($i = 0; $i < count($labels); $i++)
		{
			if (JString::strtolower($labels[$i]) == JString::strtolower($val))
			{
				$val = $values[$i];
				return $val;
			}
		}
		return $val;
	}

	/**
	 * Build the filter query for the given element.
	 * Can be overwritten in plugin - e.g. see checkbox element which checks for partial matches
	 *
	 * @param   string  $key            element name in format `tablename`.`elementname`
	 * @param   string  $condition      =/like etc
	 * @param   string  $value          search string - already quoted if specified in filter array options
	 * @param   string  $originalValue  original filter value without quotes or %'s applied
	 * @param   string  $type           filter type advanced/normal/prefilter/search/querystring/searchall
	 *
	 * @return  string	sql query part e,g, "key = value"
	 */

	public function getFilterQuery($key, $condition, $value, $originalValue, $type = 'normal')
	{
		$originalValue = trim($value, "'");
		$this->encryptFieldName($key);
		switch ($condition)
		{
			case '=':
				$db = FabrikWorker::getDbo();
				$str = "($key $condition $value " . " OR $key LIKE " . $db->quote('["' . $originalValue . '"%') . " OR $key LIKE "
					. $db->quote('%"' . $originalValue . '"%') . " OR $key LIKE " . $db->quote('%"' . $originalValue . '"]') . ")";

				break;
			default:
				$str = " $key $condition $value ";
				break;
		}
		return $str;
	}

	/**
	 * If no filter condition supplied (either via querystring or in posted filter data
	 * return the most appropriate filter option for the element.
	 *
	 * @return  string	default filter condition ('=', 'REGEXP' etc)
	 */

	public function getDefaultFilterCondition()
	{
		return '=';
	}

	/**
	 * Builds an array containing the filters value and condition
	 *
	 * @param   string  $value      initial value
	 * @param   string  $condition  intial $condition
	 * @param   string  $eval       how the value should be handled
	 *
	 * @return  array	(value condition)
	 */

	public function getFilterValue($value, $condition, $eval)
	{
		$value = $this->prepareFilterVal($value);
		return parent::getFilterValue($value, $condition, $eval);
	}

	/**
	 * Manupulates posted form data for insertion into database
	 *
	 * @param   mixed  $val   this elements posted form data
	 * @param   array  $data  posted form data
	 *
	 * @return  mixed
	 */

	public function storeDatabaseFormat($val, $data)
	{
		if (is_array($val))
		{
			// Ensure that array is incremental numeric key -otherwise json_encode turns it into an object
			$val = array_values($val);
		}
		if (is_array($val) || is_object($val))
		{
			return json_encode($val);
		}
		else
		{
			return isset($val) ? $val : '';
		}
	}

}
