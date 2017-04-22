<?php
use Slim\Http\Body;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\Response;

class PugRendererTest extends PHPUnit_Framework_TestCase
{
    public function testRenderer() {
        $renderer = new \Bnf\PugView\PugRenderer([
            'extension' => '.pug',
            'basedir' => 'tests/templates/'
        ]);

        $headers = new Headers();
        $body = new Body(fopen('php://temp', 'r+'));
        $response = new Response(200, $headers, $body);

        $newResponse = $renderer->render('test', array("hello" => "Hi"), $response);

        $newResponse->getBody()->rewind();

        $this->assertEquals("Hi", $newResponse->getBody()->getContents());
    }

    public function testAttributeMerging() {
        $renderer = new \Bnf\PugView\PugRenderer([
            'extension' => '.pug',
            'basedir' => 'tests/templates/'
        ], [
            "hello" => "Hello"
        ]);

        $headers = new Headers();
        $body = new Body(fopen('php://temp', 'r+'));
        $response = new Response(200, $headers, $body);

        $newResponse = $renderer->render('test', [
            "hello" => "Hi"
        ], $response);
        $newResponse->getBody()->rewind();
        $this->assertEquals("Hi", $newResponse->getBody()->getContents());
    }

    public function testAttributeSet() {
        $renderer = new \Bnf\PugView\PugRenderer([
            'extension' => '.pug',
            'basedir' => 'tests/templates/'
        ], [
            "hello" => "Hello"
        ]);

        $headers = new Headers();
        $body = new Body(fopen('php://temp', 'r+'));
        $response = new Response(200, $headers, $body);

        $renderer->set('hello', 'Hi');
        $newResponse = $renderer->render('test', [], $response);
        $newResponse->getBody()->rewind();
        $this->assertEquals("Hi", $newResponse->getBody()->getContents());
    }

    public function testMiddlewareInvoke() {
        $renderer = new \Bnf\PugView\PugRenderer([
            'extension' => '.pug',
            'basedir' => 'tests/templates/'
        ]);

        $headers = new Headers();
        $body = new Body(fopen('php://temp', 'r+'));
        $response = new Response(200, $headers, $body);
        $request = $this->mockRequest();

        $renderer->__invoke($request, $response, function($request, $response) {
            return $response;
        });

        $renderer->set('hello', 'Hi');
        $newResponse = $renderer->render('test');
        $newResponse->getBody()->rewind();
        $this->assertEquals("Hi", $newResponse->getBody()->getContents());
    }

    public function testExceptionInTemplate() {
        $renderer = new \Bnf\PugView\PugRenderer([
            'extension' => '.pug',
            'basedir' => 'tests/templates/'
        ]);

        $headers = new Headers();
        $body = new Body(fopen('php://temp', 'r+'));
        $response = new Response(200, $headers, $body);

        try {
            $newResponse = $renderer->render('test-exception', [], $response);
        } catch (Throwable $t) { // PHP 7+
            // Simulates an error template
            $newResponse = $renderer->render('test', [
                "hello" => "Hi"
            ]);
        } catch (Exception $e) { // PHP < 7
            // Simulates an error template
            $newResponse = $renderer->render('test', [
                "hello" => "Hi"
            ], $response);
        }

        $newResponse->getBody()->rewind();

        $this->assertEquals("Hi", $newResponse->getBody()->getContents());
    }


    /**
     * @expectedException RuntimeException
     */
    public function testTemplateNotFound() {
        $renderer = new \Bnf\PugView\PugRenderer([
            'extension' => '.pug',
            'basedir' => 'tests/templates'
        ]);

        $headers = new Headers();
        $body = new Body(fopen('php://temp', 'r+'));
        $response = new Response(200, $headers, $body);

        $renderer->render('adfadftestTemplate', [], $response);
    }

    /**
     * @return \Slim\Http\Request
     */
    protected function mockRequest()
    {
        $env = \Slim\Http\Environment::mock([
            'SCRIPT_NAME' => '/index.php',
            'REQUEST_URI' => '/',
            'REQUEST_METHOD' => 'GET',
        ]);
        $uri = \Slim\Http\Uri::createFromEnvironment($env);
        $headers = \Slim\Http\Headers::createFromEnvironment($env);
        $cookies = [];
        $serverParams = $env->all();
        $body = new \Slim\Http\RequestBody();
        $req = new \Slim\Http\Request('GET', $uri, $headers, $cookies, $serverParams, $body);

        return $req;
    }
}
