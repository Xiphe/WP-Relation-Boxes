SELECT wp_posts.*
FROM wp_posts

LEFT JOIN wp_rb_post_relationships AS rb_post_relations
ON rb_post_relations.related_post_ID = wp_posts.ID

LEFT JOIN wp_posts as rb_related_post
ON rb_post_relations.related_post_ID = wp_posts.ID
AND rb_post_relations.post_ID = rb_related_post.ID

WHERE 1=1
AND ( wp_posts.post_type = 'post' 
	OR ( wp_posts.ID = rb_post_relations.related_post_ID
		AND rb_post_relations.related_post_type = 'tile'
		AND rb_post_relations.post_ID IN (
			SELECT rb_related_posts2.ID
			FROM wp_posts as rb_related_posts2
			WHERE 1 = 1
			AND rb_related_posts2.post_type = 'post'
			AND (rb_related_posts2.post_status = 'publish' OR rb_related_posts2.post_status = 'private')
		)
	)
)
AND (wp_posts.post_status = 'publish' OR wp_posts.post_status = 'private')
GROUP BY wp_posts.ID
ORDER BY IF(rb_post_relations.post_order > 0, rb_related_post.post_date, wp_posts.post_date) DESC,
           rb_post_relations.post_order ASC
LIMIT 0, 5