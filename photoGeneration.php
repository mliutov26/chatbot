<?php
class ttfTextOnImage {  

	// Качество jpg по-умолчанияю
	public   $jpegQuality = 100;      
  
	// Каталог шрифтов
	public   $ttfFontDir   = 'ttf';  
  
	private $ttfFont    = false;
	private $ttfFontSize  = false;
	private $hImage      = false;
	private $hColor      = false;

	public function __construct($imagePath) {
    	if (!is_file($imagePath) || !list(,,$type) = @getimagesize($imagePath)) return false;
   	
    	switch ($type) {      
    		case 1:  $this->hImage = @imagecreatefromgif($imagePath);  break;
     		case 2:  $this->hImage = @imagecreatefromjpeg($imagePath);  break;
     		case 3:  $this->hImage = @imagecreatefrompng($imagePath);  break;        
    		default: $this->hImage = false;
    	}
    	
    	$width = 240;
    	$height = 240;
    	$orig_width = imagesx($this->hImage);
		$orig_height = imagesy($this->hImage);
	
		$startHeight = 0;
		$startWidth = 0;
		$startHeightShort = 0;
		$startWidthShort = 0;
		
		if (($orig_width >= $width) && ($orig_height >= $height)) {
			if ($orig_height > $orig_width) {
				$startHeight = ($orig_height - $orig_width) / 2;
				$orig_height = $orig_width;
			} else {
				$startWidth = ($orig_width - $orig_height) / 2;
				$orig_width = $orig_height;
			}
		} else {
			
			$startWidthShort = ($width - $orig_width) / 2;			
			$startHeightShort = ($width - $orig_height) / 2;
		if ($orig_width < 240) { $orig_width = 240; }
			if ($orig_height < 240) { $orig_height = 240; }
	
		}
		
		$new_image = imagecreatetruecolor($width, $height);
		
		imagecopyresized($new_image, $this->hImage,
			$startWidthShort, $startHeightShort, $startWidth, $startHeight,
			$width, $height,
			$orig_width, $orig_height);
			
		$white = imagecolorallocate($new_image, 255, 255, 255);
		imagefill($new_image, 0, 0, $white);
		
		$this->hImage = $new_image;
		
		$rectangle1 = imagecolorallocatealpha($this->hImage, 240, 240, 240, 35);
		imagefilledrectangle($this->hImage, 0, 0, 240, 46, $rectangle1);
		
		$rectangle2 = imagecolorallocatealpha($this->hImage, 240, 240, 240, 35);
		imagefilledrectangle($this->hImage, 0, 194, 240, 240, $rectangle2);
    	
	}
  
  	public function __destruct() {
    	if ($this->hImage) imagedestroy($this->hImage);
  	}
  
  	// Устанавливает шрифт 
  	public function setFont($font, $size = 14, $color = false, $alpha = false) {
    	if (!is_file($font) && !is_file($font = $this->ttfFontDir.'/'.$font))
    		return false;
    		
    	$this->ttfFont     = $font;
    	$this->ttfFontSize   = $size;
    
    	if ($color) $this->setColor($color, $alpha);
  	}
  
  	// Пишет текст 
  	public function writeText ($x, $y, $text, $angle = 0) {
    	if (!$this->ttfFont || !$this->hImage || !$this->hColor) return false;

		// Магия, так как на НИКе по другому не работает
		// $text = htmlentities(iconv('utf-u8','windows-1251',$text), ENT_COMPAT, "windows-1251" );
		
    	imagettftext(
      		$this->hImage, 
      		$this->ttfFontSize, $angle, $x, $y + $this->ttfFontSize, 
      		$this->hColor, $this->ttfFont, $text);  
  	}
  
  	// Форматирует текст (согласно текущему установленному шрифту), что бы он не вылезал за рамки ($bWidth, $bHeight)
   	// Убирает слишком длинные слова
	public function textFormat($bWidth, $bHeight, $text) {
    	// Если в строке есть длинные слова, разбиваем их на более короткие
    	// Разбиваем текст по строкам
    
    	$strings = explode("\n", preg_replace('!([^\s]{24})[^\s]!su', '\\1 ', str_replace(array("\r", "\t"),array("\n", ' '), $text)));        
        
    	$textOut   = array(0 => ''); 
    	$i = 0;
          
    	foreach ($strings as $str) {
      		// Уничтожаем совокупности пробелов, разбиваем по словам
      		$words = array_filter(explode(' ', $str)); 
      
      		foreach ($words as $word) {
        		// Какие параметры у текста в строке?
        		$sizes = imagettfbbox($this->ttfFontSize, 0, $this->ttfFont, $textOut[$i].$word.' ');  
        
        		// Если размер линии превышает заданный, принудительно перескакиваем на следующую строку. Иначе пишем на этой же строке
        		if ($sizes[2] > $bWidth) $textOut[++$i] = $word.' '; else $textOut[$i].= $word.' '; 
        
        		// Если вышли за границы текста по вертикали, то заканчиваем
        		if ($i*$this->ttfFontSize >= $bHeight) break(2);
      		}
      
      		// "Естественный" переход на новую строку 
      		$textOut[++$i] = ''; if ($i*$this->ttfFontSize >= $bHeight) break; 
    	}
    
    	return implode ("\n", $textOut);
  	}
  
