<?php
require_once("../includes/initialize.php");
if($session->is_logged_in()) {
	redirect_to("member");
}
$filename = basename(__FILE__);
$title    = "پارس کلیک - ورود به سایت";
$username = "";
$password = "";
$errors   = "";
if(isset($_POST["submit"])) { // if form submitted
	if(request_is_post()) {
		if($session->csrf_token_is_valid() && $session->csrf_token_is_recent()) {
			$username = trim($_POST["username"]);
			$password = trim($_POST["password"]);
			if(has_presence($username) && has_presence($password)) {
				// check the database to see if username or password exist
				$found_user = Member::authenticate($username, $password);
				if($found_user) {
					$session->login($found_user);
					redirect_to("member");
				} else {
					$errors = "اسم کاربری یا پسورد درست نیست!";
				}
			} else {
				$errors = "اسم کاربری یا پسورد خالی نمی توانند باشند.!";
			}
		} else {
			$errors = "شناسه CSRF معتبر نیست!";
			$session->die_on_csrf_token_failure();
		}
	} else {
		$errors = "درخواست معتبر نیست!";
	}
} else { // form has not been submitted
}
?>
<?php include_layout_template("header.php"); ?>
<?php include "_/components/php/nav.php"; ?>
<?php echo output_message($message, $errors); ?>
	<section class="main col-sm-12 col-md-8 col-lg-8">
		<?php include "_/components/php/article-login.php"; ?>
	</section>
	<section class="sidebar col-sm-12 col-md-4 col-lg-4">
		<?php include "_/components/php/aside-register.php"; ?>
	</section>
<?php include_layout_template("footer.php"); ?>