<?php

/**
 * @param $class_name String will get the class name for each PHP class and finds the file name associate to it
 */
function __autoload($class_name) {
	$class_name = strtolower($class_name);
	$path       = LIB_PATH . DS . $class_name . "php";
	if(file_exists($path)) {
		require_once($path);
	} else {
		die("The file {$class_name}.php could not be found!");
	}
}

/**
 * @param null $location is by default NULL which will redirect the article to a particular location
 */
function redirect_to($location = NULL) {
	if($location != NULL) {
		header("Location: " . $location);
		exit;
	}
}

/**
 * @param string $message string shows the messages
 * @param string $errors  string shows the errors
 * @return string
 */
function output_message($message = "", $errors = "") {
	if(! empty($message)) {
		$output = "<div class='alert alert-success alert-dismissible' role='alert'>";
		$output .= "<button type='button' class='close' data-dismiss='alert'>";
		$output .= "<span aria-hidden='true'>&times;</span>";
		$output .= "<span class='sr-only'></span>";
		$output .= "</button>";
		$output .= "<i class='fa fa-check-circle-o fa-fw fa-lg'></i> ";
		$output .= "<strong>" . htmlentities($message) . "</strong>";
		$output .= "</div>";
		return $output;
	} elseif(! empty($errors)) {
		$output = "<div class='animated flash alert alert-danger alert-dismissible' role='alert'>";
		$output .= "<button type='button' class='close' data-dismiss='alert'>";
		$output .= "<span aria-hidden='true'>&times;</span>";
		$output .= "<span class='sr-only'></span>";
		$output .= "</button>";
		$output .= "<i class='fa fa-times-circle-o fa-fw fa-lg'></i> ";
		$output .= "<strong>" . htmlentities($errors) . "</strong>";
		$output .= "</div>";
		return $output;
	} else {
		return "";
	}
}

/**
 * @param string $template will replace the associate layout for footer or header inside includes folder
 */
function include_layout_template($template = "") {
	include(LIB_PATH . DS . 'layouts' . DS . $template);
}

/**
 * validate value has presence
 * @param $value        string uses trim() so empty spaces don't count
 *                      use === to avoid false positives
 *                      empty() would consider "0" to be empty
 * @return bool true or false
 */
function has_presence($value) {
	$trimmed_value = trim($value);
	return isset($trimmed_value) && $trimmed_value !== "";
}

/**
 * @param       $value   string validate value has string length
 * @param array $options leading and trailing spaces will count
 * @return bool options: exact, max, min
 *                       has_length($first_name, ['exact' => 20])
 *                       has_length($first_name, ['min' => 5, 'max' => 100])
 */
function has_length($value, $options = []) {
	if(isset($options['max']) && (strlen($value) > (int)$options['max'])) {
		return FALSE;
	}
	if(isset($options['min']) && (strlen($value) < (int)$options['min'])) {
		return FALSE;
	}
	if(isset($options['exact']) && (strlen($value) != (int)$options['exact'])) {
		return FALSE;
	}
	return TRUE;
}

/**
 * Example:
 * has_format_matching('1234', '/\d{4}/') is true
 * has_format_matching('12345', '/\d{4}/') is also true
 * has_format_matching('12345', '/\A\d{4}\Z/') is false
 * @param        $value string has a format matching
 * @param string $regex regular expression
 *                      Be sure to use anchor expressions to match start and end of string.
 *                      (Use \A and \Z, not ^ and $ which allow line returns.)
 * @return int
 */
function has_format_matching($value, $regex = '//') {
	return preg_match($regex, $value);
}

/** validate value is a number
 * @param       $value   string so use is_numeric instead of is_int
 * @param array $options : max, min
 * @return bool has_number($items_to_order, ['min' => 1, 'max' => 5])
 */
function has_number($value, $options = []) {
	if(!is_numeric($value)) {
		return FALSE;
	}
	if(isset($options['max']) && ($value > (int)$options['max'])) {
		return FALSE;
	}
	if(isset($options['min']) && ($value < (int)$options['min'])) {
		return FALSE;
	}
	return TRUE;
}

/**
 * validate value is included in a set
 * @param       $value
 * @param array $set
 * @return bool
 */
