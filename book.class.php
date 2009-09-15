<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

class Book
{
	var $id = 0,
		$stageWidth = '100%',
		$stageHeight = 480,
		$width = 640,
		$height = 480,
		$firstPage = 0,
		$navigationBarPlacement = 'bottom',
		$pageBack = '0x99CCFF',
		$backgroundColor = '0xFFFFFF',
		$backgroundImage,
		$backgroundImagePlacement = 'fit',
		$staticShadowsType =  'Symmetric',
		$staticShadowsDepth = 1,
		$autoFlip = 50,
		$centerBook = 'true',
		$scaleContent = 'true',
		$alwaysOpened = 'false',
		$flipCornerStyle = 'manually',
		$hardcover = 'false',
		$downloadURL = '',
		$downloadTitle = '',
		$downloadSize = '',
		$allowPagesUnload = 'false',
		$fullscreenEnabled = 'true',
		$zoomEnabled = 'true',
		$zoomImageWidth = 900,
		$zoomImageHeight = 1165,
		$zoomUIColor = '0x8f9ea6',
		$slideshowButton = 'false',
		$slideshowAutoPlay = 'false',
		$slideshowDisplayDuration = '5000',
		$goToPageField = 'true',
		$firstLastButtons = 'true',
		$printEnabled = 'true',
		$zoomOnClick = 'true',
		$moveSpeed = 2,
		$closeSpeed = 3,
		$gotoSpeed = 3,
		$rigidPageSpeed = 5,
		$zoomHint = 'Double click for zooming.',
		$printTitle = 'Print Pages',
		$downloadComplete = 'Complete',
		$dropShadowEnabled = 'true',
		$flipSound = '1.mp3',
		$hardcoverSound,
		$preloaderType = 'Progress Bar',
		$pages = array(),
		$album,
		$countPages = 0,
		$navigation = 'true',
		$popup = 'false',
		$autoReduce = 'false',
		$state = 1; //состояние: 1 - нормальное, 0 - незагружаеца, хитрый 2 - левая флешка

	var $preserveProportions = 'false',
		$centerContent = 'true',
		$hardcoverThickness = 3,
		$hardcoverEdgeColor = '0xFFFFFF',
		$highlightHardcover = 'true',
		$frameWidth = 0,
		$frameColor = '0xFFFFFF',
		$frameAlpha = 100,
		$navigationFlipOffset = 30,
		$flipOnClick = 'true',
		$handOverCorner = 'true',
		$handOverPage = 'true',
		$staticShadowsLightColor = '0xFFFFFF',
		$staticShadowsDarkColor = '0x000000',
		$dynamicShadowsDepth = 1,
		$dynamicShadowsLightColor = '0xFFFFFF',
		$dynamicShadowsDarkColor = '0x000000',
		$loadOnDemand = 'true',
		$showUnderlyingPages = 'false',
		$playOnDemand = 'true',
		$freezeOnFlip = 'false',
		$darkPages = 'false',
		$smoothPages = 'false',
		$rigidPages = 'false',
		$flipCornerPosition = 'top-right',
		$flipCornerAmount = 70,
		$flipCornerAngle = 45,
		$flipCornerRelease = 'true',
		$flipCornerVibrate = 'true',
		$flipCornerPlaySound = 'false',
		$useCustomCursors = 'false',
		$dropShadowHideWhenFlipping = 'true';

