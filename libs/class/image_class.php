<?php
/**
 *  image_class.php 图片操作类
 *
 * @copyright			(C) 2015-2016 PHP300
 * @license				http://framework.php300.cn
 * @lastmodify			2016-07-02
 */
class Image_class extends System_class{

    public $image;
    private $CreateImage;

    public function __construct() {
        
    }
    
    /**
	*获取图片 
	* @return 设置的图片
	*/
    function getImage() {
        return $this->image;
    }
    
    /**
	* 设置图片
	* @param 图片路径 $image
	* @return
	*/
    function setImage($image) {
        $this->image = $image;

        /* 检查扩展后缀 */
        $FullPath = pathinfo($this->image);
        switch ($FullPath['extension']) {
            case 'jpg':
            case 'jpeg':
                $this->CreateImage = imagecreatefromjpeg($this->image);
                break;
            case 'gif':
                $this->CreateImage = imagecreatefromgif($this->image);
                break;
            case 'png':
                $this->CreateImage = imagecreatefrompng($this->image);
                break;
            default:
                $this->CreateImage = false;
                break;
        }
    }
    
    /**
	* 上传图片
	* @param 接收的文件 $file
	* @param 文件最大值 $maxSize
	* @param 对象名称 $Target_name
	* @param 对象路径 $target_path
	* @param input名称 $uploadName
	* 
	* @return
	*/
    public function upload($file, $maxSize = 4325720, $Target_name = null, $target_path = '', $uploadName = 'file') {
        /* Check if file is empty or not */

        if (!empty($file)) {
            if ($file[$uploadName]['error'] == 4) {
                throw new Exception('file cannot be empty');
            }

            /* Check if the file is an array */
            if (!is_array($file)) {
                throw new Exception('File gotta be an array');
            }

            $Mime = array(
                'image/jpeg',
                'image/gif',
                'image/png'
            );
            
            if (!in_array($file[$uploadName]['type'], $Mime)) {
                throw new Exception("File type isnt supported");
            }
            if ($file[$uploadName]['size'] > $maxSize) {
                throw new Exception("File is too big");
            }
            
            if ($Target_name == null) {
                $Target_name = $file[$uploadName]['name'];
            }

            if (move_uploaded_file($file[$uploadName]['tmp_name'], $target_path . $Target_name)) {
                return $target_path . $Target_name;
            } else {
                return 'failed to upload';
            }
        }
    }
    
    /**
	* 返回图片的高 
	* @return Height
	*/
    public function Height() {
    	
    	 $size = getimagesize($this->image);
        $Height = $size[1];

        return $Height;
    }
    
    /**
	* 返回图片的宽
	* @return Width
	*/
    public function Width() {
    	
    	  $size = getimagesize($this->image);
    	  
        $Width = $size[0];

        return $Width;
    }
    
    /**
	*返回图片宽X高 
	* @return Imagesize
	*/
    public function Imagesize() {
    	
        $dimension = getimagesize($this->image);

        $dimension = $dimension[0] . ' x ' . $dimension[1];
        return $dimension;
    }
    
    /**
	*返回图片后缀 
	* @return Extension
	*/
    public function Extension() {
    	
        $FullPath = pathinfo($this->image);
        $Extension = $FullPath['extension'];

        return $Extension;
    }
    
    /**
	* 返回图片mime
	* @return mime
	*/
    public function mime() {
    	
    	 $size = getimagesize($this->image);
        $mime = $size['mime'];

        return $mime;
    }
    
    /**
	* 调整图片宽高
	* @param 宽 $NewWidth
	* @param 高 $NewHeight
	* @return Boolean
	*/
    public function resize($NewWidth = null, $NewHeight = null) {
    	
        list($width, $height) = getimagesize($this->image);

        if ($NewHeight == null AND $NewWidth == null) {
            throw new Exception('Cannot resize without height or width');
        }

        if (!is_numeric($NewHeight)) {
            $NewHeight = $height;
        }

        if (!is_numeric($NewWidth)) {

            $NewWidth = $width;
        }

        $ImageResize = imagecreatetruecolor($NewWidth, $NewHeight);

        imagecopyresized($ImageResize, $this->CreateImage, 0, 0, 0, 0, $NewWidth, $NewHeight, $width, $height);
        $this->CreateImage = $ImageResize;

        return true;
    }
    
