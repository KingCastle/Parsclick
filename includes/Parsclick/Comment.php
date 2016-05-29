<?php //namespace Parsclick; 

class Comment extends DatabaseObject
{
	protected static $table_name = 'comments';
	protected static $db_fields  = ['id', 'member_id', 'course_id', 'created', 'body'];
	public           $id;
	public           $member_id;
	public           $course_id;
	public           $created;
	public           $body;

	/**
	 * @param int $course_id gets the course ID
	 * @return mixed number of comments for the course
	 */
	public static function count_comments_for_course($course_id = 0)
	{
		global $database;
		$sql = 'SELECT COUNT(*) FROM ' . self::$table_name;
		$sql .= ' WHERE course_id = ' . $database->escape_value($course_id);
		$result_set = $database->query($sql);
		$row        = $database->fetch_assoc($result_set);

		return array_shift($row);
	}

	/**
	 * @param        $member_id int gets the member ID
	 * @param        $course_id int gets the course ID
	 * @param string $body      gets the message body
	 * @return bool|\Comment TRUE if comment inserted into database and FALSE if not
	 */
	public static function make($member_id, $course_id, $body = '')
	{
		if ( ! empty($member_id) && ! empty($course_id) && ! empty($body)) {
			$comment            = new Comment();
			$comment->id        = (int) '';
			$comment->member_id = (int) $member_id;
			$comment->course_id = (int) $course_id;
			$comment->created   = strftime('%Y-%m-%d %H:%M:%S', time());
			$comment->body      = preg_replace('/`(.*?)`/', '<code>$1</code>', $body);

			return $comment;
		} else {
			return FALSE;
		}
	}

	/**
	 * @param int $course_id gets the course ID
	 * @return array of comments for the course
	 */
	public static function find_comments_for_course($course_id = 0)
	{
		global $database;
		$sql = 'SELECT * FROM ' . self::$table_name;
		$sql .= ' WHERE course_id = ' . $database->escape_value($course_id);
		$sql .= ' ORDER BY created DESC';

		return self::find_by_sql($sql);
	}

	/**
	 * @param int $course_id gets the course related ID
	 * @param int $limit     limits comments per page
	 * @param int $offset    the pagination offset
	 * @return array of comments in each page
	 */
	public static function find_comments($course_id = 0, $limit = 0, $offset = 0)
	{
		$sql = 'SELECT * FROM ' . self::$table_name . " WHERE course_id = {$course_id} ORDER BY created DESC LIMIT {$limit} OFFSET {$offset}";

		return self::find_by_sql($sql);
	}

	/**
	 * @param string $search
	 * @return array|null
	 */
	public static function search($search = '')
	{
		global $database;
		$sql = 'SELECT * FROM ' . self::$table_name . ' WHERE ';
		$sql .= "body LIKE '%{$database->escape_value($search)}%'";
		$result_set = self::find_by_sql($sql);

		return ! empty($result_set) ? $result_set : NULL;
	}

} // END of CLASS