function has_inclusion_in($value, $set = []) {
	return in_array($value, $set);
}

/**
 * validate value is excluded from a set
 * @param       $value
 * @param array $set
 * @return bool
 */
function has_exclusion_from($value, $set = []) {
	return !in_array($value, $set);
}

/**
 * @param string $marked_string is the marked string and the date you need to pas in which first removes the marked
 *                              zeros, then removes any remaining marks.
 * @return mixed the clean date output
 */
function strip_zeros_from_date($marked_string = "") {
	$no_zeros       = str_replace('*0', '', $marked_string);
	$cleaned_string = str_replace('*', '', $no_zeros);
	return $cleaned_string;
}

/**
 * @param string $datetime will get the date and time as a simple text
 * @return string ready format to insert into MySQL
 */
function datetime_to_text($datetime = "") {
	$unixdatetime = strtotime($datetime);
	return strftime("%B %d, %Y at %I:%M %p", $unixdatetime);
}

/**
 * @param        $action  string represents the login or logout action for each user
 * @param string $message represent the message for every user
 */
function log_action($action, $message = "") {
	$logfile = SITE_ROOT . DS . 'logs' . DS . 'log.txt';
	$new     = file_exists($logfile) ? FALSE : TRUE;
	if($handle = fopen($logfile, 'a')) { //appends
		$timestamp = datetime_to_text(strftime("%Y-%m-%d %H:%M:%S", time()));
		$content   = "{$timestamp} | {$action}: {$message}\n";
		fwrite($handle, $content);
		fclose($handle);
		if($new) {
			chmod($logfile, 0777);
		}
	} else {
		echo "Could not open log file for writing";
	}
}

/**
 * Function for super admins to show the subjects and articles
 * @param $subject_array array gets the subject ID form URL and return it as an array
 * @param $article_array array gets the article ID form URL and return it as an array
 * @return string subjects as an HTML ordered list along with articles as an HTML unordered list
 */
function admin_articles($subject_array, $article_array) {
	$output      = "<ol>";
	$subject_set = Subject::find_all(FALSE);
	foreach($subject_set as $subject) {
		$output .= "<li>";
		$output .= "<div class='lead'>";
		$output .= "<a href='admin_articles.php?subject=";
		$output .= urlencode($subject->id) . "'";
		if($subject_array && $subject->id == $subject_array->id) {
			$output .= " class='selected'";
		}
		$output .= ">";
		if(!empty($subject->name)) {
			$output .= htmlentities(ucwords($subject->name));
		} else {
			$output .= htmlentities("(موضوع اسم ندارد)");
		}
		$output .= "</a>";
		if(!$subject->visible) {
			$output .= "&nbsp;<i class='text-danger fa fa-eye-slash'></i>";
		} else {
			$output .= "&nbsp;<i class='text-success fa fa-eye'></i>";
		}
		$output .= "</div>";
		$article_set = Article::find_articles_for_subject($subject->id, FALSE);
		$output .= "<ul>";
		foreach($article_set as $article) {
			$output .= "<li>";
			$output .= "<a href='admin_articles.php?subject=";
			$output .= urlencode($subject->id) . "&article=";
			$output .= $article->id . "'";
			if($article_array && $article->id == $article_array->id) {
				$output .= " class='selected'";
			}
			$output .= ">";
			if(!empty($article->name)) {
				$output .= htmlentities(ucwords($article->name));
			} else {
				$output .= htmlentities("(مقاله اسم ندارد)");
			}
			$output .= "</a>";
			if(!$article->visible) {
				$output .= "&nbsp;<i class='text-danger fa fa-eye-slash'></i>";
			}
			$output .= "</li>";
		}
		$output .= "</ul></li>";
	}
	$output .= "</ol>";
	return $output;
}

/**
 * Function for authors to show the subjects and articles
 * @param $subject_array array gets the subject ID form URL and return it as an array
 * @param $article_array array gets the article ID form URL and return it as an array
 * @return string subjects as an HTML ordered list along with articles as an HTML unordered list
 */
