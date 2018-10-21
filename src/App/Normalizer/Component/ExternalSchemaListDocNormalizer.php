<?php
namespace Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component;

use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Resolver\DefinitionRefResolver;
use Yoanm\JsonRpcServerDoc\Domain\Model\MethodDoc;
use Yoanm\JsonRpcServerDoc\Domain\Model\ServerDoc;

/**
 * Class ExternalSchemaListDocNormalizer
 */
class ExternalSchemaListDocNormalizer
{
    /** @var TypeDocNormalizer */
    private $typeDocNormalizer;
    /** @var DefinitionRefResolver */
    private $definitionRefResolver;
    /** @var ErrorDocNormalizer */
    private $errorDocNormalizer;

    /**
     * @param DefinitionRefResolver  $definitionRefResolver
     * @param TypeDocNormalizer $typeDocNormalizer
     * @param ErrorDocNormalizer     $errorDocNormalizer
     */
    public function __construct(
        DefinitionRefResolver $definitionRefResolver,
        TypeDocNormalizer $typeDocNormalizer,
        ErrorDocNormalizer $errorDocNormalizer
    ) {
        $this->definitionRefResolver = $definitionRefResolver;
        $this->typeDocNormalizer = $typeDocNormalizer;
        $this->errorDocNormalizer = $errorDocNormalizer;
    }

    /**
     * @param ServerDoc $doc
     * @return array
     */
    public function normalize(ServerDoc $doc)
    {
        return array_merge(
            $this->getMethodsExternalSchemaList($doc),
            $this->getMethodErrorsExternalSchemaList($doc),
            $this->getServerErrorsExtraSchemaList($doc)
        );
    }

    /**
     * @param ServerDoc $doc
     *
     * @return array
     */
    protected function getMethodsExternalSchemaList(ServerDoc $doc)
    {
        $list = [];
        foreach ($doc->getMethodList() as $method) {
            // Merge extra definitions
            $list = array_merge($list, $this->getMethodExternalSchemaList($method));
        }

        return $list;
    }

    /**
     * @param ServerDoc $doc
     *
     * @return array
     */
    protected function getMethodErrorsExternalSchemaList(ServerDoc $doc)
    {
        $list = [];
        foreach ($doc->getMethodList() as $method) {
            foreach ($method->getCustomErrorList() as $errorDoc) {
                $key = $this->definitionRefResolver->getErrorDefinitionId(
                    $errorDoc,
                    DefinitionRefResolver::CUSTOM_ERROR_DEFINITION_TYPE
                );
                $list[$key] = $this->errorDocNormalizer->normalize($errorDoc);
            }
        }

        return $list;
    }


    /**
     * @return array
     */
    protected function getServerErrorsExtraSchemaList(ServerDoc $doc)
    {
        $list = [];
        foreach ($doc->getGlobalErrorList() as $errorDoc) {
            $key = $this->definitionRefResolver->getErrorDefinitionId(
                $errorDoc,
                DefinitionRefResolver::CUSTOM_ERROR_DEFINITION_TYPE
            );
            $list[$key] = $this->errorDocNormalizer->normalize($errorDoc);
        }

        foreach ($doc->getServerErrorList() as $errorDoc) {
            $key = $this->definitionRefResolver->getErrorDefinitionId(
                $errorDoc,
                DefinitionRefResolver::SERVER_ERROR_DEFINITION_TYPE
            );
            $list[$key] = $this->errorDocNormalizer->normalize($errorDoc);
        }

        return $list;
    }

    /**
     * @param MethodDoc $method
     *
     * @return array[]
     */
    protected function getMethodExternalSchemaList(MethodDoc $method) : array
    {
        $list = [];
        // Create request params schema if provided
        if (null !== $method->getParamsDoc()) {
            $key = $this->definitionRefResolver->getMethodDefinitionId(
                $method,
                DefinitionRefResolver::METHOD_PARAMS_DEFINITION_TYPE
            );
            $list[$key] = $this->typeDocNormalizer->normalize($method->getParamsDoc());
        }

        // Create custom result schema if provided
        if (null !== $method->getResultDoc()) {
            $key = $this->definitionRefResolver->getMethodDefinitionId(
                $method,
                DefinitionRefResolver::METHOD_RESULT_DEFINITION_TYPE
            );
            $list[$key] = $this->typeDocNormalizer->normalize($method->getResultDoc());
        }

        return $list;
    }
}
