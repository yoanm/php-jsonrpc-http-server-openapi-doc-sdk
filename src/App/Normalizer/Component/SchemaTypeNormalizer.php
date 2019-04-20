<?php
namespace Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component;

use Yoanm\JsonRpcServerDoc\Domain\Model\Type\TypeDoc;

/**
 * Class SchemaTypeNormalizer
 */
class SchemaTypeNormalizer
{
    /**
     * @private
     * @type array
     */
    const MANAGED_TYPE_LIST = [
        'array',
        'number',
        'object',
        'string',
        'integer',
        'boolean',
        'null',
    ];
    /**
     * @private
     * @type array
     */
    const RENAMED_TYPE_LIST = [
        'float' => 'number',
        'collection' => 'array',
    ];
    /**
     * @param TypeDoc $doc
     *
     * @return string
     *
     * @throws \ReflectionException
     */
    public function normalize(TypeDoc $doc) : string
    {
        $type = str_replace('Doc', '', lcfirst((new \ReflectionClass($doc))->getShortName()));
        if (in_array($type, self::MANAGED_TYPE_LIST)) {
            return $type;
        } elseif (in_array($type, array_keys(self::RENAMED_TYPE_LIST))) {
            return self::RENAMED_TYPE_LIST[$type];
        }
        return 'string';
    }
}
