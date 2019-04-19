Feature: Tag normalization

  Scenario: Simple tag normalization
    Given I have an HttpServerDoc
    And I have a TagDoc named "tagA"
    And I append last tag doc to server doc
    When I normalize server doc
    Then I should have a normalized tag named "tagA"

  Scenario: Tag with description
    Given I have an HttpServerDoc
    And I have a TagDoc named "tag-b" with following description:
    """
    tag-b description
    """
    And I append last tag doc to server doc
    When I normalize server doc
    And I should have a normalized tag named "tag-b" with description "tag-b description"
