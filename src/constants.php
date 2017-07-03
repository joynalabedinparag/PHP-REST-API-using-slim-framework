<?php

defined('APP_DEBUG') or define('APP_DEBUG', true);

defined('API_ACCESS_KEY') or define('API_ACCESS_KEY', 'SM_APP_BUILDER');

defined('API_SECRET_KEY') or define('API_SECRET_KEY', 'SCROLLMOTION');

defined('SUCCESS_STATUS') or define('SUCCESS_STATUS', "success");

defined('FAILURE_STATUS') or define('FAILURE_STATUS', "error");

defined('INVALID_API_ACCESS_KEY_STATUS') or define('INVALID_API_ACCESS_KEY_STATUS', "202");

defined('SUCCESS_MSG') or define('SUCCESS_MSG', 'The build has been successfully updated with %s');

defined('FAILURE_MSG') or define('FAILURE_MSG', 'An error has been occurred while updating project builds');

defined('METHODNOTALLOWED') or define('METHODNOTALLOWED', 'Method Not Allowed');

defined('INVALID_API_ACCESS_KEY') or define('INVALID_API_ACCESS_KEY', 'Invalid Api Access Key');

defined('MISSING_API_ACCESS_KEY') or define('MISSING_API_ACCESS_KEY', 'Api Access Key is missing');

defined('PARAMETER_MISSING') or define('PARAMETER_MISSING', ' Missing %s parameter');

defined('HEADER_MISSING') or define('HEADER_MISSING', ' Missing %s parameter at header');

defined('INVALID_BUNDLE_VERSION') or define('INVALID_BUNDLE_VERSION', 'Invalid Bundle Version');

defined('INVALID_BUILD_ID') or define('INVALID_BUILD_ID', 'Invalid Build Id');

defined('ACCESS_KEY_NOT_SET') or define('ACCESS_KEY_NOT_SET', 'Opps! Access Key not configured in builder settings at Web Services.');

?>