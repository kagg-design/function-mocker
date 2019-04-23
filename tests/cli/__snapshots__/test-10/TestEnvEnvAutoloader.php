<?php

/**
 * Class TestEnvEnvAutoloader
 *
 * Handles the autoloading of the  test environment classes.
 */
class TestEnvEnvAutoloader {

	/**
	 * A map of fully-qualified class names to their path.
	 * @var array 
	 */
	protected static $classMap = [
		'Acme\Company\NamespacedAbstractClass' =>  __DIR__ . '/src/Acme/Company/NamespacedAbstractClass.php',
		'Acme\Company\NamespacedInterface' =>  __DIR__ . '/src/Acme/Company/NamespacedInterface.php',
		'Acme\Company\NamespacedTrait' =>  __DIR__ . '/src/Acme/Company/NamespacedTrait.php',
		'GlobalAbstractClass' =>  __DIR__ . '/src/GlobalAbstractClass.php',
		'GlobalInterface' =>  __DIR__ . '/src/GlobalInterface.php',
		'GlobalTrait' =>  __DIR__ . '/src/GlobalTrait.php',
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
