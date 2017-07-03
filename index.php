<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use app\controller\Api as ApiController;
use app\library\config as Config;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/src/constants.php';

$settings = require __DIR__ . '/src/settings.php';

$app = new \Slim\App($settings);

$app->post('/builds/update', function (Request $request, Response $response) use ($app) {

    try {
        $params = $request->getQueryParams();
        $headers = apache_request_headers();

        /* Update Build Status */
        $api = new ApiController($this);
        $update = $api->updateBuildStatus($headers, $params, $this);
        $response_status = $update['status'];
        $msg = $update['message'];

        /* Generate Response */
        $data = array('status' => $response_status, 'message' => $msg);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withAddedHeader('Allow', 'POST');
        $newResponse = $response->withJson($data);
        return $newResponse;
        /* Generate  Response */

    } catch (Exception $e) {
        return json_encode(array("status" => "error", "message" => $e->getMessage()));
    }
});

$app->get('/certificate/log/expiration', function (Request $request, Response $response) use ($app) {
    try {
        $params = $request->getQueryParams();
        $headers = apache_request_headers();

        $api = new ApiController($this);
        $check = $api->checkCertificateLogExpiration($headers, $this);
        $response_status = $check['status'];
        $msg = $check['message'];
        $data = array('status' => $response_status, 'message' => $msg);
        $newResponse = $response->withJson($data);
        if (empty($this->access_key)) {
            $msg = ACCESS_KEY_NOT_SET;
            $response_status = FAILURE_STATUS;
            return $response = array("status" => $response_status, "message" => $msg);
        }

    } catch (Exception $e) {
        return json_encode(array("status" => "error", "message" => $e->getMessage()));
    }
});

$app->get('/provision/log/expiration', function (Request $request, Response $response) use ($app) {
    try {
        $params = $request->getQueryParams();
        $headers = apache_request_headers();

        $api = new ApiController($this);
        $check = $api->checkProvisioningLogExpiration($headers, $this);
        $response_status = $check['status'];
        $msg = $check['message'];

        $data = array('status' => $response_status, 'message' => $msg);

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withAddedHeader('Allow', 'GET');
        $newResponse = $response->withJson($data);
    } catch (Exception $e) {
        return json_encode(array("status" => "error", "message" => $e->getMessage()));
    }
});

$app->run();
?>