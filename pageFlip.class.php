<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

class pageFlip_plugin_base
{
	var $page_title,
		$menu_title,
		$access_level = 5,
		$add_page_to = 1,
		$table_name, //имя таблицы книг
		$table_img_name, //имя таблицы изображений
		$table_gal_name, //имя таблицы изображений
		$plugin_dir = PAGEFLIP_DIRNAME, //папка плагина
		$pluginFilesDir = 'pageflip', //папка файлов плагина
		$plugin_path, //путь файлов плагина
		$plugin_url, //путь файлов плагина
		$component, //путь до компонента
		$editor, //путь до редактора
		$navigation, //путь до строки навигации
		$componentJS, //путь до js файла компонента
		$jqueryJS, //файлик для запросов
		$swfObjectJS, //js для вставки флешки
		$width = 800, //ширина компонента
		$height = 600, //высота компонента
		$maxPageSize, //максимальный вес файла страницы
		$bgFile, //файл бекграунда
		$maxSoundSize, //максимальный вес файла звука
		$parent, //для submenu
		$booksDir = 'books', //папка для книг
		$soundsDir = 'sounds', //папка для звуков
		$imagesDir = 'images', //папка для изображений
		$imagesPath,
		$uploadDir = 'upload', //папка для загрузок
		$imgUrl, //адрес до изображений
 		$jsDir = 'js', //папка для js скриптов
		$langDir = 'lang', //попка для файлов перевода
		$imagesUrl, //url картинок
		$uploadPath, //путь для загрузок
		$jsUrl, //url js скриптов
		$thumbWidth = 70, //ширина превьюшки для фоток/страниц/книг
		$thumbHeight = 90, //высота превьюшки для фоток/страниц/книг
		$trial, //ограничение на количество страниц
		$functions, //дополнительные функции
		$html, //html часть
		$itemsPerPage,
		$layouts = array(), //шаблоны для флеш-редактора
		$popup_php,			// URL файла popup.php
		$usePageEditor = true;

	function pageFlip_plugin_base()
	{
		global $wpdb;

		$this->get_options();

		$this->page_title = __('FlippingBook Gallery', 'pageFlip');

		$this->menu_title = __('FlippingBook', 'pageFlip');

		$this->table_name = $wpdb->prefix . 'pageflip';
		$this->table_img_name = $wpdb->prefix . 'pageflip_img';
		$this->table_gal_name = $wpdb->prefix . 'pageflip_gallery';

		$this->maxPageSize = 5 * 1024 * 1024;
		$this->maxSoundSize = 100 * 1024;

		$this->plugin_path = WP_CONTENT_DIR . '/' .  $this->pluginFilesDir . '/';
		$this->imagesPath = $this->plugin_path . $this->imagesDir . '/';
		$this->plugin_url = WP_CONTENT_URL . '/' .  $this->pluginFilesDir . '/';
		$this->imagesUrl = $this->plugin_url . $this->imagesDir . '/';
		$this->jsUrl = WP_PLUGIN_URL . '/' . $this->plugin_dir . '/' . $this->jsDir . '/';
		$this->imgUrl = WP_PLUGIN_URL . '/' . $this->plugin_dir . '/img/';
		$this->bgFile = $this->imgUrl . 'bg.jpg';
		$this->component = WP_PLUGIN_URL . '/' .  $this->plugin_dir . '/flippingBook.swf';
		$this->editor = WP_PLUGIN_URL . '/' .  $this->plugin_dir . '/albumEditor.swf';

		$this->navigation  = WP_PLUGIN_URL . '/' .  $this->plugin_dir . '/navigation.swf';
		$this->uploadPath = $this->plugin_path . $this->uploadDir . '/';
		$this->componentJS = $this->jsUrl . 'flippingbook.js';
		$this->swfObjectJS = $this->jsUrl . 'swfobject.js';
		$this->jqueryJS = $this->jsUrl . 'jquery-1.2.6.pack.js';
		$this->trial = 10;

		$this->popup_php = WP_PLUGIN_URL.'/'.$this->plugin_dir.'/popup.php';

		if ( file_get_contents(PAGEFLIP_URL.'/do/test/1') != "OK\n1" )
			$this->usePageEditor = false;
	}

	function init()
	{
		include_once ( PAGEFLIP_DIR.'/functions.class.php' ); //подключаем файл с классом дополнительных функций
		include_once ( PAGEFLIP_DIR.'/htmlPart.class.php' ); //подключаем файл с классом html части
		include_once ( PAGEFLIP_DIR.'/book.class.php' ); //подключаем файл с классом книги
		include_once ( PAGEFLIP_DIR.'/album.class.php' ); //а тут вообще функции для флеш-редактора

		$this->functions = new Functions( );
		$this->html = new HTMLPart( );

		//задаем значения для шаблонов
		$this->layouts[1] = new Layout( 1 );
		$this->layouts[1]->addArea( 0, 0, 0, 1, 1 );
		$this->layouts[2] = new Layout( 2 );
		$this->layouts[2]->addArea( 0, 0, 0, 1, 0.5 );
		$this->layouts[2]->addArea( 1, 0, 0.5, 1, 0.5 );
		$this->layouts[3] = new Layout( 3 );
		$this->layouts[3]->addArea( 0, 0, 0, 0.5, 0.5 );
		$this->layouts[3]->addArea( 1, 0.5, 0, 0.5, 0.25 );
		$this->layouts[3]->addArea( 2, 0.5, 0.25, 0.5, 0.25 );
		$this->layouts[3]->addArea( 3, 0, 0.5, 1, 0.5 );
		$this->layouts[4] = new Layout( 4 );
		$this->layouts[4]->addArea( 0, 0, 0, 1, 0.25 );
		$this->layouts[4]->addArea( 1, 0, 0.25, 0.33, 0.25 );
		$this->layouts[4]->addArea( 2, 0.33, 0.25, 0.67, 0.5 );
		$this->layouts[4]->addArea( 3, 0, 0.5, 0.33, 0.25 );
		$this->layouts[4]->addArea( 4, 0, 0.75, 0.33, 0.25 );
		$this->layouts[4]->addArea( 5, 0.33, 0.75, 0.33, 0.25 );
		$this->layouts[4]->addArea( 6, 0.66, 0.75, 0.323, 0.25 );

		//проверяем базу и директории
		$this->check_db();
		$this->check_dir();

		//массив со значениями по сколько выводить на страницах
		$this->itemsPerPage = array(
									 0 => array ( 'value' => 25, 'label' => __('25 per page', 'pageFlip') ),
									 1 => array ( 'value' => 50, 'label' => __('50 per page', 'pageFlip') ),
									 2 => array ( 'value' => 200, 'label' => __('200 per page', 'pageFlip') ),
									 3 => array ( 'value' => 0, 'label' => __('all', 'pageFlip') )
							  		);
	}

    function get_options()
	{
		if ( !defined( 'WP_PLUGIN_DIR' ) )
		{
			if ( !defined( 'WP_CONTENT_DIR ') )
		  	  define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
		  	define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' ); // full path, no trailing slash
		}

		if ( !defined( 'WP_PLUGIN_URL' ) )
		{
			if ( !defined( 'WP_CONTENT_URL ') )
			  define( 'WP_CONTENT_URL', get_option( 'siteurl ') . '/wp-content'); // full url - WP_CONTENT_DIR is defined further up
			define( 'WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins' ); // full url, no trailing slash
		}

		if ( !defined('PLUGINDIR') )
  			define( 'PLUGINDIR', 'wp-content/plugins' ); // Relative to ABSPATH.  For back compat.
	}


	function add_admin_menu()
	{
		if ( $this->add_page_to == 1 )
			add_menu_page( $this->page_title,
				$this->menu_title, $this->access_level,
				$this->plugin_dir, array ( $this , 'main_page'), $this->imgUrl.'pageFlip.gif' );

		elseif ( $this->add_page_to == 2 )
			add_options_page( $this->page_title,
				$this->menu_title, $this->access_level,
				$this->plugin_dir, array ( $this , 'main_page'), $this->imgUrl.'pageFlip.gif' );

		elseif ( $this->add_page_to == 3 )
			add_management_page( $this->page_title,
				$this->menu_title, $this->access_level,
				$this->plugin_dir, array ( $this , 'main_page'), $this->imgUrl.'pageFlip.gif' );

		elseif ( $this->add_page_to == 4 )
			add_theme_page( $this->page_title,
				$this->menu_title, $this->access_level,
				$this->plugin_dir, array ( $this , 'main_page'), $this->imgUrl.'pageFlip.gif' );


        add_submenu_page( $this->plugin_dir, __('Main', 'pageFlip'),
								__('Main', 'pageFlip'), $this->access_level,
								$this->plugin_dir );
		add_submenu_page( $this->plugin_dir, __('Manage books and pages', 'pageFlip'),
								__('Manage books and pages', 'pageFlip'), $this->access_level,
								$this->plugin_dir . '/books', array( $this, 'manage_books' ) );
		add_submenu_page( $this->plugin_dir, __('Images', 'pageFlip'),
								__('Images', 'pageFlip'), $this->access_level,
								$this->plugin_dir . '/images', array( $this, 'images' ) );
		/*add_submenu_page( $this->plugin_dir, __('Help', 'pageFlip'),
								__('Help', 'pageFlip'), $this->access_level,
								$this->plugin_dir . '/help', array( $this, 'help_page' ) );*/
	}

	function activate()
	{
	}

	function deactivate()
	{
	}

	//делаем отображение книги в постах
	function replaceBooks( $att, $content = null )
	{
		global $wpdb;

		//если не определен id - выводим пустоту
		if ( preg_match('/(\d+)/', $att['id'], $m) )
		{
			$att['id'] = $m[1];
		}
		else
			return '';

		/*if( $att['id'] == '' )
			return '';*/

		//получаем бекграунд из базы
        $sql = "select `bgImage` from `".$this->table_name."` where `id` = '".$att['id']."'";
        $bgImage = $wpdb->get_var( $sql );

        if ( empty( $bgImage ) )
        {
        	$bgImage = $this->bgFile;
        }
        else
        {
        	if ( $bgImage == "-1" )
        		$bgImage = '';
	        else
	        {
	        	$sql = "select `filename` from `" . $this->table_img_name . "` where `id` = '" . $bgImage . "' and `type` = 'bg'";
	         	$bgImage = $this->plugin_url . $this->imagesDir . '/' . $wpdb->get_var( $sql );
	        }
        }

		$book = new Book( $att['id'] ); //получаем настройки книги

		if( $book->state !== 1 ) return false; //проверка статуса книги

		if( $book->countPages == 0 ) return false; //если страниц нет - не выводим книгу

		//если не определены высота и ширина - берем настройки из книги
		if( empty( $att['width'] ) || empty( $att['height'] ) )
		{
			 if( empty( $att['width'] ) ) $att['width'] = $book->stageWidth;
			 if( empty( $att['height'] ) ) $att['height'] = $book->stageHeight;
		}

		if ( !empty($att['popup']) || (isset($book->popup) && $book->popup == 'true') )
		{
			// ссылка для всплывающего окна
			if ( !empty($att['preview']) )
				$book->preview = $att['preview'];

			return $this->html->popupLink( $book, $att );
		}
		else
		{
			// книга
			return $this->html->viewBook( $book, $att['width'], $att['height'], $bgImage );
		}
	}

	//главная страница
	function main_page()
	{
        echo '<div class="wrap">';
		echo $this->functions->printHeader( '<a href="http://pageflipgallery.com/">' . $this->page_title . '</a>' );

		$this->functions->splitImage( WP_CONTENT_DIR . '/photo.jpg' );
		//проверки всякие
		if( defined( 'PAGEFLIP_ERROR' ) ) echo PAGEFLIP_ERROR;
		echo $this->functions->check();


		echo $this->html->mainPage();
		echo '</div>';
	}

	function page_img($book, $page, $zoom = false, $echo = true)
	{
		global $pageFlip;

		$zoomURL = $page->zoomURL;
		$imageURL = $zoom ? $zoomURL : $page->image;

		if ( !$zoom )
		{
			if ( $book->autoReduce=='true' && $pageFlip->functions->getExt($imageURL) != 'swf' && $imageURL == $zoomURL )
			{
				list($width, $height) = $pageFlip->functions->getImageSize($imageURL);
				$scale1 = $width / $book->width;
				$scale2 = $height / $book->height;
				$scale = $scale1 > $scale2 ? $scale1 : $scale2;
				$f = $scale - intval($scale);
				if ($f > 0.15 || $scale >= 3)
				{
					$imageURL =
						$pageFlip->functions->getResized(
							$zoomURL,
							array(
								'max_width' => $book->width / 2,
								'max_height' => $book->height,
								'background' => $book->pageBack,
								'quality' => 90
							)
						);
				}
			}
			$url = $imageURL;

			$pageWidth = $book->width / 2;
			$pageHeight = $book->height;

			$size = "width='{$width}' height='{$height}'";
		}
		else
		{
			$url = $zoomURL;

			$pageWidth = $book->zoomImageWidth;
			$pageHeight = $book->zoomImageHeight;
		}

		if ($echo)
		{
			list($width, $height) = $pageFlip->functions->getImageSize($url);
			$s = $pageFlip->functions->imgSize($width, $height, $pageWidth, $pageHeight);

			$size = "width='{$s['width']}' height='{$s['height']}'";

			echo "<img src='{$url}' {$size} alt='' />";
		}

		return $url;
	}

