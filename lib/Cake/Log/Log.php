<?php
/**
 * Logging.
 *
 * Log messages to text files.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       Cake.Log
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
namespace Cake\Log;
use \Cake\Error,
	\Cake\Log\Engine\FileLog,
	\Cake\Core\App;

/**
 * Set up error level constants to be used within the framework if they are not defined within the
 * system.
 *
 */
	if (!defined('LOG_WARNING')) {
		define('LOG_WARNING', 3);
	}
	if (!defined('LOG_NOTICE')) {
		define('LOG_NOTICE', 4);
	}
	if (!defined('LOG_DEBUG')) {
		define('LOG_DEBUG', 5);
	}
	if (!defined('LOG_INFO')) {
		define('LOG_INFO', 6);
	}

/**
 * Logs messages to configured Log adapters.  One or more adapters can be configured
 * using Logs's methods.  If you don't configure any adapters, and write to the logs
 * a default FileLog will be autoconfigured for you.
 *
 * ### Configuring Log adapters
 *
 * You can configure log adapters in your applications `bootstrap.php` file.  A sample configuration
 * would look like:
 *
 * `Log::config('my_log', array('engine' => 'FileLog'));`
 *
 * See the documentation on Log::config() for more detail.
 *
 * ### Writing to the log
 *
 * You write to the logs using Log::write().  See its documentation for more information.
 *
 * @package       Cake.Log
 */
class Log {

/**
 * An array of connected streams.
 * Each stream represents a callable that will be called when write() is called.
 *
 * @var array
 */
	protected static $_streams = array();

/**
 * Configure and add a new logging stream to Log
 * You can use add loggers from app/Log/Engine use app.loggername, or any plugin/Log/Engine using plugin.loggername.
 *
 * ### Usage:
 *
 * {{{
 * Log::config('second_file', array(
 * 		'engine' => 'FileLog',
 * 		'path' => '/var/logs/my_app/'
 * ));
 * }}}
 *
 * Will configure a FileLog instance to use the specified path.  All options that are not `engine`
 * are passed onto the logging adapter, and handled there.  Any class can be configured as a logging
 * adapter as long as it implements the methods in LogInterface.
 *
 * @param string $key The keyname for this logger, used to remove the logger later.
 * @param array $config Array of configuration information for the logger
 * @return boolean success of configuration.
 * @throws \Cake\Error\LogException
 */
	public static function config($key, $config) {
		if (empty($config['engine'])) {
			throw new Error\LogException(__d('cake_dev', 'Missing logger classname'));
		}
		$loggerName = $config['engine'];
		unset($config['engine']);
		$className = self::_getLogger($loggerName);
		$logger = new $className($config);
		if (!$logger instanceof LogInterface) {
			throw new Error\LogException(sprintf(
				__d('cake_dev', 'logger class %s does not implement a write method.'), $loggerName
			));
		}
		self::$_streams[$key] = $logger;
		return true;
	}

/**
 * Attempts to import a logger class from the various paths it could be on.
 * Checks that the logger class implements a write method as well.
 *
 * @param string $loggerName the plugin.className of the logger class you want to build.
 * @return mixed boolean false on any failures, string of classname to use if search was successful.
 * @throws \Cake\Error\LogException
 */
	protected static function _getLogger($loggerName) {
		$loggerName = App::classname($loggerName, 'Log/Engine');
		if (!$loggerName) {
			throw new Error\LogException(__d('cake_dev', 'Could not load class %s', $loggerName));
		}
		return $loggerName;
	}

/**
 * Returns the keynames of the currently active streams
 *
 * @return array Array of configured log streams.
 */
	public static function configured() {
		return array_keys(self::$_streams);
	}

/**
 * Removes a stream from the active streams.  Once a stream has been removed
 * it will no longer have messages sent to it.
 *
 * @param string $streamName Key name of a configured stream to remove.
 * @return void
 */
	public static function drop($streamName) {
		unset(self::$_streams[$streamName]);
	}

/**
 * Configures the automatic/default stream a FileLog.
 *
 * @return void
 */
	protected static function _autoConfig() {
		self::_getLogger('\Cake\Log\Engine\FileLog');
		self::$_streams['default'] = new FileLog(array('path' => LOGS));
	}

/**
 * Writes the given message and type to all of the configured log adapters.
 * Configured adapters are passed both the $type and $message variables. $type
 * is one of the following strings/values.
 *
 * ### Types:
 *
 * - `LOG_WARNING` => 'warning',
 * - `LOG_NOTICE` => 'notice',
 * - `LOG_INFO` => 'info',
 * - `LOG_DEBUG` => 'debug',
 * - `LOG_ERR` => 'error',
 * - `LOG_ERROR` => 'error'
 *
 * ### Usage:
 *
 * Write a message to the 'warning' log:
 *
 * `Log::write('warning', 'Stuff is broken here');`
 *
 * @param string $type Type of message being written
 * @param string $message Message content to log
 * @return boolean Success
 */
	public static function write($type, $message) {
		if (!defined('LOG_ERROR')) {
			define('LOG_ERROR', 2);
		}
		if (!defined('LOG_ERR')) {
			define('LOG_ERR', LOG_ERROR);
		}
		$levels = array(
			LOG_WARNING => 'warning',
			LOG_NOTICE => 'notice',
			LOG_INFO => 'info',
			LOG_DEBUG => 'debug',
			LOG_ERR => 'error',
			LOG_ERROR => 'error'
		);

		if (is_int($type) && isset($levels[$type])) {
			$type = $levels[$type];
		}
		if (empty(self::$_streams)) {
			self::_autoConfig();
		}
		foreach (self::$_streams as $logger) {
			$logger->write($type, $message);
		}
		return true;
	}
}