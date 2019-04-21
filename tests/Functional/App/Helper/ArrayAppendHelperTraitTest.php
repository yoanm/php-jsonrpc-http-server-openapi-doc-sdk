<?php
namespace Tests\Functional\App\Helper;

use PHPUnit\Framework\TestCase;
use Tests\Common\Helper\ConcreteArrayAppendHelper;
use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Helper\ArrayAppendHelperTrait;
use Yoanm\JsonRpcServerDoc\Domain\Model\ErrorDoc;

/**
 * @covers \Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Helper\ArrayAppendHelperTrait
 *
 * @group ArrayAppendHelperTrait
 */
class ArrayAppendHelperTraitTest extends TestCase
{
    /** @var ArrayAppendHelperTrait|ConcreteArrayAppendHelper */
    private $helper;

    const DEFAULT_ERROR_SHAPE = ['default-error-shape'];
    const DEFAULT_DATA_DOC = ['default-data-doc'];

    protected function setUp(): void
    {
        $this->helper = new ConcreteArrayAppendHelper();
    }

    /**
     * @dataProvider provideManagedAppendIfValueList
     *
     * @param ErrorDoc $errorDoc
     * @param array    $expected
     */
    public function testAppendIfShouldHandle($doAppend, $key, $value, array $previous, array $expected)
    {
        $this->assertSame(
            $expected,
            $this->helper->testAppendIf($doAppend, $key, $value, $previous)
        );
    }

    /**
     * @dataProvider provideManagedAppendIfValueNotNullValueList
     *
     * @param ErrorDoc $errorDoc
     * @param array    $expected
     */
    public function testAppendIfValueNotNullShouldHandle($key, $value, array $previous, array $expected)
    {
        $this->assertSame(
            $expected,
            $this->helper->testAppendIfValueNotNull($key, $value, $previous)
        );
    }

    /**
     * @dataProvider provideManagedAppendIfValueHaveSiblingsValueList
     *
     * @param ErrorDoc $errorDoc
     * @param array    $expected
     */
    public function testAppendIfValueHaveSiblingsShouldHandle($key, $value, array $previous, array $expected)
    {
        $this->assertSame(
            $expected,
            $this->helper->testAppendIfValueHaveSiblings($key, $value, $previous)
        );
    }

    /**
     * @return array
     */
    public function provideManagedAppendIfValueList()
    {
        return [
            'Do not append' => [
                'doAppend' => false,
                'key' => 'my-key',
                'value' => 'my-value',
                'previous' => [],
                'expected' => [],
            ],
            'Do append' => [
                'doAppend' => true,
                'key' => 'my-key',
                'value' => 'my-value',
                'previous' => [],
                'expected' => ['my-key' => 'my-value'],
            ],
            'Do not append with previous' => [
                'doAppend' => false,
                'key' => 'my-key',
                'value' => 'my-value',
                'previous' => ['my-previous-key' => 'my-previous-value'],
                'expected' => [
                    'my-previous-key' => 'my-previous-value',
                ],
            ],
            'Do append with previous' => [
                'doAppend' => true,
                'key' => 'my-key',
                'value' => 'my-value',
                'previous' => ['my-previous-key' => 'my-previous-value'],
                'expected' => [
                    'my-previous-key' => 'my-previous-value',
                    'my-key' => 'my-value'
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function provideManagedAppendIfValueNotNullValueList()
    {
        return [
            'null value' => [
                'key' => 'my-key',
                'value' => null,
                'previous' => [],
                'expected' => [],
            ],
            'Not null value' => [
                'key' => 'my-key',
                'value' => 'my-value',
                'previous' => [],
                'expected' => ['my-key' => 'my-value'],
            ],
            'null value with previous' => [
                'key' => 'my-key',
                'value' => null,
                'previous' => ['my-previous-key' => 'my-previous-value'],
                'expected' => [
                    'my-previous-key' => 'my-previous-value',
                ],
            ],
            'Not null value with previous' => [
                'key' => 'my-key',
                'value' => 'my-value',
                'previous' => ['my-previous-key' => 'my-previous-value'],
                'expected' => [
                    'my-previous-key' => 'my-previous-value',
                    'my-key' => 'my-value'
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function provideManagedAppendIfValueHaveSiblingsValueList()
    {
        return [
            'Without sibling' => [
                'key' => 'my-key',
                'value' => [],
                'previous' => [],
                'expected' => [],
            ],
            'With siblings' => [
                'key' => 'my-key',
                'value' => ['my-value'],
                'previous' => [],
                'expected' => ['my-key' => ['my-value']],
            ],
            'Without sibling and with previous' => [
                'key' => 'my-key',
                'value' => [],
                'previous' => ['my-previous-key' => 'my-previous-value'],
                'expected' => [
                    'my-previous-key' => 'my-previous-value',
                ],
            ],
            'With siblings and previous' => [
                'key' => 'my-key',
                'value' => ['my-value'],
                'previous' => ['my-previous-key' => 'my-previous-value'],
                'expected' => [
                    'my-previous-key' => 'my-previous-value',
                    'my-key' => ['my-value']
                ],
            ],
        ];
    }
}
