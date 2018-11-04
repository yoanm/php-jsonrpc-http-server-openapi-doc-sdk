<?php
namespace Tests\Functional\App\Normalizer\Component;

use PHPUnit\Framework\TestCase;
use Tests\Common\TypeDoc\NotManagedTypeDoc;
use Tests\Common\TypeDoc\NullDoc;
use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component\SchemaTypeNormalizer;
use Yoanm\JsonRpcServerDoc\Domain\Model\Type as TypeDocNS;

/**
 * @covers \Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component\SchemaTypeNormalizer
 *
 * @group SchemaTypeNormalizer
 */
class SchemaTypeNormalizerTest extends TestCase
{
    /** @var SchemaTypeNormalizer */
    private $normalizer;

    public function setUp()
    {
        $this->normalizer = new SchemaTypeNormalizer();
    }

    /**
     * @dataProvider provideManagedTypeDocClassList
     *
     * @param TypeDocNS\TypeDoc $typeDoc
     * @param string            $expected
     */
    public function testShouldHandle($typeDoc, $expected)
    {
        $this->assertSame(
            $expected,
            $this->normalizer->normalize($typeDoc)
        );
    }

    public function testShouldFallbackToString()
    {
        $this->assertSame(
            'string',
            $this->normalizer->normalize(new NotManagedTypeDoc())
        );
    }

    /**
     * @return array
     */
    public function provideManagedTypeDocClassList()
    {
        return [
            'array type' => [
                'typeDocClass' => new TypeDocNS\ArrayDoc(),
                'expected' => 'array'
            ],
            'boolean type' => [
                'typeDocClass' => new TypeDocNS\BooleanDoc(),
                'expected' => 'boolean'
            ],
            'collection type' => [
                'typeDocClass' => new TypeDocNS\CollectionDoc(),
                'expected' => 'array'
            ],
            'float type' => [
                'typeDocClass' => new TypeDocNS\FloatDoc(),
                'expected' => 'number'
            ],
            'integer type' => [
                'typeDocClass' => new TypeDocNS\IntegerDoc(),
                'expected' => 'integer'
            ],
            'number type' => [
                'typeDocClass' => new TypeDocNS\NumberDoc(),
                'expected' => 'number'
            ],
            'object type' => [
                'typeDocClass' => new TypeDocNS\ObjectDoc(),
                'expected' => 'object'
            ],
            'scalar type' => [
                'typeDocClass' => new TypeDocNS\ScalarDoc(),
                'expected' => 'string'
            ],
            'string type' => [
                'typeDocClass' => new TypeDocNS\StringDoc(),
                'expected' => 'string'
            ],
            'null type' => [
                'typeDocClass' => new NullDoc(),
                'expected' => 'null'
            ],
        ];
    }
}
