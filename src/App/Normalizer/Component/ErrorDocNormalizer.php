<?php
namespace Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component;

use Yoanm\JsonRpcServerDoc\Domain\Model\ErrorDoc;

/**
 * Class ErrorDocNormalizer
 */
class ErrorDocNormalizer
{
    /** @var TypeDocNormalizer */
    private $typeDocNormalizer;
    /** @var ShapeNormalizer */
    private $shapeNormalizer;

    /**
     * @param TypeDocNormalizer $typeDocNormalizer
     * @param ShapeNormalizer   $shapeNormalizer
     */
    public function __construct(
        TypeDocNormalizer $typeDocNormalizer,
        ShapeNormalizer $shapeNormalizer
    ) {
        $this->typeDocNormalizer = $typeDocNormalizer;
        $this->shapeNormalizer = $shapeNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize(ErrorDoc $errorDoc)
    {
        $requiredDoc = ['required' => ['code']];
        $properties = [
            'code' => ['example' => $errorDoc->getCode()]
        ];
        if (null !== $errorDoc->getDataDoc()) {
            $properties['data'] = $this->typeDocNormalizer->normalize($errorDoc->getDataDoc());
            if (false !== $errorDoc->getDataDoc()->isRequired()) {
                $requiredDoc['required'][] = 'data';
            }
        }
        if (null !== $errorDoc->getMessage()) {
            $properties['message'] = ['example' => $errorDoc->getMessage()];
        }

        return [
            'title' => $errorDoc->getTitle(),
            'allOf' => [
                $this->shapeNormalizer->getErrorShapeDefinition(),
                (['type' => 'object'] + $requiredDoc + ['properties' => $properties]),
            ],
        ];
    }
}