  	// Устанваливет цвет вида #34dc12
  	public function setColor($color, $alpha = false) {
    	if (!$this->hImage) return false;
		list($r, $g, $b) = array_map('hexdec', str_split(ltrim($color, '#')));
		
		return $alpha === false ? 
      		$this->hColor = imagecolorallocate($this->hImage, $r+1, $g+1, $b+1) :
      		$this->hColor = imagecolorallocatealpha($this->hImage, $r+1, $g+1, $b+1, $alpha);    
  	}
  
  	// Выводит картинку в файл. Тип вывода определяется из расширения.
  	public function output ($target, $replace = true) {
    	if (is_file ($target) && !$replace) return false;
		$ext = strtolower(substr($target, strrpos($target, ".") + 1));  
		  
		switch ($ext) {
      		case "gif":        
        		imagegif ($this->hImage, $target);        
        		break;         
      		case "jpg" :
      		case "jpeg":
				imagejpeg($this->hImage, $target, $this->jpegQuality);        
				break;
			case "png":
        		imagepng($this->hImage, $target);
        		break;
			default: return false;
    	}
    	
    	return true;     
  	}
  	
}

function imageGeneration($inputLink, $outputFile, $ttfFile1, $ttfFile2, $text1, $text2, $text3, $fontColor) {

	if (!file_get_contents($inputLink)) {
		
		return false;
		
	} else {
		
		file_put_contents($outputFile, file_get_contents($inputLink));
		
		$ttfImg = new ttfTextOnImage($outputFile);
		
		$ttfImg->setFont($ttfFile1, '14', $fontColor, 0);
		
		if ($text1) {

			if (strlen($text1) > 20) {
				$ttfImg->writeText(10, 7, substr($text1, 0, 20));
				if (strlen($text1) > 40) {
					$ttfImg->writeText(10, 26, substr($text1, 20, 17).'...');
				} else {
					$ttfImg->writeText(10, 26, substr($text1, 20, 20));
				}
			} else {
				$ttfImg->writeText(10, 16, $text1);
			}
			//$ttfImg->writeText(10, 15, $text1);
		}
		
		$ttfImg->setFont($ttfFile2, '14', $fontColor, 0);
		
		if ($text2) {

			$text2_add = ' ₽';
			if ($text2 > 999999) { $text2_add = ' Млн ₽'; }
			if ($text2 > 999999999) { $text2_add = ' Млрд ₽'; }
			if ($text2 > 999999999999) { $text2_add = ' Трлн ₽'; }
			if ($text2 > 999999999999999) { $text2_add = ' Квадрлн ₽'; }
			if ($text2 > 999999) {
				while ($text2 > 999) {
					$text2 = (int)($text2 / 1000);
				}
			}

			$ttfImg->writeText(10, 208, number_format($text2, 0, '.', ' ').$text2_add);
		}

		if ($text3) { 

			$text3_add = ' л';
			if ($text3 > 999) { $text3_add = ' кл'; }
			if ($text3 > 999999) { $text3_add = ' Мл'; }
			if ($text3 > 999999999) { $text3_add = ' Гл'; }
			if ($text3 > 999999999999) { $text3_add = ' Тл'; }
			if ($text3 > 999999999999999) { $text3_add = ' Пл'; }
			if ($text3 > 999999999999999999) { $text3_add = ' Эл'; }
			if ($text3 > 999999999999999999999) { $text3_add = ' Зл'; }
			if ($text3 > 999999999999999999999999) { $text3_add = ' Ил'; }
			if ($text3 > 999) { 
				while ($text3 > 999) {
					$text3 = (int)($text3 / 1000);
				}
			}

			$ttfImg->writeText(160, 208, $text3.$text3_add);
		}
		
		$ttfImg->output($outputFile);
		return true;
	
	}
}