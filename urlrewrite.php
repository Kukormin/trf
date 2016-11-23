<?
$arUrlRewrite = array(
	array(
		"CONDITION" => "#/vcard/yandex/(\\d+)/(\\d+)/\$#",
		"RULE" => "id=\$2&pid=\$1",
		"ID" => "",
		"PATH" => "/vcard/yandex/index.php",
	),
	array(
		"CONDITION" => "#^(.*)\$#",
		"RULE" => "",
		"ID" => "tim:nav",
		"PATH" => "/index.php",
	),
);

?>