function author_articles($subject_array, $article_array) {
	$output      = "<ol>";
	$subject_set = Subject::find_all(TRUE);
	foreach($subject_set as $subject):
		$output .= "<li>";
		$output .= "<div class='lead'>";
		$output .= "<a href='author_articles.php?subject=";
		$output .= urlencode($subject->id) . "'";
		if($subject_array && $subject->id == $subject_array->id) $output .= " class='selected'";
		$output .= ">";
		if(!empty($subject->name)) $output .= htmlentities(ucwords($subject->name)); else
			$output .= htmlentities("(موضوع اسم ندارد)");
		$output .= "</a>";
		$output .= "</div>";
		$article_set = Article::find_articles_for_subject($subject->id, FALSE);
		$output .= "<ul>";
		foreach($article_set as $article):
			$output .= "<li>";
			$output .= "<a href='author_articles.php?subject=";
			$output .= urlencode($subject->id) . "&article=";
			$output .= $article->id . "'";
			if($article_array && $article->id == $article_array->id) $output .= " class='selected'";
			$output .= ">";
			if(!empty($article->name)) $output .= htmlentities(ucwords($article->name)); else
				$output .= htmlentities("(مقاله اسم ندارد)");
			$output .= "</a>";
			if(!$article->visible) //if visibility is FALSE
				$output .= " <i class='text-danger fa fa-eye-slash'></i>";
			$output .= "</li>";
		endforeach;
		$output .= "</ul></li>";
	endforeach;
	$output .= "</ol>";
	return $output;
}

/**
 * Function for members to show the subjects and articles. The difference between this function with administrators
 * functions are instead of all articles to be open for every subjects, the members actually have to click on subjects
 * in order for articles to be open underneath subjects and this happens once for every subject.
 * @param $subject_array array gets the subject ID form URL and return it as an array
 * @param $article_array array gets the article ID form URL and return it as an array
 * @return string subjects as an HTML ordered list along with articles as an HTML unordered list
 */
function member_articles($subject_array, $article_array) {
	$output      = "<ul class='list-group'>";
	$subject_set = Subject::find_all(TRUE);
	foreach($subject_set as $subject) {
		$output .= "<li class='list-group-item'>";
		$output .= "<span class='badge'>" . Article::count_articles_for_subject($subject->id, TRUE) . "</span>";
		$output .= "<a href='member-articles?subject=";
		$output .= urlencode($subject->id) . "'";
		if($subject_array && $subject->id == $subject_array->id) {
			$output .= " style='font-size:25px;' ";
		}
		$output .= ">";
		if(!empty($subject->name)) {
			$output .= htmlentities(ucwords($subject->name));
		} else {
			$output .= htmlentities("(موضوع اسم ندارد!)");
		}
		$output .= "</a>";
		if($subject_array && $article_array) {
			if($subject_array->id == $subject->id || $article_array->subject_id == $subject->id) {
				$article_set = Article::find_articles_for_subject($subject->id);
				$output .= "<ul>";
				foreach($article_set as $article) {
					$output .= "<li>";
					$output .= "<a href='member-articles?subject=";
					$output .= urlencode($subject->id) . "&article=";
					$output .= urlencode($article->id) . "'";
					if($article_array && $article->id == $article_array->id) {
						$output .= " class='selected'";
					}
					$output .= ">";
					if(!empty($article->name)) {
						$output .= htmlentities(ucwords($article->name));
					} else {
						$output .= htmlentities("(مقاله اسم ندارد!)");
					}
					$output .= "</a></li>";
				}
				$output .= "</ul>";
			}
		}
		$output .= "</li>";
	}
	$output .= "</ull>";
	return $output;
}

/**
 * Finds all articles for subjects
 * @param bool $public is a condition to select the first article (the default one) for every subject upon clicking on
 *                     subjects and by default is equals to FALSE.
 */
function find_selected_article($public = FALSE) {
	global $current_subject;
	global $current_article;
	if(isset($_GET["subject"]) && isset($_GET["article"])) {
		$current_subject = Subject::find_by_id($_GET["subject"], $public);
		$current_article = Article::find_by_id($_GET["article"], $public);
	} elseif(isset($_GET["subject"])) {
		$current_subject = Subject::find_by_id($_GET["subject"], $public);
		if($current_subject && $public) {
			$current_article = Article::find_default_article_for_subject($current_subject->id);
		} else {
			$current_article = NULL;
		}
	} elseif(isset($_GET["article"])) {
		$current_article = Article::find_by_id($_GET["article"], $public);
		$current_subject = NULL;
	} else {
		$current_subject = NULL;
		$current_article = NULL;
	}
}

