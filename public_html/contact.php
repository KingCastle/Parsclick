<?php
require_once("../includes/initialize.php");
require_once("../includes/Recaptcha/autoload.php");
$filename = basename(__FILE__);
if($session->is_logged_in()) { $member = Member::find_by_id($session->id); }
$title   = "پارس کلیک - تماس با ما";
$errors  = "";
$message = "";
// reCAPTCHA supported 40+ languages listed here: https://developers.google.com/recaptcha/docs/language
$lang = 'fa';
if(isset($_POST["submit"])) {
	if(empty(RECAPTCHASITEKEY) || empty(RECAPTCHASECRETKEY)) {
		$errors = "کدهای تایید reCaptcha API خالی هستند. لطفا مدیر سایت را در جریان بگذارید.";
	} elseif(isset($_POST['g-recaptcha-response'])) {
		$recaptcha = new \ReCaptcha\ReCaptcha(RECAPTCHASECRETKEY);
		$resp      = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
		if($resp->isSuccess()) {
			$mail = new PHPMailer();
			$mail->IsSMTP();
			$mail->IsHTML(TRUE);
			$mail->CharSet    = 'UTF-8';
			$mail->Host       = SMTP;
			$mail->SMTPSecure = TLS;
			$mail->Port       = PORT;
			$mail->SMTPAuth   = TRUE;
			$mail->Username   = EMAILUSER;
			$mail->Password   = EMAILPASS;
			$mail->FromName   = $_POST["name"];
			$mail->From       = EMAILUSER;
			$mail->Subject    = "پرس و جو از " . $_POST['name'];
			$mail->AddAddress("parsclickmail@gmail.com", DOMAIN);
			$content    = nl2br($_POST['message']);
			$mail->Body = email($_POST['name'], DOMAIN, $_POST['email'], $content);
			$result     = $mail->Send();
			if($result) {
				$message = "با تشکر، پیام شما فرستاده شد.";
			} else {
				$errors = "خطا در فرستادن پیام!";
			}
		} else {
			foreach($resp->getErrorCodes() as $code) {
				$errors = "لطفا ثابت کنید ربات نیستید!   کد خطا: {$code}";
			}
		}
	} // end: elseif(isset($_POST['g-recaptcha-response']))
} else {
} // end: if(isset($_POST["submit"]))
?>
<?php include_layout_template("header.php"); ?>
<?php include "_/components/php/nav.php"; ?>
<?php echo output_message($message, $errors); ?>
	<section class="main col-sm-12 col-md-8 col-lg-8">
		<article>
			<h2>تماس با ما</h2>
			<form class="contactus" action="contact" method="POST" role="form">
				<fieldset>
					<legend>لطفا از فرم زیر برای تماس با ما استفاده کنید.</legend>
					<div class="form-group">
						<label for="name">اسم کامل</label>
						<input type="text" name="name" class="form-control" id="name" placeholder="لطفا اسم خود را اینجا وارد کنید" required
						       value="<?php echo $session->is_logged_in() ? $member->full_name() : ''; ?>"/>
					</div>
					<br/>
					<div class="form-group">
						<label for="email">ایمیل</label>
						<input type="text" name="email" class="form-control arial edit" id="email" placeholder="email" required
						       value="<?php echo $session->is_logged_in() ? $member->email : ''; ?>"/>
					</div>
					<br/><br/><br/>
					<div class="form-group">
						<label for="message">پیغام</label>
						<textarea class="form-control" name="message" id="message" rows="9" placeholder="لطفا پیام کوتاه خود را اینجا وارد کنید" required></textarea>
					</div>
					<br/>
					<!--reCaptcha-->
					<div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHASITEKEY; ?>"></div>
					<script type="text/javascript" src="https://www.google.com/recaptcha/api.js?hl=<?php echo $lang; ?>"></script>
					<!--End of reCaptcha-->
					<div class="form-group">
						<button type="submit" id="contactbtn" name="submit" class="btn btn-primary" data-loading-text="در حال ارسال <i class='fa fa-spinner fa-pulse'></i>">
							بفرست
						</button>
					</div>
				</fieldset>
			</form>
		</article>
	</section>
	<section class="sidebar col-sm-12 col-md-4 col-lg-4">
		<aside>
			<h2><i class="fa fa-info-circle"></i> اطلاعات</h2>
			<div class="well">
				لطفا از این فرم برای تماس با من استفاده کنید. سعی می کنم به اکثریت جواب بدم اگر جوابی نگرفتید به
				خاطردلایلی هست که تو صفحه <a href="faq" title="FAQ">سوالات شما</a> مطرح شده.
			</div>
			<div class="well">
				لطفا سعی کنید مختصر و مفید پیام دهید. ایمیل ها اگر بیشتر از ۲ هفته جواب داده نشد یعنی شما سوالی پرسیدید
				که یا جوابش یا دلیل جواب ندادن ما در صفحه ی <a href="faq" title="FAQ">سوالات شما</a> داده شده.
			</div>
			<div class="well">
				لطفا این رو به خاطر بسپارید ما تعداد ایمیل بالایی دریافت میکنیم اما ایمیل ها همه به نوبت خوانده خواهند
				شد و به مرور زمان جواب داده خواهند شد. برخی از ویدئوهای ما از منابع متعددی تهیه شده مثل سایتهای آموزشی و
				کتابها، اما همه ی اونها دونه دونه وقت گذاشته شده وساخته شده و اکثر اونها با برنامه ریزی طولانی مدت ساخته
				شده. بنابراین اگر موضوع تماس شما این هست که به ما منبع یادآوری کنید، وقت خودتون و ما رو تلف نکنید.
			</div>
			ممنون از همگی
		</aside>
	</section>
<?php include_layout_template("footer.php"); ?>