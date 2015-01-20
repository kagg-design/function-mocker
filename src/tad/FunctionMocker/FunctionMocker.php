<?php

namespace tad\FunctionMocker;

use PHPUnit_Framework_MockObject_Matcher_InvokedRecorder;
use tad\FunctionMocker\Call\Logger\CallLoggerFactory;
use tad\FunctionMocker\Call\Verifier\CallVerifierFactory;
use tad\FunctionMocker\Call\Verifier\FunctionCallVerifier;

class FunctionMocker
{
    // allows wrapping assert methods
    use PHPUnitFrameworkAssertWrapper;

    /**
     * @var \PHPUnit_Framework_TestCase
     */
    protected static $testCase;

    /**
     * @var array
     */
    protected static $replacedClassInstances = array();

    /** @var  array */
    protected static $defaultWhitelist = array(
        'vendor/antecedent'
    );

    protected static $defaultBlacklist = array(
        'vendor/codeception', 'vendor/phpunit', 'vendor/phpspec'
    );

    /** @var  bool */
    private static $didInit = false;

    /**
     * Stores the previous values of each global replaced.
     *
     * @var array
     */
    protected static $globalsBackup = [];

    /**
     * Loads Patchwork, use in setUp method of the test case.
     *
     * @return void
     */
    public static function setUp()
    {
        if (!self::$didInit) {
            self::init();
        }
        self::$replacedClassInstances = array();
    }

    /**
     * Undoes Patchwork bindings, use in tearDown method of test case.
     *
     * @return void
     */
    public static function tearDown()
    {
        \Patchwork\undoAll();

        // restore the globals
        if (empty(self::$globalsBackup)) {
            return;
        }
        array_walk(self::$globalsBackup, function ($value, $key) {
            $GLOBALS[$key] = $value;
        });
    }

    /**
     * Replaces a function, a static method or an instance method.
     *
     * The function or methods to be replaced must be specified with fully
     * qualified names like
     *
     *     FunctionMocker::replace('my\name\space\aFunction');
     *     FunctionMocker::replace('my\name\space\SomeClass::someMethod');
     *
     * not specifying a return value will make the replaced function or value
     * return `null`.
     *
     * @param      $functionName
     * @param null $returnValue
     *
     * @return mixed|Call\Verifier\InstanceMethodCallVerifier|static
     */
    public static function replace($functionName, $returnValue = null)
    {
        \Arg::_($functionName, 'Function name')->is_string()->_or()->is_array();
        if (is_array($functionName)) {
            $replacements = array();
            array_map(function ($_functionName) use ($returnValue, &$replacements) {
                $replacements[] = self::_replace($_functionName, $returnValue);
            }, $functionName);

            $return = self::arrayUnique($replacements);
            if (!is_array($return)) {
                return $return;
            }

            $indexedReplacements = self::getIndexedReplacements($return);

            return $indexedReplacements;
        }

        return self::_replace($functionName, $returnValue);
    }

    /**
     * @return SpoofTestCase
     */
    protected static function getTestCase()
    {
        if (!self::$testCase) {
            self::$testCase = new SpoofTestCase();
        }
        $testCase = self::$testCase;

        return $testCase;
    }

    /**
     * @param $functionName
     * @param $returnValue
     * @param $invocation
     *
     * @return callable
     */
    protected static function getReplacementFunction($functionName, $returnValue, $invocation)
    {
        $replacementFunction = function () use ($functionName, $returnValue, $invocation) {
            $trace = debug_backtrace();
            $args = array_filter($trace, function ($stackLog) use ($functionName) {
                $check = isset($stackLog['args']) && is_array($stackLog['args']) && $stackLog['function'] === $functionName;

                return $check ? true : false;
            });
            $args = array_values($args);
            $args = isset($args[0]) ? $args[0]['args'] : array();
            /** @noinspection PhpUndefinedMethodInspection */
            $invocation->called($args);

            /** @noinspection PhpUndefinedMethodInspection */

            /** @noinspection PhpUndefinedMethodInspection */

            /** @noinspection PhpUndefinedMethodInspection */

            return $returnValue->isCallable() ? $returnValue->call($args) : $returnValue->getValue();
        };

        return $replacementFunction;
    }

