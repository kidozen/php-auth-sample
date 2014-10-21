<?php
class AuthMiddleware extends \Slim\Middleware {

	public function call() {
		$app = $this->app;
		$req = $app->request;
		$res = $app->response;

		if (is_null($app->session->get('slim_session')) && is_null($req->params('wresult'))) {
			return $res->redirect("https://ad.kidozen.com/adfs/ls/?wa=wsignin1.0&wtrealm=https://" . $req->getHost());
		} elseif ($req->params('wresult')) {
			$wresult = htmlentities($req->params('wresult'));
			$token = substr(explode('RequestedSecurityToken', $wresult)[1], 4, -7);
			$app->session->put('slim_session', $token);
		}
		$this->next->call();
	}

}