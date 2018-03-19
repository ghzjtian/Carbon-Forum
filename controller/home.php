<?php
require(LanguagePath . 'home.php');//导入语言包
$Page      = intval(Request('Request', 'page'));//获得路径上面传过来的
$TotalPage = ceil($Config['NumTopics'] / $Config['TopicsPerPage']);
if (($Page < 0 || $Page == 1) && !$IsApp) 
	Redirect();
if ($Page > $TotalPage) 
	Redirect('page/' . $TotalPage);
if ($Page == 0)
	$Page = 1;
$TopicsArray = array();
if ($MCache && $Page == 1) {
	$TopicsArray = $MCache->get(MemCachePrefix . 'Homepage');
}
if (!$TopicsArray) {
	if ($Page <= 10) {
	    // http://www.cnblogs.com/cfang/archive/2013/05/22/3092596.html
        // index 的作用
		$TopicsArray = $DB->query('SELECT `ID`, `Topic`, `Tags`, `UserID`, `UserName`, `LastName`, `LastTime`, `Replies` 
			FROM ' . PREFIX . 'topics force index(LastTime) 
			WHERE IsDel=0 
			ORDER BY LastTime DESC 
			LIMIT ' . ($Page - 1) * $Config['TopicsPerPage'] . ',' . $Config['TopicsPerPage']);
		if ($MCache && $Page == 1) {
			$MCache->set(MemCachePrefix . 'Homepage', $TopicsArray, 600);
		}
	} else {
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
$ContentFile  = $TemplatePath . 'home.php';//导入  布局文件.
include($TemplatePath . 'layout.php');