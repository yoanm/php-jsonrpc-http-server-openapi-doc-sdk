Feature: TypeDoc normalization

  Scenario: Simple type normalization
    Given I have an HttpServerDoc
    And I have a TypeDoc of class "Yoanm\JsonRpcServerDoc\Domain\Model\Type\TypeDoc"
    When I normalize server doc
    Then I should have the following TypeDoc:
    """
    {
      "type": "string",
      "nullable": true
    }
    """

  Scenario: Simple scalar normalization
    Given I have an HttpServerDoc
    And I have a TypeDoc of class "Yoanm\JsonRpcServerDoc\Domain\Model\Type\ScalarDoc"
    When I normalize server doc
    Then I should have the following TypeDoc:
    """
    {
      "type": "string",
      "nullable": true
    }
    """

  Scenario: Simple boolean normalization
    Given I have an HttpServerDoc
    And I have a TypeDoc of class "Yoanm\JsonRpcServerDoc\Domain\Model\Type\BooleanDoc"
    When I normalize server doc
    Then I should have the following TypeDoc:
    """
    {
      "type": "boolean",
      "nullable": true
    }
    """

  Scenario: Simple string normalization
    Given I have an HttpServerDoc
    And I have a TypeDoc of class "Yoanm\JsonRpcServerDoc\Domain\Model\Type\StringDoc"
    When I normalize server doc
    Then I should have the following TypeDoc:
    """
    {
      "type": "string",
      "nullable": true
    }
    """

  Scenario: Simple number normalization
    Given I have an HttpServerDoc
    And I have a TypeDoc of class "Yoanm\JsonRpcServerDoc\Domain\Model\Type\NumberDoc"
    When I normalize server doc
    Then I should have the following TypeDoc:
    """
    {
      "type": "number",
      "nullable": true
    }
    """

  Scenario: Simple integer normalization
    Given I have an HttpServerDoc
    And I have a TypeDoc of class "Yoanm\JsonRpcServerDoc\Domain\Model\Type\IntegerDoc"
    When I normalize server doc
    Then I should have the following TypeDoc:
    """
    {
      "type": "integer",
      "nullable": true
    }
    """

  Scenario: Simple float normalization
    Given I have an HttpServerDoc
    And I have a TypeDoc of class "Yoanm\JsonRpcServerDoc\Domain\Model\Type\FloatDoc"
    When I normalize server doc
    Then I should have the following TypeDoc:
    """
    {
      "type": "number",
      "nullable": true
    }
    """

  Scenario: Simple collection normalization
    Given I have an HttpServerDoc
    And I have a TypeDoc of class "Yoanm\JsonRpcServerDoc\Domain\Model\Type\CollectionDoc"
    When I normalize server doc
    Then I should have the following TypeDoc:
    """
    {
      "type": "array",
      "nullable": true,
      "items": {
        "type": "string"
      }
    }
    """

  Scenario: Simple array normalization
    Given I have an HttpServerDoc
    And I have a TypeDoc of class "Yoanm\JsonRpcServerDoc\Domain\Model\Type\ArrayDoc"
    When I normalize server doc
    Then I should have the following TypeDoc:
    """
    {
      "type": "array",
      "nullable": true,
      "items": {
        "type": "string"
      }
    }
    """

  Scenario: Simple object normalization
    Given I have an HttpServerDoc
    And I have a TypeDoc of class "Yoanm\JsonRpcServerDoc\Domain\Model\Type\ObjectDoc"
    When I normalize server doc
    Then I should have the following TypeDoc:
    """
    {
      "type": "object",
      "nullable": true
    }
    """
