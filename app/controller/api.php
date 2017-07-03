<?php
namespace app\controller;

use app\model\ApiModel as apiModel;
use \Psr\Http\Message\ServerRequestInterface as Request;
use app\library\Config as Config;

class Api
{

    public $access_key;

    public function __construct($app)
    {
        $config = new Config($app);
        $this->access_key = $config->getConfigValueByKey("config_builder_access_key");
    }

    public function updateBuildStatus($headers, $params, $app)
    {

        if (empty($this->access_key)) {
            $msg = ACCESS_KEY_NOT_SET;
            $response_status = FAILURE_STATUS;
            return $response = array("status" => $response_status, "message" => $msg);
        }

        $required_params = array(
            'mandatory_param' => array('build_id'),
            'update_param' => array('build_status', 'bundle_version'),
            'header_param' => array('access_key'),
        );

        $params = sanitizeParameters($params);

        $is_valid_params = $this->validateApiParameters($headers, $required_params, $params);

        if (!is_bool($is_valid_params)) {
            $response_status = FAILURE_STATUS;
            $missing_params = (isset($is_valid_params['missing_params'])) ? filterParamName($is_valid_params['missing_params']) : "";
            $missing_headers = (isset($is_valid_params['missing_headers'])) ? filterParamName($is_valid_params['missing_headers']) : "";
            $invalid_param = (isset($is_valid_params['invalid_parameter'])) ? filterParamName($is_valid_params['invalid_parameter']) : "";

            if (!empty($invalid_param)) {
                $msg = INVALID_API_ACCESS_KEY;
                $response_status = FAILURE_STATUS;
            } else {
                $msg = "";
                $msg = (!empty($missing_params)) ? sprintf(PARAMETER_MISSING, implode(', ', $missing_params)) : "";
                $msg .= (!empty($missing_headers)) ? sprintf(HEADER_MISSING, implode(', ', $missing_headers)) : "";
            }
            return $response = array("status" => $response_status, "message" => $msg);
        }

        $api_model = new apiModel($app);
        $update_build = $api_model->updateProjectBuild($params);
        $msg = $update_build['msg'];
        $response_status = $update_build['response_status'];


        return $response = array("status" => $response_status, "message" => $msg);
    }

    private function validateApiParameters($headers, $required_params, $params)
    {
        $missing_params = array();
        $missing_headers = array();
        $response = "";

        $mendatory_param = (isset($required_params['mandatory_param'])) ? $required_params['mandatory_param'] : false;
        $either_one_mendatory_param = (isset($required_params['update_param'])) ? $required_params['update_param'] : false;
        $mendatory_header = (isset($required_params['header_param'])) ? $required_params['header_param'] : false;

        /* Header Mendatory Parameter Check */
        if ($mendatory_header) {
            foreach ($mendatory_header as $mh) {
                $is_missing = (isset($headers[$mh])) ? $headers[$mh] : false;
                if ($is_missing == false) {
                    $missing_headers[] = $mh;
                }
            }
            if (!empty($missing_headers)) {
                return $this->returnMissingParam($missing_headers, $missing_params);
            }
        }

        /* Check API Access Key */
        $api_access_key = isset($headers['access_key']) ? $headers['access_key'] : "";
        if (htmlspecialchars_decode($api_access_key) != htmlspecialchars_decode($this->access_key)) {
            $invalid_params = "access_key";
            return $this->returnMissingParam($missing_headers, $missing_params, $invalid_params);
        }

        /* Mendatory Parameter Check */
        if ($mendatory_param) {
            foreach ($mendatory_param as $mp) {
                $is_missing = (!isset($params[$mp])) ? true : false;
                if ($is_missing) {
                    $missing_params[] = $mp;
                }
            }
            if (!empty($missing_params)) {
                return $this->returnMissingParam($missing_headers, $missing_params);
            }
        }

        /* Either One Mendatory Parameter Check */
        if ($either_one_mendatory_param) {
            $either_one_mendatory_missing = true;
            foreach ($either_one_mendatory_param as $eomp) {
                $exists = (isset($params[$eomp])) ? $params[$eomp] : false;
                if ($exists) {
                    $either_one_mendatory_missing = false;
                    break;
                }
            }
            if ($either_one_mendatory_missing) {
                $missing_params[] = "both " . implode(' and ', $either_one_mendatory_param);
            }

            if (!empty($missing_params)) {
                return $this->returnMissingParam($missing_headers, $missing_params);
            }
        }

        return true;

    }

