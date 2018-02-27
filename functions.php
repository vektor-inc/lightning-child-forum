<?php

/*
	トピックを REST API で使えるようにする
 */
add_filter( 'bbp_register_topic_post_type', 'vkf_topic_post_type_custom' );
function vkf_topic_post_type_custom( $args ){

	// return array(
	// 	'labels'              => bbp_get_topic_post_type_labels(),
	// 	'rewrite'             => bbp_get_topic_post_type_rewrite(),
	// 	'supports'            => bbp_get_topic_post_type_supports(),
	// 	'description'         => __( 'bbPress Topics', 'bbpress' ),
	// 	'capabilities'        => bbp_get_topic_caps(),
	// 	'capability_type'     => array( 'topic', 'topics' ),
	// 	'menu_position'       => 555555,
	// 	'has_archive'         => ( 'forums' === bbp_show_on_root() ) ? bbp_get_topic_archive_slug() : false,
	// 	'exclude_from_search' => true,
	// 	'show_in_nav_menus'   => false,
	// 	'public'              => true,
	// 	'show_ui'             => current_user_can( 'bbp_topics_admin' ),
	// 	'can_export'          => true,
	// 	'hierarchical'        => false,
	// 	'query_var'           => true,
	// 	'menu_icon'           => '',
	// 	'show_in_rest' => true,
	// 	'rest_base' => 'topics',
	// );

	$rest_args = array(
		'show_in_rest' => true,
		'rest_base' => 'topics',
	);
	$args = array_merge( $args, $rest_args );
	return $args;

}
