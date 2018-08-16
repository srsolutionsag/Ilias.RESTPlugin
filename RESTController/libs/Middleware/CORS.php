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

			// We can not use $response->headers->set() because ilFileDelivery may kills the request.
			header("Access-Control-Allow-Origin: *");
			header("Access-Control-Expose-Headers: ETag");

			if ($this->app->request()->getMethod() === "OPTIONS" && $this->app->request()->headers("Access-Control-Request-Method") !== NULL) {

				// $response->headers->set() can be used while serving the CORS preflight request.
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