Feature: Error normalization

  Scenario: Simple error normalization for server errors
    Given I have an HttpServerDoc
    And I have an ErrorDoc named "my-error" with code 123
    And I append last error doc to server errors
    When I normalize server doc
    Then I should have a normalized components schema named "ServerError-My-error123"
    And normalized components schema named "ServerError-My-error123" should be the following:
    """
    {
      "title": "my-error",
      "allOf": [
        {
          "type": "object",
          "required": ["code", "message"],
          "properties": {
            "code": {
              "type": "number"
            },
            "message": {
              "type": "string"
            }
          }
        },
        {
          "type": "object",
          "required": ["code"],
          "properties": {
            "code": {
              "example": 123
            }
          }
        }
      ]
    }
    """

  Scenario: Simple error normalization for global server errors
    Given I have an HttpServerDoc
    And I have an ErrorDoc named "my-error" with code 123
    And I append last error doc to global server errors
    When I normalize server doc
    Then I should have a normalized components schema named "Error-My-error123"
    And normalized components schema named "Error-My-error123" should be the following:
    """
    {
      "title": "my-error",
      "allOf": [
        {
          "type": "object",
          "required": ["code", "message"],
          "properties": {
            "code": {
              "type": "number"
            },
            "message": {
              "type": "string"
            }
          }
        },
        {
          "type": "object",
          "required": ["code"],
          "properties": {
            "code": {
              "example": 123
            }
          }
        }
      ]
    }
    """

  Scenario: Fully configured error normalization for server errors
    Given I have an HttpServerDoc
    And I have an ErrorDoc named "my-error" with code 123, message "error-message" and following calls:
    """
    [
      {"method": "setIdentifier", "arguments": ["error identifier-test"]}
    ]
    """
    And I append last error doc to server errors
    When I normalize server doc
    Then I should have a normalized components schema named "ServerError-ErrorIdentifier-test"
    And normalized components schema named "ServerError-ErrorIdentifier-test" should be the following:
    """
    {
      "title": "my-error",
      "allOf": [
        {
          "type": "object",
          "required": ["code", "message"],
          "properties": {
            "code": {
              "type": "number"
            },
            "message": {
              "type": "string"
            }
          }
        },
        {
          "type": "object",
          "required": ["code"],
          "properties": {
            "code": {
              "example": 123
            },
            "message": {
              "example": "error-message"
            }
          }
        }
      ]
    }
    """

  Scenario: Fully configured error normalization for global server errors
    Given I have an HttpServerDoc
    And I have an ErrorDoc named "my-error" with code 123, message "error-message" and following calls:
    """
    [
      {"method": "setIdentifier", "arguments": ["error identifier-test"]}
    ]
    """
    And I append last error doc to global server errors
    When I normalize server doc
    Then I should have a normalized components schema named "Error-ErrorIdentifier-test"
    And normalized components schema named "Error-ErrorIdentifier-test" should be the following:
    """
    {
      "title": "my-error",
      "allOf": [
        {
          "type": "object",
          "required": ["code", "message"],
          "properties": {
            "code": {
              "type": "number"
            },
            "message": {
              "type": "string"
            }
          }
        },
        {
          "type": "object",
          "required": ["code"],
          "properties": {
            "code": {
              "example": 123
            },
            "message": {
              "example": "error-message"
            }
          }
        }
      ]
    }
    """
