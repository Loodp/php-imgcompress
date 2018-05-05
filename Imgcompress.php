<?php

/**
 * 图片处理类
 * xiang.li
 */
class imgcompress{
    // 图片资源地址
    private $src;
    //创建图片模板
    private $image;
    //图片信息
    private $imageInfo;
    //保存文件名(可空，为空时随机生成)
    private $name = '';
    //保存路径
    private $path = './';
    //最小宽度
    private $maxWidth = 0;
    //是否展示图片（展示时不进行保存）
    private $show = false;
    //输出图片信息
    private $picInfo;

    /**
     * imgcompress constructor.
     * @param $src  图片地址
     */
    public function __construct($src)
    {
        $this->src = $src;
    }

    /**
     * 设置图片名
     * @param string $name
     * @return $this
     */
    public function setName($name = '')
    {
        $this->name = $name;
        return $this;
    }

    /**
     * 设置保存路径
     * @param string $path
     * @return $this
     */
    public function setPath($path = '')
    {
        $this->path = $path;
        return $this;
    }

    /**
     * 设置最小宽度
     * @param int $width
     * @return $this
     */
    public function setMaxWidth( $width = 0 )
    {
        $this->maxWidth = $width;
        return $this;
    }

    /**
     *
     */
    public function show()
    {
        $this->show = true;
        return $this;
    }

    /**
     * 处理
     */
    public function handle()
    {
        if(!file_exists($this->src)){
            return false;
        }

        //文件类型判断
        $fileName = strrchr($this->src, '/');
        //可能存在脚本后缀攻击，文件名中不能携带 '.'
        if(substr_count($fileName, '.') > 1) return false;

        //是否支持的文件类型
        list($name, $type) = explode('.', $fileName);
        if(!in_array(strtolower($type), ['gif', 'jpg', 'jpeg', 'png', 'swf', 'swc', 'psd', 'tiff', 'bmp', 'iff', 'jp2', 'jpx', 'jb2', 'jpc', 'xbm', 'wbmp'])) return false;

        $this->open();
        if($this->show) $this->_showImage();
        else return $this->_saveImage();
        return true;
    }

    /**
     * 1、获取文件信息
     * 2、创建模板
     */
    private function open()
    {
        list($width, $height, $type, $attr) = getimagesize($this->src);
        $this->imageInfo = array(
            'width'=>$width,
            'height'=>$height,
            'type'=>image_type_to_extension($type,false),
            'attr'=>$attr
        );
        $fun = "imagecreatefrom".$this->imageInfo['type'];
        $this->image = $fun($this->src);
        $this->_thumpImage();
    }

    /**
     * 按宽度等比复制图片至载体
     */
    private function _thumpImage()
    {
        $this->maxWidth = $this->maxWidth == 0 ? $this->imageInfo['width'] : $this->maxWidth;
        $proportion = $this->imageInfo['width'] > $this->maxWidth ? $this->maxWidth / $this->imageInfo['width'] : 1;
        $this->picInfo['width'] = $this->imageInfo['width'] * $proportion;
        $this->picInfo['height'] = $this->imageInfo['height'] * $proportion;
        $image_thump = imagecreatetruecolor($this->picInfo['width'],$this->picInfo['height']);
        //将原图复制带图片载体上面，并且按照一定比例压缩,极大的保持了清晰度
        imagecopyresampled($image_thump,$this->image,0,0,0,0,$this->picInfo['width'],$this->picInfo['height'],$this->imageInfo['width'],$this->imageInfo['height']);
        imagedestroy($this->image);
        $this->image = $image_thump;
    }

    /**
     * 保存图片
     */
    public function _saveImage()
    {
        $name = $this->name == '' ? date('Ymd').$this->imageInfo['type'].rand(1000, 9999) : $this->name;

        //使用原图扩展名
        $dstName = $this->path.$name.'.'.$this->imageInfo['type'];

        $funcs = "image".$this->imageInfo['type'];
        $funcs($this->image,$dstName);

        $this->picInfo['type'] = $this->imageInfo['type'];
        $this->picInfo['byte'] = $this->getsize(filesize($dstName));
        return $this->picInfo;
    }

    /**
     * 输出图片
     */
    private function _showImage()
    {
        header('Content-Type: image/'.$this->imageInfo['type']);
        $funcs = "image".$this->imageInfo['type'];
        $funcs($this->image);
    }

    /**
     * 单位换算
     * @param $size
     * @param string $format
     * @param bool $showFormat
     * @return string
     */
    public function getsize($size, $format = 'KB', $showFormat = false) {
        $p = 0;
        if ($format == 'KB') {
            $p = 1;
        } elseif ($format == 'MB') {
            $p = 2;
        } elseif ($format == 'GB') {
            $p = 3;
        }
        $size /= pow(1024, $p);
        if($showFormat) return number_format($size, 3).$format ;
        else return number_format($size, 3) ;
    }

    /**
     * 销毁图片
     */
    public function __destruct(){
        if(!empty($this->image)){
            imagedestroy($this->image);
        }
    }
}

$source =  './123.jpeg';//原图片名称
$res = (new imgcompress($source))->setMaxWidth(1000)->handle();
var_dump($res);