    /**
     * @param $functionName
     * @param $returnValue
     *
     * @return mixed|null|Call\Verifier\InstanceMethodCallVerifier|static
     * @throws \Exception
     */
    private static function _replace($functionName, $returnValue)
    {
        $request = ReplacementRequest::on($functionName);
        $checker = Checker::fromName($functionName);
        $returnValue = ReturnValue::from($returnValue);

        $callLogger = CallLoggerFactory::make($functionName);
        $verifier = CallVerifierFactory::make($request, $checker, $returnValue, $callLogger);

        $methodName = $request->getMethodName();
        if ($request->isInstanceMethod()) {
            $testCase = self::getTestCase();
            $className = $request->getClassName();

            if (!array_key_exists($className, self::$replacedClassInstances)) {
                self::$replacedClassInstances[$className] = array();
                self::$replacedClassInstances[$className]['replacedMethods'] = array();
            }
            self::$replacedClassInstances[$className]['replacedMethods'][$methodName] = $returnValue;

            $classReplacedMethods = self::$replacedClassInstances[$className]['replacedMethods'];
            $methods = array_map(function ($methodName) {
                return $methodName;
            }, array_keys($classReplacedMethods));
            $methods[] = '__construct';

            $mockObject = self::getPHPUnitMockObject($className, $testCase, $methods);

            $times = 'any';

            /**
             * @var PHPUnit_Framework_MockObject_Matcher_InvokedRecorder
             */
            $invokedRecorder = $testCase->$times();

            array_walk($classReplacedMethods, function (ReturnValue $returnValue, $methodName, \PHPUnit_Framework_MockObject_MockObject &$mockObject) use ($invokedRecorder) {
                if ($returnValue->isCallable()) {
                    // callback
                    $mockObject->expects($invokedRecorder)->method($methodName)
                        ->willReturnCallback($returnValue->getValue());
                } else if ($returnValue->isSelf()) {
                    // ->
                    $mockObject->expects($invokedRecorder)->method($methodName)->willReturn($mockObject);
                } else {
                    // value
                    $mockObject->expects($invokedRecorder)->method($methodName)
                        ->willReturn($returnValue->getValue());
                }
            }, $mockObject);

            if (empty(self::$replacedClassInstances[$className]['instance'])) {
                $mockWrapper = new MockWrapper();
                $mockWrapper->setOriginalClassName($className);
                $wrapperInstance = $mockWrapper->wrap($mockObject, $invokedRecorder, $request);
                self::$replacedClassInstances[$className]['instance'] = $wrapperInstance;
            } else {
                $wrapperInstance = self::$replacedClassInstances[$className]['instance'];
                /** @noinspection PhpUndefinedMethodInspection */
                $prevInvokedRecorder = $wrapperInstance->__get_functionMocker_invokedRecorder();
                // set the new invokedRecorder on the wrapper instance
                /** @noinspection PhpUndefinedMethodInspection */
                $wrapperInstance->__set_functionMocker_invokedRecorder($invokedRecorder);
                // set the new invoked recorder on the callHandler
                $callHandler = $wrapperInstance->__get_functionMocker_CallHandler();
                $callHandler->setInvokedRecorder($invokedRecorder);
                // sync the prev and the actual invokedRecorder
                $invocations = $prevInvokedRecorder->getInvocations();
                array_map(function (\PHPUnit_Framework_MockObject_Invocation $invocation) use (&$invokedRecorder) {
                    $invokedRecorder->invoked($invocation);
                }, $invocations);
                // set the mock object to the new one
                $wrapperInstance->__set_functionMocker_originalMockObject($mockObject);
            }

            return $wrapperInstance;
        }

        // function or static method
        $functionOrMethodName = $request->isMethod() ? $methodName : $functionName;

        $replacementFunction = self::getReplacementFunction($functionOrMethodName, $returnValue, $callLogger);

        if (function_exists('\Patchwork\replace')) {

            \Patchwork\replace($functionName, $replacementFunction);
        }


        return $verifier;
    }

    /**
     * @param $elements
     *
     * @return array|mixed
     */
    private static function arrayUnique($elements)
    {
        $uniqueReplacements = array();
        array_map(function ($replacement) use (&$uniqueReplacements) {
            if (!in_array($replacement, $uniqueReplacements)) {
                $uniqueReplacements[] = $replacement;
            }
        }, $elements);
        $uniqueReplacements = array_values($uniqueReplacements);

        return count($uniqueReplacements) === 1 ? $uniqueReplacements[0] : $uniqueReplacements;
    }

