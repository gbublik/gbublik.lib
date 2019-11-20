<?php
if (empty($_SERVER['DOCUMENT_ROOT'])) {
    $_SERVER['DOCUMENT_ROOT'] = __DIR__ .'/../../../..';
}
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
define('STOP_STATISTICS', true);
define('PERFMON_STOP', true);
define('SM_SAFE_MODE', true);


require($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");