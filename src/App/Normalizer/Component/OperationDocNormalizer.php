<?php
namespace Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component;

use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Resolver\DefinitionRefResolver;
use Yoanm\JsonRpcServerDoc\Domain\Model\ErrorDoc;
use Yoanm\JsonRpcServerDoc\Domain\Model\MethodDoc;
use Yoanm\JsonRpcServerDoc\Domain\Model\ServerDoc;

/**
 * Class OperationDocNormalizer
 */
class OperationDocNormalizer
{
    /** @var RequestDocNormalizer */
    private $requestDocTransformer;
    /** @var ResponseDocNormalizer */
    private $responseDocNormalizer;
    /** @var DefinitionRefResolver */
    private $definitionRefResolver;

    /**
     * @param DefinitionRefResolver $definitionRefResolver
     * @param RequestDocNormalizer  $requestDocTransformer
     * @param ResponseDocNormalizer $responseDocNormalizer
     */
    public function __construct(
        DefinitionRefResolver $definitionRefResolver,
        RequestDocNormalizer $requestDocTransformer,
        ResponseDocNormalizer $responseDocNormalizer
    ) {
        $this->requestDocTransformer = $requestDocTransformer;
        $this->responseDocNormalizer = $responseDocNormalizer;
        $this->definitionRefResolver = $definitionRefResolver;
    }

    /**
     * @param MethodDoc $method
     * @param ServerDoc $serverDoc
     *
     * @return array
     */
    public function normalize(MethodDoc $method, ServerDoc $serverDoc) : array
    {
        $self = $this;

        $extraErrorDefinitionIdRefList = array_map(
            function (ErrorDoc $errorDoc) use ($self) {
                return [
                    '$ref' => $self->definitionRefResolver->getDefinitionRef(
                        $self->definitionRefResolver->getErrorDefinitionId(
                            $errorDoc,
                            DefinitionRefResolver::SERVER_ERROR_DEFINITION_TYPE
                        )
                    )
                ];
            },
            $serverDoc->getServerErrorList()
        );

        $docDescription = $docTags = [];

        if (null !== $method->getDescription()) {
            $docDescription['description'] = $method->getDescription();
        }

        if (count($method->getTagList())) {
            $docTags['tags'] = $method->getTagList();
        }

        return [
                'summary' => sprintf('"%s" json-rpc method', $method->getMethodName()),
            ]
            + $docDescription
            + $docTags
            + [
                'operationId' => $method->getIdentifier(),
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => $this->requestDocTransformer->normalize($method)
                        ]
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'JSON-RPC response',
                        'content' => [
                            'application/json' => [
                                'schema' => $this->responseDocNormalizer->normalize(
                                    $method,
                                    $extraErrorDefinitionIdRefList
                                )
                            ],
                        ]
                    ]
                ]
            ]
        ;
    }
}
