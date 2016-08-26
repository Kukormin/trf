<?
$arUrlRewrite = array(
	array(
		"CONDITION" => "#^/projects/([0-9]+)/#",
		"RULE" => "id=\$1",
		"ID" => "",
		"PATH" => "/projects/detail.php",
	),
	array(
		"CONDITION" => "#^/ads/([0-9]+)/#",
		"RULE" => "id=\$1",
		"ID" => "",
		"PATH" => "/ads/index.php",
	),
);

?>