	function edit_page()
	{
		$tPrev = '&laquo; '.__('Previous', 'pageFlip');
		$tNext = __('Next', 'pageFlip').' &raquo;';
		$tZoomIn = '+ '.__('Zoom In', 'pageFlip');
		$tZoomOut = '&minus; '.__('Zoom Out', 'pageFlip');

		global $pageFlip;

		$bookID = htmlspecialchars($_POST['id']);
		$pageID = htmlspecialchars($_POST['pageId']);
		$zoom = htmlspecialchars($_POST['zoom']) == $tZoomIn ? true : false;

		$book = new Book($bookID);
		$book->load();

		$pageBack = sprintf('#%06x', hexdec($book->pageBack));

		$pageWidth = $zoom ? $book->zoomImageWidth : $book->width / 2;
		$pageHeight = $zoom ? $book->zoomImageHeight : $book->height;

		$navPage = htmlentities($_POST['page'], ENT_COMPAT, 'utf-8');

		if ($navPage == 'Home')
			$pageID = 0;
		else if ($navPage == 'End')
			$pageID = count($book->pages)-1;
		else if ( $navPage == $tPrev && $pageID > 0 )
			$pageID--;
		else if ( $navPage == $tNext && $pageID < count($book->pages)-1 )
			$pageID++;

		$page = &$book->pages[$pageID];

		$prevAtt = $pageID == 0 ? 'disabled="disabled"' : '';
		$nextAtt = $pageID >= count($book->pages)-1 ? 'disabled="disabled"' : '';
?>
		<div class="wrap">
			<h2><?php _e('Page Properties', 'pageFlip'); ?></h2>

			<link rel="stylesheet" type="text/css" href="<?php echo WP_PLUGIN_URL.'/'.$this->plugin_dir; ?>/css/pageflip-admin.css" />

			<form id="pageForm" action="#" method="post" style="margin:1em 0 3em 0;">
				<input type="hidden" name="action" value="<?php _e('Page Properties', 'pageFlip'); ?>" />
				<input type="hidden" name="id" value="<?php echo $bookID; ?>" />
				<input type="hidden" name="pageId" value="<?php echo $pageID; ?>" />
				<input type="hidden" name="zoom" value="<?php echo $zoom ? $tZoomIn : $tZoomOut; ?>" />
				<input type="hidden" name="pageWidth" value="<?php echo $pageWidth; ?>" />
				<input type="hidden" name="pageHeight" value="<?php echo $pageHeight; ?>" />

				<a name="1"></a>
				<table style="clear:both; margin:1em 0 1.5em 0; /*width:<?php echo $pageWidth /* 2*/; ?>px;*/">
				<tr>
					<td align="left" width="10%">
						<label for="pageName"><?php _e('Name', 'pageFlip'); ?></label>
					</td>
					<td align="left" width="65%">
						<input type="text" id="pageName" name="pageName" value="<?php echo $book->pages[$pageID]->name; ?>" size="40" style="width:90%;" />
					</td>
					<td align="center" width="25%" style="white-space:nowrap;">
						<input type="submit" class="button-primary" id="saveButton" name="save" value="<?php _e('Update', 'pageFlip'); ?>" />
						<input type="submit" class="button" id="cancelButton" name="cancel" value="<?php _e('Cancel', 'pageFlip'); ?>" />
					</td>
				</tr>
				</table>

			<?php if ($this->functions->getExt($page->image) != 'swf') : ?>
				<div style="height:2.5em; margin:0.5em 0;"><a class="button" href="#" style="font-weight:bold;" onclick="this.parentNode.style.display='none'; jQuery('#writeText').fadeIn(750); return false;">Write Text</a></div>
				<fieldset id="writeText" class="text widefat" style="display:none; float:left; width:auto; margin:0 0 1.5em 0; padding:0.5em 1em 1em; background:none;">
					<legend>Write Text</legend>

					<?php echo $this->html->fontPanel('fontPanel', array('color'=>'CC0000')); ?>
					<div>
						<textarea id="textToWrite" name="textToWrite" rows="3" style="width:100%;"></textarea>
					</div>
					<input type="hidden" id="textLeft" name="textLeft" value="" />
					<input type="hidden" id="textTop" name="textTop" value="" />
				</fieldset>
				<div class="clear"></div>

				<?php /* ?><div style="width:<?php echo $pageWidth * 2; ?>px; height:2em; text-align:center;">
					<input type="submit" class="button" name="page" value="Home" <?php echo $prevAtt; ?> title="<?php _e('First page', 'pageFlip'); ?>" style="padding:0 3px; font-size:9px !important; font-family:Verdana,sans-serif;" />
					<input type="submit" class="button" name="page" value="<?php echo $tPrev; ?>" <?php echo $prevAtt; ?> onclick="document.getElementById('pageForm').action='#1';" />
					<big style="font-size:20px; padding:0 0.25em;"><?php echo $pageID; ?></big>
					<input type="submit" class="button" name="page" value="<?php echo $tNext; ?>" <?php echo $nextAtt; ?> onclick="document.getElementById('pageForm').action='#1';" />
					<input type="submit" class="button" name="page" value="End" <?php echo $nextAtt; ?> title="<?php _e('Last page', 'pageFlip'); ?>" onclick="document.getElementById('pageForm').action='#1';" style="padding:0 3px; font-size:9px !important; font-family:Verdana,sans-serif;" />
					<span>&nbsp;&nbsp;</span>
				</div><?php */ ?>

				<?php ///* ?><div style="height:2em; /*margin:-2em 0 0 0;*/ padding-top:0.2em;">
					<input type="submit" class="button" name="zoom" value="<?php echo !$zoom ? $tZoomIn : $tZoomOut; ?>" />
				</div><?php //*/ ?>

				<div id="pageView" style="width:<?php echo $pageWidth /* 2*/; ?>px; height:<?php echo $pageHeight; ?>px; padding:4px; background:<?php echo $pageBack; ?>; border:2px outset <?php echo $pageBack; ?>;">
<?php
					/*if ( !($pageID % 2) && $pageID > 0)
					{
						$id = $pageID - 1;
						//$img = $this->page_img( $book, $book->pages[$id], $zoom, false );
						echo "<a class='inactive page' href='#' onclick='editPage({$id}); return false;' style='display:block; position:relative; float:left; width:{$pageWidth}px; height:{$pageHeight}px;'>";
						$this->page_img( $book, $book->pages[$id], $zoom );
						echo "</a>";
					}*/

					//$img = $this->page_img( $book, $book->pages[$pageID], $zoom, false );
					echo "<div class='active page' id='active_page' style='display:block; position:relative; float:left; width:{$pageWidth}px; height:{$pageHeight}px;'>";
					$this->page_img( $book, $book->pages[$pageID], $zoom );
					echo "<div id='text_001' style='float:left; cursor:move; font-family:Arial,Helvetica,sans-serif; position:absolute; left:0; top:0;'></div>";
					echo "</div>";

					/*if ( $pageID % 2 && $pageID < count($book->pages)-1 )
					{
						$id = $pageID + 1;
						//$img = $this->page_img( $book, $book->pages[$id], $zoom, false );
						echo "<a class='inactive page' href='#' onclick='editPage({$id}); return false;' style='display:block; float:left; width:{$pageWidth}px; height:{$pageHeight}px;'>";
						$this->page_img( $book, $book->pages[$id], $zoom );
						echo "</a>";
					}*/
?>
				</div>
				<div class="clear"></div>

				<div style="width:<?php echo $pageWidth * 2 - 18; ?>px; padding:0 15px; text-align:<?php echo $pageID % 2 ? 'left' : 'right'; ?>;">
					<?php if ($zoom) : ?><big><?php endif; ?>
						<a href="<?php echo $url = $zoom ? $image['zoomURL'] : $image['imageURL']; ?>" target="_blank" style="text-decoration:none;">
							<?php echo $pageFlip->functions->getExt($url); ?>
						</a>
					<?php if ($zoom) : ?></big><?php endif; ?>
				</div>
			</form>

			<script type="text/javascript">
			//<![CDATA[
				function editPage(id)
				{
					var form = document.getElementById('pageForm');
					form.elements.pageId.value = id;
					form.submit();
				}

				function fontToFamily(font)
				{
					f = font.toLowerCase();
					switch (f)
					{
						case 'arial':
							return "Arial, Helvetica, sans-serif";
						case 'times':
							return "'Times New Roman', Times, serif";
						default:
							return font;
					}
				}


				var
					page = document.getElementById('active_page'),
					text = document.getElementById('text_001'),
					textToWrite = document.getElementById('textToWrite'),
					fontFamily = document.getElementById('fontPanel_fontFamily'),
					fontSize = document.getElementById('fontPanel_fontSize'),
					color = document.getElementById('fontPanel_color');

				textToWrite.onkeyup = function ()
				{
					text.innerHTML = textToWrite.value.replace(/\n/g, '<br />');
				};
				textToWrite.onkeyup();

				fontFamily.onchange = function ()
				{
					text.style.fontFamily = fontToFamily(fontFamily.value);
				};
				fontFamily.onchange();

				fontSize.onchange = function ()
				{
					text.style.fontSize = fontSize.value + 'px';
				};
				fontSize.onchange();

				color.onchange = function ()
				{
					text.style.color = '#' + color.value;
				};
				color.onchange();

				jQuery(document).ready(function($)
				{
					$('#text_001').draggable(
					{
						containment: 'parent',
						stop: function (event, ui)
						{
							document.getElementById('textLeft').value = Math.round(ui.position.left);
							document.getElementById('textTop').value = Math.round(ui.position.top);
						}
					});
				});

			//]]>
			</script>
		<?php endif; ?>

		</div>
<?php
	}

