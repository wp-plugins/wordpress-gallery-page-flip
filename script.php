<?php
error_reporting(0);

if ($_GET['action'] == 'test')
{
	echo "OK\n".$_GET['id'];
	exit();
}

$actions = array( 'loadalbumxml', 'savealbumxml', 'loadlayouts' );
if( !in_array( $_GET['action'], $actions ) || empty( $_GET['id'] ) )
{
	die('You are not allowed to call this page directly.');
}

$base = substr( $_SERVER['SCRIPT_NAME'], 0, strpos( $_SERVER['SCRIPT_NAME'], '/wp-content/' ) );

$debug = fopen($_SERVER['DOCUMENT_ROOT'].$base.'/wp-content/pageflip/debug.txt', 'w');
ob_start();

function make_http_post_request($server, $uri, $post, $uagent)
{
	global $debug;
	$_post = Array();

	if (is_array($post))
	{
		foreach ($post as $name => $value)
			$_post[] = $name.'='.urlencode($value);
	}
	$post = implode('&', $_post);

	if ( $fp = fsockopen($server, 80, $errno, $errstr) )
	{
		fputs($fp,
			"POST $uri HTTP/1.1\r\n".
			"Host: $server\r\n".
			"User-Agent: $uagent\r\n".
			"Content-Type: application/x-www-form-urlencoded\r\n".
			"Content-Length: ".strlen($post)."\r\n".
			"Connection: Close\r\n\r\n$post"
		);

		$content = '';
		while (!feof($fp))
			$content .= fgets($fp);

		fclose($fp);
		return $content;
	}
	else
	{
		fwrite($debug, "fsockopen failed: $errstr ($errno)\n");
	}

	return false;
}


$cookie = isset($_SERVER['HTTP_COOKIE']) ? urlencode( $_SERVER['HTTP_COOKIE'] ) : '';

$post = Array('feAction' => $_GET['action'], 'cookie' => $cookie, 'bookId' => (int)$_GET['id']);

if ( $_GET['action'] == 'savealbumxml' )
{
	$post['xml'] = urlencode(file_get_contents('php://input'));
}

$uri = $base.'/wp-admin/admin-ajax.php';

$content = make_http_post_request($_SERVER['HTTP_HOST'], $uri, $post, $_SERVER['HTTP_USER_AGENT']);
if ($content !== false)
{
	header( 'Content-Type: text/xml' );

	$content = substr( $content, strpos( $content, '<?xml' ) );
	$content = trim( $content );
	if ( substr( $content, -1 ) == '0' )
		$content = substr( $content, 0, strlen( $content ) - 1 );

	echo $content;
}
else
{
	fwrite($debug, "Request failed.\n");
}

$out = ob_get_flush();

fwrite($debug, $out);
fclose($debug);
?>