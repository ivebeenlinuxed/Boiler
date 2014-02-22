<?php
/**
 * All controllers extend this class. Holds function such as login
 * 
 * PHP version 5.4
 * 
 * @category  Controller
 * @package   Portal
 * @author    Will Tinsdeall <will.tinsdeall@mercianlabels.com>
 * @copyright 2013 Mercian Labels
 * @license   http://www.mercianlabels.com All Rights Reserved
 * @link      http://www.mercianlabels.com
 */

namespace Controller;


/**
 * All controllers extend this class. Holds function such as login
 *
 * PHP version 5.4
 *
 * @category  Controller
 * @package   Portal
 * @author    Will Tinsdeall <will.tinsdeall@mercianlabels.com>
 * @copyright 2013 Mercian Labels
 * @license   http://www.mercianlabels.com All Rights Reserved
 * @link      http://www.mercianlabels.com
 */
class BaseController {
	
	/**
	 * Gets the currently logged in user
	 * 
	 * @return \Model\User
	 
	public static function getCurrentUser() {
		if (isset($_GET['__api_key']) && ($api = \Model\ApiKey::Fetch($_GET['__api_key'])) !== false && $api->secret == $_GET['__api_secret']) {
			$_SESSION['user'] = $api->user;
		}
		
		if (isset($_SESSION['user'])) {
			return new \Model\User($_SESSION['user']);
		}
		return null;
	}
	*/
	public static function getCurrentUser() {
		return null;
	}
}