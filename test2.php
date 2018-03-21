<!DOCTYPE html>
<html>
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<body>

<h2>The Button Element</h2>>
<!--这样跳转，$_SERVER['HTTP_REFERER'] 就会显示前一个跳转过来的 地址. !!!-->
<a href="http://myserver.com/" class="w3-button w3-black">Link Button</a>
</body>
</html>




<?php
/**
 * Created by PhpStorm.
 * User: tianzeng
 * Date: 2018/3/20
 * Time: 16:35
 *
 * 测试 $_SERVER['HTTP_REFERER'] 的效果.
 * 直接由 header
 */

//http://carbon2.com/test2.php

//直接用 header 跳转，  $_SERVER['HTTP_REFERER'] 会为 null !!!
//header("location:http://myserver.com/");

?>