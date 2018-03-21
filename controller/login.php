<?php
require(LanguagePath . 'login.php');
$Error     = '';
$ErrorCode     = 101000;
$UserName  = '';

//$_SERVER['HTTP_REFERER'],用户上一个访问的地址.
$ReturnUrl = isset($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER["HTTP_REFERER"]) : '';

if ($CurUserCode && Request('Get', 'logout') == $CurUserCode) {
	LogOut();
	if ($ReturnUrl) {
		header('location: ' . $ReturnUrl);
		exit('logout');
	} else {
		Redirect('', 'logout');
	}
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' || $IsApp) {
    //检查表单是否正确
	if (!ReferCheck(Request('Post', 'FormHash'))) {
		AlertMsg($Lang['Error_Unknown_Referer'], $Lang['Error_Unknown_Referer'], 403);
	}
	$ReturnUrl  = htmlspecialchars(Request('Post', 'ReturnUrl'));
	$UserName   = strtolower(Request('Post', 'UserName'));
	$Password   = Request('Post', 'Password');
	$Expires    = min(intval(Request('Post', 'Expires', 30)), 30); //最多保持登陆30天
//    $verifyCode1 = isset($_POST['VerifyCode']) ? trim($_POST['VerifyCode']) : "nul";
	$VerifyCode = Request('Post', 'VerifyCode');//验证码
	do{
	    //验证必填的项是否为空.
		if (!$UserName || !$Password || !$VerifyCode) {
			$Error = $Lang['Forms_Can_Not_Be_Empty'];
			$ErrorCode     = 101001;
			break;
		}
        //验证验证码 .
		session_start();
		$TempVerificationCode = "";
		if (isset($_SESSION[PREFIX . 'VerificationCode'])) {
			$TempVerificationCode = intval($_SESSION[PREFIX . 'VerificationCode']);
			unset($_SESSION[PREFIX . 'VerificationCode']);
		} elseif (DEBUG_MODE === true) {
			$TempVerificationCode = 1234;
		} else {
			$Error = $Lang['Verification_Code_Error'];
			$ErrorCode     = 101002;
			break;
		}
		session_write_close();
		if (intval($VerifyCode) !== $TempVerificationCode) {
			$Error = $Lang['Verification_Code_Error'];
			$ErrorCode     = 101002;
			break;
		}

		//用户是否存在
		$DBUser = $DB->row("SELECT ID,UserName,Salt,Password,UserRoleID,UserMail,UserIntro FROM " . PREFIX . "users WHERE UserName = :UserName", array(
			"UserName" => $UserName
		));
		if (!$DBUser) {
			$Error = $Lang['User_Does_Not_Exist'];
			$ErrorCode     = 101003;
			break;
		}

		//检查密码是否正确.
		if (!HashEquals($DBUser['Password'], md5($Password . $DBUser['Salt']))) {
			$Error = $Lang['Password_Error'];
			$ErrorCode     = 101004;
			break;
		}

		UpdateUserInfo(array(
			'LastLoginTime' => $TimeStamp,
			'UserLastIP' => CurIP()
		), $DBUser['ID']);
		$TemporaryUserExpirationTime = $Expires * 86400 + $TimeStamp;

		if( !$IsApp ){
//		    登录完成后，就保存 UserID,ExpirationTime,UserCode 到 Cookies.
			SetCookies(array(
				'UserID' => $DBUser['ID'],
				'UserExpirationTime' => $TemporaryUserExpirationTime,
				'UserCode' => md5($DBUser['Password'] . $DBUser['Salt'] . $TemporaryUserExpirationTime . SALT)
			), $Expires);
			//重定向到前一个访问的页面.
			if ( $ReturnUrl ) {
				header('location: ' . $ReturnUrl);
				exit('logined');
			} else {
				Redirect('', 'logined');
			}
		}
	}while(false);//可以保证代码在某个地方及时退出，而不执行后面的代码
}

$DB->CloseConnection();
// 页面变量
$PageTitle   = $Lang['Log_In'];
$ContentFile = $TemplatePath . 'login.php';
include($TemplatePath . 'layout.php');