<?php 
	// Register Custom Post Type: Landing Page
	
	add_action('init', 'landing_page_register');
	 
	function landing_page_register() {
	 
		$labels = array(
			'name' => _x('Landing Page', 'post type general name'),
			'singular_name' => _x('Landing Page', 'post type singular name'),
			'add_new' => _x('Add New', 'Landing Page item'),
			'add_new_item' => __('Add New Landing Page'),
			'edit_item' => __('Edit Landing Page'),
			'new_item' => __('New Landing Page'),
			'view_item' => __('View Landing Page Item'),
			'search_items' => __('Search Landing Pages'),
			'not_found' =>  __('Nothing found'),
			'not_found_in_trash' => __('Nothing found in Trash'),
			'parent_item_colon' => ''
		);
	 
		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'query_var' => true,
			'menu_icon' => 'dashicons-media-document',
			'rewrite' => true,
			'capability_type' => 'post',
			'hierarchical' => false,
			'menu_position' => null,
			'supports' => array('title','editor','thumbnail')
		  ); 
	 
		register_post_type( 'landing-page' , $args );
	}
?>