	//страница админки manage books
    function manage_books()
    {
    	global $wpdb;

    	if ( !empty($_POST['action']) && $_POST['action'] == __('Page Properties', 'pageFlip') )
    	{
    		if ( !empty($_POST['save']) )
    		{
    			$book = new Book($_POST['id']);
    			$book->load();

    			$book->pages[$_POST['pageId']]->name = htmlspecialchars($_POST['pageName']);

    			if ( trim($_POST['textToWrite']) )
    			{
	    			$book->pages[$_POST['pageId']]->writeText(
	    				$_POST['textToWrite'],
	    				array(
	    					'pageWidth' => $_POST['pageWidth'],
	    					'pageHeight' => $_POST['pageHeight'],
	    					'left' => $_POST['textLeft'],
	    					'top' => $_POST['textTop'],
	    					'fontFamily' => $_POST['fontFamily'],
	    					'fontSize' => $_POST['fontSize'],
	    					'color' => $_POST['color']
	    				),
	    				strstr($_POST['zoom'], __('Zoom In', 'pageFlip')) ? true : false
	    			);
    			}

    			$book->save();
    		}
    		else if ( empty($_POST['cancel']) )
    		{
    			return $this->edit_page();
    		}
    	}

    	echo '<div class="wrap">';

    	if( defined( 'PAGEFLIP_ERROR' ) )
		{
			echo PAGEFLIP_ERROR . '</div>';
			return false;
		}

		echo '<noscript>'.$this->functions->errorMessage( 'JavaScript is disabled. Please, enable JavaScript for correctly work.' ).'</noscript>';

		//заплатка через задницу
		if( !empty( $_POST['thisdo'] ) ) $_POST['do'] = $_POST['thisdo'];

    	if( isset( $_POST['actionButton'] ) )
			switch( $_POST['action'] )
	        {
	         	case 'addbook' : $this->add_book(); break;
	         	case 'editbook' : $this->edit_book(); break;
	         	case 'addpage' : $this->add_page( $_POST['imageId'], $_POST['type'] ); break;
	         	case 'Assign Selected Images to Page' :
	         	{
	         		if( count( $_POST['images'] ) > 0 )
					 foreach( $_POST['images'] as $imageId )
	         			if( !$this->add_page( $imageId, $_POST['type'] ) ) break;
	         		unset( $_POST['do'] );
	         	} break;
	         	case 'Assign Images from Gallery' :
	         	{
	         		$this->addPageFromGallery( $_POST['galleryId'], $_POST['type'] );
	         		unset( $_POST['do'] );
	         	} break;
	         	case 'uploadimage' :
	         		if( ( $_POST['do'] == 'New Page' ) )
	         		{
	         			$imagesId = $this->upload_image( 'New page' );
	         			if( count( $imagesId ) > 1 )
	         			{
	         				foreach( $imagesId as $imageId )
	         					if( !$this->add_page( $imageId, $_POST['type']  ) ) break;
	         				unset( $_POST['do'] );
	         			}
	         		}
	         	 	break;
         	 	case __('Delete Book', 'pageFlip') :
         	 		$this->delete_book( $_POST['id'] );
         	 		break;
	        }

        if( isset( $_POST['do'] ) )
         switch( $_POST['do'] )
         {
         	case __('Book Properties', 'pageFlip') :
         		$this->book_form( $_POST['id'] );
         		break;

         	case __('Add Page', 'pageFlip') :
         	case 'New Page' :
         		echo $this->functions->printHeader( 'New Page to book #' . $_POST['id'] );
         		if( isset( $_POST['imageId'] ) && $_POST['action'] == 'Assign Image to Page' && isset( $_POST['actionButton'] ) )
         			$this->add_page_form( $_POST['id'], $_POST['imageId'], $_POST['type'] );
         		elseif( ( $_POST['action'] == 'uploadimage' ) && ( count( $imagesId ) == 1 ) )
         			$this->add_page_form( $_POST['id'], $imagesId[0] );
         		else
				{
					echo '<div id="addPageMenu">' . $this->html->addPageMenu() . '</div>';
   	    $tButtonName = __('Create New Gallery', 'pageFlip');
   	    $tUploadButton = __('Upload New Images', 'pageFlip');
    	$tGalleryName = __('Gallery Name', 'pageFlip');
    	$tAddGallery = __('Add Gallery', 'pageFlip');
		$text = <<<HTML
   	    	<form method="post" name="addGalleryForm" id="addGalleryForm" action="" style="margin:1em 0 -1em 0;">
	   	    	<input type="hidden" name="action" value="add_gallery">
				<input class="button" id="createGalleryButton" name="button" value="{$tButtonName}" type="button" onclick="viewAddGalleryForm(); return false;" style="margin:0.5em 0 1.5em 0;" />
				<div id="addNewGallery" style="margin:0 0 1.5em 0;">
					<label for="galleryName">{$tGalleryName}</label>
					<input name="galleryName" id="galleryName" size="40" type="text" />
					<input class="button" name="actionButton" value="{$tAddGallery}" type="submit" onclick="addGallery( this.form ); return false;" />
				</div>
				<script type="text/javascript">//<![CDATA[
					document.getElementById('addNewGallery').style.display = 'none';
				//]]>
				</script>
			</form>
HTML;
					echo $text;
					$this->galleriesList( $_POST['id'] );
					//$this->images_list( $_POST['id'] );
				}
         		break;

         	case __('Upload New Images', 'pageFlip') :
         		echo $this->html->uploadImageForm( $_POST['id'] );
         		break;

         	case __('Add New Book', 'pageFlip') :
         		$this->book_form();
         		break;
         }
        else
        {
        	echo $this->functions->printHeader( __( 'Manage books and pages', 'pageFlip' ) );
        	echo $this->html->operationBookPreview();

			$this->books_list();

			echo $this->html->operationBookPreview( 'bottom' );
        }
        echo "</div>";
    }

    //вкладка images
    function images()
    {
        echo '<div class="wrap">';

		if( defined( 'PAGEFLIP_ERROR' ) )
		{
			echo PAGEFLIP_ERROR . '</div>';
			return false;
		}

		echo '<noscript>'.$this->functions->errorMessage( 'JavaScript is disabled. Please, enable JavaScript for correctly work.' ).'</noscript>';

		if( isset( $_POST['actionButton'] ) )
			switch( $_POST['action'] )
	        {
	        	case 'add_gallery' :
	        		$this->addGallery(false);
	        		break;
	        	case 'addbook' : $this->add_book(); break;
	        	case 'uploadimage' :
				 	{
				 		$this->upload_image();
				 		unset( $_POST['do'] );
				 	} break;
	        }

		$do = empty($_POST['do']) ? '' : $_POST['do'];
		switch( $do )
        {
         	case 'Upload New Images' : echo $this->html->uploadImageForm(); break;
         	case 'Upload Image' : echo $this->html->uploadImageForm( $_POST['bookId'] ); break;
         	case __('Create Book', 'pageFlip') :
         		global $wpdb;
         		$gallery = $wpdb->get_row("SELECT * FROM {$this->table_gal_name} WHERE id='{$_POST['galleryId']}' ");
         		$this->book_form('', $gallery->id);
         		break;
         	default :
         	    {
	         		//echo $this->functions->printHeader( __('Images', 'pageFlip') );
		     		//$this->images_list();
		     		$this->galleriesList();
	     		}
        }
        echo '</div>';

    }

	//список книг
	function books_list()
	{
		global $wpdb;

        $list = $this->html->ajaxPreviewBook();

        $list .= '<div id="bookList">';

        $list .= $this->html->headerPreviewBook();

	    $sql = "select `id`, `name`, `date` from `".$this->table_name."` order by `id`";
	    $books = $wpdb->get_results( $sql, ARRAY_A );

	    if( count($books) == "0" ) $list .= $this->html->noBooksPreviewBook();
        else foreach( $books as $curBook )
        {
        	 $creationDate = date( "m/d/Y", $curBook['date'] );

        	 $book = new Book( $curBook['id'] );

        	 $bookPreview = $this->bookPreview( $book );

             $list .= $this->html->previewBook ( $book, $curBook['name'], $creationDate, $bookPreview['first'], $bookPreview['second'] );
		}

        $list .= $this->html->footerPreviewBook();

        if( isset( $_POST['id'] ) )
			$list .= '<script type="text/javascript">
						//<![CDATA[
						pageList(' . $_POST['id'] . ');
						//]]>
				 	  </script>';

		$list .= '</div>';

        echo $list;
	}

	function bookPreview( $book = '' )
	{
		if ( empty( $book ))
		{
			$book = new Book( $_POST['bookId'] );
			$ajax = true;
		}
		else
			$ajax = false;

		//определяем что будет на превьюшке
         //в первом окне
         if( ( ( $book->alwaysOpened == 'false' ) && ( (int)$book->firstPage % 2 == 1 ) ) ||
             ( ( $book->alwaysOpened == 'true' ) && ( (int)$book->firstPage % 2 == 0 ) ) )
	     {

	     		$firstPage = (int)$book->firstPage;
	     		$secondPage = (int)$book->firstPage + 1;
	     }
         else
         {
          	 $firstPage = (int)$book->firstPage - 1;
          	 $secondPage = (int)$book->firstPage;
         }

         $result['first'] = !empty($book->pages[$firstPage]) ? $this->functions->printImg( $book->pages[$firstPage]->image ) : '';
         $result['second'] = !empty($book->pages[$secondPage]) ? $this->functions->printImg( $book->pages[$secondPage]->image ) : '';

		 if ( $ajax )
		 {
			echo $result['first'] . '<split>' . $result['second'];
			exit;
		 }
		 else
		 	return $result;
	}


	function replacePages()
	{
		$book = new Book( $_POST['bookId'] );

		switch( $_POST['op'] )
		{
			case 'up' : {
				if( (int)$_POST['pageId'] > 0 )
				{
					$book->pages[((int)$_POST['pageId'] - 1)]->number++;
					$book->pages[(int)$_POST['pageId']]->number--;
				}
			} break;
			case 'down' : {
				if( (int)$_POST['pageId'] < $book->countPages )
				{
					$book->pages[(int)$_POST['pageId']]->number++;
					$book->pages[((int)$_POST['pageId'] + 1)]->number--;
				}
			} break;
			default : {
				$pages = split( ';', $_POST['pages'] );

				for( $i=0; $i < $book->countPages; $i++ )
					$book->pages[(int)$pages[$i]]->number = $i;
			}
		}

		$book->refreshPages(); //обновляем массив страниц

		$book->save();

		exit;
	}

	function pagesList()
	{
        global $wpdb;

        $list  = $this->html->headerPreviewPage( $_POST['bookId'] );

        $book = new Book( $_POST['bookId'] );

        if( (int)$book->countPages === 0 ) $list .= $this->html->noPagesPreviewPage();
     	else
	        foreach( $book->pages as $id=>$page )
	        {
	        	if( trim( $book->alwaysOpened ) == 'false' )
	        	{
	        		if( ( $id % 2 == 0 ) ) $side = 'right';
	        		else $side = 'left';
	        	}
	        	else
	        	{
	        		if( ( $id % 2 == 0 ) ) $side = 'left';
	        		else $side = 'right';
	        	}

	        	$list .= $this->html->previewPage( $_POST['bookId'], $page, $side,
	        										 $this->functions->printImg( $page->image, $page->number, $this->thumbWidth, $this->thumbHeight, true ),
													   $book->countPages );
	        }

		$list .= $this->html->footerPreviewPage();

        echo $list;
        exit;
	}

	//добавление книги
	function add_book()
	{
        global $wpdb;

        foreach( $_POST as $key=>$value )
        {
        	$_POST["$key"] = trim( $value ); //пробелы обрезаем
        	$_POST["$key"] = stripslashes( $value );
			$_POST["$key"] = htmlspecialchars( $value );
			$_POST["$key"] = $wpdb->escape( $value ); //подготавливаем к записи в базу
        	//if( ( $value == "" ) && ( $key !== "flipSound" ) && ( $key !== "sound" ) && ( $key !== "image[]" ) ) {echo "All fields is nessesury".$key; return 0;}
        }

        if( empty( $_POST['bookName'] ) ) $_POST['bookName'] = 'unnamed';

        //загружаем бекграунд
        if( !empty( $_FILES['image']['name'][0] ) ) $imageId = $this->upload_image( 'bgImage' );
        else $imageId[0] = $_POST['bgImage'];

        //записываем в базу
        $sql = "insert into `".$this->table_name."` (`name`, `date`, `bgImage`) values ('".$_POST['bookName']."', '".date("U")."', '".$imageId[0]."')";
        $wpdb->query( $sql );

        $id = $wpdb->get_var( "select LAST_INSERT_ID();", 0, 0 );//получаем id книги

        //звук
        $_POST['flipSound'] = $this->add_sound();

        //создаем книгу
        $newBook = new Book();

		$newBook->id = $id;
		foreach( $newBook->properties as $property )
			if( !empty( $_POST[$property] ) || $property == 'flipSound' )
				$newBook->$property = $_POST[$property];

		//сохраняем книгу
        if( !$newBook->save() )
        {
        	//если ошибка - удаляем из базы
        	$sql = "delete from `" . $this->table_name . "` where `id` = '" . $id . "'";
        	$wpdb->query( $sql );

        	echo __('Adding book error! Please setup permission to the books/ , images/ , sounds/ folders and include files to &quot;777&quot;', 'pageFlip');
		    return 0;
        }

		if ($galleryId = $_POST['galleryId'])
		{
			$images = $wpdb->get_results("SELECT * FROM `{$this->table_img_name}` WHERE `gallery`='{$galleryId}'");
			foreach ($images as $image)
			{
				$_POST['id'] = $id;
				$this->add_page($image->id, $image->type);
			}
			echo '<script type="text/javascript">location.href="?page=page-flip-image-gallery/books";</script>';
		}
	}

