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

$app->get('/token', function ($name) use ($app) {
	$token = $app->session->get('slim_session');
	$client = new GuzzleHttp\Client();
	$response = $client->post('https://auth-qa.kidozen.com/v1/contoso/oauth/token', array(
		'body' => array(
			'client_id' => 'dcda76f2-f2de-444d-ba7b-a2645d418e6c',
			'client_secret' => 'GkDf4BNhHy5LOO4qokiUuCd++V9SBBrzl2KtBU6PBM4=',
			'assertion' => html_entity_decode($token),
			'scope' => 'tasks',
			'grant_type' => 'urn:ietf:params:oauth:grant-type:saml2-bearer',
		)
	))->getBody();
	$app->response->headers->set('Content-Type', 'application/json');
	$app->response->setBody($response);
});

$app->run();