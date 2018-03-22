<?php
require(__DIR__ . '/common.php');

$HTTPMethod = $_SERVER['REQUEST_METHOD'];

if (!in_array($HTTPMethod, array('GET', 'POST', 'HEAD', 'PUT', 'DELETE', 'OPTIONS'))) {
    exit('Unsupport HTTP method');
}
if ($Config['WebsitePath']) {
    $WebsitePathPosition = strpos($RequestURI, $Config['WebsitePath']);
    if ($WebsitePathPosition !== 0) {
        exit('WebsitePath Error!');
    } else {
        $ShortRequestURI = substr($RequestURI, strlen($Config['WebsitePath']));
    }
} else {
    $ShortRequestURI = $RequestURI;
}


//是否找不到指定的路径的指示 indicate
$NotFound = true;
$HTTPParameters = array();
if (in_array($HTTPMethod, array('PUT', 'DELETE', 'OPTIONS'))) {
    parse_str(file_get_contents('php://input'), $HTTPParameters);
}
$Routes = array(
    'GET' => array(),
    'POST' => array(),
    'HEAD' => array(),
    'PUT' => array(),
    'DELETE' => array(),
    'OPTIONS' => array()
);

//Support HTTP Method: GET / POST / PUT / DELETE / OPTIONS
//这里是Routes Start

$Routes['GET']['/'] = 'home';
$Routes['POST']['/'] = 'home'; //Delete later
$Routes['GET']['/dashboard'] = 'dashboard';
$Routes['POST']['/dashboard'] = 'dashboard';
$Routes['GET']['/favorites(/page/(?<page>[0-9]+))?'] = 'favorites';
$Routes['GET']['/forgot'] = 'forgot';
$Routes['POST']['/forgot'] = 'forgot';
$Routes['GET']['/goto/(?<topic_id>[0-9]+)-(?<post_id>[0-9]+)'] = 'goto';
$Routes['GET']['/inbox/(?<inbox_id>.*?)/list(/page/(?<page>[0-9]*))?'] = 'inbox_show';
$Routes['GET']['/inbox/(?<inbox_id>.*?)'] = 'inbox_view';
$Routes['POST']['/inbox/(?<inbox_id>.*?)'] = 'inbox_create';
$Routes['DELETE']['/inbox/(?<inbox_id>.*?)/delete/(?<message_id>[0-9]+)'] = 'inbox_delete';
$Routes['POST']['/json/(?<action>[0-9a-z_\-]+)'] = 'json';//前面为什么可以加 <action> ??? ,变为一个 key ？？？
$Routes['GET']['/json/(?<action>[0-9a-z_\-]+)'] = 'json';
$Routes['GET']['/login'] = 'login';
$Routes['POST']['/login'] = 'login';
$Routes['POST']['/manage'] = 'manage';
$Routes['GET']['/new'] = 'new';
$Routes['POST']['/new'] = 'new';
$Routes['GET']['/notifications/list'] = 'notifications_list';
$Routes['POST']['/notifications'] = 'notifications'; //Delete later
$Routes['GET']['/notifications/(?<type>.*?)(/page/(?<page>[0-9]*))?'] = 'notifications';
$Routes['GET']['/oauth-(?<app_id>[0-9]+)'] = 'oauth';
$Routes['POST']['/oauth-(?<app_id>[0-9]+)'] = 'oauth';
$Routes['GET']['/page/(?<page>[0-9]+)'] = 'home';
$Routes['POST']['/page/(?<page>[0-9]+)'] = 'home'; //Delete later
$Routes['GET']['/recycle-bin(/page/(?<page>[0-9]+))?'] = 'recycle_bin';
$Routes['GET']['/register'] = 'register';
$Routes['POST']['/register'] = 'register';
$Routes['GET']['/reply'] = 'reply';
$Routes['POST']['/reply'] = 'reply';
$Routes['GET']['/reset_password/(?<access_token>.*?)'] = 'reset_password';
$Routes['POST']['/reset_password/(?<access_token>.*?)'] = 'reset_password';
$Routes['GET']['/robots.txt'] = 'robots';
$Routes['GET']['/search.xml'] = 'open_search';
$Routes['GET']['/search/(?<keyword>.*?)(/page/(?<page>[0-9]*))?'] = 'search';
$Routes['GET']['/settings'] = 'settings';
$Routes['POST']['/settings'] = 'settings';
$Routes['GET']['/sitemap-(?<action>topics|pages|tags|users|index)(-(?<page>[0-9]+))?.xml'] = 'sitemap';
$Routes['GET']['/statistics'] = 'statistics';
$Routes['GET']['/t/(?<id>[0-9]+)(-(?<page>[0-9]*))?'] = 'topic';
$Routes['POST']['/t/(?<id>[0-9]+)(-(?<page>[0-9]*))?'] = 'topic'; //Delete later
$Routes['GET']['/tag/(?<name>.*?)(/page/(?<page>[0-9]*))?'] = 'tag';
$Routes['GET']['/tags/following(/page/(?<page>[0-9]*))?'] = 'favorite_tags';
$Routes['GET']['/tags(/page/(?<page>[0-9]*))?'] = 'tags';
$Routes['GET']['/u/(?<username>.*?)'] = 'user';
$Routes['GET']['/users/following(/page/(?<page>[0-9]*))?'] = 'favorite_users';
$Routes['GET']['/upload_controller'] = 'upload';
$Routes['POST']['/upload_controller'] = 'upload';
$Routes['GET']['/redirect-(?<view>desktop|mobile)'] = 'redirect';

//这里是Routes End
$UrlPath = 'home';
$ParametersVariableName = '_' . $HTTPMethod;


foreach ($Routes[$HTTPMethod] as $URL => $Controller) {

    if (preg_match("#^" . $URL . "$#i", $ShortRequestURI, $Parameters)) {
        //$Parameters 会把匹配到的参数保存下来，保存的形式为数组 Array .

        /**
         *
         * 如请求：http://carbon2.com/page/1
         * $HTTPMethod 为 GET
         * 最终匹配到的 $URL 为 /page/(?<page>[0-9]+) ，$Parameters 为 /page/1,page=>1,1=>1
         */
        $NotFound = false;
        $Parameters = array_merge($Parameters, $HTTPParameters);

        /**
         *
         * 把路径中所带有的参数，放到固定的 _GET,_POST 等等的变量 和 _REQUEST 中.
         * 如这里 http://carbon2.com/page/1 为 _GET['page'] = 1 ,_REQUEST['page'] = 1
         */
        foreach ($Parameters as $Key => $Value) {
            if (!is_int($Key)) {
                // _GET['username'] = 'tian'
                ${$ParametersVariableName}[$Key] = urldecode($Value);
                $_REQUEST[$Key] = urldecode($Value);
            }
        }
        $UrlPath = $Controller;
        break;
    }
}


if ($NotFound === true) {
    require(__DIR__ . '/404.php');
    exit();
}


//如果是有设置了另外的 mobile 服务器的域名.
if ($Config['MobileDomainName'] && $_SERVER['HTTP_HOST'] != $Config['MobileDomainName'] && $CurView == 'mobile' && !$IsApp && $UrlPath != 'view') {
    //如果是手机，则跳转到移动版
    header("HTTP/1.1 302 Moved Temporarily");
    header("Status: 302 Moved Temporarily");
    header('Location: ' . $CurProtocol . $Config['MobileDomainName'] . $RequestURI);
    exit();
}


require(__DIR__ . '/controller/' . $UrlPath . '.php');