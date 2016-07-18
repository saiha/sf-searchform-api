<?php
return array(
	'_404_' => 'api/wordpress/404',
	'tag/(?P<category_id>[1-9][,0-9]*)' => 'api/wordpress/tags',
	'post/(?P<category_id>[1-9][,0-9]*)/(?P<tag_id>[1-9][,0-9]*)' => 'api/wordpress/posts',
	'search' => 'api/wordpress/search',
);
