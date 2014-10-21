<?php
require '../vendor/autoload.php';

$app = new \Slim\Slim(array(
	'debug' => true,
	'view' => new \Slim\Views\Twig(),
	'templates.path' => '../templates',
	'cookies.encrypt' => true,
	'cookies.secret_key' => 's3cr3t-k3y',
	'sessions.driver' => 'file',
	'sessions.files' => __DIR__ . '/../sessions',
));

$view = $app->view();
$view->parserOptions = array(
	'debug' => true,
	'cache' => __DIR__ . '/../cache',
);

$app->add(new AuthMiddleware());
$manager = new \Slim\Middleware\SessionManager($app);
$manager->setFilesystem(new \Illuminate\Filesystem\Filesystem());
$app->add(new \Slim\Middleware\Session($manager));

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