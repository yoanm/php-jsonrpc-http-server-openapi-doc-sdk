<?php
namespace Tests\Functional\BehatContext;

use Behat\Gherkin\Node\PyStringNode;
use PHPUnit\Framework\Assert;
use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component\ErrorDocNormalizer;
use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component\ExternalSchemaListDocNormalizer;
use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component\OperationDocNormalizer;
use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component\RequestDocNormalizer;
use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component\ResponseDocNormalizer;
use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component\SchemaTypeNormalizer;
use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component\ShapeNormalizer;
use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Normalizer\Component\TypeDocNormalizer;
use Yoanm\JsonRpcHttpServerOpenAPIDoc\App\Resolver\DefinitionRefResolver;
use Yoanm\JsonRpcHttpServerOpenAPIDoc\Infra\Normalizer\DocNormalizer;
use Yoanm\JsonRpcServerDoc\Domain\Model\ErrorDoc;
use Yoanm\JsonRpcServerDoc\Domain\Model\HttpServerDoc;
use Yoanm\JsonRpcServerDoc\Domain\Model\MethodDoc;
use Yoanm\JsonRpcServerDoc\Domain\Model\TagDoc;
use Yoanm\JsonRpcServerDoc\Domain\Model\Type\ArrayDoc;
use Yoanm\JsonRpcServerDoc\Domain\Model\Type\ObjectDoc;
use Yoanm\JsonRpcServerDoc\Domain\Model\Type\ScalarDoc;
use Yoanm\JsonRpcServerDoc\Domain\Model\Type\StringDoc;
use Yoanm\JsonRpcServerDoc\Domain\Model\Type\TypeDoc;

class DocNormalizerContext extends AbstractContext
{
    const COMPONENTS_KEY = 'components';
    const COMPONENTS_SCHEMAS_KEY = 'schemas';
    const TAGS_KEY = 'tags';
    const PATHS_KEY = 'paths';

    const DUMMY_ERROR_NAME_FOR_TYPEDOC = 'DummyErrorForTypeDoc';
    const DUMMY_ERROR_KEY_FOR_TYPEDOC = 'Error-DummyErrorForTypeDoc123';


    /** @var HttpServerDoc|null */
    private $serverDoc = null;
    /** @var ErrorDoc|null */
    private $lastErrorDoc = null;
    /** @var MethodDoc|null */
    private $lastMethodDoc = null;
    /** @var TagDoc|null */
    private $lastTagDoc = null;
    /** @var null|array */
    private $lastNormalizedOutput = null;
    /** @var TypeDoc|null */
    private $lastTypeDoc = null;

    /**
     * @Given I have an HttpServerDoc
     * @Given I have an HttpServerDoc with following calls:
     */
    public function givenIHaveAHttpServerDoc(PyStringNode $callList = null)
    {
        $this->serverDoc = new HttpServerDoc();
        $this->callMethods(
            $this->serverDoc,
            (null !== $callList) ? $this->jsonDecode($callList->getRaw()) : []
        );
    }

    /**
     * @Given I have a MethodDoc with name :name
     * @Given I have a MethodDoc with name :name and following calls:
     * @Given I have a MethodDoc with name :name and identified by :identifier
     * @Given I have a MethodDoc with name :name, identified by :identifier and with following calls:
     */
    public function givenIHaveAMethodWithName($name, $identifier = null, PyStringNode $callList = null)
    {
        $this->lastMethodDoc = new MethodDoc($name, $identifier);
        $this->callMethods(
            $this->lastMethodDoc,
            (null !== $callList) ? $this->jsonDecode($callList->getRaw()) : []
        );
    }

    /**
     * @Given I have an ErrorDoc named :title with code :code
     * @Given I have an ErrorDoc named :title with code :code and message :message
     * @Given I have an ErrorDoc named :title with code :code and following calls:
     * @Given I have an ErrorDoc named :title with code :code, message :message and following calls:
     */
    public function givenIHaveAErrorNamed($title, $code, $message = null, PyStringNode $callList = null)
    {
        $this->lastErrorDoc = new ErrorDoc($title, $code, $message);
        $this->callMethods(
            $this->lastErrorDoc,
            (null !== $callList) ? $this->jsonDecode($callList->getRaw()) : []
        );
    }

