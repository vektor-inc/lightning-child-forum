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


/*
	トピックの状態のカスタムフィールドを追加
*/
if(function_exists("register_field_group"))
{
register_field_group(array (
	'id' => 'acf_resolve',
	'title' => 'トピックの状態',
	'fields' => array (
		array (
			'key' => 'field_resolve',
			'label' => '現在のトピックの状態',
			'name' => 'resolve_fields',
			'type' => 'checkbox',
			'choices' => array (
			'resolve' => '解決済み',
			),
			'default_value' => '',
			'layout' => 'vertical',
		),
	),
	'location' => array (
		array (
			array (
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'topic',
				'order_no' => 0,
				'group_no' => 0,
			),
		),
	),
	'options' => array (
		'position' => 'normal',
		'layout' => 'default',
		'hide_on_screen' => array (
		),
	),
	'menu_order' => 0,
));
}

/*
	タイトルに解決済み表を示する
*/
add_filter('the_title','vk_forum_title_custom');
function vk_forum_title_custom( $title ) {
	// カスタムフィールドの値を取得
	$resolve = get_field('resolve_fields');
	// チェックが入っていたら
	if ( $resolve ) {
		if(!preg_match('/解決済/',$title)){
		  //$titleのなかに解決済が含まれていない場合（これがないとパンくずリストに２重で[解決済み]がついてしまう）
			// タイトルの先頭に [ 解決済 ] を追加
			$title = '[ 解決済 ] '.$title;
		}
	}
	// 値を返す
	return $title;
}
