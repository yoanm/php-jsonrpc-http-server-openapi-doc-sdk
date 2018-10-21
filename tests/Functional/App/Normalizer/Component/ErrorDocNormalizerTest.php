<?php
namespace Tests\Functional\App\Normalizer\Component;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component\ErrorDocNormalizer;
use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component\ShapeNormalizer;
use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component\TypeDocNormalizer;
use Yoanm\JsonRpcServerDoc\Domain\Model\ErrorDoc;
use Yoanm\JsonRpcServerDoc\Domain\Model\Type as TypeDocNs;

/**
 * @covers \Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component\ErrorDocNormalizer
 *
 * @group ErrorDocNormalizer
 */
class ErrorDocNormalizerTest extends TestCase
{
    /** @var TypeDocNormalizer|ObjectProphecy */
    private $typeDocNormalizer;
    /** @var ShapeNormalizer|ObjectProphecy */
    private $shapeNormalizer;
    /** @var ErrorDocNormalizer */
    private $normalizer;

    const DEFAULT_ERROR_SHAPE = ['default-error-shape'];
    const DEFAULT_DATA_DOC = ['default-data-doc'];

    public function setUp()
    {
        $this->typeDocNormalizer = $this->prophesize(TypeDocNormalizer::class);
        $this->shapeNormalizer = $this->prophesize(ShapeNormalizer::class);

        $this->normalizer = new ErrorDocNormalizer(
            $this->typeDocNormalizer->reveal(),
            $this->shapeNormalizer->reveal()
        );
    }

    /**
     * @dataProvider provideManagedErrorDocList
     *
     * @param ErrorDoc $errorDoc
     * @param array    $expected
     */
    public function testShouldHandle(ErrorDoc $errorDoc, $expected)
    {
        $this->shapeNormalizer->getErrorShapeDefinition()
            ->willReturn(self::DEFAULT_ERROR_SHAPE)->shouldBeCalled()
        ;

        if ($errorDoc->getDataDoc()) {
            $this->typeDocNormalizer->normalize($errorDoc->getDataDoc())
                ->willReturn(self::DEFAULT_DATA_DOC)->shouldBeCalled()
            ;
        }

        $this->assertSame($expected, $this->normalizer->normalize($errorDoc));
    }

    /**
     * @return array
     */
    public function provideManagedErrorDocList()
    {
        return [
            'Simple Error' => [
                'errorDoc' => new ErrorDoc('my-title', 234),
                'expected' => [
                    'title' => 'my-title',
                    'allOf' => [
                        self::DEFAULT_ERROR_SHAPE,
                        [
                            'type' => 'object',
                            'required' => ['code'],
                            'properties' => [
                                'code' => ['example' => 234]
                            ],
                        ],
                    ]
                ],
            ],
            'Error with message' => [
                'errorDoc' => new ErrorDoc('my-title', 234, 'my-message'),
                'expected' => [
                    'title' => 'my-title',
                    'allOf' => [
                        self::DEFAULT_ERROR_SHAPE,
                        [
                            'type' => 'object',
                            'required' => ['code'],
                            'properties' => [
                                'code' => ['example' => 234],
                                'message' => ['example' => 'my-message']
                            ],
                        ],
                    ]
                ],
            ],
            'Error with data' => [
                'errorDoc' => new ErrorDoc('my-title', 234, null, new TypeDocNs\StringDoc()),
                'expected' => [
                    'title' => 'my-title',
                    'allOf' => [
                        self::DEFAULT_ERROR_SHAPE,
                        [
                            'type' => 'object',
                            'required' => ['code'],
                            'properties' => [
                                'code' => ['example' => 234],
                                'data' => self::DEFAULT_DATA_DOC,
                            ],
                        ],
                    ]
                ],
            ],
            'Error with required data' => [
                'errorDoc' => new ErrorDoc('my-title', 234, null, (new TypeDocNs\StringDoc())->setRequired(true)),
                'expected' => [
                    'title' => 'my-title',
                    'allOf' => [
                        self::DEFAULT_ERROR_SHAPE,
                        [
                            'type' => 'object',
                            'required' => ['code', 'data'],
                            'properties' => [
                                'code' => ['example' => 234],
                                'data' => self::DEFAULT_DATA_DOC,
                            ],
                        ],
                    ]
                ],
            ],
        ];
    }
}
