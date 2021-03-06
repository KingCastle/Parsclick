<?php require_once('../includes/initialize.php');
$session->confirm_logged_in();
$member = Member::find_by_id($session->id);
if ( ! $member) {
	$session->message('شناسه کاربری پیدا نشد!');
	redirect_to('member-profile');
}
$result = $member->delete();
if ( ! $result) {
	$session->message('حذف کاربر موفقیت آمیز نبود!');
	redirect_to('member-profile');
}
$session->logout();
$session->message('خوش گلدی!');
redirect_to('login');
if (isset($database)) $database->close_connection();