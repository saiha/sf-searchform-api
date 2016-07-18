<?php

class Controller_Api_Wordpress extends Controller_Base_Rest {

	public function before() {
		parent::before();
		header('Access-Control-Allow-Origin: *');	
	}

    // カテゴリから記事に設定されているタグを取得する
	public function get_tags() {
        return Model_Api::get_tags(Request::active()->param('category_id', ''));
    }

    // カテゴリとタグから記事を取得する
	public function get_posts() {
        return Model_Api::get_posts(Request::active()->param('category_id', ''),Request::active()->param('tag_id', ''));
    }

    // カテゴリとタグから記事を取得する
	public function post_search() {
		$freeword = Input::post('word');
		if ( $freeword == '' || $freeword == null ) {
			return array('result' => 'noword');
		}
		$category_id = Input::post('category_id');
		$tag_id = Input::post('tag_id');
		
        return Model_Api::get_search_posts($freeword, $category_id, $tag_id);
    }

    // 404
	public function get_404() {
        return array('result' => 'nodata');
    }

}