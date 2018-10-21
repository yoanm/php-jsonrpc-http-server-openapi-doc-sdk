<?php
namespace Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Resolver;

use Yoanm\JsonRpcServerDoc\Domain\Model\ErrorDoc;
use Yoanm\JsonRpcServerDoc\Domain\Model\MethodDoc;

/**
 * Class DefinitionRefResolver
 */
class DefinitionRefResolver
{
    const METHOD_RESULT_DEFINITION_TYPE = 'method-result';
    const METHOD_PARAMS_DEFINITION_TYPE = 'method-params';
    const CUSTOM_ERROR_DEFINITION_TYPE = 'custom-error';
    const SERVER_ERROR_DEFINITION_TYPE = 'server-error';

    /**
     * @param MethodDoc $method
     * @param string    $definitionType
     *
     * @return string
     */
    public function getMethodDefinitionId(MethodDoc $method, $definitionType)
    {
        $base = 'UNKNONWN_METHOD-%s';
        switch ($definitionType) {
            case self::METHOD_RESULT_DEFINITION_TYPE:
                $base = 'Method-%s-Result';
                break;
            case self::METHOD_PARAMS_DEFINITION_TYPE:
                $base = 'Method-%s-RequestParams';
                break;
        }

        return sprintf($base, $method->getIdentifier());
    }

    /**
     * @param ErrorDoc $error
     * @param string          $definitionType
     *
     * @return string
     */
    public function getErrorDefinitionId(ErrorDoc $error, $definitionType)
    {
        $base = 'UNKNONWN_ERROR-%s';
        switch ($definitionType) {
            case self::CUSTOM_ERROR_DEFINITION_TYPE:
                $base = 'Error-%s';
                break;
            case self::SERVER_ERROR_DEFINITION_TYPE:
                $base = 'ServerError-%s';
                break;
        }

        return sprintf($base, $error->getIdentifier());
    }

    /**
     * @param ErrorDoc $errorDoc
     * @return string
     */
    public function getDefinitionRef($path)
    {
        return sprintf('#/components/schemas/%s', $path);
    }
}
