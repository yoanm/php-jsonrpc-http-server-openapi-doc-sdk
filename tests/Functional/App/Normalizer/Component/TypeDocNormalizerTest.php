<?php
namespace Tests\Functional\App\Normalizer\Component;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component\SchemaTypeNormalizer;
use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component\TypeDocNormalizer;
use Yoanm\JsonRpcServerDoc\Domain\Model\Type as TypeDocNs;

/**
 * @covers \Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component\TypeDocNormalizer
 *
 * @group TypeDocNormalizer
 */
class TypeDocNormalizerTest extends TestCase
{
    use ProphecyTrait;

    /** @var TypeDocNormalizer */
    private $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new TypeDocNormalizer(
            new SchemaTypeNormalizer()
        );
    }

    /**
     * @dataProvider provideSimpleManagedTypeDocList
     * @dataProvider provideFullyDefinedManagedTypeDocList
     * @dataProvider provideBasicManagedTypeDocList
     *
     * @param TypeDocNs\TypeDoc $typeDoc
     * @param array             $expected
     */
    public function testShouldHandle(TypeDocNs\TypeDoc $typeDoc, $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($typeDoc));
    }

    /**
     * @return array
     */
    public function provideSimpleManagedTypeDocList()
    {
        return [
            'Simple Doc' => [
                'typeDoc' => new TypeDocNs\TypeDoc(),
                'expected' => [
                    'type' => 'string',
                    'nullable' => true,
                ],
            ],
            'Simple ScalarDoc' => [
                'typeDoc' => new TypeDocNs\ScalarDoc(),
                'expected' => [
                    'type' => 'string',
                    'nullable' => true,
                ],
            ],
            'Simple BooleanDoc' => [
                'typeDoc' => new TypeDocNs\BooleanDoc(),
                'expected' => [
                    'type' => 'boolean',
                    'nullable' => true,
                ],
            ],
            'Simple StringDoc' => [
                'typeDoc' => new TypeDocNs\StringDoc(),
                'expected' => [
                    'type' => 'string',
                    'nullable' => true,
                ],
            ],
            'Simple NumberDoc' => [
                'typeDoc' => new TypeDocNs\NumberDoc(),
                'expected' => [
                    'type' => 'number',
                    'nullable' => true,
                ],
            ],
            'Simple FloatDoc' => [
                'typeDoc' => new TypeDocNs\FloatDoc(),
                'expected' => [
                    'type' => 'number',
                    'nullable' => true,
                ],
            ],
            'Simple IntegerDoc' => [
                'typeDoc' => new TypeDocNs\IntegerDoc(),
                'expected' => [
                    'type' => 'integer',
                    'nullable' => true,
                ],
            ],
            'Simple CollectionDoc' => [
                'typeDoc' => new TypeDocNs\CollectionDoc(),
                'expected' => [
                    'type' => 'array',
                    'nullable' => true,
                    'items' => ['type' => 'string']
                ],
            ],
            'Simple ArrayDoc' => [
                'typeDoc' => new TypeDocNs\ArrayDoc(),
                'expected' => [
                    'type' => 'array',
                    'nullable' => true,
                    'items' => ['type' => 'string']
                ],
            ],
            'Simple ObjectDoc' => [
                'typeDoc' => new TypeDocNs\ObjectDoc(),
                'expected' => [
                    'type' => 'object',
                    'nullable' => true,
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function provideFullyDefinedManagedTypeDocList()
    {
        return [
            'Fully defined Doc' => [
                'typeDoc' => (new TypeDocNs\TypeDoc())
                    ->addAllowedValue('A')
                    ->addAllowedValue('B')
                    ->setNullable(false)
                    ->setName('my-name')
                    ->setRequired(true)
                    ->setDescription('my-description')
                    ->setExample('my-example')
                    ->setDefault('my-default'),
                'expected' => [
                    'description' => 'my-description',
                    'type' => 'string',
                    'nullable' => false,
                    'default' => 'my-default',
                    'example' => 'my-example',
                    'enum' => ['A', 'B'],
                ],
            ],
            'Fully defined ScalarDoc' => [
                'typeDoc' => (new TypeDocNs\ScalarDoc())
                    ->setNullable(false)
                    ->setName('my-name')
                    ->setRequired(true)
                    ->setDescription('my-description')
                    ->setExample('my-example')
                    ->setDefault('my-default'),
                'expected' => [
                    'description' => 'my-description',
                    'type' => 'string',
                    'nullable' => false,
                    'default' => 'my-default',
                    'example' => 'my-example',
                ],
            ],
            'Fully defined BooleanDoc' => [
                'typeDoc' => (new TypeDocNs\BooleanDoc())
                    ->setNullable(false)
                    ->setName('my-name')
                    ->setRequired(true)
                    ->setDescription('my-description')
                    ->setExample(true)
                    ->setDefault(false),
                'expected' => [
                    'description' => 'my-description',
                    'type' => 'boolean',
                    'nullable' => false,
                    'default' => false,
                    'example' => true,
                ],
            ],
            'Fully defined StringDoc' => [
                'typeDoc' => (new TypeDocNs\StringDoc())
                    ->setFormat('my-format')
                    ->setNullable(false)
                    ->setName('my-name')
                    ->setRequired(true)
                    ->setDescription('my-description')
                    ->setExample('my-example')
                    ->setDefault('my-default'),
                'expected' => [
                    'description' => 'my-description',
                    'type' => 'string',
                    'format' => 'my-format',
                    'nullable' => false,
                    'default' => 'my-default',
                    'example' => 'my-example',
                ],
            ],
            'Fully defined NumberDoc' => [
                'typeDoc' => (new TypeDocNs\NumberDoc())
                    ->setMin(10)
                    ->setMax(100)
                    ->setInclusiveMin(false)
                    ->setInclusiveMax(false)
                    ->setNullable(false)
                    ->setName('my-name')
                    ->setRequired(true)
                    ->setDescription('my-description')
                    ->setExample(230)
                    ->setDefault(0),
                'expected' => [
                    'description' => 'my-description',
                    'type' => 'number',
                    'nullable' => false,
                    'default' => 0,
                    'example' => 230,
                    'minimum' => 10,
                    'exclusiveMinimum' => true,
                    'maximum' => 100,
                    'exclusiveMaximum' => true,
                ],
            ],
            'Fully defined FloatDoc' => [
                'typeDoc' => (new TypeDocNs\FloatDoc())
                    ->setMin(10.3)
                    ->setMax(100.4)
                    ->setInclusiveMin(false)
                    ->setInclusiveMax(false)
                    ->setNullable(false)
                    ->setName('my-name')
                    ->setRequired(true)
                    ->setDescription('my-description')
                    ->setExample(1324.12)
                    ->setDefault(0.4),
                'expected' => [
                    'description' => 'my-description',
                    'type' => 'number',
                    'nullable' => false,
                    'default' => 0.4,
                    'example' => 1324.12,
                    'minimum' => 10.3,
                    'exclusiveMinimum' => true,
                    'maximum' => 100.4,
                    'exclusiveMaximum' => true,
                ],
            ],
            'Fully defined IntegerDoc' => [
                'typeDoc' => (new TypeDocNs\IntegerDoc())
                    ->setMin(10)
                    ->setMax(100)
                    ->setInclusiveMin(false)
                    ->setInclusiveMax(false)
                    ->setNullable(false)
                    ->setName('my-name')
                    ->setRequired(true)
                    ->setDescription('my-description')
                    ->setExample(1)
                    ->setDefault(2),
                'expected' => [
                    'description' => 'my-description',
                    'type' => 'integer',
                    'nullable' => false,
                    'default' => 2,
                    'example' => 1,
                    'minimum' => 10,
                    'exclusiveMinimum' => true,
                    'maximum' => 100,
                    'exclusiveMaximum' => true,
                ],
            ],
            'Fully defined CollectionDoc' => [
                'typeDoc' => (new TypeDocNs\CollectionDoc())
                    ->addSibling((new TypeDocNs\BooleanDoc())->setName('name1')->setRequired(true))
                    ->addSibling((new TypeDocNs\StringDoc())->setFormat('my-format')->setName('name2'))
                    ->addSibling((new TypeDocNs\IntegerDoc())->setName('name3')->setRequired(true))
                    ->setMinItem(2)
                    ->setMaxItem(5)
                    ->setAllowExtraSibling(true)
                    ->setAllowMissingSibling(true)
                    ->setNullable(false)
                    ->setName('my-name')
                    ->setRequired(true)
                    ->setDescription('my-description')
                    ->setExample(['my-example'])
                    ->setDefault(['my-default']),
                'expected' => [
                    'description' => 'my-description',
                    'type' => 'array',
                    'nullable' => false,
                    'default' => ['my-default'],
                    'example' => ['my-example'],
                    'minItems' => 2,
                    'maxItems' => 5,
                    'items' => ['type' => 'string'],
                ],
            ],
            'Fully defined ArrayDoc' => [
                'typeDoc' => (new TypeDocNs\ArrayDoc())
                    ->setItemValidation((new TypeDocNs\StringDoc())->setFormat('my-format'))
                    ->setMinItem(2)
                    ->setMaxItem(5)
                    ->setAllowExtraSibling(true)
                    ->setAllowMissingSibling(true)
                    ->setNullable(false)
                    ->setName('my-name')
                    ->setRequired(true)
                    ->setDescription('my-description')
                    ->setExample(['my-example'])
                    ->setDefault(['my-default']),
                'expected' => [
                    'description' => 'my-description',
                    'type' => 'array',
                    'nullable' => false,
                    'default' => ['my-default'],
                    'example' => ['my-example'],
                    'minItems' => 2,
                    'maxItems' => 5,
                    'items' => ['type' => 'string', 'format' => 'my-format', 'nullable' => true],
                ],
            ],
            'Fully defined ObjectDoc' => [
                'typeDoc' => (new TypeDocNs\ObjectDoc())
                    ->addSibling((new TypeDocNs\BooleanDoc())->setName('name1')->setRequired(true))
                    ->addSibling((new TypeDocNs\StringDoc())->setFormat('my-format')->setName('name2'))
                    ->addSibling((new TypeDocNs\IntegerDoc())->setName('name3')->setRequired(true))
                    ->setMinItem(2)
                    ->setMaxItem(5)
                    ->setAllowExtraSibling(true)
                    ->setAllowMissingSibling(true)
                    ->setNullable(false)
                    ->setName('my-name')
                    ->setRequired(true)
                    ->setDescription('my-description')
                    ->setExample(['my-example'])
                    ->setDefault(['my-default']),
                'expected' => [
                    'description' => 'my-description',
                    'type' => 'object',
                    'nullable' => false,
                    'required' => ['name1', 'name3'],
                    'default' => ['my-default'],
                    'example' => ['my-example'],
                    'minProperties' => 2,
                    'maxProperties' => 5,
                    'additionalProperties' => ['description' => 'Extra property'],
                    'properties' => [
                        'name1' => ['type' => 'boolean', 'nullable' => true],
                        'name2' => ['type' => 'string', 'format' => 'my-format', 'nullable' => true],
                        'name3' => ['type' => 'integer', 'nullable' => true]
                    ],
                ],
            ]
        ];
    }

    /**
     * @return array
     */
    public function provideBasicManagedTypeDocList()
    {
        return [
            'Basic Doc' => [
                'typeDoc' => (new TypeDocNs\TypeDoc())
                    ->addAllowedValue(1)
                    ->addAllowedValue(23),
                'expected' => [
                    'type' => 'string',
                    'nullable' => true,
                    'enum' => [1, 23],
                ],
            ],
            'Basic ScalarDoc' => [
                'typeDoc' => (new TypeDocNs\ScalarDoc())
                    ->setNullable(false)
                    ->setDescription('my-description')
                    ->setExample('my-example')
                ,
                'expected' => [
                    'description' => 'my-description',
                    'type' => 'string',
                    'nullable' => false,
                    'example' => 'my-example',
                ],
            ],
            'Basic BooleanDoc' => [
                'typeDoc' => (new TypeDocNs\BooleanDoc())
                    ->setNullable(false)
                    ->setDefault(false),
                'expected' => [
                    'type' => 'boolean',
                    'nullable' => false,
                    'default' => false,
                ],
            ],
            'Basic StringDoc' => [
                'typeDoc' => (new TypeDocNs\StringDoc())
                    ->setFormat('my-format')
                    ->setDescription('my-description'),
                'expected' => [
                    'description' => 'my-description',
                    'type' => 'string',
                    'format' => 'my-format',
                    'nullable' => true,
                ],
            ],
            'Basic NumberDoc' => [
                'typeDoc' => (new TypeDocNs\NumberDoc())
                    ->setMax(100)
                    ->setExample(230)
                    ->setDefault(0),
                'expected' => [
                    'type' => 'number',
                    'nullable' => true,
                    'default' => 0,
                    'example' => 230,
                    'maximum' => 100,
                ],
            ],
            'Basic FloatDoc' => [
                'typeDoc' => (new TypeDocNs\FloatDoc())
                    ->setMin(10.3)
                    ->setInclusiveMin(false)
                    ->setDescription('my-description')
                    ->setExample(1324.12),
                'expected' => [
                    'description' => 'my-description',
                    'type' => 'number',
                    'nullable' => true,
                    'example' => 1324.12,
                    'minimum' => 10.3,
                    'exclusiveMinimum' => true,
                ],
            ],
            'Basic IntegerDoc' => [
                'typeDoc' => (new TypeDocNs\IntegerDoc())
                    ->setMin(10)
                    ->setMax(100)
                    ->setExample(1)
                    ->setDefault(2),
                'expected' => [
                    'type' => 'integer',
                    'nullable' => true,
                    'default' => 2,
                    'example' => 1,
                    'minimum' => 10,
                    'maximum' => 100,
                ],
            ],
            'Basic CollectionDoc' => [
                'typeDoc' => (new TypeDocNs\CollectionDoc())
                    ->addSibling(new TypeDocNs\StringDoc())
                    ->addSibling(new TypeDocNs\StringDoc())
                    ->addSibling(new TypeDocNs\StringDoc())
                    ->setMaxItem(5)
                    ->setExample(['my-example'])
                    ->setDefault(['my-default']),
                'expected' => [
                    'type' => 'array',
                    'nullable' => true,
                    'default' => ['my-default'],
                    'example' => ['my-example'],
                    'maxItems' => 5,
                    'items' => ['type' => 'string'],
                ],
            ],
            'Basic ArrayDoc' => [
                'typeDoc' => (new TypeDocNs\ArrayDoc())
                    ->setMinItem(2)
                    ->setMaxItem(5)
                    ->setNullable(false)
                    ->setExample(['my-example']),
                'expected' => [
                    'type' => 'array',
                    'nullable' => false,
                    'example' => ['my-example'],
                    'minItems' => 2,
                    'maxItems' => 5,
                    'items' => ['type' => 'string'],
                ],
            ],
            'Basic ObjectDoc' => [
                'typeDoc' => (new TypeDocNs\ObjectDoc())
                    ->addSibling((new TypeDocNs\BooleanDoc())->setName('name1')->setRequired(true))
                    ->addSibling((new TypeDocNs\StringDoc())->setFormat('my-format')->setName('name2'))
                    ->addSibling((new TypeDocNs\IntegerDoc())->setName('name3')->setRequired(true))
                    ->setDescription('my-description')
                    ,
                'expected' => [
                    'description' => 'my-description',
                    'type' => 'object',
                    'nullable' => true,
                    'required' => ['name1', 'name3'],
                    'properties' => [
                        'name1' => ['type' => 'boolean', 'nullable' => true],
                        'name2' => ['type' => 'string', 'format' => 'my-format', 'nullable' => true],
                        'name3' => ['type' => 'integer', 'nullable' => true]
                    ],
                ],
            ]
        ];
    }
}
