<?php
	/**
	 * Created by PhpStorm.
	 * User: Luca
	 * Date: 20/11/14
	 * Time: 17:21
	 */

	namespace tad\FunctionMocker\Template\Extender;


	interface Extender {

		public function getExtenderClassName();
		public function getExtenderMethodsSignaturesAndCalls();
		public function getExtenderInterfaceName();
	}