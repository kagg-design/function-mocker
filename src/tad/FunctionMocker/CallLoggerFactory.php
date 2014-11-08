<?php

	namespace tad\FunctionMocker;


	use tad\FunctionMocker\StubCallLogger;

	class CallLoggerFactory {

		public static function make( $spying, $mocking, $functionName ) {
			if ( $spying && $mocking ) {
				throw new \BadMethodCallException( 'Either spy or mock, not both.' );
			}
			$invocation = new StubCallLogger();
			if ( $spying ) {
				$invocation = new SpyCallLogger();
			} else if ( $mocking ) {
				$invocation = MockCallLogger::from($functionName);
			}

			return $invocation;
		}
	}