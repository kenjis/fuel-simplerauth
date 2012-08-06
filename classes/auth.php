<?php
/**
 * SimplerAuth
 *
 * @author     Kenji Suzuki https://github.com/kenjis
 * @copyright  2012 Kenji Suzuki
 * @license    MIT License http://www.opensource.org/licenses/mit-license.php
 */

namespace SimplerAuth;

class Auth
{
	const STRETCH_COUNT = 1000;
	const LOCKOUT_COUNT = 10;
	
	protected static $lock_count = 0;
	
	/**
	 * Check for validated login
	 *
	 * @return  bool
	 */
	public static function check()
	{
		$username = \Session::get('username');

		if ( ! is_null($username))
		{
			return true;
		}

		\Session::delete('username');
		return false;
	}
	
	/**
	 * Get the username
	 *
	 * @return  string|false
	 */
	public static function get_username()
	{
		$username = \Session::get('username');

		if ( ! is_null($username))
		{
			return $username;
		}

		return false;
	}
	
	/**
	 * Login user
	 * 
	 * @param string $username
	 * @param string $password
	 * @return  bool
	 */
	public static function login($username = '', $password = '')
	{
		if (empty($username) or empty($password))
		{
			return false;
		}
		
		// Locked account?
		if (static::is_locked($username))
		{
			// Logging
			$msg = 'locked:' . $username;
			static::log($msg, __METHOD__);
			
			return false;
		}
		
		// Load config file
		\Config::load('simplerauth', true);
		$users = \Config::get('simplerauth.users');
		
		if (isset($users[$username]))
		{
			$salt = $users[$username][0];
			$hash = $users[$username][1];
		
			if (static::get_password_hash($salt, $password) === $hash)
			{
				\Session::set('username', $username);
				\Session::instance()->rotate();
				
				// Unlock account
				\Cache::delete('simplerauth_' . $username);
				
				// Logging
				$msg = 'login:' . $username;
				static::log($msg, __METHOD__);
				
				return true;
			}
		}
		
		// Auth NG
		static::$lock_count++;
		\Cache::set('simplerauth_' . $username, static::$lock_count, 60 * 30);
		
		\Session::delete('username');
		
		// Logging
		$msg = 'login_failed:' . $username;
		static::log($msg, __METHOD__);
		
		return false;
	}
	
	protected static function is_locked($username)
	{
		try
		{
			static::$lock_count = \Cache::get('simplerauth_' . $username);
		}
		catch (\CacheNotFoundException $e)
		{
			// no cache, no problem
		}
		
		if (static::$lock_count >= self::LOCKOUT_COUNT)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Generate user config data
	 * Paste the output into config/simplerauth.php
	 * 
	 * @param string $username
	 * @param string $password
	 */
	public static function create_user($username, $password)
	{
		// Load config file
		\Config::load('simplerauth', true);
		$users = \Config::get('simplerauth.users');
		
		if (isset($users[$username]))
		{
			$message = 'the user already exists.';
			throw new \InvalidArgumentException($message);
		}
		
		$val = static::get_validation();
		$data = array(
			'username' => $username,
			'password' => $password,
		);
		
		if ( ! $val->run($data))
		{
			$message = '';
			foreach ($val->error() as $error)
			{
				$message .= $error->get_message() . ' ';
			}
			throw new \InvalidArgumentException($message);
		}
		
		$username = $val->validated('username');
		$passowrd = $val->validated('password');
		
		$salt = static::generate_salt();
		$password_hash = static::get_password_hash($salt, $password);
		
		echo "'$username' => array(\n\t'$salt',\n\t'$password_hash'\n),\n";
	}
	
	protected static function generate_salt()
	{
		return md5(uniqid(mt_rand(), true));
	}
	
	protected static function get_password_hash($salt, $password)
	{
		$hash = '';
		for ($i = 0; $i < self::STRETCH_COUNT; $i++) {
			$hash = hash('sha256', $hash . $password . $salt);
		}
		return $hash;
	}
	
	/**
	 * Logs out
	 */
	public static function logout()
	{
		// Logging
		$username = \Session::get('username');
		$msg = 'logout:' . $username;
		static::log($msg, __METHOD__);
		
		\Session::delete('username');
		
		return true;
	}
	
	/**
	 * Get Validation object
	 * 
	 * @param string The name of the Fieldset to link to
	 * @return \Validation
	 */
	public static function get_validation($fs = null)
	{
		if (is_null($fs))
		{
			// To call Auth::create_user() more than one time via oil console
			$fs = (string) microtime(true);
		}
		
		$val = \Validation::forge($fs);
		
		$val->add('username', 'Username')
			->add_rule('trim')
			->add_rule('required')
			->add_rule('valid_string', 'alpha_numeric')
			->add_rule('max_length', 20);
		$val->add('password', 'Password')
			->add_rule('trim')
			->add_rule('required')
			->add_rule('min_length', 8)
			->add_rule('max_length', 128);
		return $val;
	}
	
	protected static function log($msg, $method)
	{
		$uri   = \Input::uri();
		$ip    = \Input::ip();
		$agent = \Input::user_agent();
		
		$msg = $msg . ' [' . $uri . ' ' . $ip . ' "' . $agent . '"]';

		\Log::write('Auth', $msg, $method);
	}
}
