<?php

namespace RESTController\libs\Middleware;

use ILIAS\HTTP\GlobalHttpState;
use SlimRestPlugin\Middleware;

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
			$this->setHeader('Access-Control-Allow-Origin', '*');
			$this->setHeader('Access-Control-Expose-Headers', 'ETag');

			if ($this->app->request()->getMethod() === "OPTIONS" && $this->app->request()->headers("Access-Control-Request-Method") !== NULL) {

				$requestedHeader = $this->app->request()->headers("Access-Control-Request-Headers");
				$this->setHeader("Access-Control-Allow-Headers", $requestedHeader !== NULL ? $requestedHeader : '');

				$this->setHeader("Access-Control-Allow-Methods", "GET,POST,PUT,PATCH,DELETE");
				$this->setHeader("Access-Control-Max-Age", 600);

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

	private function setHeader($key, $value) {
		header("$key: $value", true);
	}
}