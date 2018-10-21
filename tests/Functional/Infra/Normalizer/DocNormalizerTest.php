<?php
namespace Tests\Functional\App\Normalizer\Component;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component\ExternalSchemaListDocNormalizer;
use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component\OperationDocNormalizer;
use Yoanm\JsonRpcHttpServerOpenAPIDoc\Infra\Normalizer\DocNormalizer;
use Yoanm\JsonRpcServerDoc\Domain\Model\HttpServerDoc;
use Yoanm\JsonRpcServerDoc\Domain\Model\MethodDoc;
use Yoanm\JsonRpcServerDoc\Domain\Model\TagDoc;

/**
 * @covers \Yoanm\JsonRpcHttpServerOpenAPIDoc\Infra\Normalizer\DocNormalizer
 *
 * @group DocNormalizer
 */
class DocNormalizerTest extends TestCase
{
    /** @var ExternalSchemaListDocNormalizer|ObjectProphecy */
    private $externalSchemaListDocNormalizer;
    /** @var OperationDocNormalizer|ObjectProphecy */
    private $operationDocNormalizer;
    /** @var DocNormalizer */
    private $normalizer;

    const DEFAULT_OPERATION_DOC = ['default-opertation-doc'];
    const DEFAULT_EXTERNAL_LIST_DOC = ['default-external-list-doc'];

    public function setUp()
    {
        $this->externalSchemaListDocNormalizer = $this->prophesize(ExternalSchemaListDocNormalizer::class);
        $this->operationDocNormalizer = $this->prophesize(OperationDocNormalizer::class);

        $this->normalizer = new DocNormalizer(
            $this->externalSchemaListDocNormalizer->reveal(),
            $this->operationDocNormalizer->reveal()
        );
    }

    /**
     * @dataProvider provideManagedErrorDocList
     *
     * @param HttpServerDoc $serverDoc
     * @param array         $expected
     */
    public function testShouldHandle(HttpServerDoc $serverDoc, $expected)
    {
        $this->externalSchemaListDocNormalizer->normalize($serverDoc)
            ->willReturn(self::DEFAULT_EXTERNAL_LIST_DOC)->shouldBeCalled()
        ;

        foreach ($serverDoc->getMethodList() as $method) {
            $this->operationDocNormalizer->normalize($method, $serverDoc)
                ->willReturn(self::DEFAULT_OPERATION_DOC)->shouldBeCalled()
            ;
        }

        $this->assertSame($expected, $this->normalizer->normalize($serverDoc));
    }

