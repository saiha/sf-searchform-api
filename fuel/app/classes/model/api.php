<?php

class Model_Api extends Model {
	
    public static function get_search_posts($freeword = null, $category_id = null , $tag_id = null) {
        $data = null;

		// DBからデータを取得する
		$data = self::get_search_posts_data($freeword, $category_id, $tag_id);
		 
        return $data;
    }

    public static function get_tags($tag_ids = null) {
        $data = null;

		try {
			if ( $tag_ids == '' ) {

		// キャッシュからデータを取得する
			$data = Cache::get('all');
			} else {
				$data = Cache::get(str_replace(',','and',$tag_ids));
			}
			
		} catch (CacheNotFoundException $e) {
			// キャッシュがない場合
			// DBからデータを取得する
			$data = self::get_tags_data($tag_ids);
		 
			if ($data) {
				// 改めてキャッシュにデータをセットする
				Cache::set(str_replace(',','and',$tag_ids), $data, Constant::TIME_HOUR);
			} else {
				return $data;
			}
		}

        return $data;
    }

    public static function get_posts($category_ids = null, $tag_ids = null) {
        $data = null;

		try {
			if ( $tag_ids == '' && $category_ids = '') {

		// キャッシュからデータを取得する
				$data = Cache::get('all_posts');
			} else {
			$data = self::get_posts_data($category_ids, $tag_ids);
//				$data = Cache::get(str_replace(',','and','c'.$category_ids.'t'.$tag_ids));
			}
			
		} catch (CacheNotFoundException $e) {
			// キャッシュがない場合
			// DBからデータを取得する
			$data = self::get_posts_data($category_ids, $tag_ids);
		 
			if ($data) {
				// 改めてキャッシュにデータをセットする
				Cache::set(str_replace(',','and','c'.$category_ids.'t'.$tag_ids), $data, Constant::TIME_HOUR);
			} else {
				return $data;
			}
		}

        return $data;
    }
    
    private static function get_posts_data($category_ids = null, $tag_ids = null) {
		
		$query = DB::query('select a.ID, a.post_title, a.link as link, bc.guid from (select wp.ID as ID, wp.post_title as post_title, wp.guid as link FROM wp_posts wp, wp_term_relationships wtra, wp_term_relationships wtrb, wp_term_taxonomy wtta, wp_term_taxonomy wttb where wtra.object_id = wp.ID and wp.post_status = "publish" and wtra.term_taxonomy_id = wtta.term_taxonomy_id and wtta.taxonomy = "post_tag" and wttb.taxonomy = "category" and wttb.term_id in :category_ids and wttb.term_taxonomy_id = wtrb.term_taxonomy_id and wtrb.object_id = wtra.object_id and wtta.term_id in :tag_ids group by wp.ID, wp.post_title) a left join (select b.post_parent, b.guid from (select meta_value, post_id from wp_postmeta where meta_key = "_thumbnail_id" ) c left join wp_posts b on (b.post_parent = c.post_id and b.ID = c.meta_value) where b.post_type = "attachment") bc on a.ID = bc.post_parent');	
		$tag_ids = explode(',', $tag_ids);
		$category_ids = explode(',', $category_ids);
	    //$result = $query->bind('category_ids', $category_ids)->bind('tag_ids', $tag_ids)->cached(Constant::TIME_DAY)->execute('sleepfreaks')->as_array();
       	$result = $query->bind('category_ids', $category_ids)->bind('tag_ids', $tag_ids)->execute('sleepfreaks')->as_array();
		
		if ( $result != null && $result != '' ) {
			return array('result' => $result);
		} else {
			return array('result' => 'nodata');
		}
    }

    private static function get_tags_data($tag_ids = null) {
		
		if ( $tag_ids == '' || $tag_ids == null ) {
			$query = DB::query('aSELECT wt.term_id, wt.name FROM wp_posts wp, wp_terms wt, wp_term_relationships wtra, wp_term_taxonomy wtta where wtra.object_id = wp.ID and wp.post_status = "publish" and wtra.term_taxonomy_id = wtta.term_taxonomy_id and wtta.term_id = wt.term_id and wtta.taxonomy = "post_tag" group by wt.term_id, wt.name');
	        $result = $query->cached(Constant::TIME_DAY)->execute('sleepfreaks')->as_array();
			//$result = $query->execute('sleepfreaks')->as_array();
		} else {
			$query = DB::query('SELECT wt.term_id, wt.name FROM wp_posts wp, wp_terms wt, wp_term_relationships wtra, wp_term_relationships wtrb, wp_term_taxonomy wtta, wp_term_taxonomy wttb where wtra.object_id = wp.ID and wp.post_status = "publish" and wtra.term_taxonomy_id = wtta.term_taxonomy_id and wtta.term_id = wt.term_id and wtta.taxonomy = "post_tag" and wttb.taxonomy = "category" and wttb.term_id in :tag_ids and wttb.term_taxonomy_id = wtrb.term_taxonomy_id and wtrb.object_id = wtra.object_id group by wt.term_id, wt.name');	
			$tag_ids = explode(',', $tag_ids);
	        $result = $query->bind('tag_ids', $tag_ids)->cached(Constant::TIME_DAY)->execute('sleepfreaks')->as_array();
        	//$result = $query->bind('tag_ids', $tag_ids)->execute('sleepfreaks')->as_array();
		}

		if ( $result != null && $result != '' ) {
			return array('result' => $result);
		} else {
			return array('result' => 'nodata');
		}
    }

    private static function get_search_posts_data($freeword = null, $category_id = null , $tag_id = null) {
		
		$sql1 = 'select a.ID, a.post_title, a.link, bc.guid from (select wp.ID as ID, wp.post_title as post_title, wp.guid as link FROM wp_posts wp, wp_term_relationships wtra, wp_term_relationships wtrb, wp_term_taxonomy wtta, wp_term_taxonomy wttb where wtra.object_id = wp.ID and wp.post_status = "publish" and wtra.term_taxonomy_id = wtta.term_taxonomy_id and wtta.taxonomy = "post_tag" and wttb.taxonomy = "category" and wttb.term_taxonomy_id = wtrb.term_taxonomy_id and wtrb.object_id = wtra.object_id ';
		$sql2 = 'group by wp.ID, wp.post_title) a left join (select b.post_parent, b.guid from (select meta_value, post_id from wp_postmeta where meta_key = "_thumbnail_id" ) c left join wp_posts b on (b.post_parent = c.post_id and b.ID = c.meta_value) where b.post_type = "attachment") bc on a.ID = bc.post_parent';	

		$where = '';
		
		if ( $freeword != null && $freeword != '' ) {
			$where = $where . 'and wp.post_content like :freeword ';
		}

		if ( $category_id != null && $category_id != '' ) {
			$where = $where . 'and wttb.term_id in :category_id ';
		}

		if ( $tag_id != null && $tag_id != '' ) {
			$where = $where . 'and wtta.term_id in :tag_id ';
		}
		
		$query = DB::query($sql1 . $where . $sql2);
		$tag_id = explode(',', $tag_id);
		$category_id = explode(',', $category_id);
		$freeword = '%'.$freeword.'%';
	    //$result = $query->bind('tag_id', $tag_id)->cached(Constant::TIME_DAY)->execute('sleepfreaks')->as_array();
       	$result = $query->bind('freeword', $freeword)->bind('category_id', $category_id)->bind('tag_id', $tag_id)->execute('sleepfreaks')->as_array();
		
		if ( $result != null && $result != '' ) {
			return array('result' => $result);
		} else {
			return array('result' => 'nodata');
		}
    }

}