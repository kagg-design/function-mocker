<?php

	namespace tad\FunctionMocker\Call\Verifier;


	use PHPUnit_Framework_MockObject_Invocation;
	use tad\FunctionMocker\Call\Logger\LoggerInterface;
	use tad\FunctionMocker\ReturnValue;

	class InstanceMethodCallVerifier extends AbstractVerifier {

		protected $returnValue;
		protected $callLogger;

		public static function from( ReturnValue $returnValue, LoggerInterface $callLogger ) {
			$instance = new self;
			$instance->returnValue = $returnValue;
			$instance->callLogger = $callLogger;

			return $instance;
		}

		public function wasNotCalled() {
			$funcArgs = func_get_args();
			$this->realWasCalledTimes( 0, $funcArgs );
		}

		public function wasNotCalledWith( array $args ) {
			$funcArgs = func_get_args();
			$this->realWasCalledWithTimes( $args, 0, $funcArgs );
		}

		public function wasCalledOnce() {
			$funcArgs = func_get_args();
			$this->realWasCalledTimes( 1, $funcArgs );
		}

		public function wasCalledWithOnce( array $args ) {
			$funcArgs = func_get_args();
			$this->realWasCalledWithTimes( $args, 1, $funcArgs );
		}


		/**
		 * Checks if the function or method was called the specified number
		 * of times.
		 *
		 * @param  int $times
		 *
		 * @return void
		 */
		public function wasCalledTimes( $times ) {
			$funcArgs = func_get_args();
			$this->realWasCalledTimes( $times, $funcArgs );
		}

		/**
		 * Checks if the function or method was called with the specified
		 * arguments a number of times.
		 *
		 * @param  array $args
		 * @param  int   $times
		 *
		 * @return void
		 */
		public function wasCalledWithTimes( array $args, $times ) {
			$funcArgs = func_get_args();
			$this->realWasCalledWithTimes( $args, $times, $funcArgs );
		}

		/**
		 * @param array  $args
		 *
		 * @param string $methodName
		 *
		 * @return array
		 */
		protected function getCallTimesWithArgs( $methodName, array $args = null ) {
			$invocations = $this->invokedRecorder->getInvocations();
			$callTimes = 0;
			array_map( function ( \PHPUnit_Framework_MockObject_Invocation_Object $invocation ) use ( &$callTimes, $args, $methodName ) {
				if (is_array($args)) {
					$callTimes += $invocation->parameters === $args && $invocation->methodName === $methodName;
				} else {
					$callTimes += $invocation->methodName === $methodName;
				}
			}, $invocations );

			return $callTimes;
		}

		/**
		 * @param $times
		 * @param $funcArgs
		 */
		private function realWasCalledTimes( $times, $funcArgs ) {
			$methodName = ! empty( $funcArgs[1] ) ? $funcArgs[1] : false;
			$methodName = $methodName ? $methodName : $this->request->getMethodName();

			$callTimes = $this->getCallTimesWithArgs( $methodName );

			$this->matchCallTimes( $times, $callTimes, $methodName );
		}

		/**
		 * @param array $args
		 * @param       $times
		 * @param       $funcArgs
		 */
		private function realWasCalledWithTimes( array $args, $times, $funcArgs ) {
			$methodName = ! empty( $funcArgs[2] ) ? $funcArgs[2] : false;
			$methodName = $methodName ? $methodName : $this->request->getMethodName();

			$callTimes = $this->getCallTimesWithArgs( $methodName, $args );
			$functionName = $this->request->getMethodName();

			$this->matchCallWithTimes( $args, $times, $functionName, $callTimes );
		}
	}
