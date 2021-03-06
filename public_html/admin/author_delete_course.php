<?php require_once('../../includes/initialize.php');
$session->confirm_author_logged_in();
if (empty($_GET['course'])) {
	$session->message('شناسه درس پیدا نشد!');
	redirect_to('author_courses.php');
}
$course   = Course::find_by_id($_GET['course'], FALSE);
$category = Category::find_by_id($_GET['category'], FALSE);
$result   = $course->delete();
if ($result) {
	$session->message("درس {$course->name} با موفقیت حذف شد.");
	redirect_to("author_courses.php?category={$category->id}&course={$course->id}");
} else {
	$session->message('درس حذف نشد.');
	redirect_to("edit_courses.php?category={$category->id}&course={$course->id}");
}
if (isset($database)) $database->close_connection();