    /**
	* 滤镜调整
	* @param 像素 $pixel
	* @return Boolean
	*/
    public function pixelate($pixel = 1) {
        if (!is_numeric($pixel)) {
            throw new Exception('Has to be a number');
        }

        if ($pixel > 15) {
            throw new Exception('pixelate only support 1-15 levels');
        }
        
        imagefilter($this->CreateImage, IMG_FILTER_PIXELATE, $pixel);

        return true;
    }
    
    /**
	* 翻转
	* @param 翻转类型【v and h】 $flip
	* @return Boolean
	*/
    public function flip($flip = null) {

        if (!$flip) {
            throw new Exception('Require flip value');
        }

        if ($flip == 'h' OR $flip == 'v') {
            if ($flip == 'v') {
            	
                $flip = imageflip($this->CreateImage, IMG_FLIP_VERTICAL);
            }
            if ($flip == 'h') {
            	
                $flip = imageflip($this->CreateImage, IMG_FLIP_HORIZONTAL);
            }
        } else {
            throw new Exception('flip require value');
        }

        $this->image = $flip;
        return true;
    }
    
    /**
	* 模糊
	* @param 模糊度 $blur
	* @return Boolean
	*/
    public function blur($blur = 1) {

        if (!is_numeric($blur)) {
            throw new Exception('blur value has to be a number');
        }

        if ($blur > 20) {
            throw new Exception('Blur level effect only goes from 1-20');
        }
        for ($i = 1; $i < $blur; $i++) {
            imagefilter($this->CreateImage, IMG_FILTER_GAUSSIAN_BLUR);
        }
        return true;
    }
    
    /**
	* 亮度
	* @param 亮度值 $bright
	* @return Boolean
	*/
    public function brightness($bright = 0) {

        if (!is_numeric($bright)) {
            throw new Exception('Bright value gotta be a number');
        } else {
            imagefilter($this->CreateImage, IMG_FILTER_BRIGHTNESS, $bright);
        }
        return true;
    }
    
    /**
	* 对比
	* @param 对比值 $contrastVal
	* @return Boolean
	*/
    public function contrast($contrastVal = 0) {
        if ($contrastVal > 100 OR $contrastVal < -100) {
            throw new Exception('Contrast value is betwend -100 and 100');
        }

        if (!is_numeric($contrastVal)) {
            throw new Exception('Contrast gotta be a number');
        } else {
            imagefilter($this->CreateImage, IMG_FILTER_CONTRAST, $contrastVal);
        }
        return true;
    }
    
    /**
	* 灰色
	* @return Boolean
	*/
    public function grayscale() {

        imagefilter($this->CreateImage, IMG_FILTER_GRAYSCALE);
        return true;
    }

    public function gamma($gammaRatio = 1) {

        if (!is_numeric($gammaRatio)) {
            throw new Exception('gamma gotta be a number');
        }

        if ($gammaRatio > 100 OR $gammaRatio < -100) {
            throw new Exception('Gamma only goes between -100 and 100');
        }

        imagegammacorrect($this->CreateImage, 1.0, $gammaRatio);
        return true;
    }
    
