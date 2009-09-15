<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

class Page
{
	var $image,
		$number,
		$name,
		$zoomURL,
		$zoomType = 'type4',
		$target = 'type1',
		$zoomHeight = 600,
		$zoomWidth = 800;

	function Page( $image, $number, $name, $zoomURL = '' )
	{
		if (empty($zoomURL)) $zoomURL = $image;

		$this->image = (string)$image;
		$this->number = (int)$number;
		$this->name = (string)$name;
		$this->zoomURL = (string)$zoomURL;
	}

	function writeText( $text, $style = array('left'=>0, 'top'=>0, 'fontFamily'=>'Helvetica', 'fontSize'=>14, 'color'=>'000000'), $zoom = false )
	{
		global $pageFlip;

		$style2 = $style;

		$imageFile = str_replace(get_option('siteurl').'/', ABSPATH, $this->image);
		list($imageWidth, $imageHeight) = $imageSize = $pageFlip->functions->getImageSize($imageFile);

		$style2['left'] = intval( $imageWidth * $style2['left'] / $style2['pageWidth'] );
		$style2['top'] = intval( $imageHeight * $style2['top'] / $style2['pageHeight'] );
		$style2['fontSize'] = $style2['fontSize'] * $imageHeight / $style2['pageHeight'];

		$imageFile = $pageFlip->functions->imgWriteText($imageFile, $text, $style2, true);
		$this->image = str_replace(ABSPATH, get_option('siteurl').'/', $imageFile);


		$zoomImageFile = str_replace(get_option('siteurl').'/', ABSPATH, $this->zoomURL);
		if ($zoomImageFile != $imageFile)
		{
			$style2 = $style;

			$imageFile = $zoomImageFile;
			list($imageWidth, $imageHeight) = $imageSize = $pageFlip->functions->getImageSize($imageFile);

			$style2['left'] = intval( $imageWidth * $style2['left'] / $style2['pageWidth'] );
			$style2['top'] = intval( $imageHeight * $style2['top'] / $style2['pageHeight'] );
			$style2['fontSize'] = $style2['fontSize'] * $imageHeight / $style2['pageHeight'];

			$imageFile = $pageFlip->functions->imgWriteText($imageFile, $text, $style2, true);
			$this->zoomURL = str_replace(ABSPATH, get_option('siteurl').'/', $imageFile);
		}
	}
}
?>