	//добавление страницы
	function add_page( $imageId, $type )
	{
        global $wpdb;

        $book = new Book( $_POST['id'] );

        $imageName = isset($_POST['name']) ? htmlspecialchars( stripslashes( $_POST['name'] ) ) : NULL;
        $zoomURL = '';

        switch( $type )
        {
        	case 'WPMedia' : {
        		$uploads = wp_upload_dir();
				$location = get_post_meta( $imageId, '_wp_attached_file', true );

				//$image = $uploads['baseurl'].'/'.$location;
				$image_path = $uploads['basedir'].'/'.$location;
				$new_url = $this->imagesUrl.basename($location);
				$new_path = $this->plugin_path.$this->imagesDir.'/'.basename($location);

				$_POST['galleryId'] = 0;
				$this->copyImage($new_path, $image_path, filesize($image_path), 'img', basename($location), 'copy');
        		$sql = "select `filename` from `" . $this->table_img_name . "` where `id` = '" . $wpdb->insert_id . "'";
	    		$img = $wpdb->get_row( $sql, ARRAY_A, 0 );
				$image = $this->functions->getImageUrl( $img['filename'] );
    			$filename = $img['filename'];
        	} break;
        	case 'NGGallery' : {
				$sql = "SELECT `filename`, `galleryid`, `alttext` FROM `".$wpdb->prefix."ngg_pictures` where `pid` = '".$imageId."'";
				$img = $wpdb->get_row($sql, ARRAY_A);
    			$filename = $img['filename'];
				$sql = "select `path` from `".$wpdb->prefix."ngg_gallery` where `gid` = '".$img['galleryid']."'";
				$path = $wpdb->get_var( $sql );

    			//$image = get_option( 'siteurl' ) . '/' . $path . '/' . $img['filename'];
    			$image_path = ABSPATH.$path.'/'.$img['filename'];
    			$new_url = $this->imagesUrl.$img['filename'];
    			$new_path = $this->plugin_path.$this->imagesDir.'/'.$img['filename'];

				$_POST['galleryId'] = 0;
				$this->copyImage($new_path, $image_path, filesize($image_path), 'img', $img['filename'], 'copy');
        		$sql = "select `filename` from `" . $this->table_img_name . "` where `id` = '" . $wpdb->insert_id . "'";
	    		$img = $wpdb->get_row( $sql, ARRAY_A, 0 );
				$image = $this->functions->getImageUrl( $img['filename'] );
        	} break;
        	default : {
        		$sql = "select `filename`, `name` from `" . $this->table_img_name . "` where `id` = '" . $imageId . "'";
	    		$img = $wpdb->get_row( $sql, ARRAY_A, 0 );
	    		$image = $this->functions->getImageUrl( $img['filename'] );
	    		if ( file_exists($this->imagesPath.'z_'.$img['filename']) )
	    			$zoomURL = $this->functions->getImageUrl( 'z_'.$img['filename'] );
    			$filename = $img['name'];
	    	}
        }

    	if (!$imageName)
    	{
    		preg_match('|(.*)\..*?|', $filename, $m);
    		$imageName = $m[1];
    	}


    	$book->pages[$book->countPages] = new Page( $image, $book->countPages, $imageName, $zoomURL );

        if( !$book->save() )
        {
        	echo __('Save file error! Please setup permission to the books/ , images/ , sounds/ folders and include files to &quot;777&quot;', 'pageFlip');
        	return false;
        }

        return true;
	}

	//добавление страниц из галлереи
	//добавление страницы
	function addPageFromGallery( $galleryId, $type )
	{
        global $wpdb;

        switch( $type )
		{
			case 'NGGallery' :
				$sql = "SELECT `pid` AS id FROM `".$wpdb->prefix."ngg_pictures` WHERE `galleryId` = '".$galleryId."'";
			 break;
			default :
				$sql = "select `id` from `".$this->table_img_name."` where `type` = 'img' and `gallery` = '".$galleryId."'";
		}

		$images = $wpdb->get_results( $sql, ARRAY_A );
		if( count( $images ) > 0 )
			foreach( $images as $img )
				$this->add_page( $img['id'], $type );
	}

	//добавление галереи
	function addGallery($exit = true)
	{
		global $wpdb;

		$name = $wpdb->escape( $_POST['galleryName'] );

		$sql = "insert into `".$this->table_gal_name."` (`name`, `date`, `preview`) values ('".$name."', '".date("U")."', 0)";
		$wpdb->query( $sql );

		if ($exit)
			exit;
	}

	//добавление страницы
	function upload_image( $action='' )
	{
        global $wpdb;

        $imagesId = array();

	    //если загружаем по урл
	    if( !empty( $_POST['url'] ) && $_POST['uploadFormType']=='uploadFromUrlForm' )
        {
	       	//проверяем url
	       	if( !$this->functions->isUrl( $_POST['url'] ) )
	       	{
	       		$txt = '<strong>' . $_POST['url'] . '</strong> - <strong>' . __('Error', 'pageFlip') . '</strong>: ' . __('Incorrect url', 'pageFlip') . '<br />';
				echo $this->functions->errorMessage( $txt );
				return false;
	       	}

	       	if( !$this->functions->checkImage( $_POST['url'] ) ) return false;

	       	$type = 'image';

			if( empty( $_POST['name'] ) ) $_POST['name'] = basename( $_POST['url'] );

			$_POST['name'] = stripslashes( $_POST['name'] );
			$_POST['name'] = htmlspecialchars( $_POST['name'] );
			$_POST['name'] = $wpdb->escape( $_POST['name'] ); //подготавливаем к записи в базу

			//записываем в базу
		    $sql = "insert into `".$this->table_img_name."` (`name`, `filename`, `date`, `type`, `gallery`) values ('".$_POST['name']."', '".$_POST['url']."', '".date("U")."', '".$type."', '".$_POST['galleryId']."')";
		    $wpdb->query($sql);

	        if( ( $action == 'New page' ) || ( $action == 'bgImage' ) )
	        {
	          	$sql = "select LAST_INSERT_ID();";
	          	$imagesId[] = $wpdb->get_var( $sql, 0, 0 );
	        }

	        return true;
        }

        if ( $_POST['uploadFormType']=='uploadSwfForm' )
        {
        	$_POST['folder'] = 'wp-content/pageflip/upload/';
        }

		if( !empty( $_POST['folder'] ) && ($_POST['uploadFormType']=='uploadFromFolder' || $_POST['uploadFormType']=='uploadSwfForm') )
		{
			$curDir = ABSPATH . $_POST['folder'];
			if( is_dir($curDir) )
			{
				$dir = opendir($curDir); //открываем директорию

				for ($n_files = 0; $file = readdir($dir); )
					if ( is_file($curDir . $file) ) $n_files++;

				$dir = opendir($curDir);

				ob_start();

				$i = 0;
				while ( $file = readdir($dir) )
				{
					if ( is_file( $curDir . $file ) )
					{
						$i++;
						$progress = "($i / $n_files)";
						echo "<p style='margin:0.5em 0;'>Loading <strong>". $curDir . $file ."</strong> {$progress} ";

						$size = filesize( $curDir . $file );
						$id = $this->copyImage( $file, $curDir . $file, $size, $action, '', 'rename' );
						if( $id ) $imagesId[] = $id;

						echo "</p>\n";
						ob_flush(); flush();
					}
				}
				closedir( $dir ); //закрываем директорию
				echo '<script type="text/javascript">location.href=location.href;</script>';
			}
		}

		//если загружается зип архив - погнали обрабатывать
		if( !empty( $_FILES['zip']['name'] ) && $_POST['uploadFormType']=='uploadZipForm' )
        {
        	@ini_set('memory_limit', '256M');

			// проверяем, является ли zip-архивом
			if( $_FILES['zip']['type'] != 'application/zip'
				&& $_FILES['zip']['type'] != 'application/x-zip-compressed' )
				 {
				 	$txt = '<strong>' . $_FILES['zip']['name'] . '</strong> - <strong> ' . __('Error', 'pageFlip') . '</strong>: ' . __('This is not a zip file', 'pageFlip') . '<br />';
				    echo $this->functions->errorMessage( $txt );
					return false;
				 }

			if( ! class_exists( 'PclZip' ) )
			   require_once( ABSPATH . 'wp-admin/includes/class-pclzip.php' );

			// копируем архив в папку images
			$dir =  $this->plugin_path . $this->imagesDir . '/';
			$archiveName = $dir . basename( $_FILES['zip']['tmp_name'] );
			$folderName = $archiveName . '_folder/';
			copy( $_FILES['zip']['tmp_name'], $archiveName );

			//создаем объект
			$zip = new PclZip( $archiveName );

			// извлекаем
			$extractFiles = $zip->extract( PCLZIP_OPT_PATH, $folderName );

			// Если возвращает 0, выводим ошибку и выходим
			if( $extractFiles == 0 )
			{
			 	$txt = '<strong>' . $_FILES['zip']['name'] . '</strong> - <strong>' . __('Extracting Error', 'pageFlip') . '</strong><br />';
			    echo $this->functions->errorMessage( $txt );
				return false;
			}

			// копируем себе загруженные файлы
			foreach ( $extractFiles as $image )
			{
				$id = $this->copyImage( $image['stored_filename'], $image['filename'], $image['size'], $action, '', 'rename' );
	            if( $id ) $imagesId[] = $id;
			}

			// и удаляем уже распакованый архив
			@unlink( $archiveName );
			//вместе с директорией
			$this->functions->removeDir( $folderName );
        }

        //если тупо фотки грузим
		if( !empty( $_FILES['image']['name'] ) && $_POST['uploadFormType']=='uploadImgForm' )
		{
			foreach( $_FILES['image']['name'] as $id=>$imageName )
	        {
	            if ( !empty($imageName) )
	            {
		        	$zoomImageName = empty($_FILES['zoomImage']['tmp_name'][$id]) ? '' : $_FILES['zoomImage']['tmp_name'][$id];
		        	$id = $this->copyImage( $imageName, $_FILES['image']['tmp_name'][$id], $_FILES['image']['size'][$id], $action, $_POST['name'][$id], 'move_uploaded_file', $zoomImageName );
		            if( $id ) $imagesId[] = $id;
	            }
	        }
		}

        unset( $_POST['name'] ); //чтобы с именем глюков не было

	    return $imagesId;
	}

	//механизм копирования изображения
	function copyImage( $imageName, $tmpName, $size, $action = 'img', $name = '', $functionName = 'move_uploaded_file', $zoomImageName = '' )
	{
	   global $wpdb;

	   @ini_set( 'memory_limit', '256M' );

	   //if(($size > $this->maxPageSize) || ($size == 0)) {echo "This file is too big"; return 0;} //проверяем размер файла
	   if( $size == 0 )
	   {
	   	$txt = '<strong>' . $imageName . '</strong> - <strong>' . __('Error', 'pageFlip') . '</strong>: '. __('This file is too big', 'pageFlip') . '<br />';
		echo $this->functions->errorMessage( $txt );
		return false;
	   } //проверяем размер файла

	   if( !$this->functions->checkImage( $imageName ) ) return false;

	   //определяемся бекграунд это или нет
	   switch( $action )
	   {
	   		case 'bgImage' :
	   			$type = 'bg';
	   			break;
	   		default :
	   			$type = 'img';
	   }

	   //$fileExt = split( "\.", $imageName );
	   preg_match('/.*\.(.*)$/', $imageName, $fileExt);

	   //копируем в папку
       $dir =  $this->plugin_path . $this->imagesDir . '/';
       //$this->functions->createDir( $dir ); //проверяем на всякий случай есть ли такая папка

	   //заменяем имя нах
       //$filename = $this->functions->fileName( $type, $imageName );
       //$new_filename = $dir . $filename;

	   //если файл существует-изменяем имя
		do
		{
			$filename = $this->functions->fileName( $type, $imageName );
			$new_filename = $dir . $filename;
		}
		while( file_exists( $new_filename ) );

		//получаем имя файла-превьюшки и размеры
	    $thumbName = $dir . 't_' . basename( $new_filename );
	    $imgSize = $this->functions->getImageSize( $tmpName );
	    $newSize = $this->functions->imgSize( $imgSize[0], $imgSize[1], $this->thumbWidth, $this->thumbHeight );

	    $zoomName = $dir. 'z_'.basename($new_filename);

		switch( strtolower( $fileExt['1'] ) )
        {
        	case 'swf' :
        		if( !$functionName( $tmpName, $new_filename ) )
        		{
	            	unlink( $new_filename );
	            	$txt = '<strong>' . $imageName . '</strong> - <strong>' . __('Error', 'pageFlip') . ' [001]</strong>: ' . __('Write file error! Please setup permission to the books/ , images/ , sounds/ folders and include files to &quot;777&quot;', 'pageFlip') . '<br/>';
	            	echo $this->functions->errorMessage( $txt );
					return false;
	            } break;
        	default :
        		if( !$this->functions->img_resize( $tmpName, $thumbName, $newSize['width'], $newSize['height'] )
	                || !$functionName( $tmpName, $new_filename ) )
	            {
	            	unlink( $new_filename ); unlink( $thumbName );
	            	$txt = '<strong>' . $imageName . '</strong> - <strong>' . __('Error', 'pageFlip') . ' [002]</strong>: ' . __('Write file error! Please setup permission to the books/ , images/ , sounds/ folders and include files to &quot;777&quot;', 'pageFlip') . '<br/>';
	            	echo $this->functions->errorMessage( $txt );
					return false;
	            }
	            if ($zoomImageName)
	            {
	            	$functionName($zoomImageName, $zoomName);
	            }
        }

        //пишем инфо в базу
		//проверяем, если имя пустое - то имя файла пихаем туда
		if( empty( $name ) ) $name = $imageName;
	    else $name = $wpdb->escape( $name ); //подготавливаем к записи в базу

	    //записываем в базу
	    if ( empty($_POST['galleryId']) )
	    	$_POST['galleryId'] = '0';

	    $sql = "insert into `".$this->table_img_name."` (`name`, `filename`, `date`, `type`, `gallery`) values ('".$name."', '".basename($new_filename)."', '".date("U")."', '".$type."', '".$_POST['galleryId']."')";
	    $wpdb->query( $sql );

        if( ($action == 'New page') || ($action == 'bgImage') )
        {
          	$sql = "select LAST_INSERT_ID();";
          	return $wpdb->get_var( $sql, 0, 0 );
        }

		return false;
	}

