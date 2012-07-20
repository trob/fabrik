<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.sms
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
* Twilio SMS gateway class
*
* @package     Joomla.Plugin
* @subpackage  Fabrik.form.sms
* @since       3.0
*/

class Twilio extends JObject
{

	/**
	* Send SMS
	*
	* @param   string  $message  sms message
	*
	* @return  void
	*/

	public function process($message = '')
	{
		jimport('twilio.services.twilio');
		$params = $this->getParams();
		$username = $params->get('sms-username');
		$token = $params->get('sms-password');
		$smsto = $params->get('sms-to');

		// From a valid Twilio number
		$smsfrom = $params->get('sms-from');
		$smstos = explode(",", $smsto);
		foreach ($smstos as $smsto)
		{
			$client = new Services_Twilio($username, $token);
			$call = $client->account->sms_messages->create(
			$smsfrom,
			$smsto,
			$message
			);
		}
	}

	/**
	 * Get plugin params
	 *
	 * @return  object  params
	 */

	private function getParams()
	{
		return $this->params;
	}

}
