<?php
    
/* 
 	Copyright (C) Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Misc. shell helper functions
*/

require_once(dirname(__FILE__).'/cache.php');
require_once(dirname(__FILE__).'/../settings.php');
require_once(dirname(__FILE__).'/../utilities/log.php');

class Image {
	private static $saved_info = array();
	public static function convert($source, $destination) {
		Cache::lock('ConvertFile-'.$destination);
		
		$output = '';
		
		if (file_exists($source)) 
			exec("convert ".$source." -quality 90 -profile /usr/share/color/icc/sRGB.icm ".$destination, $output);
		
		if (!empty($output)) Log::error('Picture', print_r($output, true));
		
		Cache::unlock('ConvertFile-'.$destination);
	}
	
	public static function getInfo($image) {		
		if (!isset(Image::$saved_info[$image])) {
			try {
				$img = new imagick($image); 
			
				$size = $img->getImageGeometry(); 
				Image::$saved_info[$image] = $size;
			} catch (ImagickException $e) {
				return null;
			}
		}
		return Image::$saved_info[$image];
	}
	
	public static function evalEXIFRational($exif_focal_length) {
		$value = explode("/", $exif_focal_length);
		if (isset($value[0]) && isset($value[1])) {
			if (floatval($value[1]) == 0) return 0.0;
			return (floatval($value[0]) / floatval($value[1]));
		} else return floatval($exif_focal_length);
	}
	
	public static function evalEXIFDate($exif_date_time_original) {
		$value = explode(" ", $exif_date_time_original);
		if (isset($value[0]) && isset($value[1])) {
			$date = explode(":", $value[0]);
			$time = explode(":", $value[1]);
			if (isset($date[0]) && isset($date[1]) && isset($date[2]) && isset($time[0]) && isset($time[1]) && isset($time[2]))
				return mktime($time[0], $time[1], $time[2], $date[1], $date[2], $date[0]);
		}
		return 0;	
	}
	
	public static function getDisqualificationOverlay($user) {
		global $WEBSITE_LOCAL_PATH;
		global $DYNAMIC_PICTURES_LOCAL_PATH;
		global $DYNAMIC_PICTURES_PATH;
		
		$text = '<translate id="IMAGE_DISQUALIFICATION_OVERLAY">disqualified</translate>';
		$translated_text = I18N::translateHTML($user, $text);
		
		if (!file_exists($DYNAMIC_PICTURES_LOCAL_PATH.'disq_'.$translated_text.'.png')) {		
			$im = imagecreatetruecolor(128, 128);
			imagesavealpha($im, true);
			
			imagealphablending( $im, false );
			$col = imagecolorallocatealpha( $im, 0, 0, 0, 127 );
			imagefill($im, 0, 0, $col );
			imagealphablending( $im, true );
			
			$text_color = imagecolorallocate($im, 255, 0, 0);
			
			imagefttext($im, 12, 45, 20, 90, $text_color, $WEBSITE_LOCAL_PATH.'arialbd.ttf', $translated_text);
			
			$text_color2 = imagecolorallocate($im, 255, 255, 255);
			
			imagefttext($im, 12, 45, 40, 110, $text_color2, $WEBSITE_LOCAL_PATH.'arialbd.ttf', $translated_text);
			
			imagepng($im, $DYNAMIC_PICTURES_LOCAL_PATH.'disq_'.$translated_text.'.png');
			
			imagedestroy($im);
		}
		
		return $DYNAMIC_PICTURES_PATH.rawurlencode('disq_'.$translated_text.'.png');
	}
}

?>