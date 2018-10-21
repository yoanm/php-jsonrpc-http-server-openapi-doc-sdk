<?php
namespace Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component;

/**
 * Class ShapeNormalizer
 */
class ShapeNormalizer
{
    /**
     * @return array
     */
    public function getRequestShapeDefinition()
    {
        return [
            'type' => 'object',
            'required' => [
                'jsonrpc',
                'method'
            ],
            'properties' => [
                'id' => [
                    'example' => 'req_id',
                    'oneOf' => [
                        ['type' => 'string'],
                        ['type' => 'number'],
                    ],
                ],
                'jsonrpc' => [
                    'type' => 'string',
                    'example' => '2.0',
                ],
                'method' => [
                    'type' => 'string',
                ],
                'params' => [
                    'title' => 'Method parameters',
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function getResponseShapeDefinition()
    {
        return [
            'type' => 'object',
            'required' => [
                'jsonrpc',
            ],
            'properties' => [
                'id' => [
                    'example' => 'req_id',
                    'oneOf' => [
                        ['type' => 'string'],
                        ['type' => 'number'],
                    ],
                ],
                'jsonrpc' => [
                    'type' => 'string',
                    'example' => '2.0',
                ],
                'result' => [
                    'title' => 'Result'
                ],
                'error' => [
                    'title' => 'Error'
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function getErrorShapeDefinition()
    {
        return [
            'type' => 'object',
            'required' => [
                'code',
                'message',
            ],
            'properties' => [
                'code' => [
                    'type' => 'number',
                ],
                'message' => [
                    'type' => 'string',
                ],
            ]
        ];
    }
}