    /**
     * @Given I have a TagDoc named :name
     * @Given I have a TagDoc named :name with following description:
     */
    public function givenIHaveATagNamed($name, PyStringNode $description = null)
    {
        $this->lastTagDoc = new TagDoc($name);

        if (null !== $description) {
            $this->lastTagDoc->setDescription($description->getRaw());
        }
    }

    /**
     * @Given I append last error doc to server errors
     */
    public function iAppendLastErrorDocToServerErrors()
    {
        $this->serverDoc->addServerError($this->lastErrorDoc);
        $this->lastErrorDoc = null;
    }

    /**
     * @Given I append last error doc to global server errors
     */
    public function iAppendLastErrorDocToGlobalServerErrors()
    {
        $this->serverDoc->addGlobalError($this->lastErrorDoc);
        $this->lastErrorDoc = null;
    }

    /**
     * @Given I append last method doc to server doc
     */
    public function iAppendLastMethodDocToServerDoc()
    {
        $this->serverDoc->addMethod($this->lastMethodDoc);
        $this->lastMethodDoc = null;
    }

    /**
     * @Given I append last tag doc to server doc
     */
    public function iAppendLastTagDocToServerDoc()
    {
        $this->serverDoc->addTag($this->lastTagDoc);
        $this->lastTagDoc = null;
    }

    /**
     * @Given I have a TypeDoc of class :class
     * @Given I have a TypeDoc of class :class with following calls:
     */
    public function givenIHaveATypeDocOfClass($class, PyStringNode $callList = null)
    {

        $this->lastTypeDoc = new $class();

        $this->callMethods(
            $this->lastTypeDoc,
            (null !== $callList) ? $this->jsonDecode($callList->getRaw()) : []
        );

        // Create a dummy error to hold TypeDoc
        $this->givenIHaveAErrorNamed(self::DUMMY_ERROR_NAME_FOR_TYPEDOC, 123);
        // Append TypeDoc as data doc
        $this->lastErrorDoc->setDataDoc($this->lastTypeDoc);
        // Append dummy error to server error
        $this->iAppendLastErrorDocToGlobalServerErrors();
    }

    /**
     * @Given last TypeDoc will have a scalar item validation
     */
    public function givenLastTypeDocWillHaveAnItemValidationOf()
    {
        $this->lastTypeDoc->setItemValidation(new ScalarDoc());
    }

    /**
     * @Given last MethodDoc will have a string and array params doc
     */
    public function givenLastMethodDocWillHaveAStringAndArrayParamsDoc()
    {
        $this->lastMethodDoc->setParamsDoc(
            (new ObjectDoc())
                ->addSibling(
                    (new StringDoc())
                        ->setName('string-val')
                )
                ->addSibling(
                    (new ArrayDoc())
                        ->setName('array-val')
                )
        );
    }

    /**
     * @Given last MethodDoc will have a string and array result doc
     */
    public function givenLastMethodDocWillHaveAStringAndArrayResultDoc()
    {
        $this->lastMethodDoc->setResultDoc(
            (new ObjectDoc())
                ->addSibling(
                    (new StringDoc())
                        ->setName('string-val')
                )
                ->addSibling(
                    (new ArrayDoc())
                        ->setName('array-val')
                )
        );
    }

    /**
     * @Given last MethodDoc will have a custom errors doc
     */
    public function givenLastMethodDocWillHaveACustomErrorDoc()
    {
        $this->lastMethodDoc->addCustomError((new ErrorDoc('error-a', 123)));
        $this->lastMethodDoc->addCustomError((new ErrorDoc('error-b', 321, 'message-error-b')));
    }

