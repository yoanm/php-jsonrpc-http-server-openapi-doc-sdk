<?php
namespace Tests\Functional\App\Normalizer\Component;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component\RequestDocNormalizer;
use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component\ShapeNormalizer;
use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Resolver\DefinitionRefResolver;
use Yoanm\JsonRpcServerDoc\Domain\Model\MethodDoc;
use Yoanm\JsonRpcServerDoc\Domain\Model\Type as TypeDocNs;

/**
 * @covers \Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component\RequestDocNormalizer
 *
 * @group RequestDocNormalizer
 */
class RequestDocNormalizerTest extends TestCase
{
    /** @var DefinitionRefResolver|ObjectProphecy */
    private $definitionRefResolver;
    /** @var ShapeNormalizer|ObjectProphecy */
    private $shapeNormalizer;
    /** @var RequestDocNormalizer */
    private $normalizer;

    const DEFAULT_REQUEST_SHAPE = ['default-request-shape'];
    const DEFAULT_DEFINITION_ID = 'default-definition-id';
    const DEFAULT_DEFINITION_ID_REF = 'default-definition-id-ref';

    public function setUp()
    {
        $this->definitionRefResolver = $this->prophesize(DefinitionRefResolver::class);
        $this->shapeNormalizer = $this->prophesize(ShapeNormalizer::class);

        $this->normalizer = new RequestDocNormalizer(
            $this->definitionRefResolver->reveal(),
            $this->shapeNormalizer->reveal()
        );
    }

    /**
     * @dataProvider provideManagedMethodDocList
     *
     * @param MethodDoc $methodDoc
     * @param array    $expected
     */
    public function testShouldHandle(MethodDoc $methodDoc, $expected)
    {
        $this->shapeNormalizer->getRequestShapeDefinition()
            ->willReturn(self::DEFAULT_REQUEST_SHAPE)->shouldBeCalled()
        ;

        if ($methodDoc->getParamsDoc()) {
            $this->definitionRefResolver->getMethodDefinitionId(
                $methodDoc,
                DefinitionRefResolver::METHOD_PARAMS_DEFINITION_TYPE
            )
                ->willReturn(self::DEFAULT_DEFINITION_ID)->shouldBeCalled()
            ;
            $this->definitionRefResolver->getDefinitionRef(self::DEFAULT_DEFINITION_ID)
                ->willReturn(self::DEFAULT_DEFINITION_ID_REF)->shouldBeCalled()
            ;
        }

        $this->assertSame($expected, $this->normalizer->normalize($methodDoc));
    }

    /**
     * @return array
     */
    public function provideManagedMethodDocList()
    {
        return [
            'Simple Request' => [
                'methodDoc' => new MethodDoc('my-method-name'),
                'expected' => [
                    'allOf' => [
                        self::DEFAULT_REQUEST_SHAPE,
                        [
                            'type' => 'object',
                            'properties' => [
                                'method' => ['example' => 'my-method-name'],
                            ],
                        ],
                    ]
                ],
            ],
            'Request with params' => [
                'methodDoc' => (new MethodDoc('my-method-name'))
                    ->setParamsDoc(new TypeDocNs\ObjectDoc()),
                'expected' => [
                    'allOf' => [
                        self::DEFAULT_REQUEST_SHAPE,
                        [
                            'type' => 'object',
                            'required' => ['params'],
                            'properties' => [
                                'params' => ['$ref' => self::DEFAULT_DEFINITION_ID_REF],
                            ],
                        ],
                        [
                            'type' => 'object',
                            'properties' => [
                                'method' => ['example' => 'my-method-name'],
                            ],
                        ],
                    ]
                ],
            ]
        ];
    }
}
