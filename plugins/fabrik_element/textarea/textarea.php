<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.textarea
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Plugin element to render text area or wysiwyg editor
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.textarea
 * @since       3.0
 */

class PlgFabrik_ElementTextarea extends PlgFabrik_Element
{

	/**
	 * Db table field type
	 *
	 * @var string
	 */
	protected $fieldDesc = 'TEXT';

	/**
	 * Tagify a string
	 *
	 * @param   string  $data  tagify
	 *
	 * @return  string	tagified string
	 */

	protected function tagify($data)
	{
		$name = $this->getFullName(false, true, false);
		$params = $this->getParams();
		$listModel = $this->getlistModel();
		$filters = $listModel->getFilterArray();
		$fkeys = JArrayHelper::getValue($filters, 'key', array());
		$data = explode(",", strip_tags($data));
		$tags = array();
		$url = $params->get('textarea_tagifyurl');
		if ($url == '')
		{
			$url = $_SERVER['REQUEST_URI'];
			$bits = explode('?', $url);
			$root = JArrayHelper::getValue($bits, 0, '', 'string');
			$bits = JArrayHelper::getValue($bits, 1, '', 'string');
			$bits = explode("&", $bits);
			$fullName = $this->getFullName(false, true, false);
			for ($b = count($bits) -1; $b >= 0; $b --)
			{
				$parts = explode("=", $bits[$b]);
				if (count($parts) > 1)
				{
					$key = FabrikString::ltrimword(FabrikString::safeColNameToArrayKey($parts[0]), '&');

					if ($key == $fullName)
					{
						unset($bits[$b]);
					}
					if ($key == $fullName . '[value]')
					{
						unset($bits[$b]);
					}
					if ($key == $fullName . '[condition]')
					{
						unset($bits[$b]);
					}
				}
			}
		}
		$url = $root . '?' . implode('&', $bits);

		// $$$ rob 24/02/2011 remove duplicates from tags
		$data = array_unique($data);
		$img = FabrikWorker::j3() ? 'bookmark.png' : 'tag.png';
		$icon = FabrikHelperHTML::image($img, 'form', @$this->tmpl, array('alt' => 'tag'));
		foreach ($data as $d)
		{
			$d = trim($d);
			if ($d != '')
			{
				if (trim($params->get('textarea_tagifyurl')) == '')
				{
					$qs = strstr($url, '?');
					if (substr($url, -1) === '?')
					{
						$thisurl = $url . $name . '[value]=' . $d;
					}
					else
					{
						$thisurl = strstr($url, '?') ? $url . '&' . $name . '[value]=' . urlencode($d) : $url . '?' . $name . '[value]=' . urlencode($d);
					}
					$thisurl .= '&' . $name . '[condition]=CONTAINS';
					$thisurl .= '&resetfilters=1';
				}
				else
				{
					$thisurl = str_replace('{tag}', urlencode($d), $url);
				}
				$tags[] = '<a href="' . $thisurl . '" class="fabrikTag">' . $icon . $d . '</a>';
			}
		}
		return implode(' ', $tags);
	}

	/**
	 * Shows the data formatted for the list view
	 *
	 * @param   string  $data      elements data
	 * @param   object  &$thisRow  all the data in the lists current row
	 *
	 * @return  string	formatted value
	 */

	public function renderListData($data, &$thisRow)
	{
		$data = parent::renderListData($data, $thisRow);
		$params = $this->getParams();

		if ($params->get('textarea-tagify') == true)
		{
			$data = $this->tagify($data);
		}
		// $$$rob dont strip slashes here - this is done when saving to db now
		if (!$this->useWysiwyg())
		{
			if (is_array($data))
			{
				for ($i = 0; $i < count($data); $i++)
				{
					$data[$i] = nl2br($data[$i]);
				}
			}
			else
			{
				if (is_object($data))
				{
					$this->convertDataToString($data);
				}
				$data = nl2br($data);
			}
		}
		if (!$params->get('textarea-tagify') && $data !== '' && (int) $params->get('textarea-truncate', 0) !== 0)
		{
			$opts = array();
			$opts['wordcount'] = (int) $params->get('textarea-truncate', 0);
			$opts['tip'] = $params->get('textarea-hover');
			$opts['position'] = $params->get('textarea_hover_location', 'top');
			$data = FabrikString::truncate($data, $opts);
			$data = fabrikString::truncate($data, $opts);
			$listModel = $this->getListModel();
			$data = $listModel->_addLink($data, $this, $thisRow);
		}
		return $data;
	}

	/**
	 * Does the element use the WYSWYG editor
	 *
	 * @return  bool	use wysiwyg editor
	 */

