<?php
namespace Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component;

use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Helper\ArrayAppendHelperTrait;
use Yoanm\JsonRpcServerDoc\Domain\Model\Type\ArrayDoc;
use Yoanm\JsonRpcServerDoc\Domain\Model\Type\CollectionDoc;
use Yoanm\JsonRpcServerDoc\Domain\Model\Type\NumberDoc;
use Yoanm\JsonRpcServerDoc\Domain\Model\Type\ObjectDoc;
use Yoanm\JsonRpcServerDoc\Domain\Model\Type\StringDoc;
use Yoanm\JsonRpcServerDoc\Domain\Model\Type\TypeDoc;

/**
 * Class TypeDocNormalizer
 */
class TypeDocNormalizer
{
    use ArrayAppendHelperTrait;

    /** @var SchemaTypeNormalizer */
    private $schemaTypeNormalizer;

    /**
     * @param SchemaTypeNormalizer $schemaTypeNormalizer
     */
    public function __construct(SchemaTypeNormalizer $schemaTypeNormalizer)
    {
        $this->schemaTypeNormalizer = $schemaTypeNormalizer;
    }

    /**
     * @param TypeDoc $doc
     *
     * @return array|mixed
     *
     * @throws \ReflectionException
     */
    public function normalize(TypeDoc $doc)
    {
        $siblingsDoc = $paramDocRequired = [];

        $siblingsDoc = $this->appendArrayDoc($doc, $siblingsDoc);
        list (
            $siblingsDoc,
            $paramDocRequired
            ) = $this->appendObjectDoc($doc, $siblingsDoc, $paramDocRequired);

        $format = ($doc instanceof StringDoc ? $doc->getFormat() : null);

        return $this->appendIfValueNotNull('description', $doc->getDescription())
        + ['type' => $this->schemaTypeNormalizer->normalize($doc)]
        + $this->appendIfValueNotNull('format', $format)
        + ['nullable' => $doc->isNullable()]
        + $paramDocRequired
        + $this->appendIfValueNotNull('default', $doc->getDefault())
        + $this->appendIfValueNotNull('example', $doc->getExample())
        + $this->appendIfValueHaveSiblings('enum', array_values($doc->getAllowedValueList()))
        + $this->getMinMaxDoc($doc)
        + $siblingsDoc
            ;
    }

    /**
     * @param TypeDoc $doc
     *
     * @return array
     */
    protected function getMinMaxDoc(TypeDoc $doc)
    {
        $paramDocMinMax = [];
        if ($doc instanceof StringDoc) {
            $paramDocMinMax = $this->appendIfValueNotNull('minLength', $doc->getMinLength(), $paramDocMinMax);
            $paramDocMinMax = $this->appendIfValueNotNull('maxLength', $doc->getMaxLength(), $paramDocMinMax);
        } elseif ($doc instanceof NumberDoc) {
            $paramDocMinMax = $this->appendNumberMinMax($doc, $paramDocMinMax);
        } elseif ($doc instanceof CollectionDoc) {
            if ($doc instanceof ObjectDoc) {
                $paramDocMinMax = $this->appendIfValueNotNull('minProperties', $doc->getMinItem(), $paramDocMinMax);
                $paramDocMinMax = $this->appendIfValueNotNull('maxProperties', $doc->getMaxItem(), $paramDocMinMax);
            } else {
                $paramDocMinMax = $this->appendIfValueNotNull('minItems', $doc->getMinItem(), $paramDocMinMax);
                $paramDocMinMax = $this->appendIfValueNotNull('maxItems', $doc->getMaxItem(), $paramDocMinMax);
            }
        }

        return $paramDocMinMax;
    }

    /**
     * @param TypeDoc $doc
     * @param array   $siblingsDoc
     *
     * @return array
     *
     * @throws \ReflectionException
     */
    protected function appendArrayDoc(TypeDoc $doc, array $siblingsDoc)
    {
        // CollectionDoc should be managed as ArrayDoc
        if (!$doc instanceof ArrayDoc && get_class($doc) !== CollectionDoc::class) {
            return $siblingsDoc;
        }
        /** @var $doc ArrayDoc|CollectionDoc */
        // add mandatory "items" field
        if ($doc instanceof ArrayDoc && null !== $doc->getItemValidation()) {
            $siblingsDoc['items'] = $this->normalize($doc->getItemValidation());
        } else {
            $siblingsDoc['items']['type'] = $this->guessItemsType($doc->getSiblingList());
        }

        return $siblingsDoc;
    }

    /**
     * @param TypeDoc $doc
     * @param array   $siblingsDoc
     * @param array   $paramDocRequired
     *
     * @return array
     */
    protected function appendObjectDoc(TypeDoc $doc, array $siblingsDoc, array $paramDocRequired)
    {
        if (!$doc instanceof ObjectDoc) {
            return [$siblingsDoc, $paramDocRequired];
        }

        if (true === $doc->isAllowExtraSibling()) {
            $siblingsDoc['additionalProperties']['description'] = "Extra property";
        }

        $self = $this;
        $siblingDocList = array_reduce(
            $doc->getSiblingList(),
            function (array $carry, TypeDoc $sibling) use ($self) {
                $carry[$sibling->getName()] = $self->normalize($sibling);

                return $carry;
            },
            []
        );
        $requiredSiblings = array_keys(// Keeps only keys
            array_filter(// Remove not required
                array_reduce(// Convert to $carray[PROPERTY_NAME] = IS_REQUIRED
                    $doc->getSiblingList(),
                    function (array $carry, TypeDoc $sibling) {
                        $carry[$sibling->getName()] = $sibling->isRequired();

                        return $carry;
                    },
                    []
                )
            )
        );

        $siblingsDoc = $this->appendIfValueHaveSiblings('properties', $siblingDocList, $siblingsDoc);
        $paramDocRequired = $this->appendIfValueHaveSiblings('required', $requiredSiblings, $paramDocRequired);

        return [$siblingsDoc, $paramDocRequired];
    }

    /**
     * @param TypeDoc[] $siblingList
     *
     * @return string
     */
    protected function guessItemsType(array $siblingList)
    {
        $self = $this;
        $uniqueTypeList = array_unique(
            array_map(
                function (TypeDoc $sibling) use ($self) {
                    return $self->schemaTypeNormalizer->normalize($sibling);
                },
                $siblingList
            )
        );

        if (count($uniqueTypeList) !== 1) {
            // default string if sub item type not guessable
            return 'string';
        }

        return array_shift($uniqueTypeList);
    }

    /**
     * @param NumberDoc $doc
     * @param array     $paramDocMinMax
     *
     * @return array
     */
    protected function appendNumberMinMax(NumberDoc $doc, array $paramDocMinMax)
    {
        $paramDocMinMax = $this->appendIfValueNotNull('minimum', $doc->getMin(), $paramDocMinMax);
        $paramDocMinMax = $this->appendIf(
            ($doc->getMin() && false === $doc->isInclusiveMin()),
            'exclusiveMinimum',
            true,
            $paramDocMinMax
        );
        $paramDocMinMax = $this->appendIfValueNotNull('maximum', $doc->getMax(), $paramDocMinMax);
        $paramDocMinMax = $this->appendIf(
            ($doc->getMax() && false === $doc->isInclusiveMax()),
            'exclusiveMaximum',
            true,
            $paramDocMinMax
        );

        return $paramDocMinMax;
    }
}