/**
 * Function for super admins to show the categories and courses
 * @param $category_array array gets the subject ID form URL and return it as an array
 * @param $course_array   array gets the article ID form URL and return it as an array
 * @return string categories as an HTML ordered list along with courses as an HTML unordered list
 */
function admin_courses($category_array, $course_array) {
	$output       = "<ol>";
	$category_set = Category::find_all(FALSE);
	foreach($category_set as $category) {
		$output .= "<li>";
		$output .= "<div class='lead'>";
		$output .= "<a href='admin_courses.php?category=";
		$output .= urlencode($category->id) . "'";
		if($category_array && $category->id == $category_array->id) {
			$output .= " class='selected'";
		}
		$output .= ">";
		if(!empty($category->name)) {
			$output .= htmlentities(ucwords($category->name));
		} else {
			$output .= htmlentities("(موضوع اسم ندارد)");
		}
		$output .= "</a>";
		if(!$category->visible) {
			$output .= "&nbsp;<i class='text-danger fa fa-eye-slash'></i>";
		} else {
			$output .= "&nbsp;<i class='text-success fa fa-eye'></i>";
		}
		$output .= "</div>";
		$course_set = Course::find_courses_for_category($category->id, FALSE);
		$output .= "<ul>";
		foreach($course_set as $course) {
			$output .= "<li>";
			$output .= "<a href='admin_courses.php?category=";
			$output .= urlencode($category->id) . "&course=";
			$output .= $course->id . "'";
			if($course_array && $course->id == $course_array->id) {
				$output .= " class='selected'";
			}
			$output .= ">";
			if(!empty($course->name)) {
				$output .= htmlentities(ucwords($course->name));
			} else {
				$output .= htmlentities("(درس اسم ندارد)");
			}
			$output .= "</a>";
			if(!$course->visible) {
				$output .= "&nbsp;<i class='text-danger fa fa-eye-slash'></i>";
			}
			$output .= "</li>";
		}
		$output .= "</ul></li>";
	}
	$output .= "</ol>";
	return $output;
}

/**
 * Function for authors to show the categories and courses
 * @param $category_array array gets the category ID form URL and return it as an array
 * @param $course_array   array gets the course ID form URL and return it as an array
 * @return string categories as an HTML ordered list along with courses as an HTML unordered list
 */
function author_courses($category_array, $course_array) {
	$output       = "<ol>";
	$category_set = Category::find_all(TRUE);
	foreach($category_set as $category):
		$output .= "<li>";
		$output .= "<div class='lead'>";
		$output .= "<a href='author_courses.php?category=";
		$output .= urlencode($category->id) . "'";
		if($category_array && $category->id == $category_array->id) $output .= " class='selected'";
		$output .= ">";
		if(!empty($category->name)) $output .= htmlentities(ucwords($category->name)); else
			$output .= htmlentities("(no category title)");
		$output .= "</a>";
		$output .= "</div>";
		$course_set = Course::find_courses_for_category($category->id, FALSE);
		$output .= "<ul>";
		foreach($course_set as $course):
			$output .= "<li>";
			$output .= "<a href='author_courses.php?category=";
			$output .= urlencode($category->id) . "&course=";
			$output .= $course->id . "'";
			if($course_array && $course->id == $course_array->id) $output .= " class='selected'";
			$output .= ">";
			if(!empty($course->name)) $output .= htmlentities(ucwords($course->name)); else
				$output .= htmlentities("(no course title)");
			$output .= "</a>";
			if(!$course->visible) //if visibility is FALSE
				$output .= "&nbsp;<i class='text-danger fa fa-eye-slash'></i>";
			$output .= "</li>";
		endforeach;
		$output .= "</ul></li>";
	endforeach;
	$output .= "</ol>";
	return $output;
}

/**
 * Function for members to show the categories and courses. The difference between this function with administrators
 * functions are instead of all courses to be open for every categories, the members actually have to click on
 * categories in order for courses to be open underneath categories and this happens once for every category.
 * @param $category_array array gets the category ID form URL and return it as an array
 * @param $course_array   array gets the course ID form URL and return it as an array
 * @return string categories as an HTML ordered list along with courses as an HTML unordered list
 */