    /**
     * @param $return
     *
     * @return array
     */
    private static function getIndexedReplacements($return)
    {
        $indexedReplacements = array();
        if ($return[0] instanceof FunctionCallVerifier) {
            array_map(function (FunctionCallVerifier $replacement) use (&$indexedReplacements) {
                $fullFunctionName = $replacement->__getFunctionName();
                $functionNameElements = preg_split('/(\\\\|::)/', $fullFunctionName);
                $functionName = array_pop($functionNameElements);
                $indexedReplacements[$functionName] = $replacement;
            }, $return);

        }

        return $indexedReplacements;
    }

    /**
     * Calls the original function or static method with the given arguments
     * and returns the return value if any.
     *
     * @param array $args
     *
     * @return mixed
     */
    public static function callOriginal(array $args = null)
    {
        return \Patchwork\callOriginal($args);
    }

    public static function init(array $options = null)
    {
        if (self::$didInit) {
            return;
        }

        /** @noinspection PhpIncludeInspection */
        require_once Utils::getPatchworkFilePath();

        $_whitelist = is_array($options['include']) ? array_merge(self::$defaultWhitelist, $options['include']) : self::$defaultWhitelist;
        $_blacklist = is_array($options['exclude']) ? array_merge(self::$defaultBlacklist, $options['exclude']) : self::$defaultBlacklist;

        $rootDir = Utils::findParentContainingFrom('vendor', dirname(__FILE__));
        $whitelist = Utils::filterPathListFrom($_whitelist, $rootDir);
        $blacklist = Utils::filterPathListFrom($_blacklist, $rootDir);

        $blacklist = array_diff($blacklist, $whitelist);

        array_map(function ($path) {
            \Patchwork\blacklist($path);
        }, $blacklist);

        self::$didInit = true;
    }

    /**
     * @param $className
     * @param $testCase
     * @param $methods
     *
     * @return mixed
     */
    private static function getPHPUnitMockObject($className, \PHPUnit_Framework_TestCase $testCase, array $methods)
    {
        $rc = new \ReflectionClass($className);
        $type = 100 * $rc->isInterface() + 10 * $rc->isAbstract() + $rc->isTrait();
        switch ($type) {
            case 110:
                // Interfaces will also be abstract classes
                $mockObject = $testCase->getMock($className);
                break;
            case 10:
                // abstract class
                $mockObject = $testCase->getMockBuilder($className)->disableOriginalConstructor()
                    ->setMethods($methods)->getMockForAbstractClass();
                break;
            case 11:
                // traits will also be abstract classes
                $mockObject = $testCase->getMockBuilder($className)->disableOriginalConstructor()
                    ->setMethods($methods)->getMockForTrait();
                break;
            default:
                $mockObject = $testCase->getMockBuilder($className)->disableOriginalConstructor()
                    ->setMethods($methods)->getMock();
                break;
        }

        return $mockObject;
    }

    /**
     * Replaces/sets a global object with an instance replacement of the class.
     *
     * The $GLOBALS state will be reset at the next `FunctionMocker::tearDown` call.
     *
     * @param  string $globalHandle The key the value is associated to in the $GLOBALS array.
     * @param  string $functionName A `Class::method` format string
     * @param  mixed $returnValue The return value or callback, see `replace` method.
     *
     * @return mixed               The object that's been set in the $GLOBALS array.
     */
    public static function replaceGlobal($globalHandle, $functionName, $returnValue = null)
    {
        \Arg::_($globalHandle, 'Global var key')->is_string();

        self::backupGlobal($globalHandle);

        $replacement = FunctionMocker::_replace($functionName, $returnValue);
        $GLOBALS[$globalHandle] = $replacement;

        return $replacement;
    }

    /**
     * Sets a global value restoring the state after the test ran.
     *
     * @param string $globalHandle The key the value will be associated to in the $GLOBALS array.
     * @param mixed $replacement The value that will be set in the $GLOBALS array.
     *
     * @return mixed               The object that's been set in the $GLOBALS array.
     */
    public static function setGlobal($globalHandle, $replacement = null)
    {
        \Arg::_($globalHandle, 'Global var key')->is_string();

        self::backupGlobal($globalHandle);

        $GLOBALS[$globalHandle] = $replacement;

        return $replacement;
    }

    protected static function backupGlobal($globalHandle)
    {
        $shouldSave = !isset(self::$globalsBackup[$globalHandle]);
        if (!$shouldSave) {
            return;
        }
        self::$globalsBackup[$globalHandle] = isset($GLOBALS[$globalHandle]) ? $GLOBALS[$globalHandle] : null;
    }

    /**
     * @param \PHPUnit_Framework_TestCase $testCase
     */
    public static function setTestCase($testCase)
    {
        self::$testCase = $testCase;
    }
}