	//разные формы загрузки
	function uploadForm()
	{
		echo $this->html->uploadImageMenu();
		echo '<split>';

		switch( $_POST['type'] )
		{
			case 'swfUpload' : echo $this->html->uploadSwfForm(); break;
			case 'zip' : echo $this->html->uploadZipForm(); break;
			case 'fromUrl' : echo $this->html->uploadFromUrlForm(); break;
			case 'fromFolder' : echo $this->html->uploadFromFolder(); break;
			default : echo $this->html->uploadImgForm();
		}

		exit;
	}

	//редактирование книги
	function edit_book()
	{
        global $wpdb;

        foreach($_POST as $key=>$value)
        {
        	$_POST[$key] = trim( $value );
        	$_POST[$key] = stripslashes( $value );
			$_POST[$key] = htmlspecialchars( $value );
			$_POST[$key] = $wpdb->escape($value); //подготавливаем к записи в базу
        	//if(($value == "") && ($key !== "flipSound") && ($key !== "image[]")) {echo "All fields is nessesury"; return 0;}
        }

        if( empty( $_POST['bookName'] ) ) $_POST['bookName'] = 'unnamed';

        //загружаем бекграунд
        if( !empty($_FILES['image']['name'][0]) ) $imageId = $this->upload_image( "bgImage" );
        else $imageId[0] = $_POST['bgImage'];

        //записываем в базу
        $sql = "update `".$this->table_name."` set `name` = '".$_POST['bookName']."', `bgImage` = '".$imageId[0]."' where `id` = '".$_POST['bookId']."'";
        $wpdb->query( $sql );

        //звук
        $_POST['flipSound'] = $this->add_sound();;

        $book = new Book( $_POST['bookId'] );

        //свойства пишем
        foreach( $book->properties as $property )
			if( (string)$_POST[$property] !== '' || $property == 'flipSound' )
				$book->$property = $_POST[$property];

		//сохраняем изменения
        if( !$book->save() )
        {
        	$txt = __('Save file error! Please setup permission to the books/ , images/ , sounds/ folders and include files to &quot;777&quot;', 'pageFlip');
        	echo $this->functions->errorMessage( $txt );
			return false;
        }
	}

	//удаление книги
	function delete_book($bookId)
	{
        global $wpdb;

        @unlink($this->plugin_path . $this->booksDir . '/' . $bookId . '.xml');//удаляем дирректорию

        //удаляем запись из базы
        $sql = "delete from `".$this->table_name."` where `id` = '".$bookId."'";
        //выполняем запрос
        $wpdb->query($sql);

        unset($_POST['do']);//обнуляем переменную, чтобы ничего не глючило =)

        //выводим стандартную страницу
        //$this->manage_books();
	}

	//менеждер изображений
    function images_list( $bookId = 0, $gallery = 0 )
    {
    	global $wpdb;

    	if( isset( $_POST['bookId'] ) )
    	{
    		$bookId = $_POST['bookId'];
    		$gallery = $_POST['gallery'];
    	}

    	if( (int)$_POST['page'] < 1 ) $_POST['page'] = 1;

		$navigation = $this->functions->navigationBar( $_POST['page'], get_option( 'pageFlip_imgPerPage' ), $_POST['type'], '', $gallery );

    	$start = ( $navigation['page'] - 1 ) * get_option( 'pageFlip_imgPerPage' );

    	switch( $_POST['type'] )
    	{
    		case 'NGGallery' : {
    			$sql = "select `title` from `".$wpdb->prefix."ngg_gallery` where `gid` = '".$gallery."'";
		    	$galleryName = $wpdb->get_var( $sql );
    		} break;
    		case 'pageFlip' : {
    			if( (int)$gallery === 0 ) $galleryName = __('Unsorted', 'pageFlip');
		    	else
		    	{
		    		$sql = "select `name` from `".$this->table_gal_name."` where `id` = '".$gallery."'";
		    		$galleryName = $wpdb->get_var( $sql );
		    	}
    		} break;
    	}

    	$list = '';

		if( $_POST['type'] === 'pageFlip' || $_POST['type'] === 'NGGallery' )
    	{
			$header = '<a href="#" onclick="return viewGalleries();">' . __('Galleries', 'pageFlip') . '</a> -> ' . __('Images from gallery', 'pageFlip') . ' &quot;'. $galleryName . '&quot;';

			if( (int)$bookId === 0 )
				$list .= $this->functions->printHeader( $header );
			else
				$list .= '<p style="font-size: medium;">' . $header . '</p>';
		}
    	//$list .= 'return to galleries list';
    	//$list = $this->html->ajaxPreviewImage( $bookId );

 		//$list = '<div id="addPage">';

    	//if( $bookId > 0 ) $list .= '<div id="addPageMenu">' . $this->html->addPageMenu() . '</div>';

		$list .= $this->html->operationPreviewImage( $bookId, 'top', $navigation, $_POST['type'], $gallery );
		$list .= $this->html->headerPreviewImage();
        $list .= $this->viewImagesList( $bookId, $start, get_option( 'pageFlip_imgPerPage' ), $_POST['type'], $gallery );
		$list .= $this->html->footerPreviewImage();
		$list .= $this->html->operationPreviewImage( $bookId, 'bottom', $navigation, $_POST['type'], $gallery );

		//$list .= '</div>';

        echo $list;

        if( isset( $_POST['bookId'] ) )
		{
			echo '<split>' . $navigation['page'];
			exit;
		}
    }

    //менеждер изображений
    function galleriesList( $bookId = 0 )
    {
    	global $wpdb;

    	$list = '';

		if( isset( $_POST['bookId'] ) )
		{
			$bookId = $_POST['bookId'];
			$type = $_POST['type'];
		}
		else
		{
    	  	$list = $this->html->ajaxPreviewImage( $bookId );
    	  	$type = 'pageFlip';

    		$list .= '<div id="addPage">';
    	}

    	if( (int)$bookId === 0 )
			$list .= $this->functions->printHeader( __('Galleries', 'pageFlip') );

    	$list .= '<div id="pageFlipTop">';

		if( (int)$bookId === 0 )
			$list .= $this->html->operationPreviewGallery( $bookId );
		else $list .= '&nbsp;';

		$list .= '</div>';

    	$list .= '<div id="pageFlipList">';

 		//$list .= $this->galleriesTable( $bookId );
 		$list .= $this->html->headerPreviewGallery();

        $list .= $this->viewGalleriesList( $bookId, $type );

		$list .= $this->html->footerPreviewGallery();

		$list .= '</div>';

		if( isset( $_POST['bookId'] ) )
		{
			echo $list;
			exit;
		}
		else
		{
			$list .= '</div>';
        	echo $list;
  		}
    }

    function pagingImages()
    {
    	if( (int)$_POST['page'] < 1 ) $_POST['page'] = 1;

		$navigation = $this->functions->navigationBar( $_POST['page'], get_option( 'pageFlip_imgPerPage' ), $_POST['type'] );

		echo $navigation['bar'];

    	echo '<split>';

    	$start = ( $navigation['page'] - 1 ) * get_option( 'pageFlip_imgPerPage' );

    	echo $this->viewImagesList( $_POST['bookId'], $start, get_option( 'pageFlip_imgPerPage' ), $_POST['type'], $_POST['gallery'] );

		//---
		//echo '<div id="addPage">';

    	//if( $bookId > 0 ) echo '<div id="addPageMenu">' . $this->html->addPageMenu() . '</div>';

		//echo $this->html->operationPreviewImage( $bookId, 'top' );

		//echo $this->html->headerPreviewImage();

        //echo $this->viewImagesList( $_POST['bookId'], $start, get_option( 'pageFlip_imgPerPage' ), $_POST['type'], $_POST['gallery'] );

		//echo $this->html->footerPreviewImage();

		//echo $this->html->operationPreviewImage( $bookId, 'bottom' );

		//echo '</div>';
		//---

		echo '<split>';

		echo $navigation['page'];

		exit;
    }

	//собственно список
	function viewImagesList( $bookId = 0, $start = 0, $count = 0, $type = 'pageFlip', $gallery = 0 )
	{
		if ( $count > 0 ) $limit = "limit ".$start.", ".$count;
		else $limit = '';

		switch( $type )
		{
			case 'WPMedia' : return $this->viewWPMediaImgList( $bookId, $limit ); break;
			//case 'NGGallery' : return $this->viewNGGalleriesList( $bookId ); break;
			case 'NGGallery' : return $this->viewNGGalleryImgList( $bookId, $limit, $gallery ); break;
			default : return $this->viewPageFlipImgList( $bookId, $gallery, $limit );
		}
	}

	//собственно список
	function viewGalleriesList( $bookId = 0, $type = 'pageFlip' )
	{
		global $wpdb;

		$result = '';

		switch( $type )
		{
			case 'NGGallery' : {
				$sql = "select `gid` as id, `path`, `title` as name, `previewpic` from `".$wpdb->prefix."ngg_gallery`";
				$galleries = $wpdb->get_results($sql, ARRAY_A);
			} break;
			default : {
				$sql = "SELECT `id`, `name`, `date`, `preview` FROM `".$this->table_gal_name."` ORDER BY `name` ASC ";
				$galleries = $wpdb->get_results( $sql, ARRAY_A );
			}
		}

        /*
		$result = "<tr class=\"alternate author-self status-publish\" valign=\"top\">
	                 					          <td colspan=\"5\" style=\"text-align: center;\"><strong>" . __('No galleries', 'pageFlip') ."</strong></td>
					  				            </tr>";
					  				            */
		if( (int)count( $galleries ) > 0 )
		    foreach( $galleries as $gallery )
		    {
		       	$sql = $this->functions->sqlImgList( 'count', $type, $gallery['id'] );
		    	$countImg = $wpdb->get_var( $sql );

		    	if( $countImg > 0 )
		    		$imageUrl = $this->functions->getGalleryPreview( $gallery['id'], $type );
		    	else $imageUrl = '';

				if( $type === 'pageFlip' ) $creationDate = date( "d/m/Y", $gallery['date'] );
				else $creationDate = '';

		        $result .= $this->html->previewGallery( $bookId, $gallery['id'], $gallery['name'], $countImg, $creationDate,
		        										 $this->functions->printImg( $imageUrl, '', '', '', true ), $type );
		    }

	    if( $type === 'pageFlip' )
	    {
			$sql = $this->functions->sqlImgList( 'count', $type, 0 );
			$count = $wpdb->get_var( $sql );

		    if( (int)$count > 0 )
		    {
				$sql = "select `filename` from `".$this->table_img_name."` where `type` = 'img' and `gallery` = '0' order by RAND() limit 1";
		    	$imageUrl = $this->functions->getImageUrl( $wpdb->get_var( $sql ) );

				$result .= $this->html->previewGallery( $bookId, 0, __('Unsorted', 'pageFlip'), $count, '',
		        										 $this->functions->printImg( $imageUrl, '', '', '', true ) );
		    }
	    }
	    elseif( count( $galleries )  == 0 )
			$result = "<tr class=\"alternate author-self status-publish\" valign=\"top\">
				          <td colspan=\"5\" style=\"text-align: center;\"><strong>" . __('No galleries', 'pageFlip') ."</strong></td>
		        	   </tr>";

		return $result;
	}

	//список альбомов
	function viewAlbumsList( $bookId = 0, $start = 0, $count = 0, $type = 'pageFlip' )
	{
		if ( $count > 0 ) $limit = "limit ".$start.", ".$count;
		else $limit = '';

		switch( $type )
		{
			case 'WPMedia' : return $this->viewWPMediaImgList( $bookId, $limit ); break;
			//case 'NGGallery' : return $this->viewNGGalleriesList( $bookId ); break;
			case 'NGGallery' : return $this->viewNGGalleryImgList( $bookId, $limit ); break;
			default : return $this->viewPageFlipImgList( $bookId, $limit );
		}
	}