    /**
     * @When I normalize server doc
     */
    public function whenINormalizeServerDoc()
    {
        $shapeNormalizer = new ShapeNormalizer();
        $definitionRefResolver = new DefinitionRefResolver();
        $typeDocNormalizer = new TypeDocNormalizer(
            new SchemaTypeNormalizer()
        );
        $normalizer = new DocNormalizer(
            new ExternalSchemaListDocNormalizer(
                $definitionRefResolver,
                $typeDocNormalizer,
                new ErrorDocNormalizer(
                    $typeDocNormalizer,
                    $shapeNormalizer
                )
            ),
            new OperationDocNormalizer(
                $definitionRefResolver,
                new RequestDocNormalizer(
                    $definitionRefResolver,
                    $shapeNormalizer
                ),
                new ResponseDocNormalizer(
                    $definitionRefResolver,
                    $shapeNormalizer
                )
            )
        );

        $this->lastNormalizedOutput = $normalizer->normalize($this->serverDoc);
    }

    /**
     * @Then I should have following normalized doc:
     */
    public function thenIShouldHaveFollowingNormalizedDoc(PyStringNode $data)
    {
        print_r(json_encode($this->lastNormalizedOutput));
        Assert::assertSame(
            $this->jsonDecode($data->getRaw()),
            $this->lastNormalizedOutput
        );
    }

    /** DEFINITIONS */

    /**
     * @Then I should have a normalized components schema named :definitionName
     */
    public function thenIShouldHaveComponentsSchemaNamed($definitionName)
    {
        Assert::assertArrayHasKey(self::COMPONENTS_KEY, $this->lastNormalizedOutput);
        Assert::assertArrayHasKey(self::COMPONENTS_SCHEMAS_KEY, $this->lastNormalizedOutput[self::COMPONENTS_KEY]);
        Assert::assertArrayHasKey(
            $definitionName,
            $this->lastNormalizedOutput[self::COMPONENTS_KEY][self::COMPONENTS_SCHEMAS_KEY]
        );
    }

    /**
     * @Then normalized components schema named :definitionName should be the following:
     */
    public function thenNormalizedComponentsSchemaNamedShouldBeTheFollowing($definitionName, PyStringNode $data)
    {
        $this->thenIShouldHaveComponentsSchemaNamed($definitionName);

        Assert::assertSame(
            $this->jsonDecode($data->getRaw()),
            $this->lastNormalizedOutput[self::COMPONENTS_KEY][self::COMPONENTS_SCHEMAS_KEY][$definitionName]
        );
    }

    /**
     * @Then normalized components schema named :definitionName should have a key :key containing:
     */
    public function thenNormalizedComponentsSchemaNamedShouldHaveAKeyContaining(
        $definitionName,
        $key,
        PyStringNode $data
    ) {
        $this->thenIShouldHaveComponentsSchemaNamed($definitionName);

        Assert::assertArrayHasKey($key, $this->lastNormalizedOutput[self::COMPONENTS_KEY][$definitionName]);

        Assert::assertContains(
            $this->jsonDecode($data->getRaw()),
            $this->lastNormalizedOutput[self::COMPONENTS_KEY][self::COMPONENTS_SCHEMAS_KEY][$definitionName][$key],
            sprintf(
                'Failed asserting that key "%s" under "%s" contains "%s"',
                $key,
                self::COMPONENTS_KEY,
                $data->getRaw()
            )
        );
    }

    /** END - DEFINITIONS */

    /** TAGS */

    /**
     * @Then I should have a normalized tag named :tagName
     * @Then I should have a normalized tag named :tagName with description :description
     */
    public function thenIShouldHaveTagNamed($tagName, $description = null)
    {
        Assert::assertArrayHasKey(self::TAGS_KEY, $this->lastNormalizedOutput);

        $expected = ['name' => $tagName];
        if (null !== $description) {
            $expected['description'] = $description;
        }
        Assert::assertContains($expected, $this->lastNormalizedOutput[self::TAGS_KEY]);
    }

    /** END - TAGS */

    /** TYPES */

    /**
     * @Then I should have the following TypeDoc:
     */
    public function thenIShouldHaveTheFollowingTypeDoc(PyStringNode $data, $isRequired = false)
    {
        $this->thenIShouldHaveComponentsSchemaNamed(self::DUMMY_ERROR_KEY_FOR_TYPEDOC);
        [
            self::COMPONENTS_KEY => [
                self::COMPONENTS_SCHEMAS_KEY => [
                    self::DUMMY_ERROR_KEY_FOR_TYPEDOC => $dummyError
                ]
            ]
        ] = $this->lastNormalizedOutput;
        $dummyErrorShape = $dummyError['allOf'][1];

        if (true === $isRequired) {
            Assert::assertContains('data', $dummyErrorShape['required']);
        } else {
            Assert::assertNotContains('data', $dummyErrorShape['required']);
        }
        // Take the second arguments of 'allOf' (where data doc is defined)
        $typeDoc = $dummyErrorShape['properties']['data'];
        Assert::assertSame(
            $this->jsonDecode($data->getRaw()),
            $typeDoc
        );
    }

