<?php
//当用户点击了忘记密码邮件中的重设密码后，跳转到此页.
require(LanguagePath . 'reset_password.php');
$AccessToken      = base64_decode(Request('Request', 'access_token'));
$AccessTokenArray = $AccessToken ? explode('|', $AccessToken) : false;
$Message          = '';
if (count($AccessTokenArray) === 3) {
	$UserName            = $AccessTokenArray[0];
	$TokenExpirationTime = intval($AccessTokenArray[1]);
	$Token               = $AccessTokenArray[2];
} else {
	AlertMsg('Bad Request', 'Bad Request', 400);
}

//验证 时间是否已经过期了 或者 时间被恶意写入很大的值.
if ($TokenExpirationTime < $TimeStamp || $TokenExpirationTime >= ($TimeStamp + 7200)) {
	AlertMsg($Lang['Page_Has_Expired'], $Lang['Page_Has_Expired']);
}
$UserInfo = array();
$UserInfo = $DB->row('SELECT * FROM ' . PREFIX . 'users Where UserName=:UserName', array(
	'UserName' => $UserName
));
//验证该用户是否不存在
if (!$UserInfo) {
	AlertMsg('404 Not Found', '404 Not Found', 404);
} else {
    //验证 Token 是否正确.
    $correctToken = md5($UserInfo['Password'] . $UserInfo['Salt'] . md5($TokenExpirationTime) . md5(SALT));
	if (HashEquals($correctToken, $Token)) {

	    //在 重置密码页，填写了相关的资料并且提交后
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			if (!ReferCheck(Request('Post', 'FormHash'))) {
				AlertMsg($Lang['Error_Unknown_Referer'], $Lang['Error_Unknown_Referer'], 403);
			}
			//重设密码
			$Password   = Request('Post', 'Password');
			$Password2  = Request('Post', 'Password2');
			$VerifyCode = intval(Request('Post', 'VerifyCode'));
			
			if ($Password && $Password2 && $VerifyCode) {
				if ($Password === $Password2) {
					session_start();
					if (isset($_SESSION[PREFIX . 'VerificationCode']) && $VerifyCode === intval($_SESSION[PREFIX . 'VerificationCode'])) {
						
						$NewSalt         = $UserInfo['Salt'];
						$NewPasswordHash = md5(md5($Password) . $NewSalt);
						if (UpdateUserInfo(array(
							'Salt' => $NewSalt,
							'Password' => $NewPasswordHash
						), $UserInfo['ID'])) {
							$TemporaryUserExpirationTime = 30 * 86400 + $TimeStamp; //默认保持30天登陆状态
							SetCookies(array(
								'UserExpirationTime' => $TemporaryUserExpirationTime,
								'UserCode' => md5($NewPasswordHash . $NewSalt . $TemporaryUserExpirationTime . SALT)
							), 30);
							$CurUserInfo['Salt']     = $NewSalt;
							$CurUserInfo['Password'] = $NewPasswordHash;
							AlertMsg($Lang['Reset_Password_Success'], $Lang['Reset_Password_Success']);
						} else {
							AlertMsg($Lang['Reset_Password_Failure'], $Lang['Reset_Password_Failure']);
						}
						
					} else {
						$Message = $Lang['VerificationCode_Error'];
					}
					unset($_SESSION[PREFIX . 'VerificationCode']);
				} else {
					$Message = $Lang['Passwords_Inconsistent'];
				}
			} else {
				$Message = $Lang['Forms_Can_Not_Be_Empty'];
			}
		}
	} else {
		AlertMsg('Bad Request', 'Bad Request', 400);
	}
}

$DB->CloseConnection();
$PageTitle   = $Lang['Reset_Password'];
$ContentFile = $TemplatePath . 'reset_password.php';
include($TemplatePath . 'layout.php');