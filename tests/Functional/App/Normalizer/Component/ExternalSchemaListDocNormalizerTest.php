<?php
namespace Tests\Functional\App\Normalizer\Component;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component\ErrorDocNormalizer;
use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component\ExternalSchemaListDocNormalizer;
use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component\TypeDocNormalizer;
use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Resolver\DefinitionRefResolver;
use Yoanm\JsonRpcServerDoc\Domain\Model\ErrorDoc;
use Yoanm\JsonRpcServerDoc\Domain\Model\MethodDoc;
use Yoanm\JsonRpcServerDoc\Domain\Model\ServerDoc;
use Yoanm\JsonRpcServerDoc\Domain\Model\Type as TypeDocNS;

/**
 * @covers \Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component\ExternalSchemaListDocNormalizer
 *
 * @group ExternalSchemaListDocNormalizer
 */
class ExternalSchemaListDocNormalizerTest extends TestCase
{
    /** @var TypeDocNormalizer|ObjectProphecy */
    private $typeDocNormalizer;
    /** @var ErrorDocNormalizer|ObjectProphecy */
    private $errorDocNormalizer;
    /** @var ExternalSchemaListDocNormalizer */
    private $normalizer;

    protected function setUp(): void
    {
        $this->typeDocNormalizer = $this->prophesize(TypeDocNormalizer::class);
        $this->errorDocNormalizer = $this->prophesize(ErrorDocNormalizer::class);

        $this->normalizer = new ExternalSchemaListDocNormalizer(
            new DefinitionRefResolver(),
            $this->typeDocNormalizer->reveal(),
            $this->errorDocNormalizer->reveal()
        );
    }

    public function testShouldAppendServerErrorList()
    {
        $normalizedServerError1 = ['normalized-server-error1'];
        $normalizedServerError2 = ['normalized-server-error2'];
        $serverError1 = new ErrorDoc('firstError', 1);
        $serverError2 = new ErrorDoc('secondError', 2);
        $doc = new ServerDoc();
        $doc->addServerError($serverError1)
            ->addServerError($serverError2)
        ;

        $this->errorDocNormalizer->normalize($serverError1)
            ->willReturn($normalizedServerError1)->shouldBeCalled()
        ;
        $this->errorDocNormalizer->normalize($serverError2)
            ->willReturn($normalizedServerError2)->shouldBeCalled()
        ;

        $normalizedDoc = $this->normalizer->normalize($doc);

        $this->assertSame(
            $normalizedServerError1,
            $normalizedDoc['ServerError-FirstError1']
        );
        $this->assertSame(
            $normalizedServerError2,
            $normalizedDoc['ServerError-SecondError2']
        );
    }

    public function testShouldAppendGlobalErrorList()
    {
        $normalizedGlobalError1 = ['normalized-global-error1'];
        $normalizedGlobalError2 = ['normalized-global-error2'];
        $globalError1 = new ErrorDoc('firstError', 1);
        $globalError2 = new ErrorDoc('secondError', 2);
        $doc = new ServerDoc();
        $doc->addGlobalError($globalError1)
            ->addGlobalError($globalError2)
        ;

        $this->errorDocNormalizer->normalize($globalError1)
            ->willReturn($normalizedGlobalError1)->shouldBeCalled()
        ;
        $this->errorDocNormalizer->normalize($globalError2)
            ->willReturn($normalizedGlobalError2)->shouldBeCalled()
        ;

        $normalizedDoc = $this->normalizer->normalize($doc);

        $this->assertSame(
            $normalizedGlobalError1,
            $normalizedDoc['Error-FirstError1']
        );
        $this->assertSame(
            $normalizedGlobalError2,
            $normalizedDoc['Error-SecondError2']
        );
    }

    public function testShouldAppendCustomMethodErrorList()
    {
        $normalizedCustomMethodError1 = ['normalized-customMethod-error1'];
        $normalizedCustomMethodError2 = ['normalized-customMethod-error2'];
        $customMethodError1 = new ErrorDoc('firstCustomError', 1);
        $customMethodError2 = new ErrorDoc('secondCustomError', 2);
        $doc = new ServerDoc();
        $doc->addMethod((new MethodDoc('method-name'))->addCustomError($customMethodError1))
            ->addMethod((new MethodDoc('method-name-2'))->addCustomError($customMethodError2))
        ;

        $this->errorDocNormalizer->normalize($customMethodError1)
            ->willReturn($normalizedCustomMethodError1)->shouldBeCalled()
        ;
        $this->errorDocNormalizer->normalize($customMethodError2)
            ->willReturn($normalizedCustomMethodError2)->shouldBeCalled()
        ;

        $normalizedDoc = $this->normalizer->normalize($doc);

        $this->assertSame(
            $normalizedCustomMethodError1,
            $normalizedDoc['Error-FirstCustomError1']
        );
        $this->assertSame(
            $normalizedCustomMethodError2,
            $normalizedDoc['Error-SecondCustomError2']
        );
    }

    public function testShouldAppendMethodResultList()
    {
        $normalizedResult1 = ['normalized-result1'];
        $normalizedResult2 = ['normalized-result2'];
        $result1 = new TypeDocNS\StringDoc();
        $result2 = new TypeDocNS\BooleanDoc();
        $doc = new ServerDoc();
        $doc->addMethod((new MethodDoc('method-name'))->setResultDoc($result1))
            ->addMethod((new MethodDoc('method-name-2')))
            ->addMethod((new MethodDoc('method-name-3'))->setResultDoc($result2))
        ;

        $this->typeDocNormalizer->normalize($result1)
            ->willReturn($normalizedResult1)->shouldBeCalled()
        ;
        $this->typeDocNormalizer->normalize($result2)
            ->willReturn($normalizedResult2)->shouldBeCalled()
        ;

        $normalizedDoc = $this->normalizer->normalize($doc);

        $this->assertSame(
            $normalizedResult1,
            $normalizedDoc['Method-Method-name-Result']
        );
        $this->assertSame(
            $normalizedResult2,
            $normalizedDoc['Method-Method-name-3-Result']
        );
    }

    public function testShouldAppendMethodParamsList()
    {
        $normalizedParams1 = ['normalized-params1'];
        $normalizedParams2 = ['normalized-params2'];
        $params1 = new TypeDocNS\ObjectDoc();
        $params2 = new TypeDocNS\ArrayDoc();
        $doc = new ServerDoc();
        $doc->addMethod((new MethodDoc('method-name'))->setParamsDoc($params1))
            ->addMethod((new MethodDoc('method-name-2')))
            ->addMethod((new MethodDoc('method-name-3'))->setParamsDoc($params2))
        ;

        $this->typeDocNormalizer->normalize($params1)
            ->willReturn($normalizedParams1)->shouldBeCalled()
        ;
        $this->typeDocNormalizer->normalize($params2)
            ->willReturn($normalizedParams2)->shouldBeCalled()
        ;

        $normalizedDoc = $this->normalizer->normalize($doc);

        $this->assertSame(
            $normalizedParams1,
            $normalizedDoc['Method-Method-name-RequestParams']
        );
        $this->assertSame(
            $normalizedParams2,
            $normalizedDoc['Method-Method-name-3-RequestParams']
        );
    }
}
