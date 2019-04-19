Feature: HttpServerDocNormalizer

  Scenario: Simple http server normalization
    Given I have an HttpServerDoc with following calls:
    """
    [
      {"method": "setName", "arguments": ["my-server"]}
    ]
    """
    When I normalize server doc
    Then I should have following normalized doc:
    """
    {
      "openapi": "3.0.0",
      "info": {
        "title": "my-server"
      },
      "components": {
        "schemas": {}
      }
    }
    """

  Scenario: Fully described Http server normalization
    Given I have an HttpServerDoc with following calls:
    """
    [
      {"method": "setName", "arguments": ["my-server-2"]},
      {"method": "setVersion", "arguments": ["4.2.6"]},
      {"method": "setEndpoint", "arguments": ["/my-endpoint"]},
      {"method": "setHost", "arguments": ["127.10.20.30"]},
      {"method": "setBasePath", "arguments": ["/my/custom-base/path"]},
      {"method": "setSchemeList", "arguments": [["http", "https"]]}
    ]
    """
    When I normalize server doc
    Then I should have following normalized doc:
    """
    {
      "openapi": "3.0.0",
      "info": {
        "title": "my-server-2",
        "version": "4.2.6"
      },
      "servers": [
        {"url": "http://127.10.20.30/my/custom-base/path"},
        {"url": "https://127.10.20.30/my/custom-base/path"}
      ],
      "components": {
        "schemas": {}
      }
    }
    """
