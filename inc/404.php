<?
/** @global CMain $APPLICATION */

$APPLICATION->SetTitle("Ошибка 404. Неправильно указан адрес.");

if(!defined('ERROR_404')) {
	define('ERROR_404', 'Y');
	CHTTP::SetStatus('404 Not Found');
}

?>
<h3>Страницы с указанным адресом не существует, либо в данный момент она недоступна.</h3>
<p>Вы можете перейти на <a href="/">Главную страницу</a></p>
