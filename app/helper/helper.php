<?php

function filterParamName($arr) {
    $search = "_";
    $replace=" ";
    return json_decode(str_replace($search, $replace, json_encode($arr)), true);
}

/* Secures API paramenters */
function sanitizeParameters($params) {
    $params = array_map(function($param) {
        $param = trim($param);
        $param = strip_tags($param);
        $param = filter_var($param, FILTER_SANITIZE_STRING);
        $param = mres($param);
        $param = htmlspecialchars($param, ENT_QUOTES, 'UTF-8');
        return $param;
    }, $params);
    return $params;
}

/* Equivalent to mysql_real_escape_string */
function mres($value) {
    $search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
    $replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");
    return str_replace($search, $replace, $value);
}
?>