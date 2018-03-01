CHDDATA
===============

## plugins

* 阿里大鱼短信插件：已过时，待修复。
* Qrcode：
  ```
  //使用方法,123456是二维码内容。
  plugin_action('Qrcode/Qrcode/generate', ['123456']);
  //指定存放路径
  plugin_action('Qrcode/Qrcode/generate', ['123456', APP_PATH.'test.png']);
  //在模板中调用
  <img src="%7B%3Aplugin_url('Qrcode/Qrcode/generate',%20%5B'text'%20=" alt="generate', ['text' => 123])}" />
  //新建方法调用
  public function qrcode($text = ‘’)
  {
  		plugin_action(‘Qrcode/Qrcode/generate’, [$text])；
  	}
  ```
* Excel: Excel导入导出插件
  
  > 导出教程
  > http://bbs.dolphinphp.com/?/article/16
  >
  > 导入教程
  > http://bbs.dolphinphp.com/?/question/126

* 待添加:定时任务插件 http://www.dolphinphp.com/module/vNwk5YamCg7zloDO.html

