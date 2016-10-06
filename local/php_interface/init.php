<?

// Константы
require('const.php');

// Функции
require('functions.php');

// Библиотеки композера
require($_SERVER['DOCUMENT_ROOT'] . '/local/vendor/autoload.php');

// Обработчики событий
\Local\Handlers::addEventHandlers();

// Модули битрикса
\Bitrix\Main\Loader::IncludeModule('iblock');
\Bitrix\Main\Loader::IncludeModule('highloadblock');

// Adwords API
define('SRC_PATH', $_SERVER['DOCUMENT_ROOT'] . '/local/vendor/googleads/googleads-php-lib/src/');
define('LIB_PATH', 'Google/Api/Ads/AdWords/Lib');
define('UTIL_PATH', 'Google/Api/Ads/Common/Util');
define('ADWORDS_UTIL_PATH', 'Google/Api/Ads/AdWords/Util');
define('ADWORDS_UTIL_VERSION_PATH', 'Google/Api/Ads/AdWords/Util/v201607');
define('ADWORDS_VERSION', 'v201607');

// Configure include path
ini_set('include_path', implode(array(
	ini_get('include_path'), PATH_SEPARATOR, SRC_PATH
)));

// Include the AdWordsUser
require_once LIB_PATH . '/AdWordsUser.php';
//require_once dirname(__FILE__) . '/../../Common/ExampleUtils.php';