    /**
     * @return array
     */
    public function provideManagedErrorDocList()
    {
        return [
            'Simple Doc' => [
                'errorDoc' => new HttpServerDoc(),
                'expected' => [
                    'openapi' => '3.0.0',
                    'components' => ['schemas' => self::DEFAULT_EXTERNAL_LIST_DOC],
                ],
            ],
            'Doc with name' => [
                'errorDoc' => (new HttpServerDoc())
                    ->setName('my-name')
                ,
                'expected' => [
                    'openapi' => '3.0.0',
                    'info' => ['title' => 'my-name'],
                    'components' => ['schemas' => self::DEFAULT_EXTERNAL_LIST_DOC],
                ],
            ],
            'Doc with version' => [
                'errorDoc' => (new HttpServerDoc())
                    ->setVersion('my-version')
                ,
                'expected' => [
                    'openapi' => '3.0.0',
                    'info' => ['version' => 'my-version'],
                    'components' => ['schemas' => self::DEFAULT_EXTERNAL_LIST_DOC],
                ],
            ],
            'Doc with tags' => [
                'errorDoc' => (new HttpServerDoc())
                    ->addTag(new TagDoc('tag1'))
                    ->addTag((new TagDoc('tag2'))->setDescription('tag2 desc'))
                ,
                'expected' => [
                    'openapi' => '3.0.0',
                    'tags' => [
                        ['name' => 'tag1'],
                        ['name' => 'tag2', 'description' => 'tag2 desc'],
                    ],
                    'components' => ['schemas' => self::DEFAULT_EXTERNAL_LIST_DOC],
                ],
            ],
            'Doc with host' => [
                'errorDoc' => (new HttpServerDoc())
                    ->setHost('my-host')
                ,
                'expected' => [
                    'openapi' => '3.0.0',
                    'servers' => [['url' => 'http://my-host']],
                    'components' => ['schemas' => self::DEFAULT_EXTERNAL_LIST_DOC],
                ],
            ],
            'Doc with basePath' => [
                'errorDoc' => (new HttpServerDoc())
                    ->setHost('my-host')
                    ->setBasePath('/my-basePath')
                ,
                'expected' => [
                    'openapi' => '3.0.0',
                    'servers' => [['url' => 'http://my-host/my-basePath']],
                    'components' => ['schemas' => self::DEFAULT_EXTERNAL_LIST_DOC],
                ],
            ],
            'Doc with schemes' => [
                'errorDoc' => (new HttpServerDoc())
                    ->setHost('my-host')
                    ->setSchemeList(['my-scheme', 'my-scheme2'])
                ,
                'expected' => [
                    'openapi' => '3.0.0',
                    'servers' => [['url' => 'my-scheme://my-host'], ['url' => 'my-scheme2://my-host']],
                    'components' => ['schemas' => self::DEFAULT_EXTERNAL_LIST_DOC],
                ],
            ],
            'Doc with methods' => [
                'errorDoc' => (new HttpServerDoc())
                    ->addMethod(new MethodDoc('method-1', 'MethodId1'))
                    ->addMethod(new MethodDoc('method-2', 'MethodId2'))
                ,
                'expected' => [
                    'openapi' => '3.0.0',
                    'paths' => [
                        '/MethodId1/..' => ['post' => self::DEFAULT_OPERATION_DOC],
                        '/MethodId2/..' => ['post' => self::DEFAULT_OPERATION_DOC],
                    ],
                    'components' => ['schemas' => self::DEFAULT_EXTERNAL_LIST_DOC],
                ],
            ],
            'Doc with methods and endpoint' => [
                'errorDoc' => (new HttpServerDoc())
                    ->setEndpoint('/my-endpoint')
                    ->addMethod(new MethodDoc('method-1', 'MethodId1'))
                    ->addMethod(new MethodDoc('method-2', 'MethodId2'))
                ,
                'expected' => [
                    'openapi' => '3.0.0',
                    'paths' => [
                        '/MethodId1/../my-endpoint' => ['post' => self::DEFAULT_OPERATION_DOC],
                        '/MethodId2/../my-endpoint' => ['post' => self::DEFAULT_OPERATION_DOC],
                    ],
                    'components' => ['schemas' => self::DEFAULT_EXTERNAL_LIST_DOC],
                ],
            ],
            'Fully configured Doc' => [
                'errorDoc' => (new HttpServerDoc())
                    ->setHost('my-host')
                    ->setBasePath('/my-basePath')
                    ->setEndpoint('/my-endpoint')
                    ->setName('my-name')
                    ->setVersion('my-version')
                    ->addTag(new TagDoc('tag1'))
                    ->addTag((new TagDoc('tag2'))->setDescription('tag2 desc'))
                    ->addMethod(new MethodDoc('method-1', 'MethodId1'))
                    ->addMethod(new MethodDoc('method-2', 'MethodId2'))
                ,
                'expected' => [
                    'openapi' => '3.0.0',
                    'info' => [
                        'title' => 'my-name',
                        'version' => 'my-version',
                    ],
                    'servers' => [['url' => 'http://my-host/my-basePath']],
                    'tags' => [
                        ['name' => 'tag1'],
                        ['name' => 'tag2', 'description' => 'tag2 desc'],
                    ],
                    'paths' => [
                        '/MethodId1/../my-endpoint' => ['post' => self::DEFAULT_OPERATION_DOC],
                        '/MethodId2/../my-endpoint' => ['post' => self::DEFAULT_OPERATION_DOC],
                    ],
                    'components' => ['schemas' => self::DEFAULT_EXTERNAL_LIST_DOC],
                ],
            ],
        ];
    }
}
