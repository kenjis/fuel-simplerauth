<?php
/**
 * SimplerAuth
 *
 * @author     Kenji Suzuki https://github.com/kenjis
 * @copyright  2012 Kenji Suzuki
 * @license    MIT License http://www.opensource.org/licenses/mit-license.php
 */

return array(
	/**
	 * User List
	 *   as 'username' => array('salt', 'encrypted password')
	 *   
	 *   You can get the entry with oil console:
	 *   >>> Auth::create_user('username', 'password');
	 */
	'users' => array(
		/*
		 * Example
		'admin' => array(
			'a32ca7aa311e7e6dcadc208303aa1562',
			'8e072d2df13769a6a88cd0a20ca3d789a50c780147c03abd4b1443431729d7bc'
		),
		*/
	),
);
