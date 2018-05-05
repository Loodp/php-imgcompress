# php-imgcompress

### php 图片压缩

```php
$res = (new imgcompress($source))
        ->setMaxWidth(1000)     //设置最大宽度（等比缩放）
        ->setName(name)         //设置图片名
        ->setPath(path)         //目标路径（可配置）
        ->show()                //展示图片（不保存）
        ->handle();             //执行
 ```
 
 超出设定最大宽度后按照设定最大宽度等比缩放