    /**
     * @Then I should have the following required TypeDoc:
     */
    public function thenIShouldHaveTheFollowingRequiredTypeDoc(PyStringNode $data)
    {
        $this->thenIShouldHaveTheFollowingTypeDoc($data, true);
    }

    /** END - TYPES */

    /** PATHS */

    /**
     * @Then I should have a :httpMethod path named :pathName
     * @Then I should have a :httpMethod path named :pathName like following:
     */
    public function thenIShouldHaveAPathNamed($httpMethod, $pathName, PyStringNode $data = null)
    {
        Assert::assertArrayHasKey(self::PATHS_KEY, $this->lastNormalizedOutput);
        Assert::assertArrayHasKey($pathName, $this->lastNormalizedOutput[self::PATHS_KEY]);

        Assert::assertArrayHasKey(
            strtolower($httpMethod),
            $this->lastNormalizedOutput[self::PATHS_KEY][$pathName],
            sprintf(
                'Failed asserting that the path "%s" have "%s" http method',
                $pathName,
                $httpMethod
            )
        );

        if (null !== $data) {
            $operation = $this->extractPath($httpMethod, $pathName);
            $decodedExpected = $this->jsonDecode($data->getRaw());

            Assert::assertSame($decodedExpected, $operation);
        }
    }

    /**
     * @Then I should have a :httpMethod path named :pathName containing the following:
     */
    public function thenIShouldHaveAPathNamedLikeFollowing($httpMethod, $pathName, PyStringNode $data)
    {
        $this->thenIShouldHaveAPathNamed($httpMethod, $pathName);

        $operation = $this->extractPath($httpMethod, $pathName);
        $decodedExpected = $this->jsonDecode($data->getRaw());

        foreach ($decodedExpected as $expectedKey => $expectedContent) {
            Assert::assertArrayHasKey($expectedKey, $operation);
            Assert::assertSame($expectedContent, $operation[$expectedKey]);
        }
    }

    /**
     * @Then :httpMethod path named :pathName should have the following parameters:
     */
    public function thenPathNamedShouldHaveTheFollowingParameters($httpMethod, $pathName, PyStringNode $data)
    {
        $this->thenIShouldHaveAPathNamed($httpMethod, $pathName);

        $operation = $this->extractPath($httpMethod, $pathName);
        $operationParameters = $operation['requestBody']['content']['application/json']['schema']['allOf'];
        var_dump(json_encode($operationParameters));
        $decodedExpected = $this->jsonDecode($data->getRaw());

        Assert::assertContains($decodedExpected, $operationParameters);
    }

    /**
     * Error and response result are stored under the same key
     *
     * @Then :httpMethod path named :pathName should have the following response:
     * @Then :httpMethod path named :pathName should have the following error:
     */
    public function thenPathNamedShouldHaveTheFollowingResponse($httpMethod, $pathName, PyStringNode $data)
    {
        $this->thenIShouldHaveAPathNamed($httpMethod, $pathName);

        $operation = $this->extractPath($httpMethod, $pathName);
        $operationResponseSchemaList = $operation['responses']['200']['content']['application/json']['schema']['allOf'];
        var_dump(json_encode($operationResponseSchemaList));
        $decodedExpected = $this->jsonDecode($data->getRaw());

        Assert::assertContains($decodedExpected, $operationResponseSchemaList);
    }

    /**
     * @param $httpMethod
     * @param $pathName
     *
     * @return mixed
     */
    private function extractPath($httpMethod, $pathName)
    {
        return $this->lastNormalizedOutput[self::PATHS_KEY][$pathName][strtolower($httpMethod)];
    }

    /** END - PATHS */
}
