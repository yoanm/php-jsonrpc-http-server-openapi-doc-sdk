<?php
namespace Tests\Functional\App\Normalizer\Component;

use PHPUnit\Framework\TestCase;
use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component\ShapeNormalizer;

/**
 * @covers \Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component\ShapeNormalizer
 *
 * @group ShapeNormalizer
 */
class ShapeNormalizerTest extends TestCase
{
    /** @var ShapeNormalizer */
    private $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new ShapeNormalizer();
    }

    public function testShouldManageRequestShape()
    {
        $shape = $this->normalizer->getRequestShapeDefinition();
        $this->assertSame(
            $shape['type'],
            'object',
            'Unexpected schema type for request'
        );
        // Check minimal required properties and properties list
        $this->assertSame(
            $shape['required'],
            ['jsonrpc', 'method'],
            'Unexpected required properties for request'
        );
        $this->assertSame(
            array_keys($shape['properties']),
            ['id', 'jsonrpc', 'method', 'params'],
            'Unexpected properties for request'
        );
        // Check properties type
        $this->assertSame(
            $shape['properties']['id']['oneOf'],
            [['type' => 'string'], ['type' => 'number']],
            'Unexpected id property type for request'
        );
        $this->assertSame(
            $shape['properties']['jsonrpc']['type'],
            'string',
            'Unexpected jsonrpc property type for request'
        );
        $this->assertSame(
            $shape['properties']['method']['type'],
            'string',
            'Unexpected method property type for request'
        );
        // Check example and title
        $this->assertSame(
            $shape['properties']['id']['example'],
            'req_id',
            'Unexpected id property example for request'
        );
        $this->assertSame(
            $shape['properties']['jsonrpc']['example'],
            '2.0',
            'Unexpected jsonrpc property example for request'
        );
        $this->assertSame(
            $shape['properties']['params']['title'],
            'Method parameters',
            'Unexpected params property title for request'
        );
    }

    public function testShouldManageResponseShape()
    {
        $shape = $this->normalizer->getResponseShapeDefinition();
        $this->assertSame(
            $shape['type'],
            'object',
            'Unexpected schema type for response'
        );
        // Check minimal required properties and properties list
        $this->assertSame(
            $shape['required'],
            ['jsonrpc'],
            'Unexpected required properties for response'
        );
        $this->assertSame(
            array_keys($shape['properties']),
            ['id', 'jsonrpc', 'result', 'error'],
            'Unexpected properties for response'
        );
        // Check properties type
        $this->assertSame(
            $shape['properties']['id']['oneOf'],
            [['type' => 'string'], ['type' => 'number']],
            'Unexpected id property type for response'
        );
        $this->assertSame(
            $shape['properties']['jsonrpc']['type'],
            'string',
            'Unexpected jsonrpc property type for response'
        );
        // Check example and title
        $this->assertSame(
            $shape['properties']['id']['example'],
            'req_id',
            'Unexpected id property example for response'
        );
        $this->assertSame(
            $shape['properties']['jsonrpc']['example'],
            '2.0',
            'Unexpected jsonrpc property example for response'
        );
        $this->assertSame(
            $shape['properties']['result']['title'],
            'Result',
            'Unexpected result property title for response'
        );
        $this->assertSame(
            $shape['properties']['error']['title'],
            'Error',
            'Unexpected error property title for response'
        );
    }

    public function testShouldManageErrorShape()
    {
        $shape = $this->normalizer->getErrorShapeDefinition();
        $this->assertSame(
            $shape['type'],
            'object',
            'Unexpected schema type for error'
        );
        // Check minimal required properties and properties list
        $this->assertSame(
            $shape['required'],
            ['code', 'message'],
            'Unexpected required properties for error'
        );
        $this->assertSame(
            array_keys($shape['properties']),
            ['code', 'message'],
            'Unexpected properties for error'
        );
        // Check properties type
        $this->assertSame(
            $shape['properties']['code']['type'],
            'number',
            'Unexpected code property type for error'
        );
        $this->assertSame(
            $shape['properties']['message']['type'],
            'string',
            'Unexpected message property type for error'
        );
    }
}
