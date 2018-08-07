<?php

/**
 * wordpress environment bootstrap file
 *
 * @generated by function-mocker environment generation tool on 2018-08-07 08:29:38 (UTC)
 * @link https://github.com/lucatume/function-mocker
 */


require_once __DIR__ . '/utils.php';
require_once __DIR__ . '/filters.php';

class EnvAutoloader_wordpress {

	protected static $classMap = [
		'WP_Hook' =>  __DIR__ . '/WP_Hook.php',
	];

	protected $rootDir;

	public function __construct( $rootDir ) {
		$this->rootDir = $rootDir;
	}

	public function autoload( $class ) {
		if ( array_key_exists( $class, static::$classMap ) ) {
			include_once static::$classMap[ $class ];

			return true;
		}

		return false;
	}
}

spl_autoload_register( [ new EnvAutoloader_wordpress( __DIR__ ), 'autoload' ] );