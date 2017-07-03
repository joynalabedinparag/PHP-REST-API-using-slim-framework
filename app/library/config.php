<?php
namespace app\library;

class Config
{
    public $con;
    public function __construct($app) {
        $this->con = $app->get('settings')['db']['con'];
    }

    public function getConfigValueByKey($key) {
        $sql = "select value from sm_setting where `key`='$key' order by setting_id limit 1";
        $result = $this->con->query($sql);
        $result = $result->fetch_array(MYSQLI_ASSOC);
        return $result['value'];
    }
}