	//список изображений из pageflip images
	function viewPageFlipImgList( $bookId, $gallery, $limit )
	{
		global $wpdb;

		$result = '';

		$sql = $this->functions->sqlImgList( 'list', 'pageFlip', $gallery ).$limit;

		$images = $wpdb->get_results($sql, ARRAY_A);
        if( count($images) == "0" ) $result = "<tr class=\"alternate author-self status-publish\" valign=\"top\">
	                 					          <td colspan=\"5\" style=\"text-align: center;\"><strong>" . __('No images', 'pageFlip') ."</strong></td>
					  				            </tr>";
	    else foreach($images as $img)
	    {
	       	$imageUrl = $this->functions->getImageUrl( $img['filename'] );

			$uploadDate = date( "d/m/Y", $img['date'] );
	        $result .= $this->html->previewImage( $bookId, $img['id'], $img['name'], $uploadDate,
	        										 $this->functions->printImg( $imageUrl, $img['name'], '', '', true ), $gallery );
	    }

		return $result;
	}

	//список изображений из wp media
	function viewWPMediaImgList( $bookId, $limit )
	{
		global $wpdb;

		$result = '';

    	$uploads = wp_upload_dir();

    	$sql = $this->functions->sqlImgList( 'list', 'WPMedia' ).$limit;
		$WPImages = $wpdb->get_results($sql, ARRAY_A);
		if ( count($WPImages) == 0 ) {
			$result =
				"<tr class=\"alternate author-self status-publish\" valign=\"top\">" .
				"<td colspan=\"5\" style=\"text-align: center;\"><strong>" . __('No images', 'pageFlip') ."</strong></td>" .
				"</tr>";
		}
		else {
			foreach ($WPImages as $img)
			{
				$location = get_post_meta( $img['post_id'], '_wp_attached_file', true );
	    		$filetype = wp_check_filetype( $location );

				if ( ( substr($filetype['type'], 0, 5) == 'image' ) && ( $thumb = wp_get_attachment_image( $img['post_id'], array(80, 60), true ) ) )
	    		{
					$att_title = wp_specialchars( _draft_or_post_title( $img['post_id'] ) );
	    			$result .= $this->html->previewImage( $bookId, $img['post_id'], $att_title, '', $thumb, 'WPMedia' );
	    		}
			}
		}
		return $result;
	}

	//список галерей из NGGallery
	function viewNGGalleriesList( $bookId )
	{
		global $wpdb;

		$result = '';

    	$sql = "select `gid`, `path`, `title`, `previewpic` from `".$wpdb->prefix."ngg_gallery`";
		$NGGalleries = $wpdb->get_results($sql, ARRAY_A);
		if ( count($NGGalleries) == 0 ) {
			$result =
				"<tr class=\"alternate author-self status-publish\" valign=\"top\">" .
				"<td colspan=\"5\" style=\"text-align: center;\"><strong>" . __('No galleries', 'pageFlip') ."</strong></td>" .
				"</tr>";
		}
		else {
			foreach ($NGGalleries as $gallery) {
				$sql = "SELECT `filename` FROM `".$wpdb->prefix."ngg_pictures` where `pid` = '".$gallery['previewpic']."'";
				$imageUrl = get_option( 'siteurl' ) . '/' . $gallery['path'] . '/thumbs/thumbs_' . $wpdb->get_var( $sql );
				$result .= $this->html->previewImage( $bookId, $gallery['pid'], $gallery['title'], '',
				$this->functions->printImg( $imageUrl, $gallery['title'], '', '', true ) , 'NGGallery' );
			}
		}
		return $result;
	}

	//список изображений из NGGallery
	function viewNGGalleryImgList( $bookId, $limit, $gallery )
	{
		global $wpdb;

		$result = '';

    	$sql = $this->functions->sqlImgList( 'list', 'NGGallery', $gallery ).$limit;
		$NGGImages = $wpdb->get_results($sql, ARRAY_A);
		if ( count($NGGImages) == 0 ) {
			$result =
				"<tr class=\"alternate author-self status-publish\" valign=\"top\">" .
				"<td colspan=\"5\" style=\"text-align: center;\"><strong>" . __('No images', 'pageFlip') ."</strong></td>" .
				"</tr>";
		}
		else {
			foreach ($NGGImages as $img) {
				$sql = "select `path` from `".$wpdb->prefix."ngg_gallery` where `gid` = '".$img['galleryid']."'";
				$imageUrl = get_option( 'siteurl' ) . '/' . $wpdb->get_var( $sql ) . '/thumbs/thumbs_' . $img['filename'];

				$result .= $this->html->previewImage( $bookId, $img['pid'], $img['alttext'], $img['imagedate'],
				$this->functions->printImg( $imageUrl, $img['alttext'], '', '', true ) , 'NGGallery' );
			}
		}
		return $result;
	}

	//аякс обработчик перехода по страницам
	function addPageMenu()
	{
		echo $this->html->addPageMenu( $_POST['type'] );
		echo '<split>';
		echo $this->html->buttonsOpImages( $_POST['bookId'], $_POST['type'] );

		exit;
	}

	//обновляем параметр количества страниц
	function setImgPerPage()
	{
		update_option( 'pageFlip_imgPerPage', (int)$_POST['count'] );
		exit;
	}

    //функция удаляющая страницу нах
    function delete_page()
    {
		$book = new Book( $_POST['bookId'] );

        $book->deletePage( $_POST['pageId'] ); //удаляем запись

		//сохраняем
        $book->save();
    }

    //функция деления изображения пополам и в книгу
    function splitImage()
    {
		$book = new Book( $_POST['bookId'] );

		if( $this->functions->checkPic( $book->pages[(int)$_POST['pageId']]->image ) != 'pageFlip' ) exit;

        $image = $this->imagesPath . basename( $book->pages[(int)$_POST['pageId']]->image );
        $zoomImage = $this->imagesPath . basename( $book->pages[(int)$_POST['pageId']]->zoomURL );

		$newImages = $this->functions->splitImage( $image );
        if( !$newImages ) return false;

        $firstImage = $this->imagesUrl . basename( $newImages[0] );
        $secondImage = $this->imagesUrl . basename( $newImages[1] );

        if ( $zoomImage != $image )
        {
        	$newZoomImages = $this->functions->splitImage( $zoomImage );
	        $firstZoomImage = $this->imagesUrl . basename( $newZoomImages[0] );
	        $secondZoomImage = $this->imagesUrl . basename( $newZoomImages[1] );
        }
        else
        {
	        $firstZoomImage = '';
	        $secondZoomImage = '';
        }

        //$book->deletePage( $_POST['pageId'] ); //удаляем запись

        $book->pages[(int)$_POST['pageId']] = new Page( $firstImage, $_POST['pageId'], '', $firstZoomImage );

        for( $i = $book->countPages; $i > $_POST['pageId'] + 1; $i-- )
        {
        	$book->pages[$i] = $book->pages[($i - 1)];
        	$book->pages[$i]->number = $i;
        }

        $book->pages[($_POST['pageId'] + 1)] = new Page( $secondImage, ($_POST['pageId'] + 1), '', $secondZoomImage );

		$book->refreshPages(); //обновляем массив страниц

		//сохраняем
        $book->save();

        exit;
    }

    //функция деления изображения пополам и в книгу
    function mergeImage()
    {
		$book = new Book( $_POST['bookId'] );

		$secondImage = substr( basename( $book->pages[(int)$_POST['pageId']]->image ), 2 );
		$mergeImage = $this->imagesUrl . $this->functions->getSplitImageName( $book->pages[(int)$_POST['pageId']]->image );

		@unlink( $this->imagesPath . basename( $book->pages[(int)$_POST['pageId']]->image ) ); //удаляем файл
		@unlink( $this->imagesPath . 't_' . basename( $book->pages[(int)$_POST['pageId']]->image ) ); //и превьюшку

		//заменяем одно изображение на исходное
		$book->pages[(int)$_POST['pageId']] = new Page( $mergeImage, $_POST['pageId'], '' );

        //ищем второе и удаляем нах
        foreach( $book->pages as $page )
         	if( substr( basename( $page->image ), 2) == $secondImage )
        	{
				@unlink( $this->imagesPath . basename( $page->image ) ); //удаляем файл
				@unlink( $this->imagesPath . 't_' . basename( $page->image ) ); //и превьюшку

				$book->deletePage( $page->number );
				break;
        	}

		//сохраняем
        $book->save();

        exit;
    }

    //функция удаления картинки
    function delete_image( $imageId = '' )
    {
        global $wpdb;

        if( $imageId === '' )
		{
			$imageId = $_POST['imageId'];
			$ajax = true;
		}

        $sql = "select `filename` from `".$this->table_img_name."` where `id` = '".$imageId."'";
	    $img = $wpdb->get_row($sql, ARRAY_A, 0);

        //удаляем запись из базы
        $sql = "delete from `".$this->table_img_name."` where `id` = '".$imageId."'";
        //выполняем запрос
        $wpdb->query($sql);

        if( !$this->functions->isUrl( $img['filename'] ) )
        {
			$page =  $this->plugin_path . $this->imagesDir . '/' . $img['filename'];

	        @unlink( $page );//удаляем файл

	        //если это не swf, то и превьюшку удаляем
	        $fileExt = split( "\.", $img['filename'] );
	        if( $fileExt[1] != "swf" )
	        {
	        	 $thumb = $this->plugin_path . $this->imagesDir . '/t_' . $img['filename'];
	        	 @unlink( $thumb );//удаляем файл

	        	 $zoom = $this->imagesPath. 'z_'.$img['filename'];
	        	 if (file_exists($zoom))
	        	 	@unlink($zoom);
	        }
        }

        if( $ajax ) exit;

        //unset($_POST['do']);//обнуляем переменную, чтобы ничего не глючило =)
    }

    //удаление нескольких картинок
    function deleteImages()
    {
    	if( empty( $_POST['imageList'] ) ) return false;

		$images = split( ';', $_POST['imageList'] );

		foreach( $images as $imageId )
			$this->delete_image( $imageId );

		exit;
    }

	function deleteGallery()
	{
		global $wpdb;

		//удаляем изображения принадлежащие галереи
		$sql = "select `id` from `".$this->table_img_name."` where `type` = 'img' and `gallery` = '".$_POST['gallery']."'";
		$images = $wpdb->get_results($sql, ARRAY_A);
		if( count( $images ) > 0 )
			foreach( $images as $img ) $this->delete_image( $img['id'] );

		//удаляем запись из базы
        $sql = "delete from `".$this->table_gal_name."` where `id` = '".$_POST['gallery']."'";
        //выполняем запрос
        $wpdb->query($sql);

		exit;
	}

    //функция перемещения фоток в другую галерею
    function moveImgTo( $galleryId = '', $imageId = '' )
    {
    	global $wpdb;

    	if( ( $galleryId === '' ) || ( $imageId === '' ) )
    	{
    		$galleryId = (int)$_POST['gallery'];
    		$imageId = (int)$_POST['imageId'];
    	}

		$sql = "update `".$this->table_img_name."` set `gallery` = '".$galleryId."' where `id` = '".$imageId."'";
    	$wpdb->query( $sql );

		if( ( $galleryId === '' ) || ( $imageId === '' ) ) exit;
    }

    //функция перемещения фоток в другую галерею
    function moveImgsTo()
    {
    	if( empty( $_POST['imageList'] ) ) return false;

		$images = split( ';', $_POST['imageList'] );

		foreach( $images as $imageId )
			$this->moveImgTo( $_POST['gallery'], $imageId );

		exit;
	}

