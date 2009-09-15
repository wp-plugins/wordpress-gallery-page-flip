<?php

include '../../../wp-config.php';

$book_id = $wpdb->escape(htmlspecialchars($_GET['book_id']));
$book = new Book($book_id);

$title = $wpdb->get_var("SELECT `name` FROM `{$pageFlip->table_name}` WHERE `id`='{$book_id}'");

//получаем бекграунд из базы
$sql = "SELECT `bgImage` FROM `".$pageFlip->table_name."` WHERE `id` = '{$book_id}'";
$bgImage = $wpdb->get_var( $sql );

if ( empty( $bgImage ) )
{
	$bgImage = $pageFlip->bgFile;
}
else
{
	if ( $bgImage == "-1" )
		$bgImage = '';
	else
	{
		$sql = "select `filename` from `" . $pageFlip->table_img_name . "` where `id` = '" . $bgImage . "' and `type` = 'bg'";
		$bgImage = $pageFlip->plugin_url . $pageFlip->imagesDir . '/' . $wpdb->get_var( $sql );
	}
}

$backgroundImage = $bgImage;

if (preg_match('/^0x([0-9A-Fa-f]+)$/', $book->backgroundColor, $m))
	$backgroundColor = '#'.$m[1];

$backgroundImageUrl = "url($backgroundImage)";
$backgroundPosition = $book->backgroundImagePlacement;


?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $title; ?></title>
<?php wp_head(); ?>
<style type="text/css">
body {
	margin: 0;
	font-family: sans-serif;
<?php
	echo $backgroundColor ? "\tbackground-color: {$backgroundColor};\n" : '';
	echo $backgroundImage ? "\tbackground-image: {$backgroundImageUrl};\n" : '';
	echo $backgroundPosition ? "\tbackground-position: {$backgroundPosition};\n" : '';
?>
}
body, a {
}
</style>
</head>

<body>
<div>
<?php

echo $pageFlip->html->viewBook($book, '100%', $book->stageHeight, $backgroundImage);

?>
</div>
</body>
</html>