function member_courses($category_array, $course_array) {
	$output       = "<ul class='list-group'>";
	$category_set = Category::find_all(TRUE);
	foreach($category_set as $category) {
		$output .= "<li class='list-group-item'>";
		$output .= "<span class='badge'>" . Course::count_courses_for_category($category->id, TRUE) . "</span>";
		$output .= "<a href='member-courses?category=";
		$output .= urlencode($category->id) . "'";
		if($category_array && $category->id == $category_array->id) {
			$output .= " style='font-size:25px;' ";
		}
		$output .= ">";
		if(!empty($category->name)) {
			$output .= htmlentities(ucwords($category->name));
		} else {
			$output .= htmlentities("(موضوع اسم ندارد!)");
		}
		$output .= "</a>";
		if($category_array && $course_array) {
			if($category_array->id == $category->id || $course_array->category_id == $category->id) {
				$course_set = Course::find_courses_for_category($category->id);
				$output .= "<ul>";
				foreach($course_set as $course) {
					$output .= "<li>";
					$output .= "<a href='member-courses?category=";
					$output .= urlencode($category->id) . "&course=";
					$output .= urlencode($course->id) . "'";
					if($course_array && $course->id == $course_array->id) {
						$output .= " class='selected'";
					}
					$output .= ">";
					if(!empty($course->name)) {
						$output .= htmlentities(ucwords($course->name));
					} else {
						$output .= htmlentities("(درس اسم ندارد!)");
					}
					$output .= "</a></li>";
				}
				$output .= "</ul>";
			}
		}
		$output .= "</li>";
	}
	$output .= "</ul>";
	return $output;
}

/**
 * Function for public to show the categories and courses
 * @return string categories as an HTML ordered list along with courses as an HTML unordered list
 */
function public_courses() {
	$output       = "<ol>";
	$category_set = Category::find_all(TRUE);
	foreach($category_set as $category) {
		$output .= "<li>";
		$output .= "<div class='lead'>";
		if(!empty($category->name)) {
			$output .= htmlentities(ucwords($category->name));
		} else {
			$output .= htmlentities("موضوع اسم ندارد");
		}
		$output .= "</div>";
		$course_set = Course::find_courses_for_category($category->id, TRUE);
		$output .= "<ul>";
		foreach($course_set as $course) {
			$output .= "<li>";
			$output .= "<a target='_blank' data-toggle='tooltip' data-placement='left' title='برو به یوتیوب' href='https://www.youtube.com/playlist?list=";
			$output .= $course->youtubePlaylist;
			$output .= "'>";
			if(!empty($course->name)) {
				$output .= htmlentities(ucwords($course->name));
			} else {
				$output .= htmlentities("درس اسم ندارد");
			}
			$output .= "</a></li>";
		}
		$output .= "</ul></li>";
	}
	$output .= "</ol>";
	return $output;
}

/**
 * Finds all courses for categories
 * @param bool $public is a condition to select the first course (the default one) for every category upon clicking on
 *                     categories and by default is equals to FALSE.
 */
function find_selected_course($public = FALSE) {
	global $current_category;
	global $current_course;
	if(isset($_GET["category"]) && isset($_GET["course"])) {
		$current_category = Category::find_by_id($_GET["category"], $public);
		$current_course   = Course::find_by_id($_GET["course"], $public);
	} elseif(isset($_GET["category"])) {
		$current_category = Category::find_by_id($_GET["category"], $public);
		if($current_category && $public) {
			$current_course = Course::find_default_course_for_category($current_category->id);
		} else {
			$current_course = NULL;
		}
	} elseif(isset($_GET["course"])) {
		$current_course   = Course::find_by_id($_GET["course"], $public);
		$current_category = NULL;
	} else {
		$current_category = NULL;
		$current_course   = NULL;
	}
}

/**
 * This function will simply check if the parameters given are identical or not
 * @param $id         integer to compare
 * @param $session_id integer to compare
 * @return bool return TRUE if two values are identical
 */
function check_ownership($id, $session_id) {
	if($id === $session_id) {
		return TRUE;
	} else {
		return FALSE;
	}
}

