<?php
namespace Tests\Common\Helper;

use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Helper\ArrayAppendHelperTrait;

class ConcreteArrayAppendHelper
{
    use ArrayAppendHelperTrait;

    /**
     * @param string $key
     * @param mixed  $value
     * @param array  $doc
     *
     * @return array
     */
    public function testAppendIfValueHaveSiblings(string $key, array $value, array $doc = [])
    {
        return $this->appendIfValueHaveSiblings($key, $value, $doc);
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @param array  $doc
     *
     * @return array
     */
    public function testAppendIfValueNotNull(string $key, $value, array $doc = [])
    {
        return $this->appendIfValueNotNull($key, $value, $doc);
    }

    /**
     * @param bool   $doAppend
     * @param string $key
     * @param mixed  $value
     * @param array  $doc
     *
     * @return array
     */
    public function testAppendIf(bool $doAppend, string $key, $value, array $doc = [])
    {
        return $this->appendIf($doAppend, $key, $value, $doc);
    }
}