	var $properties = array( 'stageWidth',
							 'stageHeight',
							 'width',
							 'height',
							 'scaleContent',
							 'centerContent',
							 'preserveProportions',
							 'hardcover',
							 'hardcoverThickness',
							 'frameWidth',
							 'frameColor',
							 'frameAlpha',
							 'firstPage',
							 'flipOnClick',
							 'handOverCorner',
							 'handOverPage',
							 'alwaysOpened',
							 'staticShadowsType',
							 'staticShadowsDepth',
							 'rigidPageSpeed',
							 'flipSound',
							 'preloaderType',
							 'rigidPages',
							 'zoomEnabled',
							 'zoomImageWidth',
							 'zoomImageHeight',
							 'zoomOnClick',
							 'zoomHint',
							 'centerBook',
							 'useCustomCursors',
							 'dropShadowEnabled',
							 'dropShadowHideWhenFlipping',
							 'backgroundColor',
							 'pageBack',
							 'backgroundImage',
							 'backgroundImagePlacement',
							 'printEnabled',
							 'printTitle',
							 'navigation',
						//download у нас есть
							 'downloadURL',
							 'downloadTitle',
							 'downloadSize',
							 'downloadComplete',
						//advanced параметры
							 'allowPagesUnload',
							 'autoFlip',
							 'closeSpeed',
							 'darkPages',
							 'dynamicShadowsDarkColor',
							 'dynamicShadowsDepth',
							 'dynamicShadowsLightColor',
							 'firstLastButtons',
							 'flipCornerAmount',
							 'flipCornerAngle',
							 'flipCornerPlaySound',
							 'flipCornerPosition',
							 'flipCornerRelease',
							 'flipCornerStyle',
							 'flipCornerVibrate',
							 'freezeOnFlip',
							 'fullscreenEnabled',
							 'goToPageField',
							 'gotoSpeed',
							 'hardcoverEdgeColor',
							 'hardcoverSound',
							 'highlightHardcover',
							 'loadOnDemand',
							 'moveSpeed',
							 'navigationBarPlacement',
							 'navigationFlipOffset',
							 'playOnDemand',
							 'showUnderlyingPages',
							 'slideshowAutoPlay',
							 'slideshowButton',
							 'slideshowDisplayDuration',
							 'smoothPages',
							 'staticShadowsDarkColor',
							 'staticShadowsLightColor',
							 'zoomUIColor',
							 'popup',
							 'autoReduce'
							);

	function Book( $id = '' )
	{
		include_once( PAGEFLIP_DIR.'/page.class.php' );

		if( $id !== '' )
		{
			$this->id = $id;
			$this->load();
		}
	}

	//загрузка книги
	function load()
	{
        global $pageFlip;

		$file = $pageFlip->plugin_path . $pageFlip->booksDir . '/' . $this->id . '.xml';

		$this->album = new Album( $this->id, ceil( $this->width / 2 ), $this->height );

        if( PHP_VERSION >= "5" ) $this->get_xml_php5( $file );
        else $this->get_xml_php4( $file );

        //$this->flipSound = basename( $this->flipSound );
		$this->countPages = count( $this->pages );

		//если альбом не отпарсился
		/*if( $this->album->load === false )
		{*/
			unset($this->album->pages);
			unset($this->album->images);

			foreach( $this->pages as $id=>$page )
			{
				if ( $this->autoReduce=='true' && !defined('WP_ADMIN') && $pageFlip->functions->getExt($page->image) != 'swf' && $page->image == $page->zoomURL )
				{
					list($width, $height) = $pageFlip->functions->getImageSize($page->image);
					$scale1 = $width / $this->width;
					$scale2 = $height / $this->height;
					$scale = $scale1 > $scale2 ? $scale1 : $scale2;
					$f = $scale - intval($scale);
					if ($f > 0.15 || $scale >= 3)
					{
						$this->pages[$id]->image =
							$pageFlip->functions->getResized(
								$page->zoomURL,
								array(
									'max_width' => $this->width / 2,
									'max_height' => $this->height,
									'background' => $this->pageBack,
									'quality' => 90,
								)
							);
					}
				}
				if( $pageFlip->functions->getExt( $page->image ) == 'swf' )
				{
					$key = $this->album->addPage( $id, 1 );
					continue;
				}

				$size = $pageFlip->functions->getImageSize( $page->image );
				$thumb = $pageFlip->functions->getThumb( $page->image );

				$this->album->addImage( $id, $thumb, $size[0], $size[1] );
				$key = $this->album->addPage( $id, 1 );
				$this->album->pages[$key]->addImg( $id, 'scaleToFill', 0, 0 );
			}

			/*$this->album->load = true;
		}*/

		//----trial version----
		if( $this->state === 1 )
		{
			$stat = stat( WP_PLUGIN_DIR . '/' . $pageFlip->plugin_dir . '/' . basename( $pageFlip->component ) );
		    if($stat[7] != 123291) $this->state = 2;
		}
		//----trial version----
	}

