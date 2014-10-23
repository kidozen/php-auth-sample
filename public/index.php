<?php require '../vendor/autoload.php';

$app = new \Slim\Slim([
	'debug' => true,
	'mode' => 'development',
	'view' => new \Slim\Views\Twig(),
	'templates.path' => '../templates',
	'cookies.encrypt' => true,
	'cookies.secret_key' => 's3cr3t-k3y',
	'sessions.driver' => 'file',
	'sessions.files' => __DIR__ . '/../sessions',
]);

$view = $app->view();
$view->parserOptions = [
	'debug' => true,
	'cache' => __DIR__ . '/../cache',
];

$app->add(new AuthMiddleware());
$manager = new \Slim\Middleware\SessionManager($app);
$manager->setFilesystem(new \Illuminate\Filesystem\Filesystem());
$app->add(new \Slim\Middleware\Session($manager));

$marketplace = 'https://contoso.local.kidozen.com';
$appName = 'tasks';
$kido = new Kido($marketplace, $appName, $app->mode);

$app->get('/', function () use ($app) {
	$app->redirect('/client-side');
});

$app->get('/client-side', function () use ($app, $kido) {
	$kido->setAssertion($app->session->get('slim_session'));
	$app->render('client-side-sample.html');
});

$app->get('/server-side', function () use ($app, $kido) {
	$kido->setAssertion($app->session->get('slim_session'));
	$app->render('server-side-sample.html', [
		'response' => json_encode($kido->getObjectSets()),
	]);
});

$app->post('/', function ($name) use ($app) {
	$app->redirect('/client-side');
});

$app->get('/token', function () use ($app, $kido) {
	$kido->setAssertion($app->session->get('slim_session'));
	$app->response->headers->set('Content-Type', 'application/json');
	$app->response->setBody(json_encode($kido->getKidoToken()));
});

$app->run();