<?php
/**
 * Created by PhpStorm.
 * User: tianzeng
 * Date: 2018/1/9
 * Time: 15:57
 */

//echo phpinfo();

echo __DIR__;

echo "<hr/>";

$server = $_SERVER['REQUEST_METHOD'];

echo $server;

echo "<hr/>";

$Page      = intval(" 5");

var_dump($Page);

echo '<hr/>';

$isInclude = @include __DIR__ . '/config.php';

var_dump($isInclude);

echo '<hr/>';

$myModules = apache_get_modules();
//var_dump($myModules);
echo '<hr/>';

//$filename = '/path/to/data-file';
//$file = fopen($filename, 'r')
//or die("unable to open file ($filename)");

echo '<hr/>';

var_dump((__FILE__));
echo '<hr/>';
var_dump(dirname(__FILE__));

echo '<hr/>';
var_dump(is_writable(dirname(dirname(__FILE__))));
echo '<hr/>';

var_dump($_SERVER['HTTP_ACCEPT_LANGUAGE']);

echo '<hr/>';
preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $LangParse);
var_dump($LangParse);

echo "<hr/>";

if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    // break up string into pieces (languages and q factors)
    preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $LangParse);
    if (count($LangParse[1])) {
        // create a list like "en" => 0.8
        // $UserLanguages = array_combine($LangParse[1], $LangParse[4]);
        foreach ($LangParse[1] as $Key => $Value) {
            $UserLanguages[strtolower($LangParse[1][$Key])] = $LangParse[4][$Key];
        }
        // set default to 1 for any without q factor
        foreach ($UserLanguages as $Lang => $Val) {
            if ($Val === '')
                $UserLanguages[strtotime($Lang)] = 1;
        }
        // sort list based on value
        arsort($UserLanguages, SORT_NUMERIC);
    }
}
var_dump($UserLanguages);

echo "<hr/>";

$WebsitePath = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
var_dump($_SERVER['SCRIPT_NAME']);

echo "<hr/>";

//Redirect('localhost');

echo "<hr/>";
/*<p><label><input type="text" name="VerifyCode" class="w100" onfocus="document.getElementById('Verification_Code_Img').src='<?php echo $Config['WebsitePath']; ?>/seccode.php';document.getElementById('Verification_Code_Img').style.display='inline';" value="" placeholder="<?php echo $Lang['Verification_Code']; ?>" /></label>*/
/*				<img src="" id="Verification_Code_Img" style="cursor: pointer;display:none;" onclick="this.src+=''" alt="<?php echo $Lang['Verification_Code']; ?>" align="middle" /></p>*/

echo "<hr/>";

$DoNotNeedOriginalPassword = (stripos($CurUserInfo['Password'], 'zzz')===0);

var_dump($DoNotNeedOriginalPassword);

echo "<hr/>";

//var_dump($_SERVER['HTTP_CLIENT_IP']);
var_dump($_SERVER['REQUEST_TIME']);

echo "<hr/>";

function VersionCompare($Version, $OldVersion)
{
    $VersionArray = array_map("intval", explode('.', $Version));
    ;
    $OldVersionArray = array_map("intval", explode('.', $OldVersion));
    $NeedToUpdate    = false;
    foreach ($VersionArray as $Key => $Value) {
        var_dump('key:'.$Key);
        var_dump('value:'.$Value);

        if ($VersionArray[$Key] != $OldVersionArray[$Key]) {
            var_dump($VersionArray[$Key]);

            if ($VersionArray[$Key] > $OldVersionArray[$Key]) {
                $NeedToUpdate = true;
            }
            break;
        }

        echo "<br/>";
    }
    return $NeedToUpdate;
}

VersionCompare("5.9.0",'5.8.0');

echo "<hr/>";

var_dump($_SERVER['PHP_SELF']);


echo "<hr/>";

var_dump($_SERVER['HTTP_HOST']);

echo "<hr/>";

var_dump($_SERVER['HTTP_USER_AGENT']);


echo "<hr/>";

echo strtotime("now"), "\n";
echo strtotime("10 September 2000"), "\n";
echo strtotime("+1 day"), "\n";
echo strtotime("+1 week"), "\n";
echo strtotime("+1 week 2 days 4 hours 2 seconds"), "\n";
echo strtotime("next Thursday"), "\n";
echo strtotime("last Monday"), "\n";


echo "<hr/>";
var_dump($_SERVER['QUERY_STRING']);


echo "<hr/>";

var_dump($_SERVER['HTTP_X_REQUESTED_WITH']);


echo "<hr/>";

var_dump($_SERVER['HTTPS']);

echo "<hr/>";

var_dump(time());

echo "<hr/>";