	public function useEditor()
	{
		$params = $this->getParams();
		$element = $this->getElement();
		$app = JFactory::getApplication();
		if ($params->get('use_wysiwyg', 0) && $app->input->getInt('ajax') !== 1)
		{
			return preg_replace("/[^A-Za-z0-9]/", "_", $element->name);
		}
		else
		{
			return false;
		}
	}

	/**
	 * Determines if the element can contain data used in sending receipts,
	 * e.g. fabrikfield returns true
	 *
	 * @return  bool
	 */

	public function isReceiptElement()
	{
		return true;
	}

	/**
	 * Should the element use the WYSIWYG editor
	 *
	 * @since   3.0.6.2
	 *
	 * @return  bool
	 */

	protected function useWysiwyg()
	{
		$params = $this->getParams();
		$app = JFactory::getApplication();
		$input = $app->input;
		if ($input->get('format') == 'raw')
		{
			return false;
		}
		if ($input->get('ajax') == '1')
		{
			return false;
		}
		return (bool) $params->get('use_wysiwyg', 0);
	}

	/**
	 * Draws the html form element
	 *
	 * @param   array  $data           to preopulate element with
	 * @param   int    $repeatCounter  repeat group counter
	 *
	 * @return  string	elements html
	 */

	public function render($data, $repeatCounter = 0)
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$name = $this->getHTMLName($repeatCounter);
		$id = $this->getHTMLId($repeatCounter);
		$element = $this->getElement();
		if ($element->hidden == '1')
		{
			return $this->getHiddenField($name, $this->getValue($data, $repeatCounter), $id);
		}
		$params = $this->getParams();
		$cols = $params->get('width', $element->width);
		$rows = $params->get('height', $element->height);
		$value = $this->getValue($data, $repeatCounter);
		$bits = array();
		$wysiwyg = $this->useWysiwyg();
		if (!$this->isEditable())
		{
			if (!$wysiwyg)
			{
				$value = nl2br($value);
			}
			if ($params->get('textarea-tagify') == true)
			{
				$value = $this->tagify($value);
			}
			return $value;
		}
		if ($params->get('textarea_placeholder', '') !== '')
		{
			$bits['placeholder'] = $params->get('textarea_placeholder');
		}
		$bits['class'] = "fabrikinput inputbox " . $params->get('bootstrap_class');
		if ($this->elementError != '')
		{
			$bits['class'] .= ' elementErrorHighlight';
		}
		if ($wysiwyg)
		{
			if ($input->get('ajax') == 1)
			{
				// $bits['class'] .= " mce_editable";
				$str = "<textarea ";
				foreach ($bits as $key => $val)
				{
					$str .= $key . '="' . $val . '" ';
				}
				$str .= 'name="' . $name . '" id="' . $id . '" cols="' . $cols . '" rows="' . $rows . '">' . $value . '</textarea>';
			}
			else
			{
				$editor = JFactory::getEditor();
				$buttons = (bool) $params->get('wysiwyg_extra_buttons', true);
				$str = $editor->display($name, $value, $cols * 10, $rows * 15, $cols, $rows, $buttons, $id);
			}
		}
		else
		{
			if ($params->get('disable'))
			{
				$bits['class'] .= " disabled";
				$bits['disabled'] = 'disabled';
			}
			if ($params->get('textarea-showmax') && $params->get('textarea_limit_type', 'char') === 'char')
			{
				$bits['maxlength'] = $params->get('textarea-maxlength');
			}

			$str = "<textarea ";
			foreach ($bits as $key => $val)
			{
				$str .= $key . '="' . $val . '" ';
			}

			$str .= 'name="' . $name . '" id="' . $id . '" cols="' . $cols . '" rows="' . $rows . '">' . $value . '</textarea>';
		}
		if ($params->get('textarea-showmax'))
		{
			if ($params->get('textarea_limit_type', 'char') === 'char')
			{
				$label = JText::_('PLG_ELEMENT_TEXTAREA_CHARACTERS_LEFT');
				$charsLeft = $params->get('textarea-maxlength') - JString::strlen($value);
			}
			else
			{
				$label = JText::_('PLG_ELEMENT_TEXTAREA_WORDS_LEFT');
				$charsLeft = $params->get('textarea-maxlength') - count(explode(' ', $value));
			}

			$str .= '<div class="fabrik_characters_left"><span>' . $charsLeft . '</span> ' . $label . '</div>';
		}
		return $str;
	}

	/**
	 * Used to format the data when shown in the form's email
	 *
	 * @param   mixed  $value          element's data
	 * @param   array  $data           form records data
	 * @param   int    $repeatCounter  repeat group counter
	 *
	 * @return  string	formatted value
	 */

	public function getEmailValue($value, $data = array(), $repeatCounter = 0)
	{
		$groupModel = $this->getGroup();
		if ($groupModel->isJoin() && $groupModel->canRepeat())
		{
			$value = $value[$repeatCounter];
		}
		return $this->renderListData($value, new stdClass);
	}

	/**
	 * Used by radio and dropdown elements to get a dropdown list of their unique
	 * unique values OR all options - basedon filter_build_method
	 *
	 * @param   bool    $normal     do we render as a normal filter or as an advanced search filter
	 * @param   string  $tableName  table name to use - defaults to element's current table
	 * @param   string  $label      field to use, defaults to element name
	 * @param   string  $id         field to use, defaults to element name
	 * @param   bool    $incjoin    include join
	 *
	 * @return  array  text/value objects
	 */

	public function filterValueList($normal, $tableName = '', $label = '', $id = '', $incjoin = true)
	{
		$params = $this->getParams();
		if ($params->get('textarea-tagify') == true)
		{
			return $this->getTags();
		}
		else
		{
			return parent::filterValueList($normal, $tableName, $label, $id, $incjoin);
		}
	}

	/**
	 * Used for filter lists - get distinct array of all recorded tags
	 *
	 * @since   3.0.7
	 *
	 * @return   array
	 */

	protected function getTags()
	{
		$listModel = $this->getListModel();
		$id = $this->getElement()->id;
		$cols = $listModel->getColumnData($id);
		$tags = array();
		foreach ($cols as $col)
		{
			$col = explode(',', $col);
			foreach ($col as $word)
			{
				$word = strtolower(trim($word));
				if ($word !== '')
				{
					$tags[$word] = JHTML::_('select.option', $word, $word);
				}
			}
		}
		$tags = array_values($tags);
		return $tags;
	}

	/**
	 * Returns javascript which creates an instance of the class defined in formJavascriptClass()
	 *
	 * @param   int  $repeatCounter  Repeat group counter
	 *
	 * @return  array
	 */

	public function elementJavascript($repeatCounter)
	{
		$params = $this->getParams();
		if ($this->useWysiwyg())
		{
			// $$$ rob need to use the NAME as the ID when wysiwyg end in joined group
			$id = $this->getHTMLName($repeatCounter);

			// Testing not using name as duplication of group does not trigger clone()
			// $id = $this->getHTMLId($repeatCounter);

			if ($this->inDetailedView)
			{
				$id .= '_ro';
			}
		}
		else
		{
			$id = $this->getHTMLId($repeatCounter);
		}
		$opts = $this->getElementJSOptions($repeatCounter);
		$opts->max = $params->get('textarea-maxlength');
		$opts->maxType = $params->get('textarea_limit_type', 'char');
		$opts->wysiwyg = $this->useWysiwyg();
		$opts->deleteOverflow = $params->get('delete_overflow', true) ? true : false;
		$opts->htmlId = $this->getHTMLId($repeatCounter);
		return array('FbTextarea', $id, $opts);
	}

	/**
	 * Internal element validation
	 *
	 * @param   array  $data           form data
	 * @param   int    $repeatCounter  repeeat group counter
	 *
	 * @return bool
	 */

	public function validate($data, $repeatCounter = 0)
	{
		$params = $this->getParams();
		if (!$params->get('textarea-showmax', false))
		{
			return true;
		}
		if ($params->get('delete_overflow', true))
		{
			return true;
		}
		if (JString::strlen($data) > (int) $params->get('textarea-maxlength'))
		{
			return false;
		}
		return true;
	}

	/**
	 * Get validation error - run through JText
	 *
	 * @return  string
	 */

	public function getValidationErr()
	{
		return JText::_('PLG_ELEMENT_TEXTAREA_CONTENT_TOO_LONG');
	}

	/**
	 * Get Joomfish translation type
	 *
	 * @deprecated
	 *
	 * @return  string	joomfish translation type e.g. text/textarea/referenceid/titletext
	 */

	public function getJoomfishTranslationType()
	{
		return 'textarea';
	}

	/**
	 * Get Joomfish translation options
	 *
	 * @deprecated
	 *
	 * @return  array	key=>value options
	 */

	public function getJoomfishOptions()
	{
		$params = $this->getParams();
		$return = array();
		if ($params->get('textarea-showmax'))
		{
			$return['maxlength'] = $params->get('textarea-maxlength');
		}
		return $return;
	}

	/**
	 * Can the element plugin encrypt data
	 *
	 * @return  bool
	 */

	public function canEncrypt()
	{
		return true;
	}
}