    /**
	* 添加文字
	* @param 文字 $text
	* @param 字体 $font
	* @param 字体大小 $fontsize
	* @param 设置 $option
	* @param 颜色值 $rgb
	* @return Boolean
	*/
    public function AddText($text = null, $font = null, $fontsize = 13, $option = 'bottom-left', $rgb = '255,255,255') {
        $opacity = 0;
        $rotates = 0;
        $option = explode('-', $option);
        $Colors = explode(',', $rgb);
        if (count($Colors) > 3) {
            throw new Exception('Only 3 value is valid in rgb');
        }

        $bbox = imagettfbbox(0, 0, $font, $text);
        list($width, $height) = getimagesize($this->image);

        $text_width = $bbox[2] - $bbox[0];
        $text_height = $bbox[3] - $bbox[1];

        if (isset($option[2])) {
            if ($option[2] != null) {
                echo '1';
                if ($option[2] != 'watermark') {
                    throw new Exception('3rd value only for watermark');
                } elseif ($option[2] == 'watermark') {
                    $rotates = 45;
                    $opacity = 75;
                }
            }
        }

        if (empty($option[1])) {
            throw new Exception('Gotta fill second parameter for text position');
        } elseif ($option[1] != 'center' AND $option[1] != 'right' AND $option[1] != 'left') {

            throw new Exception('Wrong parameter for position Y');
        } else {

            if ($option[1] == 'left') {
                $y = ($text_width) + 20;
            } elseif ($option[1] == 'right') {
                $y = ($width) - ($text_width) - ($fontsize * 3);
            } elseif ($option[1] == 'center') {
                $y = ($width / 2) - ($text_width / 2);
            }
        }

        if ($option[0] != 'middle' AND $option[0] != 'bottom' AND $option[0] != 'top') {
            throw new Exception('Wrong parameter for position X');
        } else {

            if ($option[0] == 'middle') {
                $x = ($height / 2) - ($text_height / 2);
            } elseif ($option[0] == 'bottom') {
                $x = ($height / 1) - ($text_height / 2) - 25;
            } elseif ($option[0] == 'top') {
                $x = ($height / 6) - ($text_height / 6) - 5;
            }
        }

        if (!is_numeric($Colors[0]) OR ! is_numeric($Colors[1]) OR ! is_numeric($Colors[2])) {
            throw new Exception('has to be a rgb number');
        }

        $white = imagecolorallocatealpha($this->CreateImage, $Colors[0], $Colors[1], $Colors[2], $opacity);

        imagettftext($this->CreateImage, $fontsize, $rotates, $y, $x, $white, $font, $text);
        return true;
    }
    
    /**
	* 修剪
	* @param X坐标 $x
	* @param Y坐标 $y
	* @param 宽 $Target_Width
	* @param 高 $Target_Height
	* @return Boolean
	*/
    public function crop($x = 334, $y = 178, $Target_Width = 79, $Target_Height = 75) {

        if (!is_numeric($x) OR ! is_numeric($y) OR ! is_numeric($Target_Width) OR ! is_numeric($Target_Height)) {
            throw new Exception('Cordinates has to be numbers');
        }

        $ImageResize = imagecreatetruecolor($Target_Width, $Target_Height);

        imagecopyresampled($ImageResize, $this->CreateImage, 0, 0, $x, $y, $Target_Width, $Target_Height, $Target_Width, $Target_Height);
        $this->CreateImage = $ImageResize;

        return true;
    }
    
    /**
	* 保存
	* @param 名称 $name
	* @param 质量 $quality
	* @return Boolean
	*/
    public function save($name, $quality = 100) {
        if (empty($name)) {
            throw new Exception('Need a name for image');
        } else {
            if (!is_numeric($quality)) {
                throw new Exception('Quality gotta be a number');
            } else {
                $PathSave = pathinfo($name);
                $FullPath = pathinfo($this->image);
                if (!isset($PathSave['extension'])) {
                    switch ($FullPath['extension']) {
                        case 'jpg':
                        case 'jpeg':
                            imagejpeg($this->CreateImage, $name, $quality);
                            break;
                        case 'gif':
                            imagegif($this->CreateImage, $name, $quality);
                            break;
                        case 'png':
                            imagepng($this->CreateImage, $name, $quality);
                            break;
                        default:
                            $this->CreateImage = false;
                            break;
                    }
                } else {
                    switch ($PathSave['extension']) {
                        case 'jpg':
                        case 'jpeg':
                            imagejpeg($this->CreateImage, $name, $quality);
                            break;
                        case 'gif':
                            imagegif($this->CreateImage, $name, $quality);
                            break;
                        case 'png':
                            if ($quality > 9 OR $quality < 0) {
                                throw new Exception('Png decode compress level is betwend 0 and 9');
                            }
                            imagepng($this->CreateImage, $name, $quality);
                            break;
                        default:
                            $this->CreateImage = false;
                            break;
                    }
                }
            }
        }
        return true;
    }
    
    /**
	* 销毁 
	* @return Boolean
	*/
    public function destroy() {
        imagedestroy($this->CreateImage);
        return true;
    }

}
