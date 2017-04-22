[![Build Status](https://travis-ci.org/bnf/pug-view.svg?branch=master)](https://travis-ci.org/bnf/pug-view)

## PUG Renderer

This is a renderer for rendering PUG templates into a PSR-7 Response object. It works well with Slim Framework 3.


## Installation

Install with [Composer](http://getcomposer.org):

    composer require bnf/pug-view


## Usage with Slim 3

```php
use Bnf\PugView\PugRenderer;

include 'vendor/autoload.php';

$app = new Slim\App();
$container = $app->getContainer();

$settings = [
    'extension' => '.pug',
    'basedir' => 'templates/'
];

$container['view'] = function($c) {
	return new PugRenderer($settings);
};

/* PugRenderer is added as middleware to automatically inject the $response object. */
$app->add($container->get('view'));

/* Add global template variables */
$app->add(function($request, $response, $next) {
    $this->view->set('title', 'default title');
    // Make the container accessible in the view, so that every object can be accessed in the template:
    // E.g: a(href=c.router.pathFor('named-route'))
    $this->view->set('c', $this);

    return $next($request, $response);
});

$app->get('/hello/{name}', function ($request, $response, $args) {
    return $this->view->render('hello', $args);
});

$app->run();
```

## Usage with any PSR-7 Project
```php
//Construct the View
$settings = [
    'extension' => '.pug',
    'basedir' => 'templates/'
];
$phpView = new PugRenderer($settings);

//Render a Template
$response = $phpView->render('template', $yourData, new Response());
```
