<?php
require(LanguagePath . 'home.php');//导入语言包
$Page      = intval(Request('Request', 'page'));//获得路径上面传过来的当前想要请求的页码
$TotalPage = ceil($Config['NumTopics'] / $Config['TopicsPerPage']);//用在 DB Config 中保存的 总主题数/每页的主题数.

//如果 Page =1 而且 不是 APP 的时候,就 重定向,把 page/1 隐藏掉.
if (($Page < 0 || $Page == 1) && !$IsApp) 
	Redirect();

//如果当前的页面大于总的页面,就跳转到最后的一页.
if ($Page > $TotalPage) 
	Redirect('page/' . $TotalPage);
if ($Page == 0)
	$Page = 1;


//加载 topics
$TopicsArray = array();
if ($MCache && $Page == 1) {
	$TopicsArray = $MCache->get(MemCachePrefix . 'Homepage');
}
if (!$TopicsArray) {

    //因为 SQL 中的  Limit offset 数据量一大，就会变得很慢,所以这里只查询 10条数据就用 Limit offset
    // https://www.jianshu.com/p/efecd0b66c55
	if ($Page <= 10) {
	    // index 的作用 : http://www.cnblogs.com/cfang/archive/2013/05/22/3092596.html
        //
        // Limit: limit 的用法是 limit [offset], [rows]，其中 offset 表示偏移值， rows 表示需要返回的数据行。
		$TopicsArray = $DB->query('SELECT `ID`, `Topic`, `Tags`, `UserID`, `UserName`, `LastName`, `LastTime`, `Replies` 
			FROM ' . PREFIX . 'topics force index(LastTime) 
			WHERE IsDel=0 
			ORDER BY LastTime DESC 
			LIMIT ' . ($Page - 1) * $Config['TopicsPerPage'] . ',' . $Config['TopicsPerPage']);
		if ($MCache && $Page == 1) {
			$MCache->set(MemCachePrefix . 'Homepage', $TopicsArray, 600);
		}
	} else {
	    //1.先取得要取得数据的那一页的第一条 topics 的时间，
        //2.然后再取少于那个时间的 TopicsPerPage 条数据
		$TopicsArray = $DB->query('SELECT `ID`, `Topic`, `Tags`, `UserID`, `UserName`, `LastName`, `LastTime`, `Replies` 
			FROM ' . PREFIX . 'topics force index(LastTime) 
			WHERE LastTime<=(SELECT LastTime 
					FROM ' . PREFIX . 'topics force index(LastTime) 
					WHERE IsDel=0 
					ORDER BY LastTime DESC 
					LIMIT ' . ($Page - 1) * $Config['TopicsPerPage'] . ', 1) 
				and IsDel=0 
			ORDER BY LastTime DESC 
			LIMIT ' . $Config['TopicsPerPage']);
	}
}
$DB->CloseConnection();
$PageTitle = $Page > 1 ? ' Page' . $Page . '-' : '';
$PageTitle .= $Config['SiteName'];
$PageMetaDesc = htmlspecialchars(mb_substr($Config['SiteDesc'], 0, 150, 'utf-8'));
$ContentFile  = $TemplatePath . 'home.php';// 取得 topics 列表 布局文件的路径.
include($TemplatePath . 'layout.php');