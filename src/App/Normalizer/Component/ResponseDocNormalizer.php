<?php
namespace Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component;

use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Helper\ArrayAppendHelperTrait;
use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Resolver\DefinitionRefResolver;
use Yoanm\JsonRpcServerDoc\Domain\Model\ErrorDoc;
use Yoanm\JsonRpcServerDoc\Domain\Model\MethodDoc;

/**
 * Class ResponseDocNormalizer
 */
class ResponseDocNormalizer
{
    use ArrayAppendHelperTrait;

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
     * @param MethodDoc $method
     * @param array     $extraErrorDefinitionIdRefList
     *
     * @return array
     */
    public function normalize(MethodDoc $method, array $extraErrorDefinitionIdRefList = [])
    {
        $responseErrorShape = [];
        $errorArrayDoc = $this->getMethodErrorArrayDoc($method, $extraErrorDefinitionIdRefList);
        if (count($errorArrayDoc) > 0) {
            $responseErrorShape = [
                'type' => 'object',
                'properties' => ['error' => $errorArrayDoc],
            ];
        }
        return [
            'allOf' => [
                $this->shapeNormalizer->getResponseShapeDefinition(),
                [
                    'type' => 'object',
                    'properties' => ['result' => $this->getMethodResultArrayDoc($method)],
                ],
                $responseErrorShape,
            ],
        ];
    }

    /**
     * @param MethodDoc $method
     *
     * @return array
     */
    protected function getMethodResultArrayDoc(MethodDoc $method)
    {
        if (null !== $method->getResultDoc()) {
            $result = [
                '$ref' => $this->definitionRefResolver->getDefinitionRef(
                    $this->definitionRefResolver->getMethodDefinitionId(
                        $method,
                        DefinitionRefResolver::METHOD_RESULT_DEFINITION_TYPE
                    )
                )
            ];
        } else {
            $result = ['description' => 'Method result'];
        }

        return $result;
    }

    /**
     * @param MethodDoc $method
     * @param string[]  $extraErrorDefinitionIdRefList
     *
     * @return array
     */
    protected function getMethodErrorArrayDoc(MethodDoc $method, array $extraErrorDefinitionIdRefList = [])
    {
        $self = $this;

        $errorDocList = array_merge(
            array_map(
                function ($errorRef) use ($self) {
                    $errorDoc = new ErrorDoc('', 0, null, null, $errorRef);
                    return [
                        '$ref' => $self->definitionRefResolver->getDefinitionRef(
                            $self->definitionRefResolver->getErrorDefinitionId(
                                $errorDoc,
                                DefinitionRefResolver::CUSTOM_ERROR_DEFINITION_TYPE
                            )
                        )
                    ];
                },
                $method->getGlobalErrorRefList()
            ),
            array_map(
                function (ErrorDoc $errorDoc) use ($self) {
                    return [
                        '$ref' => $self->definitionRefResolver->getDefinitionRef(
                            $self->definitionRefResolver->getErrorDefinitionId(
                                $errorDoc,
                                DefinitionRefResolver::CUSTOM_ERROR_DEFINITION_TYPE
                            )
                        )
                    ];
                },
                $method->getCustomErrorList()
            ),
            $extraErrorDefinitionIdRefList
        );

        if (count($errorDocList) > 0) {
            return ['oneOf' => $errorDocList];
        }

        return ['type' => 'object'];
    }
}
