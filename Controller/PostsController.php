<?php

class PostsController extends AppController {

	public $name = 'Posts';
	
	
	public $paginate = array(
		'Post' => array(						   
        	'limit' => 10
		)	
	);	

	public function index()
	{
				
		$this->paginate['Post']['paginateQuery'] = "SELECT Post.ID, Post.post_title, Post.post_date, Post.post_name, Post.post_excerpt, Image.guid
		FROM wp_posts AS Post 
		JOIN wp_posts AS Image ON Image.post_parent = Post.id AND Image.post_type = 'attachment' 
		JOIN wp_postmeta AS PostMeta ON Post.ID = PostMeta.post_id AND PostMeta.meta_key = '_thumbnail_id' AND PostMeta.meta_value = Image.ID
		WHERE Post.post_status = 'publish' 
		ORDER BY Post.post_date DESC";
		
		$posts = $this->paginate('Post');
		
		foreach($posts as $key => $post)
		{
			if (strlen($posts[$key]['Post']['post_excerpt']) >= 240)
			{
					$posts[$key]['Post']['post_excerpt'] = substr($posts[$key]['Post']['post_excerpt'], 0, 237) . "...";	
			}
			
			$posts[$key]['Post']['post_date'] = substr($post['Post']['post_date'], 0, strpos($post['Post']['post_date'], " "));
			$posts[$key]['Image']['src'] = substr($post['Image']['guid'], strpos($post['Image']['guid'], "/img"));
		}		
		
		$this->set('results', $posts);
		$this->set('class_for_layout', 'rightSidebarEnabled');
		
	}
	
	public function view($postName)
	{
		$post = $this->Post->findByPostName($postName);
		
		//debug($post);
		$this->set('class_for_layout', 'rightSidebarEnabled');
		if (!empty($post))
		{
			
			if (!empty($post['Post']['post_date'])) $post['Post']['post_date'] = substr($post['Post']['post_date'], 0, strpos($post['Post']['post_date'], " "));
			
			$this->set('title_for_layout', $post['Post']['post_title']);	
			$this->set('post', $post);
		}

		//$page->post_content;
		//$this->set('title_for_layout', $page->post_title);
		//$page = $this->Wordpress->get_page_by_path($path);	
	}
	
	public function sidebar()
	{
		$pages = $this->Post->find('all', array(
			'order' => "Post.menu_order ASC",
			'fields' => array('ID', 'post_title', 'post_date', 'post_name'),
			'conditions' => array(
				'post_status' => 'publish',
				'post_type' => 'page'
			)
		));			
		
		$posts = $this->Post->find('all', array(
			'limit' => 3,
			'fields' => array('ID', 'post_title', 'post_date', 'post_name'),
			'conditions' => array(
				'post_status' => 'publish',
				'post_type' => 'post'
			)
		));			
		
		$data['pages'] = $pages;
		$data['posts'] = $posts;
		
		return $data;		
	}
	


}//end class
?>