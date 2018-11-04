<?php
namespace Tests\Functional\App\Normalizer\Component;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component\OperationDocNormalizer;
use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component\RequestDocNormalizer;
use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component\ResponseDocNormalizer;
use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Resolver\DefinitionRefResolver;
use Yoanm\JsonRpcServerDoc\Domain\Model\ErrorDoc;
use Yoanm\JsonRpcServerDoc\Domain\Model\MethodDoc;
use Yoanm\JsonRpcServerDoc\Domain\Model\ServerDoc;

/**
 * @covers \Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component\OperationDocNormalizer
 *
 * @group OperationDocNormalizer
 */
class OperationDocNormalizerTest extends TestCase
{
    /** @var RequestDocNormalizer|ObjectProphecy */
    private $requestDocTransformer;
    /** @var ResponseDocNormalizer|ObjectProphecy */
    private $responseDocNormalizer;
    /** @var OperationDocNormalizer */
    private $normalizer;

    const DEFAULT_REQUEST_DEFINITION = ['default-request-definition'];
    const DEFAULT_RESPONSE_DEFINITION = ['default-response-definition'];
    const DEFAULT_RESPONSE_WITH_SERVER_ERRORS_DEFINITION = ['default-response-with-servers-errors-definition'];

    public function setUp()
    {
        $this->requestDocTransformer = $this->prophesize(RequestDocNormalizer::class);
        $this->responseDocNormalizer = $this->prophesize(ResponseDocNormalizer::class);

        $this->normalizer = new OperationDocNormalizer(
            new DefinitionRefResolver(),
            $this->requestDocTransformer->reveal(),
            $this->responseDocNormalizer->reveal()
        );
    }

    /**
     * @dataProvider provideManagedMethodDocList
     *
     * @param MethodDoc $methodDoc
     * @param ServerDoc $serverDoc
     * @param array    $expected
     */
    public function testShouldHandle(MethodDoc $methodDoc, ServerDoc $serverDoc, $expected)
    {
        $this->requestDocTransformer->normalize($methodDoc)
            ->willReturn(self::DEFAULT_REQUEST_DEFINITION)->shouldBeCalled()
        ;
        if (count($serverDoc->getServerErrorList()) > 0) {
            $this->responseDocNormalizer->normalize($methodDoc, Argument::type('array'))
                ->willReturn(self::DEFAULT_RESPONSE_WITH_SERVER_ERRORS_DEFINITION)->shouldBeCalled()
            ;
        } else {
            $this->responseDocNormalizer->normalize($methodDoc, [])
                ->willReturn(self::DEFAULT_RESPONSE_DEFINITION)->shouldBeCalled();
        }



        $this->assertSame($expected, $this->normalizer->normalize($methodDoc, $serverDoc));
    }

    /**
     * @return array
     */
    public function provideManagedMethodDocList()
    {
        return [
            'Simple Operation' => [
                'methodDoc' => new MethodDoc('my-method-name', 'MethodId'),
                'serverDoc' => new ServerDoc(),
                'expected' => [
                        'summary' => '"my-method-name" json-rpc method',
                        'operationId' => 'MethodId',
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => ['schema' => self::DEFAULT_REQUEST_DEFINITION]
                            ]
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'JSON-RPC response',
                                'content' => [
                                    'application/json' => ['schema' => self::DEFAULT_RESPONSE_DEFINITION]
                                ]
                            ]
                        ]
                    ],
            ],
            'Operation with tags' => [
                'methodDoc' => (new MethodDoc('my-method-name', 'MethodId'))
                    ->addTag('tag1')
                    ->addTag('tag2'),
                'serverDoc' => new ServerDoc(),
                'expected' => [
                    'summary' => '"my-method-name" json-rpc method',
                    'tags' => ['tag1', 'tag2'],
                    'operationId' => 'MethodId',
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => ['schema' => self::DEFAULT_REQUEST_DEFINITION]
                        ]
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'JSON-RPC response',
                            'content' => [
                                'application/json' => ['schema' => self::DEFAULT_RESPONSE_DEFINITION]
                            ]
                        ]
                    ]
                ],
            ],
            'Operation with description' => [
                'methodDoc' => (new MethodDoc('my-method-name', 'MethodId'))
                    ->setDescription('method-description'),
                'serverDoc' => new ServerDoc(),
                'expected' => [
                    'summary' => '"my-method-name" json-rpc method',
                    'description' => 'method-description',
                    'operationId' => 'MethodId',
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => ['schema' => self::DEFAULT_REQUEST_DEFINITION]
                        ]
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'JSON-RPC response',
                            'content' => [
                                'application/json' => ['schema' => self::DEFAULT_RESPONSE_DEFINITION]
                            ]
                        ]
                    ]
                ],
            ],
            'Server with custom errors' => [
                'methodDoc' => new MethodDoc('my-method-name', 'MethodId'),
                'serverDoc' => (new ServerDoc())
                    ->addServerError(new ErrorDoc('Custom1', 1))
                    ->addServerError(new ErrorDoc('Custom2', 2)),
                'expected' => [
                    'summary' => '"my-method-name" json-rpc method',
                    'operationId' => 'MethodId',
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => ['schema' => self::DEFAULT_REQUEST_DEFINITION]
                        ]
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'JSON-RPC response',
                            'content' => [
                                'application/json' => ['schema' => self::DEFAULT_RESPONSE_WITH_SERVER_ERRORS_DEFINITION]
                            ]
                        ]
                    ]
                ],
            ],
            'Fully configured operation' => [
                'methodDoc' => (new MethodDoc('my-method-name', 'MethodId'))
                    ->addTag('tag1')
                    ->addTag('tag2')
                    ->setDescription('method-description')
                ,
                'serverDoc' => new ServerDoc(),
                'expected' => [
                    'summary' => '"my-method-name" json-rpc method',
                    'description' => 'method-description',
                    'tags' => ['tag1', 'tag2'],
                    'operationId' => 'MethodId',
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => ['schema' => self::DEFAULT_REQUEST_DEFINITION]
                        ]
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'JSON-RPC response',
                            'content' => [
                                'application/json' => ['schema' => self::DEFAULT_RESPONSE_DEFINITION]
                            ]
                        ]
                    ]
                ],
            ],
        ];
    }
}
