<?php
class AuthMiddleware extends \Slim\Middleware {

	public function call() {
		$app = $this->app;
		$req = $app->request;
		$res = $app->response;

		if ($app->getCookie('slim_session') == null && $req->params('wresult') == null) {
			return $res->redirect("https://ad.kidozen.com/adfs/ls/?wa=wsignin1.0&wtrealm=https://" . $req->getHost());
		}
		if ($req->params('wresult') != null) {
			$wresult = htmlentities($req->params('wresult'));
			$token = substr(explode('RequestedSecurityToken', $wresult)[1], 4, -7);
			$app->setCookie('slim_session', $token);
		}
		$this->next->call();
	}

}