    //добавление звука
	function add_sound()
	{
        if ($_FILES['sound']['name'])
        {
           if($_FILES["sound"]["size"] > $this->maxSoundSize) {echo __("This file is too big", 'pageFlip'); return 0;} //проверяем размер файла
           //получаем расширение файла
	       $fileExt = split("\.", $_FILES['sound']['name']);
	       if(strtolower($fileExt['1']) != "mp3"){echo __("Wrong file type", 'pageFlip'); return 0;} //проверяем расширение файла
	       //копируем в папку
           $dirName = $this->plugin_path.$this->soundsDir."/";

           //получаем максимальное имя звука
           $maxNum = 0;
           $dir = opendir($dirName); //открываем директорию

	       while ($sound = readdir($dir))
	       {
	          if ($sound != '.' && $sound != '..')
	          {
	            $name = split("\.", $sound);
	            if((int)$name["0"] > (int)$maxNum) $maxNum = $name["0"];
	          }
	       }

	       closedir ($dir); //закрываем директорию

	       //делаем имя файла
           $filename =  ( $maxNum + 1 ) . '.' . $fileExt['1'];

	       $new_filename = $dirName . $filename;

	       $_POST['flipSound'] = basename($new_filename);

	       if(!copy( $_FILES['sound']['tmp_name'], $new_filename ) ) {echo __("Write file error!", 'pageFlip'); return '';}
	    }

	    if( $_POST['flipSound'] !== '' ) $flipSound = $this->plugin_url . $this->soundsDir . '/' . $_POST['flipSound']; //звук
	    else $flipSound = '';

	    return $flipSound;
	}
	//функция провеки и создания нужных таблиц, если их нет в бд
    function check_db()
    {
         global $wpdb;

         $fieldsPageFlip = array( 'id' => 'BIGINT( 20 ) NOT NULL AUTO_INCREMENT PRIMARY KEY',
		 						  'name' => 'TEXT NOT NULL',
								  'date' => 'BIGINT( 11 ) NOT NULL DEFAULT \''.date("U").'\'',
								  'bgImage' => 'BIGINT( 11 ) NOT NULL'
								 );

		 $fieldsPageFlipImg = array( 'id' => 'BIGINT( 20 ) NOT NULL AUTO_INCREMENT PRIMARY KEY',
		 							 'name' => 'TEXT NOT NULL',
		 							 'filename' => 'TEXT NOT NULL',
		 							 'date' => 'BIGINT( 11 ) NOT NULL DEFAULT \''.date("U").'\'',
		 							 'type' => 'VARCHAR( 10 ) NOT NULL DEFAULT \'img\'',
		 							 'gallery' => 'BIGINT( 20 ) NOT NULL'
								   );

		$fieldsPageFlipGallery = array( 'id' => 'BIGINT( 20 ) NOT NULL AUTO_INCREMENT PRIMARY KEY',
		 							 	'name' => 'TEXT NOT NULL',
		 							 	'date' => 'BIGINT( 11 ) NOT NULL DEFAULT \''.date("U").'\'',
		 							 	'preview' => 'BIGINT( 20 ) NOT NULL'
								  	  );


		 //если в базе нет нашей таблицы - создаем
		 $this->functions->createTable( $this->table_name, $fieldsPageFlip );

		 //проверяем таблицу
		 $this->functions->checkTable( $this->table_name, $fieldsPageFlip );

		 //если в базе нет второй нашей таблицы - тоже создаем =)
		 $this->functions->createTable( $this->table_img_name, $fieldsPageFlipImg );

		 //проверяем таблицу
		 $this->functions->checkTable( $this->table_img_name, $fieldsPageFlipImg );

		 //если в базе нет второй нашей таблицы - тоже создаем =)
		 $this->functions->createTable( $this->table_gal_name, $fieldsPageFlipGallery );

		 //проверяем таблицу
		 $this->functions->checkTable( $this->table_gal_name, $fieldsPageFlipGallery );
    }

	//функция провеки и создания нужных директорий, если их нет еще
    function check_dir()
    {
          global $pageFlipError;

		  //@chmod( $this->plugin_path , 0777 );

          $pageFlipError = '';

          if( $this->functions->createDir( $this->plugin_path ) )
          {
	          $this->functions->createDir( $this->plugin_path . $this->booksDir );
	          $this->functions->createDir( $this->plugin_path . $this->soundsDir );
	          $this->functions->createDir( $this->plugin_path . $this->imagesDir );
	          $this->functions->createDir( $this->plugin_path . $this->uploadDir );
          }

          if( $pageFlipError !== '' ) define( 'PAGEFLIP_ERROR', $pageFlipError );

		  //проверяем если была старая версия плагина
		  $oldFolders = array( $this->booksDir, $this->imagesDir, $this->soundsDir );
		  foreach( $oldFolders as $folder)
		  {
          	$curDir = WP_PLUGIN_DIR . '/' . $this->plugin_dir . '/' . $folder . '/';
			if( is_dir( $curDir ) )
          	{
	  			$dir = opendir( $curDir ); //открываем директорию

				while ( $file = readdir( $dir ) )
				  if ( is_file( $curDir . $file ) )
				  {
		            if( $folder === $this->booksDir ) //если это книги, то меняем пути изображений
		            {
		            	$book = join( '', file( $curDir . $file ) );
		            	$book = str_replace( WP_PLUGIN_URL . '/' . $this->plugin_dir . '/', $this->plugin_url, $book );
						$bookFile = fopen( $this->plugin_path . $folder . '/' . $file, 'w+' );
						if( fwrite( $bookFile, $book ) ) @unlink( $curDir . $file );
						fclose( $bookFile );
		            }
		            else
						@rename( $curDir . $file, $this->plugin_path . $folder . '/' . $file );
				  }

		        closedir ( $dir ); //закрываем директорию

		        @rmdir( $curDir );
          	}
          }
    }

	//функция удаления каталога
	function removeDir($dirName)
	{
	      //проверяем есть ли такой каталог
	      if(!is_dir($dirName)) return true;
	      //if(!rmdir($dirName)) return false;
	      $delete_dir = opendir($dirName);
	      chdir($dirName);
	      while ($delete = readdir($delete_dir))
	      {
	             if(is_dir($delete) && ($delete !== ".") && ($delete !== "..")) $del_dir_names[] = $delete;
	             if(is_file($delete)) $del_file_names[] = $delete;
	      }
	      //отдельное иззвращение для папки с именем 0
	      if( is_dir("0/") ) $del_dir_names[] = "0/";

	      if(isset($del_file_names))
	       foreach($del_file_names as $delete_this_file) unlink($dirName.$delete_this_file);

	      if(isset($del_dir_names))
	       foreach($del_dir_names as $delete_this_dir) $this->removeDir($dirName.$delete_this_dir."/");

	      closedir($delete_dir);
	      if(rmdir($dirName)) return true;
	      else return false;
	}

	//аякс обработчик, выдающий форму добавления страницы
	function addPageForm()
	{
		echo $this->add_page_form( $_POST['bookId'], $_POST['imageId'], $_POST['type'] );
		exit;
	}


	//форма добавления страницы
	function add_page_form($id, $imageId, $type='pageFlip' )
	{
        global $wpdb;

        switch( $type )
        {
        	case 'WPMedia' : {
    			$image = wp_get_attachment_image( $imageId, array(80, 60), true );
    			$name = wp_specialchars( _draft_or_post_title( $imageId ) );
        	} break;
        	case 'NGGallery' : {
				$sql = "SELECT `filename`, `galleryid`, `alttext` FROM `".$wpdb->prefix."ngg_pictures` where `pid` = '".$imageId."'";
				$img = $wpdb->get_row($sql, ARRAY_A);
				$sql = "select `path` from `".$wpdb->prefix."ngg_gallery` where `gid` = '".$img['galleryid']."'";

    			$image = get_option( 'siteurl' ) . '/' . $wpdb->get_var( $sql ) . '/thumbs/thumbs_' . $img['filename'];
				$image = $this->functions->printImg( $image, $img['alttext'] );
				$name = $img['alttext'];
        	} break;
        	default : {
        		$sql = "select `name`, `filename` from `".$this->table_img_name."` where `id` = '".$imageId."'";
			    $img = $wpdb->get_row($sql, ARRAY_A, 0);

			    $imageUrl = $this->functions->getImageUrl( $img['filename'] );
			    $image = $this->functions->printImg( $imageUrl, $img['name'] );
    			$name = $img['name'];
        	}
        }

        echo $this->html->addPageForm( $id, $imageId, $image, $name, $type );
	}

	//обработчик для загрузки альбома
	function flashEditor( $do )
	{
		switch( $do )
		{
			case 'loadalbumxml' : echo $this->functions->loadAlbumXml( (int)$_POST['bookId'] ); break;
			case 'savealbumxml' : $this->functions->saveAlbumXml( (int)$_POST['bookId'] ); break;
			case 'loadlayouts' : echo $this->functions->loadLayouts( ); break;
		}
		exit;
	}

    //форма добавления книги
	function book_form( $bookId = '', $galleryId = '' )
	{
		global $wpdb;
		//addPageFromGallery

		$thisBook = new Book( $bookId );

		if( $bookId == '' )//если не задан id книги - то значения по дефолту
        {
            $book['name'] = '';
            $book['button'] = __('Add Book', 'pageFlip');
            $book['title'] = __('Add Book', 'pageFlip');
            $book['action'] = 'addbook';
            $book['bgImage'] = '0';
        	if ($galleryId)
        	{
        		$gallery = $wpdb->get_row("SELECT * FROM `{$this->table_gal_name}` WHERE `id`='{$galleryId}'");
        		$book['name'] = $gallery->name;
        	}
        }
        else //если задан - получаем
        {
            global $wpdb;
            $sql = "select `name`, `bgImage` from `".$this->table_name."` where `id` = '".$bookId."'";

            //$book['flipSound'] = basename( $book['flipSound'] );
            $book['name'] = $wpdb->get_var($sql, 0, 0);
            $book['button'] = __('Save Changes', 'pageFlip');
            $book['title'] = __('Book properties', 'pageFlip');
            $book['action'] = 'editbook';
            $book['bgImage'] = $wpdb->get_var($sql, 1, 0);
        }

        //получаем звуки
        $dir_name = $this->plugin_path . $this->soundsDir . '/';
        $dir = opendir( $dir_name ); //открываем директорию

        $flipSound = '<select size="1" name="flipSound" id="flipSound">';
        $flipSound .= '<option value="">' . __('No sound', 'pageFlip') . '</option>';
        while ( $sound = readdir( $dir ) )
        {
          if ( $sound != '.' && $sound != '..' )
          {
            $flipSound .= '<option value="' . $sound . '"';
            if( basename( $thisBook->flipSound ) == $sound )   $flipSound .= ' selected="selected"';
            $flipSound .= '>' . $sound . '</option>';
          }
        }
        $flipSound .= '</select>';

        closedir ( $dir ); //закрываем директорию*/

        //получаем бекграунды
        $sql = "select `id`, `name`, `filename` from `" . $this->table_img_name . "` where `type` = 'bg' order by `id`";
	    $bgrounds = $wpdb->get_results( $sql, ARRAY_A );

	    $bgImageUrl = '';

        $bgImageList = '<select size="1" name="bgImage" id="bgImage" onchange="viewBackground(this);">';
        $bgImageList .= '<option value="-1"';
        if($book['bgImage'] == "-1")   $bgImageList .= ' selected="selected"';
        $bgImageList .= '>' . __('No Background', 'pageFlip') . '</option>' .
						'<option value="0"';

		if( $book['bgImage'] == "0" )
        {
        	$bgImageList .= ' selected="selected"';
        	$bgImageUrl = $this->bgFile;
        }

        $bgImageList .= '>' . __('default', 'pageFlip') . '</option>';

        $bgImagesAr = 'case \'0\' : preview = \'' . str_replace( "/", "\\/", $this->functions->printImg( $this->bgFile, 'default' ) ) . '\'; break; ' . "\n";

        $bgImageName = '';
        if( count( $bgrounds ) > 0 )
         foreach ( $bgrounds as $bground )
         {
             $bgImageList .= '<option value="' . $bground['id'] . '"';
             if( $book['bgImage'] == $bground['id'] )
             {
             	$bgImageList .= ' selected="selected"';
             	$bgImageUrl = $this->plugin_url . $this->imagesDir . '/' . $bground['filename'];
             	$bgImageName = $bground['name'];
             }
             $bgImageList .= '>' . $bground['name'] . '</option>';

             $bgImagesAr .= 'case \'' . $bground['id'] . '\' : preview = \'' . str_replace( "/", "\\/", $this->functions->printImg( $this->plugin_url.$this->imagesDir . '/' . $bground['filename'], $bground['name'] ) ) . '\'; break; \n';
         }
        $bgImageList .= '</select>';

        //выводим форму
        echo $this->html->bookForm( $book['title'], $book['name'], $thisBook,
									  $this->functions->printImg( $bgImageUrl, $bgImageName ),
        						  	   $bgImagesAr, $flipSound, $bgImageList,
        						         $book['action'], $book['button'], $galleryId );
	}


    //для формы редактирования
	function mce_external_plugins( $plugin_array )
	{
		$plugin_array['pageFlip'] = $this->jsUrl . 'editor_plugin.js';
	    return $plugin_array;
	}

	//добавляем кнопку на форму редактирования
	function mce_buttons( $buttons )
	{
	    array_push( $buttons, "pageFlip" );
	    return $buttons;
	}

	//интернационализация плагина
	function init_textdomain()
	{
    	if ( function_exists( 'load_plugin_textdomain' ) )
        	load_plugin_textdomain( 'pageFlip', PLUGINDIR . '/' . $this->plugin_dir . '/' . $this->langDir . '/' );
	}


	function sortBook()
	{
		$book = new Book($_POST['bookId']);
		$sortBy = $_POST['sortBy'];
		$sortOrder = $_POST['sortOrder'] == 'desc' ? SORT_DESC : SORT_ASC;

		foreach ($book->pages as $id => $page)
		{
			$sort[$id] = $page->$sortBy;
		}
		array_multisort($sort, $sortOrder, $book->pages);

        if( !$book->save() )
        {
        	echo __('Save file error! Please setup permission to the books/ , images/ , sounds/ folders and include files to &quot;777&quot;', 'pageFlip');
        	return false;
        }
	}


