<?php
namespace Tests\Functional\App\Resolver;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Resolver\DefinitionRefResolver;
use Yoanm\JsonRpcServerDoc\Domain\Model\ErrorDoc;
use Yoanm\JsonRpcServerDoc\Domain\Model\MethodDoc;

/**
 * @covers \Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Resolver\DefinitionRefResolver
 *
 * @group DefinitionRefResolver
 */
class DefinitionRefResolverTest extends TestCase
{
    /** @var DefinitionRefResolver */
    private $resolver;

    public function setUp()
    {
        $this->resolver = new DefinitionRefResolver();
    }

    public function testGetDefinitionRefShouldReturnValidSwaggerRef()
    {
        $this->assertSame(
            '#/components/schemas/my-path',
            $this->resolver->getDefinitionRef('my-path')
        );
    }

    /**
     * @dataProvider provideHandledMethodDefinitionIdList
     *
     * @param string $identifier
     * @param string $definitionType
     * @param string $expected
     */
    public function testGetMethodDefinitionIdShouldHandle($identifier, $definitionType, $expected)
    {
        /** @var MethodDoc|ObjectProphecy $method */
        $method = $this->prophesize(MethodDoc::class);
        $method->getIdentifier()->willReturn($identifier)->shouldBeCalled();

        $this->assertSame(
            $expected,
            $this->resolver->getMethodDefinitionId($method->reveal(), $definitionType)
        );
    }

    public function provideHandledMethodDefinitionIdList()
    {
        return [
            'Method result definition type' => [
                'identifier' => 'my-method_id',
                'definitionType' => DefinitionRefResolver::METHOD_RESULT_DEFINITION_TYPE,
                'expected' => 'Method-my-method_id-Result'
            ],
            'Method params definition type' => [
                'identifier' => 'my-method_id',
                'definitionType' => DefinitionRefResolver::METHOD_PARAMS_DEFINITION_TYPE,
                'expected' => 'Method-my-method_id-RequestParams'
            ],
            'Unhandled definition type' => [
                'identifier' => 'my-method_id',
                'definitionType' => 'not handled-definition_type',
                'expected' => 'UNKNONWN_METHOD-my-method_id'
            ],
        ];
    }

    /**
     * @dataProvider provideHandledErrorDefinitionIdList
     *
     * @param string $identifier
     * @param string $definitionType
     * @param string $expected
     */
    public function testGetErrorDefinitionIdShouldHandle($identifier, $definitionType, $expected)
    {
        /** @var ErrorDoc|ObjectProphecy $method */
        $method = $this->prophesize(ErrorDoc::class);
        $method->getIdentifier()->willReturn($identifier)->shouldBeCalled();

        $this->assertSame(
            $expected,
            $this->resolver->getErrorDefinitionId($method->reveal(), $definitionType)
        );
    }

    public function provideHandledErrorDefinitionIdList()
    {
        return [
            'Custom error definition type' => [
                'identifier' => 'my-error_id',
                'definitionType' => DefinitionRefResolver::CUSTOM_ERROR_DEFINITION_TYPE,
                'expected' => 'Error-my-error_id'
            ],
            'Server error definition type' => [
                'identifier' => 'my-error_id',
                'definitionType' => DefinitionRefResolver::SERVER_ERROR_DEFINITION_TYPE,
                'expected' => 'ServerError-my-error_id'
            ],
            'Unhandled definition type' => [
                'identifier' => 'my-error_id',
                'definitionType' => 'not handled-definition_type',
                'expected' => 'UNKNONWN_ERROR-my-error_id'
            ],
        ];
    }
}