/**
 * @param $size integer parameter getting the size as bytes
 * @return string format for size
 */
function check_size($size) {
	if($size > 1024000) {
		return round($size / 1024000) . " MB";
	} elseif($size > 1024) {
		return round($size / 1024) . " KB";
	} else {
		return $size . " bytes";
	}
}

/**
 * @param        $string string text to truncate
 * @param        $length integer length to truncate from the string
 * @param string $dots   string default (...) to show immediately after the string
 * @return string from 0 character to length and ... after it
 */
function truncate($string, $length, $dots = " (برای ادامه کلیک کنید ...) ") {
	return (strlen($string) > $length) ? substr($string, 0, $length - strlen($dots)) . $dots : $string;
}

/**
 * This function adds the active class by jQuery for the navbar by checking the file name.
 * There is <?php $filename = basename(__FILE__); ?> on top of every PHP file which finds the file name and based on
 * that name jQuery adds the active class for the particular menu.
 */
function active() {
	global $filename;
	if(($filename == "index.php") || ($filename == "member.php") || ($filename == "admin.php") || ($filename == "author.php")
	) {
		echo "<script>$(\"a:contains('خانه')\").parent().addClass('active');</script>";
	} elseif($filename == "authors.php") {
		echo "<script>$(\"a:contains('نویسندگان')\").parent().addClass('active');</script>";
	} elseif($filename == "about.php") {
		echo "<script>$(\"a:contains('درباره ما')\").parent().addClass('active');</script>";
	} elseif($filename == "courses.php") {
		echo "<script>$(\"a:contains('درس ها')\").parent().addClass('active');</script>";
	} elseif($filename == "faq.php") {
		echo "<script>$(\"a:contains('سوالات شما')\").parent().addClass('active');</script>";
	} elseif($filename == "help.php") {
		echo "<script>$(\"a:contains('کمک به ما')\").parent().addClass('active');</script>";
	} elseif(($filename == "login.php") || ($filename == "register.php") || ($filename == "forgot.php") ||
	         ($filename == "reset-password.php") || ($filename == "forgot-username.php")
	) {
		echo "<script>$(\"a:contains('ورود')\").parent().addClass('active');</script>";
	} elseif(($filename == "admin_courses.php") || ($filename == "admin_articles.php") || ($filename == "new_subject.php") || ($filename == "author_articles.php") || ($filename == "author_courses.php") || ($filename == "new_courses.php") || ($filename == "edit_courses.php") || ($filename == "new_article.php") || ($filename == "edit_article.php") || ($filename == "author_edit_article.php") || ($filename == "new_course.php") || ($filename == "author_edit_course.php") || ($filename == "author_add_video.php") || ($filename == "author_edit_video_description.php") || ($filename == "edit_video_description.php") || ($filename == "admin_comments.php") || ($filename == "edit_course.php")
	) {
		echo "<script>$(\"a:contains('محتوی')\").parent().addClass('active');</script>";
	} elseif(($filename == "member-profile.php") || ($filename == "member-edit-profile.php") ||
	         ($filename == "author_profile.php") || ($filename == "author_edit_profile.php")
	) {
		echo "<script>$(\"a:contains('حساب کاربری')\").parent().addClass('active');</script>";
	} elseif(($filename == "member-courses.php") || ($filename == "member-articles.php")) {
		echo "<script>$(\"a:contains('محتوی')\").parent().addClass('active');</script>";
	} elseif($filename == "member-playlist.php") {
		echo "<script>$(\"a:contains('لیست پخش')\").parent().addClass('active');</script>";
	} elseif(($filename == "member_list.php") || ($filename == "edit_member.php") || ($filename == "new_member.php")) {
		echo "<script>$(\"a:contains('لیست اعضا')\").parent().addClass('active');</script>";
	} elseif(($filename == "admin_list.php") || ($filename == "author_list.php") || ($filename == "new_admin.php") || ($filename == "new_author.php") || ($filename == "edit_admin.php") || ($filename == "edit_author.php")
	) {
		echo "<script>$(\"a:contains('لیست کارکنان')\").parent().addClass('active');</script>";
	} elseif(($filename == "contact.php")) {
		echo "<script>$(\"a:contains('تماس با ما')\").parent().addClass('active');</script>";
	}
}