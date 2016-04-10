<article>
	<?php
	global $current_article;
	global $newest_article;
	global $session;
	$current_article = $newest_article;
	?>
	<h2>
		<?php echo htmlentities($newest_article->name); ?>
		<span class="badge">جدیدترین مقاله</span>
	</h2>
	<h5>
		<?php if(isset($newest_article->author_id)):
			$author = Author::find_by_id($newest_article->author_id);
			?><i class="fa fa-user fa-lg"></i>&nbsp;
			<?php echo "توسط: " . $author->full_name();
			if( ! empty($author->photo)): ?>
				<img class="author-photo img-circle pull-left" alt="<?php echo $author->full_name(); ?>" src="data:image/jpeg;base64,<?php echo base64_encode($author->photo); ?>"/>
			<?php endif; ?>
		<?php endif; ?>
	</h5>
	<h5>
		<i class="fa fa-calendar"></i>&nbsp;&nbsp;<?php echo htmlentities(datetime_to_text($newest_article->created_at)); ?>
	</h5>
	<h5>
		<i class="fa fa-calendar"></i>&nbsp;&nbsp;<?php echo htmlentities(datetime_to_shamsi($newest_article->created_at)); ?>
	</h5>
	<hr>
	<?php echo nl2br(strip_tags($newest_article->content, ARTICLE_ALLOWABLE_TAGS)); ?>
	<hr/>
	<article id="comments">
		<?php include_layout_template('article-comments.php'); ?>
	</article>
</article>