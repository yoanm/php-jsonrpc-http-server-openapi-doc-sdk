<?php
namespace Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component;

use Yoanm\JsonRpcServerDoc\Domain\Model\Type\TypeDoc;

/**
 * Class SchemaTypeNormalizer
 */
class SchemaTypeNormalizer
{
    /**
     * @param TypeDoc $doc
     *
     * @return mixed|string
     *
     * @throws \ReflectionException
     */
    public function normalize(TypeDoc $doc) : string
    {
        $type = str_replace('Doc', '', lcfirst((new \ReflectionClass($doc))->getShortName()));
        // translate type
        switch ($type) {
            case 'array':
            case 'number':
            case 'object':
            case 'string':
            case 'integer':
            case 'boolean':
            case 'null':
                return $type;
            case 'float':
                return 'number';
            case 'collection':
                return 'array';
            default:
                return 'string';
        }
    }
}
