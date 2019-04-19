Feature: TypeDoc normalization

  Scenario: Fully configured type normalization
    Given I have an HttpServerDoc
    And I have a TypeDoc of class "Yoanm\JsonRpcServerDoc\Domain\Model\Type\TypeDoc" with following calls:
    """
    [
      {"method": "setName", "arguments": ["type-b"]},
      {"method": "setDescription", "arguments": ["type-b-description"]},
      {"method": "setDefault", "arguments": ["type-b-default"]},
      {"method": "setExample", "arguments": ["type-b-example"]},
      {"method": "setRequired", "arguments": [true]},
      {"method": "setNullable", "arguments": [false]},
      {"method": "addAllowedValue", "arguments": ["type-b-allowed-value-a"]},
      {"method": "addAllowedValue", "arguments": ["type-b-allowed-value-b"]}
    ]
    """
    When I normalize server doc
    Then I should have the following required TypeDoc:
    """
    {
      "description": "type-b-description",
      "type": "string",
      "nullable": false,
      "default": "type-b-default",
      "example": "type-b-example",
      "enum": ["type-b-allowed-value-a", "type-b-allowed-value-b"]
    }
    """

  Scenario: Fully configured scalar normalization
    Given I have an HttpServerDoc
    And I have a TypeDoc of class "Yoanm\JsonRpcServerDoc\Domain\Model\Type\ScalarDoc" with following calls:
    """
    [
      {"method": "setName", "arguments": ["type-b"]},
      {"method": "setDescription", "arguments": ["type-b-description"]},
      {"method": "setDefault", "arguments": ["type-b-default"]},
      {"method": "setExample", "arguments": ["type-b-example"]},
      {"method": "setRequired", "arguments": [true]},
      {"method": "setNullable", "arguments": [false]},
      {"method": "addAllowedValue", "arguments": ["type-b-allowed-value-a"]},
      {"method": "addAllowedValue", "arguments": ["type-b-allowed-value-b"]}
    ]
    """
    When I normalize server doc
    Then I should have the following required TypeDoc:
    """
    {
      "description": "type-b-description",
      "type": "string",
      "nullable": false,
      "default": "type-b-default",
      "example": "type-b-example",
      "enum": ["type-b-allowed-value-a", "type-b-allowed-value-b"]
    }
    """

  Scenario: Fully configured boolean normalization
    Given I have an HttpServerDoc
    And I have a TypeDoc of class "Yoanm\JsonRpcServerDoc\Domain\Model\Type\BooleanDoc" with following calls:
    """
    [
      {"method": "setName", "arguments": ["type-b"]},
      {"method": "setDescription", "arguments": ["type-b-description"]},
      {"method": "setDefault", "arguments": [true]},
      {"method": "setExample", "arguments": [true]},
      {"method": "setRequired", "arguments": [true]},
      {"method": "setNullable", "arguments": [false]},
      {"method": "addAllowedValue", "arguments": [true]},
      {"method": "addAllowedValue", "arguments": [false]}
    ]
    """
    When I normalize server doc
    Then I should have the following required TypeDoc:
    """
    {
      "description": "type-b-description",
      "type": "boolean",
      "nullable": false,
      "default": true,
      "example": true,
      "enum": [true, false]
    }
    """

  Scenario: Fully configured string normalization
    Given I have an HttpServerDoc
    And I have a TypeDoc of class "Yoanm\JsonRpcServerDoc\Domain\Model\Type\StringDoc" with following calls:
    """
    [
      {"method": "setName", "arguments": ["type-b"]},
      {"method": "setDescription", "arguments": ["type-b-description"]},
      {"method": "setDefault", "arguments": ["type-b-default"]},
      {"method": "setExample", "arguments": ["type-b-example"]},
      {"method": "setRequired", "arguments": [true]},
      {"method": "setNullable", "arguments": [false]},
      {"method": "addAllowedValue", "arguments": ["type-b-allowed-value-a"]},
      {"method": "addAllowedValue", "arguments": ["type-b-allowed-value-b"]},
      {"method": "setFormat", "arguments": ["type-b-format"]},
      {"method": "setMinLength", "arguments": [2]},
      {"method": "setMaxLength", "arguments": [5]}
    ]
    """
    When I normalize server doc
    Then I should have the following required TypeDoc:
    """
    {
      "description": "type-b-description",
      "type": "string",
      "format": "type-b-format",
      "nullable": false,
      "default": "type-b-default",
      "example": "type-b-example",
      "enum": ["type-b-allowed-value-a", "type-b-allowed-value-b"],
      "minLength": 2,
      "maxLength": 5
    }
    """

  Scenario: Fully configured number normalization
    Given I have an HttpServerDoc
    And I have a TypeDoc of class "Yoanm\JsonRpcServerDoc\Domain\Model\Type\NumberDoc" with following calls:
    """
    [
      {"method": "setName", "arguments": ["type-b"]},
      {"method": "setDescription", "arguments": ["type-b-description"]},
      {"method": "setDefault", "arguments": [2]},
      {"method": "setExample", "arguments": [5]},
      {"method": "setRequired", "arguments": [true]},
      {"method": "setNullable", "arguments": [false]},
      {"method": "addAllowedValue", "arguments": [1]},
      {"method": "addAllowedValue", "arguments": [2]},
      {"method": "addAllowedValue", "arguments": [3]},
      {"method": "addAllowedValue", "arguments": [4]},
      {"method": "addAllowedValue", "arguments": [5]},
      {"method": "setMin", "arguments": [3]},
      {"method": "setMax", "arguments": [6]},
      {"method": "setInclusiveMin", "arguments": [false]},
      {"method": "setInclusiveMax", "arguments": [false]}
    ]
    """
    When I normalize server doc
    Then I should have the following required TypeDoc:
    """
    {
      "description": "type-b-description",
      "type": "number",
      "nullable": false,
      "default": 2,
      "example": 5,
      "enum": [1, 2, 3, 4, 5],
      "minimum": 3,
      "exclusiveMinimum": true,
      "maximum": 6,
      "exclusiveMaximum": true
    }
    """

  Scenario: Fully configured integer normalization
    Given I have an HttpServerDoc
    And I have a TypeDoc of class "Yoanm\JsonRpcServerDoc\Domain\Model\Type\IntegerDoc" with following calls:
    """
    [
      {"method": "setName", "arguments": ["type-b"]},
      {"method": "setDescription", "arguments": ["type-b-description"]},
      {"method": "setDefault", "arguments": [2]},
      {"method": "setExample", "arguments": [5]},
      {"method": "setRequired", "arguments": [true]},
      {"method": "setNullable", "arguments": [false]},
      {"method": "addAllowedValue", "arguments": [1]},
      {"method": "addAllowedValue", "arguments": [2]},
      {"method": "addAllowedValue", "arguments": [3]},
      {"method": "addAllowedValue", "arguments": [4]},
      {"method": "addAllowedValue", "arguments": [5]},
      {"method": "setMin", "arguments": [3]},
      {"method": "setMax", "arguments": [6]},
      {"method": "setInclusiveMin", "arguments": [false]},
      {"method": "setInclusiveMax", "arguments": [false]}
    ]
    """
    When I normalize server doc
    Then I should have the following required TypeDoc:
    """
    {
      "description": "type-b-description",
      "type": "integer",
      "nullable": false,
      "default": 2,
      "example": 5,
      "enum": [1, 2, 3, 4, 5],
      "minimum": 3,
      "exclusiveMinimum": true,
      "maximum": 6,
      "exclusiveMaximum": true
    }
    """

  Scenario: Fully configured float normalization
    Given I have an HttpServerDoc
    And I have a TypeDoc of class "Yoanm\JsonRpcServerDoc\Domain\Model\Type\FloatDoc" with following calls:
    """
    [
      {"method": "setName", "arguments": ["type-b"]},
      {"method": "setDescription", "arguments": ["type-b-description"]},
      {"method": "setDefault", "arguments": [2.4]},
      {"method": "setExample", "arguments": [5.8]},
      {"method": "setRequired", "arguments": [true]},
      {"method": "setNullable", "arguments": [false]},
      {"method": "addAllowedValue", "arguments": [1]},
      {"method": "addAllowedValue", "arguments": [2.4]},
      {"method": "addAllowedValue", "arguments": [5.8]},
      {"method": "setMin", "arguments": [3]},
      {"method": "setMax", "arguments": [6]},
      {"method": "setInclusiveMin", "arguments": [false]},
      {"method": "setInclusiveMax", "arguments": [false]}
    ]
    """
    When I normalize server doc
    Then I should have the following required TypeDoc:
    """
    {
      "description": "type-b-description",
      "type": "number",
      "nullable": false,
      "default": 2.4,
      "example": 5.8,
      "enum": [1, 2.4, 5.8],
      "minimum": 3,
      "exclusiveMinimum": true,
      "maximum": 6,
      "exclusiveMaximum": true
    }
    """

  Scenario: Fully configured collection normalization
    Given I have an HttpServerDoc
    And I have a TypeDoc of class "Yoanm\JsonRpcServerDoc\Domain\Model\Type\CollectionDoc" with following calls:
    """
    [
      {"method": "setName", "arguments": ["type-b"]},
      {"method": "setDescription", "arguments": ["type-b-description"]},
      {"method": "setDefault", "arguments": [["default"]]},
      {"method": "setExample", "arguments": [["example"]]},
      {"method": "setRequired", "arguments": [true]},
      {"method": "setNullable", "arguments": [false]},
      {"method": "addAllowedValue", "arguments": [["type-b-allowed-value-a"]]},
      {"method": "addAllowedValue", "arguments": [["type-b-allowed-value-b"]]},
      {"method": "setMinItem", "arguments": [2]},
      {"method": "setMaxItem", "arguments": [8]},
      {"method": "setAllowExtraSibling", "arguments": [true]},
      {"method": "setAllowMissingSibling", "arguments": [true]}

    ]
    """
    When I normalize server doc
    Then I should have the following required TypeDoc:
    """
    {
      "description": "type-b-description",
      "type": "array",
      "nullable": false,
      "default": ["default"],
      "example": ["example"],
      "enum": [["type-b-allowed-value-a"], ["type-b-allowed-value-b"]],
      "minItems": 2,
      "maxItems": 8,
      "items": {
        "type": "string"
      }
    }
    """

  Scenario: Fully configured array normalization
    Given I have an HttpServerDoc
    And I have a TypeDoc of class "Yoanm\JsonRpcServerDoc\Domain\Model\Type\ArrayDoc" with following calls:
    """
    [
      {"method": "setName", "arguments": ["type-b"]},
      {"method": "setDescription", "arguments": ["type-b-description"]},
      {"method": "setDefault", "arguments": [["default"]]},
      {"method": "setExample", "arguments": [["example"]]},
      {"method": "setRequired", "arguments": [true]},
      {"method": "setNullable", "arguments": [false]},
      {"method": "addAllowedValue", "arguments": [["type-b-allowed-value-a"]]},
      {"method": "addAllowedValue", "arguments": [["type-b-allowed-value-b"]]},
      {"method": "setMinItem", "arguments": [2]},
      {"method": "setMaxItem", "arguments": [8]},
      {"method": "setAllowExtraSibling", "arguments": [true]},
      {"method": "setAllowMissingSibling", "arguments": [true]}

    ]
    """
    And last TypeDoc will have a scalar item validation
    When I normalize server doc
    Then I should have the following required TypeDoc:
    """
    {
      "description": "type-b-description",
      "type": "array",
      "nullable": false,
      "default": ["default"],
      "example": ["example"],
      "enum": [["type-b-allowed-value-a"], ["type-b-allowed-value-b"]],
      "minItems": 2,
      "maxItems": 8,
      "items": {
        "type": "string",
        "nullable": true
      }
    }
    """

  Scenario: Fully configured object normalization
    Given I have an HttpServerDoc
    And I have a TypeDoc of class "Yoanm\JsonRpcServerDoc\Domain\Model\Type\ObjectDoc" with following calls:
    """
    [
      {"method": "setName", "arguments": ["type-b"]},
      {"method": "setDescription", "arguments": ["type-b-description"]},
      {"method": "setDefault", "arguments": [["default"]]},
      {"method": "setExample", "arguments": [["example"]]},
      {"method": "setRequired", "arguments": [true]},
      {"method": "setNullable", "arguments": [false]},
      {"method": "addAllowedValue", "arguments": [["type-b-allowed-value-a"]]},
      {"method": "addAllowedValue", "arguments": [["type-b-allowed-value-b"]]},
      {"method": "setMinItem", "arguments": [2]},
      {"method": "setMaxItem", "arguments": [8]},
      {"method": "setAllowExtraSibling", "arguments": [true]},
      {"method": "setAllowMissingSibling", "arguments": [true]}

    ]
    """
    When I normalize server doc
    Then I should have the following required TypeDoc:
    """
    {
      "description": "type-b-description",
      "type": "object",
      "nullable": false,
      "default": ["default"],
      "example": ["example"],
      "enum": [["type-b-allowed-value-a"], ["type-b-allowed-value-b"]],
      "minProperties": 2,
      "maxProperties": 8,
      "additionalProperties": {
        "description": "Extra property"
      }
    }
    """
