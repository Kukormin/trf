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