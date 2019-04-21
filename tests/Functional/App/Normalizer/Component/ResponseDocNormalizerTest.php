<?php
namespace Tests\Functional\App\Normalizer\Component;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component\ResponseDocNormalizer;
use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component\ShapeNormalizer;
use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Resolver\DefinitionRefResolver;
use Yoanm\JsonRpcServerDoc\Domain\Model\ErrorDoc;
use Yoanm\JsonRpcServerDoc\Domain\Model\MethodDoc;
use Yoanm\JsonRpcServerDoc\Domain\Model\Type as TypeDocNS;

/**
 * @covers \Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component\ResponseDocNormalizer
 *
 * @group ResponseDocNormalizer
 */
class ResponseDocNormalizerTest extends TestCase
{
    /** @var ShapeNormalizer|ObjectProphecy */
    private $shapeNormalizer;
    /** @var ResponseDocNormalizer */
    private $normalizer;

    protected function setUp(): void
    {
        $this->shapeNormalizer = $this->prophesize(ShapeNormalizer::class);

        $this->normalizer = new ResponseDocNormalizer(
            new DefinitionRefResolver(),
            $this->shapeNormalizer->reveal()
        );
    }

    public function testShouldHandleBasicResponse()
    {
        $responseShape = ['responseShape'];
        $defaultExpectedMethodResult = ['description' => 'Method result'];

        /** @var MethodDoc $method */
        $method = new MethodDoc('method-name');

        $this->shapeNormalizer->getResponseShapeDefinition()
            ->willReturn($responseShape)->shouldBeCalled()
        ;

        $this->assertSame(
            [
                'allOf' => [
                    $responseShape,
                    [
                        'type' => 'object',
                        'properties' => ['result' => $defaultExpectedMethodResult],
                    ],
                    [
                        'type' => 'object',
                        'properties' => [
                            'error' => ['type' => 'object']
                        ],
                    ],
                ],
            ],
            $this->normalizer->normalize($method)
        );
    }

    public function testShouldHandleResponseWithMethodResult()
    {
        $responseShape = ['responseShape'];

        /** @var TypeDocNS\TypeDoc|ObjectProphecy $methodResultDoc */
        $methodResultDoc = $this->prophesize(TypeDocNS\TypeDoc::class);
        /** @var MethodDoc $method */
        $method = (new MethodDoc('method-name'))
            ->setResultDoc($methodResultDoc->reveal())
        ;

        $this->shapeNormalizer->getResponseShapeDefinition()
            ->willReturn($responseShape)
            ->shouldBeCalled()
        ;

        $this->assertSame(
            [
                'allOf' => [
                    $responseShape,
                    [
                        'type' => 'object',
                        'properties' => [
                            'result' => ['$ref' => '#/components/schemas/Method-Method-name-Result']
                        ],
                    ],
                    [
                        'type' => 'object',
                        'properties' => [
                            'error' => ['type' => 'object']
                        ],
                    ],
                ],
            ],
            $this->normalizer->normalize($method)
        );
    }

    public function testShouldHandleResponseWithCustomMethodError()
    {
        $responseShape = ['responseShape'];
        $errorId = 'Error-id';
        $errorId2 = 'Error-id-2';

        /** @var ErrorDoc|ObjectProphecy $customMethodErrorDoc */
        $customMethodErrorDoc = $this->prophesize(ErrorDoc::class);
        /** @var ErrorDoc|ObjectProphecy $customMethodErrorDoc2 */
        $customMethodErrorDoc2 = $this->prophesize(ErrorDoc::class);
        /** @var MethodDoc $method */
        $method = (new MethodDoc('method-name'))
            ->addCustomError($customMethodErrorDoc->reveal())
            ->addCustomError($customMethodErrorDoc2->reveal())
        ;

        $customMethodErrorDoc->getIdentifier()
            ->willReturn($errorId)
            ->shouldBeCalled()
        ;
        $customMethodErrorDoc2->getIdentifier()
            ->willReturn($errorId2)
            ->shouldBeCalled()
        ;

        $this->shapeNormalizer->getResponseShapeDefinition()
            ->willReturn($responseShape)
            ->shouldBeCalled()
        ;

        $this->assertSame(
            [
                'allOf' => [
                    $responseShape,
                    [
                        'type' => 'object',
                        'properties' => [
                            'result' => [
                                'description' => 'Method result'
                            ]
                        ],
                    ],
                    [
                        'type' => 'object',
                        'properties' => [
                            'error' => [
                                'oneOf' => [
                                    ['$ref' => '#/components/schemas/Error-Error-id'],
                                    ['$ref' => '#/components/schemas/Error-Error-id-2'],
                                ]
                            ]
                        ],
                    ],
                ],
            ],
            $this->normalizer->normalize($method)
        );
    }

    public function testShouldHandleResponseWithMethodWithGlobalErrorRef()
    {
        $responseShape = ['responseShape'];
        $errorId = 'Error-id';
        $errorId2 = 'Error-id-2';

        /** @var MethodDoc $method */
        $method = (new MethodDoc('method-name'))
            ->addGlobalErrorRef($errorId)
            ->addGlobalErrorRef($errorId2)
        ;

        $this->shapeNormalizer->getResponseShapeDefinition()
            ->willReturn($responseShape)
            ->shouldBeCalled()
        ;

        $this->assertSame(
            [
                'allOf' => [
                    $responseShape,
                    [
                        'type' => 'object',
                        'properties' => [
                            'result' => [
                                'description' => 'Method result'
                            ]
                        ],
                    ],
                    [
                        'type' => 'object',
                        'properties' => [
                            'error' => [
                                'oneOf' => [
                                    ['$ref' => '#/components/schemas/Error-Error-id'],
                                    ['$ref' => '#/components/schemas/Error-Error-id-2'],
                                ]
                            ]
                        ],
                    ],
                ],
            ],
            $this->normalizer->normalize($method)
        );
    }
}
