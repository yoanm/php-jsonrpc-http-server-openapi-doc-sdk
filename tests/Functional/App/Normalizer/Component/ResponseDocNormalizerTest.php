<?php
namespace Tests\Functional\App\Normalizer\Component;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component\ResponseDocNormalizer;
use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component\ShapeNormalizer;
use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Resolver\DefinitionRefResolver;
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

    public function setUp()
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
        $defaultErrorRef = 'default-error-ref';
        $methodResultDefinitionId = 'method-result-definition-id';
        $methodResultDefinitionIdRef = 'method-result-definition-id-ref';

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
}