	//загрузка xml'ника в php4
	function get_xml_php4( $file )
	{
		// открытие xml-файла
		$xml = @domxml_open_file( $file );

		if( !$xml )
		{
			$this->state = 0;
			return false;
		}

		// получение корневого элемента
		$root = $xml->document_element();
		// получение массива вложеннцых тэгов (потомков)
		$nodes = $root->child_nodes();

		foreach( $nodes as $node )
		{

		  if( substr( $node->node_name(), 0, 1 ) == "#" ) continue;

		  if( $node->node_name() == "pages" )
		  {
		     $pages = $node->child_nodes();
		     $id = 0;

			 foreach( $pages as $page )
		     {
		     	if( substr( $page->node_name(),0,1 ) == "#" ) continue;

		     	$this->pages[$id] = new Page( trim( $page->get_content() ), $id, trim( $page->get_attribute('name') ) );

				//$result['pages']["$id"] = trim( $page->get_content() );
		     	//$result['pages']['name']["$id"] = trim( $page->get_attribute('name') );

		        $id++;
		     }
		     continue;
		  }

		  if( $node->node_name() == "album" )
		  {
		  	$this->album->getInfo( $node );
		  	continue;
		  }

		  $key = $node->node_name();

		  $this->$key = trim( $node->get_content() );
		  //$result["$key"] = trim( $node->get_content() );
		}
	}

	//загрузка xml'ника в php5
	function get_xml_php5( $file )
	{
		global $pageFlip;

		$config = @join( '', file( $file ) );
        $xml = @simplexml_load_string( '<?xml version="1.0" encoding="utf-8" standalone="yes"?>' . $config );

        if( !$xml )
		{
			$this->state = 0;
			return false;
		}

        foreach( $xml as $key=>$value )
        {
        	if( $key == 'pages' || $key == 'album' ) continue;
        	$this->$key = trim( $value );
			//$result["$key"] = trim( $value );
        }

        $id = 0;
		foreach( $xml->pages->page as $value )
        {
        	$this->pages[$id] = new Page( trim( $value ), $id, trim( $value['name'] ), trim($value['zoomURL']) );
			$id++;
			//$result["pages"][] = ;
        	//$result["pages"]["name"][] = ;
        }

		if( isset( $xml->album ) )
			$this->album->getInfo( $xml->album );
	}

	function save( $albumXML = '' )
	{
		global $pageFlip;

		$xml = $this->create_xml( $albumXML );
        //записываем в файл
        $xml_file = $pageFlip->plugin_path . $pageFlip->booksDir . '/' . $this->id . '.xml';

		$config_file = fopen( $xml_file, "w+" );

		if( !fwrite( $config_file, $xml ) ) return false;

		fclose( $config_file );

        return true;
	}

	//функция, создающая xml'ник
	function create_xml( $albumXML = '' )
	{
		global $pageFlip;

		//$this->flipSound = $pageFlip->plugin_url . $pageFlip->soundsDir . '/'. $this->flipSound;

		$xml = '<FlippingBook>' . "\n";

        foreach( $this->properties as $property )
 			$xml .= '	<' . $property . '>' . $this->$property . '</' . $property . '>' . "\n";

		$xml .= '	<pages>' . "\n";

		foreach( $this->pages as $id => $page )
		  $xml .= '		<page name="' . $page->name . '" ' .
			 					 'zoomURL="' . $page->zoomURL . '" ' .
								 'zoomType="' . $page->zoomType . '" ' .
								 'target="' . $page->target . '" ' .
								 'zoomHeight="' . $page->zoomHeight . '" ' .
								 'zoomWidth="' . $page->zoomWidth . '"' .
								'>' . $page->image . '</page>' . "\n";

		$xml .= '	</pages>' . "\n";

		if ( empty($this->album) )
			$this->album = new Album( $this->id, ceil( $this->width / 2 ), $this->height );

		if( empty( $albumXML ) ) $xml .= $this->album->asXML();
		else $xml .= $albumXML;

		$xml .=	'</FlippingBook>';

        return $xml;
	}

	function refreshPages()
	{
		$this->countPages = count( $this->pages );

		$pages = array();
		$rec = 0;

		for( $i = 0; $i < $this->countPages; $i++ )
		{
		  foreach( $this->pages as $id => $page )
			 if( (int)$page->number - $rec === (int)$i )
			 {
			 	$pages[$i] = $page;
			 	unset( $this->pages[$id] );
				break;
			 }

		  if( empty( $pages[$i] ) )
		  {
			 	$rec++;
			 	$i--;
		  }
		}

	 	$this->pages = $pages;
	}

	function deletePage( $number )
	{
		global $pageFlip;

		unset( $this->pages[$number] );

		$this->refreshPages();
	}
}
?>