    private function returnMissingParam($missing_headers, $missing_params, $invalid_params = [])
    {
        /* Return Missing Parameters array */
        if (!empty($missing_params) || !empty($missing_headers) || !empty($invalid_params)) {
            return $missing_input = array("missing_params" => $missing_params, "missing_headers" => $missing_headers, "invalid_parameter" => $invalid_params);
        }
    }

    public function checkCertificateLogExpiration($headers, $app)
    {
        $message = array();

        if (empty($this->access_key)) {
            $msg = ACCESS_KEY_NOT_SET;
            $response_status = FAILURE_STATUS;
            return $response = array("status" => $response_status, "message" => $msg);
        }

        $required_params = array(
            'header_param' => array('access_key'),
        );

        $is_valid_params = $this->validateApiParameters($headers, $required_params, $params = array());

        if (!is_bool($is_valid_params)) {
            $response_status = FAILURE_STATUS;
            $missing_params = (isset($is_valid_params['missing_params'])) ? filterParamName($is_valid_params['missing_params']) : "";
            $missing_headers = (isset($is_valid_params['missing_headers'])) ? filterParamName($is_valid_params['missing_headers']) : "";
            $invalid_param = (isset($is_valid_params['invalid_parameter'])) ? filterParamName($is_valid_params['invalid_parameter']) : "";

            if (!empty($invalid_param)) {
                $msg = INVALID_API_ACCESS_KEY;
                $response_status = FAILURE_STATUS;
            } else {
                $msg = "";
                $msg = (!empty($missing_params)) ? sprintf(PARAMETER_MISSING, implode(', ', $missing_params)) : "";
                $msg .= (!empty($missing_headers)) ? sprintf(HEADER_MISSING, implode(', ', $missing_headers)) : "";
            }
            return $response = array("status" => $response_status, "message" => $msg);
        }


        $api_model = new apiModel($app);

        $event_group = 'Certificate';
        $days = array(15, 5, 1);

        foreach ($days as $day) {
            if ($day == 1) {
                $event_action = "Expiration ($day day)";
            } else {
                $event_action = "Expiration ($day days)";
            }
            $event_action_data = $api_model->getActiveEventActionByGroupAndAction($event_group, $event_action);
            if (!empty($event_action_data)) {
                $date = date('Y-m-d', strtotime("+$day days"));
                $certificates = $api_model->getToBeExpiredCertificates($date);
                foreach ($certificates as $certificate) {
                    $event_data['event_prefix'] = str_replace('.p12', '', $certificate['file_name']);
                    $event_data['event_postfix'] = $certificate['name'];
                    $events_data[] = array_merge($event_data, $event_action_data);
                }
            }
        }

        if (isset($events_data) && !empty($events_data)) {
            $current_events_data = $api_model->checkEvents($events_data);
            foreach ($events_data as $event_data) {
                $duplicate = false;
                foreach ($current_events_data as $current_event_data) {
                    if (
                        $event_data['event_prefix'] == $current_event_data['event_prefix'] &&
                        $event_data['event_postfix'] == $current_event_data['event_postfix'] &&
                        $event_data['event_group_id'] == $current_event_data['event_group_id'] &&
                        $event_data['event_action_id'] == $current_event_data['event_action_id']
                    ) {
                        $duplicate = true;
                    }
                }
                if ($duplicate === false) $new_events_data[] = $event_data;
            }
        }

        if (isset($new_events_data) && !empty($new_events_data)) {
            $result = $api_model->addEvents($new_events_data);
            if (!empty($result['success'])) {
                $success_record = implode(',', $result['success']);
                $message['status'] = "success";
                $message['message'] = "Certificate event data has been added for the following file(s) : " . $success_record;
            }
            if (!empty($result['failed'])) {
                $failed_record = implode(',', $result['failed']);
                $message['status'] = "error";
                $message['message'] = "Certificate event data has been failed for the following file(s) : " . $failed_record;
            }
        } else {
            $message['status'] = "error";
            $message['message'] = "No new certificate event data available to add.";
        }

        return $message;
    }

