<?php
namespace app\model;

class ApiModel
{

    public $con;

    public function __construct($app)
    {
        $this->con = $app->get('settings')['db']['con'];
    }

    public function validateBuildId($build_id)
    {
        $sql = "select id from sm_project_builds where id='$build_id'";
        $result = $this->con->query($sql);
        return ($result->num_rows >= 1) ? true : false;
    }

    public function updateProjectBuild($params)
    {
        $response = array();

        $bundle_version = isset($params['bundle_version']) ? $params['bundle_version'] : "";
        $build_status = isset($params['build_status']) ? $params['build_status'] : "";
        $build_id = isset($params['build_id']) ? $params['build_id'] : "";

        /* Build up Update query and bind parameters */
        $update_param = array();
        $binded_value = array(); // Bind parameter array for mysqli prepared statement.
        if (!empty($build_status)) {
            $update_param['build status'] = "build_status = ? ";
            $binded_value[] =  &$build_status;
        }
        if (!empty($bundle_version)) {
            $update_param['bundle version'] = "bundle_version = ? ";
            $binded_value[] =  &$bundle_version;
        }

        $binded_value[] = &$build_id;

        if (!empty($update_param)) {
            $query_string = "update sm_project_builds ";
            $query_string .= " set " . implode(", ", $update_param);
            $query_string .= " where id = ? ";

            $bind_types = "";
            foreach ($binded_value as $b) {
                $bind_types .= "s";
            };
            $bind_types = &$bind_types;
            array_unshift($binded_value, $bind_types);
        }
        /* Build up Update query */

        /* Checks if build_id exists in sm_project_builds table */
        $is_valid_project_number = $this->validateBuildId($build_id);
        if ($is_valid_project_number) {
            $prepared_query = $this->con->prepare($query_string);
            call_user_func_array(array($prepared_query, 'bind_param'), $binded_value);
            $result = $prepared_query->execute();
            if ($result !== false) {
                $msg = sprintf(SUCCESS_MSG, implode(" and ", array_keys($update_param)));
            } else {
                $msg = (APP_DEBUG == true) ? htmlspecialchars($prepared_query->error) : FAILURE_MSG;
            }
            $response_status = ($result !== false) ? SUCCESS_STATUS : FAILURE_STATUS;
        } else {
            $msg = INVALID_BUILD_ID;
            $response_status = FAILURE_STATUS;
        }

        $response['msg'] = $msg;
        $response['response_status'] = $response_status;

        return $response;
    }

    public function getActiveEventActionByGroupAndAction($event_group, $event_action)
    {
        $sql = "SELECT ea.id as event_action_id, ea.event_group_id FROM sm_event_actions as ea, sm_event_groups as eg where eg.id = ea.event_group_id AND ea.status = 1 AND eg.event_group = '" . $event_group . "' AND ea.event_action = '" . $event_action . "'";
        $query = $this->con->query($sql);
        return $query->fetch_assoc();
    }

    public function getToBeExpiredCertificates($date)
    {
        $sql = "SELECT crt.*, cus.name from sm_certificates as crt LEFT JOIN sm_customer as cus ON crt.customer_key = cus.customer_key WHERE DATE(crt.expiration_date)='" . $date . "'";
        $result = $this->con->query($sql);
        $certificates = $result->fetch_all(MYSQLI_ASSOC);

        return $certificates;

    }

    public function getToBeExpiredProvisioningProfiles($date)
    {
        $sql = "SELECT prd.*, cus.name from sm_provision_details as prd 
                LEFT JOIN sm_provision_files as prf ON prd.provision_files_id = prf.id
                LEFT JOIN sm_customer as cus ON prf.customer_id = cus.id
                WHERE DATE(prd.expiration_date)='" . $date . "'";
        $result = $this->con->query($sql);
        $certificates = $result->fetch_all(MYSQLI_ASSOC);

        return $certificates;

    }

    public function checkEvents($events_data)
    {
        foreach ($events_data as $event) {
            $sql = "SELECT event_prefix, event_postfix, event_action_id, event_group_id FROM sm_events WHERE event_group_id='".$event['event_group_id']."' AND event_action_id='".$event['event_action_id']."' AND event_prefix='".$event['event_prefix']."' AND event_postfix='".$event['event_postfix']."' AND DATE(created_date)=DATE(NOW())";
            $result = $this->con->query($sql);
            $current_events_data[] = $result->fetch_all(MYSQLI_ASSOC)[0];
        }
        return $current_events_data;
    }

    public function addEvents($events_data)
    {
        $success_failed_event_prefix = array(
            'success', 'failed'
        );

        if(!empty($events_data)) {
            foreach ($events_data as $event) {
                $sql = "INSERT INTO sm_events (event_group_id, event_action_id, event_prefix, event_postfix, created_date)
                        VALUES ('" . $event['event_group_id'] . "', '" . $event['event_action_id'] . "', '" . $event['event_prefix'] . "', '" . $event['event_postfix'] . "', now());";
                if($this->con->query($sql)) {
                    $success_failed_event_prefix['success'][] = $event['event_prefix'];
                } else {
                    $success_failed_event_prefix['failed'][] = $event['event_prefix'];
                }
            }
        }

        return $success_failed_event_prefix;
    }

    public function __destruct()
    {
        $this->con->close();
    }
}

?>