<?php
/**
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

$app = JFactory::getApplication();
$option = JRequest::getCmd('option');

// Set option and package to {package}
JRequest::setVar('option', 'com_{component_name}');
$prevUserState = $app->getUserState('com_fabrik.package', 'fabrik');
$app->setUserState('com_fabrik.package', '{component_name}');

require_once  JPATH_SITE . '/modules/mod_fabrik_form/mod_fabrik_form_boot.php';

// Revert option and package back to component name
$app->setUserState('com_fabrik.package', $prevUserState);
JRequest::setVar('option', $option);
