<?php
namespace Yoanm\JsonRpcHttpServerOpenAPIDoc\Infra\Normalizer;

use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Helper\ArrayAppendHelperTrait;
use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component\ExternalSchemaListDocNormalizer;
use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component\OperationDocNormalizer;
use Yoanm\JsonRpcServerDoc\Domain\Model\HttpServerDoc;
use Yoanm\JsonRpcServerDoc\Domain\Model\TagDoc;

/**
 * Class DocNormalizer
 */
class DocNormalizer
{
    use ArrayAppendHelperTrait;

    /** @var ExternalSchemaListDocNormalizer */
    private $externalSchemaListDocNormalizer;
    /** @var OperationDocNormalizer */
    private $operationDocNormalizer;

    /**
     * @param ExternalSchemaListDocNormalizer $externalSchemaListDocNormalizer
     * @param OperationDocNormalizer          $operationDocNormalizer
     */
    public function __construct(
        ExternalSchemaListDocNormalizer $externalSchemaListDocNormalizer,
        OperationDocNormalizer $operationDocNormalizer
    ) {
        $this->externalSchemaListDocNormalizer = $externalSchemaListDocNormalizer;
        $this->operationDocNormalizer = $operationDocNormalizer;
    }

    /**
     * @param HttpServerDoc $doc
     *
     * @return array
     */
    public function normalize(HttpServerDoc $doc)
    {
        return [
                'openapi' => '3.0.0',
            ]
            + $this->infoArray($doc)
            + $this->serverArray($doc)
            + $this->tagsArray($doc)
            + $this->pathsArray($doc)
            + $this->externalSchemaListArray($doc);
    }

    /**
     * {@inheritdoc}
     */
    protected function infoArray(HttpServerDoc $doc)
    {
        $infoArray = [];
        $infoArray = $this->appendIfValueNotNull('title', $doc->getName(), $infoArray);
        $infoArray = $this->appendIfValueNotNull('version', $doc->getVersion(), $infoArray);

        return $this->appendIfValueHaveSiblings('info', $infoArray);
    }

    /**
     * {@inheritdoc}
     */
    protected function serverArray(HttpServerDoc $doc)
    {
        $serverList = [];
        if (null !== $doc->getHost()) {
            $host = $doc->getHost();
            if (null !== $doc->getBasePath()) {
                $host .= $doc->getBasePath();
            }
            $schemeList = $doc->getSchemeList();
            if (0 === count($schemeList)) {
                $schemeList[] = 'http';
            }
            foreach ($schemeList as $scheme) {
                $serverList[] = ['url' => sprintf('%s://%s', $scheme, $host)];
            }
        }

        return $this->appendIfValueHaveSiblings('servers', $serverList);
    }

    /**
     * {@inheritdoc}
     */
    protected function tagsArray(HttpServerDoc $doc)
    {
        $self = $this;

        return $this->appendIfValueHaveSiblings(
            'tags',
            array_map(
                function (TagDoc $tagDoc) use ($self) {
                    return $self->convertToTagDoc($tagDoc);
                },
                $doc->getTagList()
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function pathsArray(HttpServerDoc $doc)
    {
        $paths = [];
        foreach ($doc->getMethodList() as $method) {
            $operationDoc = $this->operationDocNormalizer->normalize($method, $doc);

            // As JSON-RPC use only one endpoint
            // and openApi does not handle multiple request definition for the same endpoint
            // => create a fake (but usable) endpoint by using method id and '/../'
            $openApiHttpEndpoint = sprintf(
                '/%s/..%s',
                str_replace('/', '-', $method->getIdentifier()),
                $doc->getEndpoint() ?? ''
            );

            $paths[$openApiHttpEndpoint] = ['post' => $operationDoc];
        }

        return $this->appendIfValueHaveSiblings('paths', $paths);
    }

    /**
     * @param HttpServerDoc $doc
     *
     * @return array
     */
    protected function externalSchemaListArray(HttpServerDoc $doc)
    {
        return [
            'components' => [
                'schemas' => $this->externalSchemaListDocNormalizer->normalize($doc),
            ],
        ];
    }

    /**
     * @param TagDoc $tag
     *
     * @return array
     */
    private function convertToTagDoc(TagDoc $tag)
    {
        $tagDoc = [
            'name' => $tag->getName(),
        ];
        if (null !== $tag->getDescription()) {
            $tagDoc['description'] = $tag->getDescription();
        }

        return $tagDoc;
    }
}