	/**
	 * Display the PageFlip widget, depending on the widget number.
	 *
	 * Supports multiple PageFlip widgets and keeps track of the widget number by using
	 * the $widget_args parameter. The option 'widget_pageflip' is used to store the
	 * content for the widgets.
	 *
	 * @param array $args Widget arguments.
	 * @param int $number Widget number.
	 */
	function pageFlipWidget($args, $widget_args = 1) {
		extract( $args, EXTR_SKIP );
		if ( is_numeric($widget_args) )
			$widget_args = array( 'number' => $widget_args );
		$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
		extract( $widget_args, EXTR_SKIP );

		$options = get_option('widget_pageflip');
		if ( !isset($options[$number]) )
			return;

		$book_id = $options[$number]['book_id'];
		$title = apply_filters('widget_title', $options[$number]['title']);
		$link_type = $options[$number]['link_type'];
		$link_text = $options[$number]['link_text'];
		$from = $options[$number]['from'];
		$to = $options[$number]['to'];
		$preview_width = $options[$number]['preview_width'];
		$preview_height = $options[$number]['preview_height'];
		$text = apply_filters('widget_text', $options[$number]['text']);

		if (!$this->html)
		{
			$this->init();
			$this->html->main = &$this;
			$this->functions->main = &$this;
		}

		$book = new Book($book_id);
		$book->load();

		echo $before_widget;
		if ( !empty( $title ) )
			echo $before_title . $title . $after_title;

		switch ($link_type)
		{
			case 'preview':
				$a = array('from'=>$from, 'to'=>$to, 'preview_width'=>$preview_width, 'preview_height'=>$preview_height);
				break;
			case 'text':
				$a = array('text'=>$link_text);
				break;
		}
?>
		<div class="pageflip_widget"><div class="textwidget">
			<div class="pageflip_preview"><?php echo $this->html->popupLink($book, $a); ?></div>
			<div class="pageflip_text"><?php echo $text; ?></div>
		</div></div>
<?php
		echo $after_widget;
	}

	/**
	 * Display and process PageFlip widget options form.
	 *
	 * @param int $widget_args Widget number.
	 */
	function pageFlipWidgetControl($widget_args) {
		global $wp_registered_widgets, $wpdb;
		static $updated = false;

		if ( is_numeric($widget_args) )
			$widget_args = array( 'number' => $widget_args );
		$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
		extract( $widget_args, EXTR_SKIP );

		$options = get_option('widget_pageflip');
		if ( !is_array($options) )
			$options = array();

		if ( !$updated && !empty($_POST['sidebar']) ) {
			$sidebar = (string) $_POST['sidebar'];

			$sidebars_widgets = wp_get_sidebars_widgets();
			if ( isset($sidebars_widgets[$sidebar]) )
				$this_sidebar =& $sidebars_widgets[$sidebar];
			else
				$this_sidebar = array();

			foreach ( (array) $this_sidebar as $_widget_id ) {
				if ( array($this, 'pageFlipWidget') == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number']) ) {
					$widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
					if ( !in_array( "pageflip-$widget_number", $_POST['widget-id'] ) ) // the widget has been removed.
						unset($options[$widget_number]);
				}
			}

			foreach ( (array) $_POST['widget-pageflip'] as $widget_number => $widget_pageflip ) {
				if ( !isset($widget_pageflip['book_id']) && isset($options[$widget_number]) ) // user clicked cancel
					continue;

				$title = strip_tags(stripslashes($widget_pageflip['title']));

				$link_type = strip_tags(stripslashes($widget_pageflip['link_type']));
				$link_text = strip_tags(stripslashes($widget_pageflip['link_text']));

				$from = trim(strip_tags(stripslashes($widget_pageflip['from'])));
				$to = trim(strip_tags(stripslashes($widget_pageflip['to'])));

				$preview_width = trim(strip_tags(stripslashes($widget_pageflip['preview_width'])));
				$preview_height = trim(strip_tags(stripslashes($widget_pageflip['preview_height'])));

				if ($link_type == 'preview')
				{
					if ( empty($from) && empty($to) )
					{
						$from = '1';
						$to = '1';
					}

					if ( empty($preview_width) )
						$preview_width = 70;
					if ( empty($preview_height) )
						$preview_height = 90;
				}

				if ( current_user_can('unfiltered_html') )
					$text = stripslashes( $widget_pageflip['text'] );

				$book_id = stripslashes(wp_filter_post_kses( $widget_pageflip['book_id'] ));
				$options[$widget_number] = compact( 'book_id', 'title', 'link_type', 'link_text', 'from', 'to', 'text', 'preview_width', 'preview_height' );
			}

			update_option('widget_pageflip', $options);
			$updated = true;
		}

		if ( -1 == $number ) {
			$book_id = '';
			$title = '';
			$link_type = 'preview';
			$link_text = '';
			$from = '1';
			$to = '1';
			$preview_width = $this->thumbWidth;
			$preview_height = $this->thumbHeight;
			$text = '';
			$number = '%i%';
		} else {
			$book_id = attribute_escape($options[$number]['book_id']);
			$title = attribute_escape($options[$number]['title']);
			$link_type = attribute_escape($options[$number]['link_type']);
			$link_text = attribute_escape($options[$number]['link_text']);
			$from = attribute_escape($options[$number]['from']);
			$to = attribute_escape($options[$number]['to']);
			$preview_width = attribute_escape($options[$number]['preview_width']);
			$preview_height = attribute_escape($options[$number]['preview_height']);
			$text = format_to_edit($options[$number]['text']);
		}

	    $books = $wpdb->get_results("SELECT `id`, `name` FROM `{$this->table_name}` ORDER BY `id`");
?>
			<p>
				<label for="pageflip-title-<?php echo $number; ?>">Title</label>
				<input class="widefat" id="pageflip-title-<?php echo $number; ?>" name="widget-pageflip[<?php echo $number; ?>][title]" type="text" value="<?php echo $title; ?>" />
			</p>
			<p>
				<label for="pageflip-book_id-<?php echo $number; ?>" style="width:20%; display:block; float:left; padding-top:0.33em;">Book</label>
				<select name="widget-pageflip[<?php echo $number; ?>][book_id]" style="width:70%;">
					<option value=""></option>
<?php foreach ($books as $book) : ?>
					<option value="<?php echo $book->id; ?>"<?php echo $book->id == $book_id ? ' selected="selected"' : ''; ?>><?php echo $book->id; ?> - <?php echo $book->name; ?></option>
<?php endforeach; ?>
				</select>
			</p>
			<p style="margin:2em 0 0 0;">
				<label style="margin-right:1.5em;">Link type</label>
				<input id="pageflip-link_type-text-<?php echo $number; ?>" type="radio" name="widget-pageflip[<?php echo $number; ?>][link_type]" value="text"<?php echo $link_type=='text' ? ' checked="checked"' : ''; ?> onclick="pageflip_link_type('text');" />
				<label for="pageflip-link_type-text-<?php echo $number; ?>"><?php _e('Text', 'pageFlip'); ?></label>
				<input id="pageflip-link_type-preview-<?php echo $number; ?>" type="radio" name="widget-pageflip[<?php echo $number; ?>][link_type]" value="preview"<?php echo $link_type=='preview' ? ' checked="checked"' : ''; ?> onclick="pageflip_link_type('preview');" style="margin-left:1.5em;" />
				<label for="pageflip-link_type-preview-<?php echo $number; ?>"><?php _e('Page preview', 'pageFlip'); ?></label>
			</p>
			<p id="pageflip-link-<?php echo $number; ?>" style="margin:1em 0 0 0;">
				<label for="pageflip-link_text-<?php echo $number; ?>"></label>
				<input id="pageflip-link_text-<?php echo $number; ?>" name="widget-pageflip[<?php echo $number; ?>][link_text]" value="<?php echo $link_text; ?>" style="width:21.5em; margin-left:6.5em;" />
			</p>
			<div id="pageflip-preview-<?php echo $number; ?>">
				<p style="float:left; height:6em; margin:1em 0 0 0;">
					<label for="pageflip-from-<?php echo $number; ?>" style="display:block; margin:0 0 0.5em;"><?php _e('Preview pages'); ?></label>
					<label for="pageflip-from-<?php echo $number; ?>" style="width:4em; height:3em; display:block; float:left; padding-top:0.33em;"><?php _e('from', 'pageFlip'); ?></label>
					<input id="pageflip-from-<?php echo $number; ?>" type="text" class="widefat" name="widget-pageflip[<?php echo $number; ?>][from]" value="<?php echo $from; ?>" style="width:4em;" />
					<label for="pageflip-to-<?php echo $number; ?>"><?php _e('to', 'pageFlip'); ?></label>
					<input id="pageflip-to-<?php echo $number; ?>" type="text" class="widefat" name="widget-pageflip[<?php echo $number; ?>][to]" value="<?php echo $to; ?>" style="width:4em;" />
				</p>
				<p style="float:left; height:6em; margin:1em 0 0 3em;">
					<label for="pageflip-preview_width-<?php echo $number; ?>" style="display:block; margin:0 0 0.5em;"><?php _e('Max. preview size'); ?></label>
					<input id="pageflip-preview_width-<?php echo $number; ?>" type="text" class="widefat" name="widget-pageflip[<?php echo $number; ?>][preview_width]" value="<?php echo $preview_width; ?>" title="<?php _e('Width', 'pageFlip'); ?>" style="width:4em;" />
					&times;
					<input id="pageflip-preview_height-<?php echo $number; ?>" type="text" class="widefat" name="widget-pageflip[<?php echo $number; ?>][preview_height]" value="<?php echo $preview_height; ?>" title="<?php _e('Height', 'pageFlip'); ?>" style="width:4em;" />
					px
				</p>
			</div>
			<script type="text/javascript">//<![CDATA[
				function pageflip_link_type(type)
				{
					switch (type)
					{
						case 'text':
							document.getElementById('pageflip-preview-<?php echo $number; ?>').style.display = 'none';
							document.getElementById('pageflip-link-<?php echo $number; ?>').style.display = 'block';
							document.getElementById('pageflip-link_text-<?php echo $number; ?>').focus();
							break;
						case 'preview':
							document.getElementById('pageflip-link-<?php echo $number; ?>').style.display = 'none';
							document.getElementById('pageflip-preview-<?php echo $number; ?>').style.display = 'block';
							break;
					}
				}
<?php if ($link_type == 'text') : ?>
				document.getElementById('pageflip-preview-<?php echo $number; ?>').style.display = 'none';
<?php else : ?>
				document.getElementById('pageflip-link-<?php echo $number; ?>').style.display = 'none';
<?php endif; ?>
			//]]>
			</script>
			<p style="clear:left; margin-top:2em;">
				<label for="pageflip-text-<?php echo $number; ?>"><?php _e('Text', 'pageFlip'); ?></label>
				<textarea id="pageflip-text-<?php echo $number; ?>" class="widefat" name="widget-pageflip[<?php echo $number; ?>][text]" cols="30" rows="5" style="display:block;"><?php echo $text; ?></textarea>
			</p>
			<input type="hidden" name="widget-text[<?php echo $number; ?>][submit]" value="1" />
			<!--<div style="clear:left; height:1px; overflow:hidden;">&nbsp;</div>-->
<?php
	}

	/**
	 * Register PageFlip widget on startup.
	 *
	 */
	function pageFlipWidgetRegister() {
		if ( !$options = get_option('widget_pageflip') )
			$options = array();
		$widget_ops = array('classname' => 'widget_pageflip', 'description' => __('PageFlip'));
		$control_ops = array('width' => 380, 'height' => 350, 'id_base' => 'pageflip');
		$name = __('FlippingBook');

		$id = false;
		foreach ( (array) array_keys($options) as $o ) {
			// Old widgets can have null values for some reason
			if ( !isset($options[$o]['title']) || !isset($options[$o]['book_id']) )
				continue;
			$id = "pageflip-$o"; // Never never never translate an id
			wp_register_sidebar_widget($id, $name, array($this, 'pageFlipWidget'), $widget_ops, array( 'number' => $o ));
			wp_register_widget_control($id, $name, array($this, 'pageFlipWidgetControl'), $control_ops, array( 'number' => $o ));
		}

		// If there are none, we register the widget's existance with a generic template
		if ( !$id ) {
			wp_register_sidebar_widget( 'pageflip-1', $name, array($this, 'pageFlipWidget'), $widget_ops, array( 'number' => -1 ) );
			wp_register_widget_control( 'pageflip-1', $name, array($this, 'pageFlipWidgetControl'), $control_ops, array( 'number' => -1 ) );
		}
	}

	function adminScripts()
	{
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-draggable');
		wp_enqueue_script('swfupload');
	}

	function admin_init()
	{
		session_start();
	}
}
?>