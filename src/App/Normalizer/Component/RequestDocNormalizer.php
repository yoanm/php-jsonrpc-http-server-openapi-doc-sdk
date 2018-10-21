<?php
namespace Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component;

use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Resolver\DefinitionRefResolver;
use Yoanm\JsonRpcServerDoc\Domain\Model\MethodDoc;

/**
 * Class RequestDocNormalizer
 */
class RequestDocNormalizer
{
    /** @var DefinitionRefResolver */
    private $definitionRefResolver;
    /** @var ShapeNormalizer */
    private $shapeNormalizer;

    /**
     * @param DefinitionRefResolver $definitionRefResolver
     * @param ShapeNormalizer       $shapeNormalizer
     */
    public function __construct(
        DefinitionRefResolver $definitionRefResolver,
        ShapeNormalizer $shapeNormalizer
    ) {
        $this->definitionRefResolver = $definitionRefResolver;
        $this->shapeNormalizer = $shapeNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize(MethodDoc $method)
    {
        $operationProperties = ['method' => ['example' => $method->getMethodName()]];

        $requestSchema = ['allOf' => [$this->shapeNormalizer->getRequestShapeDefinition()]];
        // Append custom if params required
        if (null !== $method->getParamsDoc()) {
            $requestSchema['allOf'][] = [
                'type' => 'object',
                'required' => ['params'],
                'properties' => [
                    'params' => [
                        '$ref' => $this->definitionRefResolver->getDefinitionRef(
                            $this->definitionRefResolver->getMethodDefinitionId(
                                $method,
                                DefinitionRefResolver::METHOD_PARAMS_DEFINITION_TYPE
                            )
                        )
                    ],
                ],
            ];
        }
        $requestSchema['allOf'][] = [
            'type' => 'object',
            'properties' => $operationProperties,
        ];

        return $requestSchema;
    }
}
