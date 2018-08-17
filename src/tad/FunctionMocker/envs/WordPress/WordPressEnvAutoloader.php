<?php
/**
 * WordPress environment autoloader.
 *
 * @package Test\Environments
 * @subpackage WordPress
 * @author Luca Tumedei <luca@theaveragedev.com>
 * @copyright 2018 Luca Tumedei
 *
 * @generated by function-mocker environment generation tool on 2018-08-17 11:02:20 (UTC)
 * @link https://github.com/lucatume/function-mocker
 */


class WordPressEnvAutoloader {

	/**
	 * A map of fully-qualified class names to their path.
	 * @var array 
	 */
	protected static $classMap = [
		'WP_List_Util' =>  __DIR__ . '/WP_List_Util.php',
		'WP_Hook' =>  __DIR__ . '/WP_Hook.php',
	];
	
	/**
	 * Finds and loads a class file managed by the autoloader.
	 * 
	 * @param string $class The class fully qualified name.
	 *
	 * @return bool Whether the file for the class was found and
	 *              loaded or not.
	 */
	public function autoload( $class ) {
		if ( array_key_exists( $class, static::$classMap ) ) {
			include_once static::$classMap[ $class ];

			return true;
		}

		return false;
	}
}