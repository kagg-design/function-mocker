<?php
/**
 * WordPress environment bootstrap file.
 *
 * @package Test\Environments
 * @subpackage WordPress
 * @author Luca Tumedei <luca@theaveragedev.com>
 * @copyright 2018 Luca Tumedei
 *
 * @generated by function-mocker environment generation tool on 2018-08-17 11:02:20 (UTC)
 * @link https://github.com/lucatume/function-mocker
 */


require_once __DIR__ . '/utils.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/filters.php';
require_once __DIR__ . '/WordPressEnvAutoloader.php';

spl_autoload_register( [ new WordPressEnvAutoloader( __DIR__ ), 'autoload' ] );