<?php
namespace Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Helper;

/**
 * Class ArrayAppendHelperTrait
 */
trait ArrayAppendHelperTrait
{
    /**
     * @param string $key
     * @param mixed  $value
     * @param array  $doc
     *
     * @return array
     */
    protected function appendIfValueHaveSiblings(string $key, array $value, array $doc = [])
    {
        return $this->appendIf((count($value) > 0), $key, $value, $doc);
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @param array  $doc
     *
     * @return array
     */
    protected function appendIfValueNotNull(string $key, $value, array $doc = [])
    {
        return $this->appendIf((null !== $value), $key, $value, $doc);
    }

    /**
     * @param bool   $doAppend
     * @param string $key
     * @param mixed  $value
     * @param array  $doc
     *
     * @return array
     */
    protected function appendIf(bool $doAppend, string $key, $value, array $doc = [])
    {
        if (true === $doAppend) {
            $doc[$key] = $value;
        }

        return $doc;
    }
}
