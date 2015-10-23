<?
// Константы
require('const.php');
// Функции
require('functions.php');
// Библиотеки композера
require($_SERVER['DOCUMENT_ROOT'] . '/local/vendor/autoload.php');

//
// Классы
//
$localPath = '/local/php_interface/lib/';
CModule::AddAutoloadClasses(
	'',
	array(
		'UserTypeNYesNo' => $localPath.'UserTypeNYesNo.php',
		'Local\\Utils' => $localPath.'Utils.php',
		'Local\\StaticCache' => $localPath.'StaticCache.php',
		'Local\\ExtCache' => $localPath.'ExtCache.php',
		'Local\\ExtUser' => $localPath.'ExtUser.php',
		'Local\\Direct\\Common' => $localPath.'Direct/Common.php',
		'Local\\Direct\\Api4' => $localPath.'Direct/Api4.php',
		'Local\\Direct\\Api5' => $localPath.'Direct/Api5.php',
		'Local\\Direct\\Clients' => $localPath.'Direct/Clients.php',
		'Local\\Direct\\Campaigns' => $localPath.'Direct/Campaigns.php',
		'Local\\Direct\\AdGroups' => $localPath.'Direct/AdGroups.php',
		'Local\\Direct\\Ads' => $localPath.'Direct/Ads.php',
		'Local\\Direct\\Keywords' => $localPath.'Direct/Keywords.php',
	)

);

//
// Юзертайпы
//
AddEventHandler('iblock', 'OnIBlockPropertyBuildList', array('UserTypeNYesNo', 'GetUserTypeDescription'));

// Модули битрикса
\Bitrix\Main\Loader::IncludeModule('iblock');