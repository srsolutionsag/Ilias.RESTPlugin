<?php

namespace RESTController\libs\Middleware;

use Slim\Middleware;

/**
 * Class CORS
 *
 * @package RESTController\libs\Middleware
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
class CORS extends Middleware {

	/**
	 * Function: call()
	 *  When used as middleware this function will be called once
	 *  the middleware is executed.
	 */
	public function call() {
		try {
			$response = $this->app->response();
			$response->headers->set("Access-Control-Allow-Origin", "*");
			$response->headers->set("Access-Control-Expose-Headers", "ETag");

			if ($this->app->request()->getMethod() === "OPTIONS" && $this->app->request()->headers("Access-Control-Request-Method") !== NULL) {
				$requestedHeader = $this->app->request()->headers("Access-Control-Request-Headers");
				$response->headers->set("Access-Control-Allow-Headers", $requestedHeader !== NULL ? $requestedHeader : '');

				$response->headers->set("Access-Control-Allow-Methods", "GET,POST,PUT,PATCH,DELETE");
				$response->headers->set("Access-Control-Max-Age", 600);
				$response->setStatus(200);
				return;
			}

			// Invoke next middleware
			$this->next->call();
		}
		catch (\Exception $e) {
			$response = $this->app->response();
			$response->setStatus(422);
			$response->setBody($e->responseObject());
		}
	}
}