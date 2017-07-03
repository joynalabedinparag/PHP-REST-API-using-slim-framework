<?php
// prevent direct accesss
defined('SM_APEXEC') or die('no direct access');

//DATABASE CONFIGURATION
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', 'localhost');
define('DB_USERNAME', '');
define('DB_PASSWORD', '');
define('DB_DATABASE', '');
define('DB_PREFIX', '');

// LDAP CREDENTIALS
define('LDAP_ENABLED', TRUE); // SET TRUE OR FALSE
//define('LDAP_SERVER', '');
define('LDAP_SERVER', '');
define('LDAP_PORT', '');
define('LDAP_BASE_DN', '');

// JIRA CREDENTIALS
define('JIRA_USERNAME', ''); // BASE64 ENCODED
define('JIRA_PASSWORD', ''); // BASE64 ENCODED
define('JIRA_API_URL', '');
define('JIRA_PROJECT_AVATAR', '');
define('JIRA_PROJECT_FILTER', '');
define('JIRA_USER_LOGGED_URL', '');

// JENKINS CREDENTIALS
define('JENKINS_USERNAME', '');
define('JENKINS_PASSWORD', '');
define('JENKINS_HOST', '');
define('JENKINS_PORT', '');
define('JENKINS_JOB_PATH', '');
define('JENKINS_TOKEN', '');