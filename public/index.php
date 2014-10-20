<?php
require '../vendor/autoload.php';

$app = new \Slim\Slim(array(
	'debug' => true,
	'view' => new \Slim\Views\Twig(),
	'templates.path' => '../templates',
));

$view = $app->view();
$view->parserOptions = array(
	'debug' => true,
	'cache' => '../cache',
);

$app->add(new \Slim\Middleware\SessionCookie());
$app->add(new AuthMiddleware());

$app->post('/', function ($name) use ($app) {
	$app->redirect('/');
});

$app->get('/', function ($name) use ($app) {
	$app->render('index.html');
});

$app->get('/hello/:name', function ($name) use ($app) {
	$app->render('index.html');
});

$app->run();