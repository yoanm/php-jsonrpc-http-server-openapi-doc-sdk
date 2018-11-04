<?php
namespace Tests\Functional\BehatContext;

use Behat\Behat\Context\Context;

class AbstractContext implements Context
{
    protected function jsonDecode($encodedData)
    {
        $decoded = json_decode($encodedData, true);

        if (JSON_ERROR_NONE != json_last_error()) {
            throw new \Exception(
                json_last_error_msg(),
                json_last_error()
            );
        }

        return $decoded;
    }

    /**
     * @param object $object
     * @param array  $decodedMethodCalls
     */
    protected function callMethods($object, array $decodedMethodCalls)
    {
        foreach ($decodedMethodCalls as $decodedMethodCall) {
            call_user_func_array(
                [$object, $decodedMethodCall['method']],
                $decodedMethodCall['arguments'] ?? []
            );
        }
    }
}
