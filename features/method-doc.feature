Feature: MethodDocNormalizer

  Scenario: Simple method doc normalization
    Given I have an HttpServerDoc
    And I have a MethodDoc with name "method-a"
    And I append last method doc to server doc
    When I normalize server doc
    Then I should have a "POST" path named "/Method-a/.."
    And "POST" path named "/Method-a/.." should have the following parameters:
    """
    {
      "type": "object",
      "properties": {
        "method": {
          "example": "method-a"
        }
      }
    }
    """
    And "POST" path named "/Method-a/.." should have the following response:
    """
    {
      "type": "object",
      "properties": {
        "result": {
          "description": "Method result"
        }
      }
    }
    """
    And "POST" path named "/Method-a/.." should have the following error:
    """
    {
      "type": "object",
      "properties": {
        "error": {
          "type": "object"
        }
      }
    }
    """

  Scenario: Method with params documentation
    Given I have an HttpServerDoc
    And I have a MethodDoc with name "method-b"
    And last MethodDoc will have a string and array params doc
    And I append last method doc to server doc
    When I normalize server doc
      # Method parameters are externalized to definitions
    Then "POST" path named "/Method-b/.." should have the following parameters:
    """
    {
      "type": "object",
      "required": ["params"],
      "properties": {
        "params": {
          "$ref": "#/components/schemas/Method-Method-b-RequestParams"
        }
      }
    }
    """
    And normalized components schema named "Method-Method-b-RequestParams" should be the following:
    """
    {
      "type": "object",
      "nullable": true,
      "properties": {
        "string-val": {
          "type": "string",
          "nullable": true
        },
        "array-val": {
          "type": "array",
          "nullable": true,
          "items": {
            "type": "string"
          }
        }
      }
    }
    """

  Scenario: Method with result documentation
    Given I have an HttpServerDoc
    And I have a MethodDoc with name "method-c"
    And last MethodDoc will have a string and array result doc
    And I append last method doc to server doc
    When I normalize server doc
      # Method result is externalized to definitions
    Then "POST" path named "/Method-c/.." should have the following response:
    """
    {
      "type": "object",
      "properties": {
        "result": {
          "$ref": "#/components/schemas/Method-Method-c-Result"
        }
      }
    }
    """
    And normalized components schema named "Method-Method-c-Result" should be the following:
    """
    {
      "type": "object",
      "nullable": true,
      "properties": {
        "string-val": {
          "type": "string",
          "nullable": true
        },
        "array-val": {
          "type": "array",
          "nullable": true,
          "items": {
            "type": "string"
          }
        }
      }
    }
    """

  Scenario: Method with custom errors documentation
    Given I have an HttpServerDoc
    And I have a MethodDoc with name "method-d"
    And last MethodDoc will have a custom errors doc
    And I append last method doc to server doc
    When I normalize server doc
      # Custom errors are externalized to definitions
    Then "POST" path named "/Method-d/.." should have the following error:
    """
    {
      "type": "object",
      "properties": {
        "error": {
          "oneOf": [
            {"$ref": "#\/components\/schemas\/Error-Error-a123"},
            {"$ref": "#\/components\/schemas\/Error-Error-b321"}
          ]
        }
      }
    }
    """
    And normalized components schema named "Error-Error-a123" should be the following:
    """
    {
      "title": "error-a",
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
    And normalized components schema named "Error-Error-b321" should be the following:
    """
    {
      "title": "error-b",
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
              "example": 321
            },
            "message": {
              "example": "message-error-b"
            }
          }
        }
      ]
    }
    """

  Scenario: Method with global error ref
    Given I have an HttpServerDoc
    And I have a MethodDoc with name "method-e" and following calls:
    """
    [
      {"method": "addGlobalErrorRef", "arguments": ["global-error-a"]}
    ]
    """
    And I append last method doc to server doc
    And I have an ErrorDoc named "error-a" with code 123 and following calls:
    """
    [
      {"method": "setIdentifier", "arguments": ["global-error-a"]}
    ]
    """
    And I append last error doc to global server errors
    When I normalize server doc
    Then "POST" path named "/Method-e/.." should have the following error:
    """
    {
      "type": "object",
      "properties": {
        "error": {
          "oneOf": [{
            "$ref": "#\/components\/schemas\/Error-Global-error-a"
          }]
        }
      }
    }
    """
    And normalized components schema named "Error-Global-error-a" should be the following:
    """
    {
      "title": "error-a",
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

  Scenario: Fully described method
    Given I have an HttpServerDoc
    And I have a MethodDoc with name "method-f", identified by "Method-f-Id" and with following calls:
    """
    [
      {"method": "setDescription", "arguments": ["method-f-description"]},
      {"method": "addTag", "arguments": ["method-f-tag-a"]},
      {"method": "addTag", "arguments": ["method-f-tag-b"]},
      {"method": "addGlobalErrorRef", "arguments": ["global-error-a"]}
    ]
    """
    And last MethodDoc will have a string and array params doc
    And last MethodDoc will have a string and array result doc
    And last MethodDoc will have a custom errors doc
    And I append last method doc to server doc
    And I have an ErrorDoc named "error-a" with code 123 and following calls:
    """
    [
      {"method": "setIdentifier", "arguments": ["global-error-a"]}
    ]
    """
    And I append last error doc to global server errors
    When I normalize server doc
    Then I should have following normalized doc:
    """
    {
      "openapi": "3.0.0",
      "paths": {
        "\/Method-f-Id\/..": {
          "post": {
            "summary": "\"method-f\" json-rpc method",
            "description": "method-f-description",
            "tags": ["method-f-tag-a", "method-f-tag-b"],
            "operationId": "Method-f-Id",
            "requestBody": {
              "required": true,
              "content": {
                "application\/json": {
                  "schema": {
                    "allOf": [
                      {
                        "type": "object",
                        "required": ["jsonrpc", "method"],
                        "properties": {
                          "id": {
                            "example": "req_id",
                            "oneOf": [
                              {"type": "string"},
                              {"type": "number"}
                            ]
                          },
                          "jsonrpc": {"type": "string", "example": "2.0"},
                          "method": {"type": "string"},
                          "params": {"title": "Method parameters"}
                        }
                      },
                      {
                        "type": "object",
                        "required": ["params"],
                        "properties": {
                          "params": {
                            "$ref": "#/components/schemas/Method-Method-f-Id-RequestParams"
                          }
                        }
                      },
                      {
                        "type": "object",
                        "properties": {
                          "method": {
                            "example": "method-f"
                          }
                        }
                      }
                    ]
                  }
                }
              }
            },
            "responses": {
              "200": {
                "description": "JSON-RPC response",
                "content": {
                  "application/json": {
                    "schema": {
                      "allOf": [
                        {
                          "type": "object",
                          "required": ["jsonrpc"],
                          "properties": {
                            "id": {
                              "example": "req_id",
                              "oneOf": [
                                {"type": "string"},
                                {"type": "number"}
                              ]
                            },
                            "jsonrpc": {"type": "string", "example": "2.0"},
                            "result": {"title": "Result"},
                            "error": {"title": "Error"}
                          }
                        },
                        {
                          "type": "object",
                          "properties": {
                            "result": {
                              "$ref": "#/components/schemas/Method-Method-f-Id-Result"
                            }
                          }
                        },
                        {
                          "type": "object",
                          "properties": {
                            "error": {
                              "oneOf": [
                                {"$ref": "#/components/schemas/Error-Global-error-a"},
                                {"$ref": "#\/components\/schemas\/Error-Error-a123"},
                                {"$ref": "#\/components\/schemas\/Error-Error-b321"}
                              ]
                            }
                          }
                        }
                      ]
                    }
                  }
                }
              }
            }
          }
        }
      },
      "components": {
        "schemas": {
          "Method-Method-f-Id-RequestParams": {
            "type": "object",
            "nullable": true,
            "properties": {
              "string-val": {
                "type": "string",
                "nullable": true
              },
              "array-val": {
                "type": "array",
                "nullable": true,
                "items": {
                  "type": "string"
                }
              }
            }
          },
          "Method-Method-f-Id-Result": {
            "type": "object",
            "nullable": true,
            "properties": {
              "string-val": {
                "type": "string",
                "nullable": true
              },
              "array-val": {
                "type": "array",
                "nullable": true,
                "items": {
                  "type": "string"
                }
              }
            }
          },
          "Error-Error-a123": {
            "title": "error-a",
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
          },
          "Error-Error-b321": {
            "title": "error-b",
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
                    "example": 321
                  },
                  "message": {
                    "example": "message-error-b"
                  }
                }
              }
            ]
          },
          "Error-Global-error-a": {
            "title": "error-a",
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
        }
      }
    }
    """