    public function checkProvisioningLogExpiration($headers, $app)
    {
        $message = array();

        if (empty($this->access_key)) {
            $msg = ACCESS_KEY_NOT_SET;
            $response_status = FAILURE_STATUS;
            return $response = array("status" => $response_status, "message" => $msg);
        }

        $required_params = array(
            'header_param' => array('access_key'),
        );

        $is_valid_params = $this->validateApiParameters($headers, $required_params, $params = array());

        if (!is_bool($is_valid_params)) {
            $response_status = FAILURE_STATUS;
            $missing_params = (isset($is_valid_params['missing_params'])) ? filterParamName($is_valid_params['missing_params']) : "";
            $missing_headers = (isset($is_valid_params['missing_headers'])) ? filterParamName($is_valid_params['missing_headers']) : "";
            $invalid_param = (isset($is_valid_params['invalid_parameter'])) ? filterParamName($is_valid_params['invalid_parameter']) : "";

            if (!empty($invalid_param)) {
                $msg = INVALID_API_ACCESS_KEY;
                $response_status = FAILURE_STATUS;
            } else {
                $msg = "";
                $msg = (!empty($missing_params)) ? sprintf(PARAMETER_MISSING, implode(', ', $missing_params)) : "";
                $msg .= (!empty($missing_headers)) ? sprintf(HEADER_MISSING, implode(', ', $missing_headers)) : "";
            }
            return $response = array("status" => $response_status, "message" => $msg);
        }

        $api_model = new apiModel($app);

        $event_group = 'Provisioning Profile';
        $days = array(15, 5, 1);

        foreach ($days as $day) {
            if ($day == 1) {
                $event_action = "Expiration ($day day)";
            } else {
                $event_action = "Expiration ($day days)";
            }
            $event_action_data = $api_model->getActiveEventActionByGroupAndAction($event_group, $event_action);
            if (!empty($event_action_data)) {
                $date = date('Y-m-d', strtotime("+$day days"));
                $provisioning_profiles = $api_model->getToBeExpiredProvisioningProfiles($date);
                foreach ($provisioning_profiles as $provisioning_profile) {
                    $event_data['event_prefix'] = $provisioning_profile['app_name'];
                    $event_data['event_postfix'] = $provisioning_profile['name'];
                    $events_data[] = array_merge($event_data, $event_action_data);
                }
            }
        }

        $current_events_data = $api_model->checkEvents($events_data);
        foreach ($events_data as $event_data) {
            $duplicate = false;
            foreach ($current_events_data as $current_event_data) {
                if (
                    $event_data['event_prefix'] == $current_event_data['event_prefix'] &&
                    $event_data['event_postfix'] == $current_event_data['event_postfix'] &&
                    $event_data['event_group_id'] == $current_event_data['event_group_id'] &&
                    $event_data['event_action_id'] == $current_event_data['event_action_id']
                ) {
                    $duplicate = true;
                }
            }
            if ($duplicate === false) $new_events_data[] = $event_data;
        }

        if (!empty($new_events_data)) {
            $result = $api_model->addEvents($new_events_data);
            if (!empty($result['success'])) {
                $success_record = implode(',', $result['success']);
                $message['status'] = "success";
                $message['message'] = "Provisioning Profile event data has been added for the following app(s) : " . $success_record;
            }
            if (!empty($result['failed'])) {
                $failed_record = implode(',', $result['failed']);
                $message['status'] = "error";
                $message['message'] = "Provisioning Profile event data has been failed for the following app(s) : " . $failed_record;
            }
        } else {
            $message['status'] = "error";
            $message['message'] = "No new provisioning profile event data available to add.";
        }

        return $message;
    }
}

?>