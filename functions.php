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
/*-------------------------------------------*/
/*  メタボックス追加
/*-------------------------------------------*/
add_action( 'admin_menu', 'vk_forum_add_resolve_meta_box' );
/*-------------------------------------------*/
/*  管理画面に入力フィールドを追加する
/*-------------------------------------------*/
  // 取得する条件
	function vk_forum_add_resolve_meta_box() {
  // $args = array(
  //   'public'   => true,
  // );
  // // タイプの取得を実行
  // $post_types = get_post_types( $args );
  // foreach ( (array) $post_types as $post_type ) {
     add_meta_box(
      'resolve-meta-box', // metaboxのID
      veu_get_little_short_name().' '. __( 'Topic status', 'vkExUnit' ), // metaboxの表示名
      'vk_forum_resolve_meta_box_body', // このメタボックスに表示する中身の関数名
      'topic', // このメタボックスをどの投稿タイプで表示するのか？
      'side' // 表示する位置
      );
  // } // foreach ( (array) $post_types as $post_type ) {
}
/*-------------------------------------------*/
/*  入力フィールドの生成
/*-------------------------------------------*/
function vk_forum_resolve_meta_box_body(){
      // シェアボタンを表示しない設定をするチェックボックスを表示
      //CSRF対策の設定（フォームにhiddenフィールドとして追加するためのnonceを「'noncename__forum_resolve」として設定）
      wp_nonce_field( wp_create_nonce(__FILE__), 'noncename__forum_resolve' );
      global $post;
      // カスタムフィールド 'forum_resolve' の値を取得
      $forum_resolve = get_post_meta( $post->ID,'forum_resolve',true );
      // チェックが入っている場合（ 表示しない ）
      if ( $forum_resolve ) {
        $checked = ' checked';
      } else {
        $checked = '';
      }
      $label = __('Resolved.', 'vkExUnit' );
      echo '<ul>';
      echo '<li><label>'.'<input type="checkbox" id="forum_resolve" name="forum_resolve" value="true"'.$checked.'> '.$label.'</label></li>';
      echo '</ul>';
}
/*-------------------------------------------*/
/*  入力された値の保存
/*-------------------------------------------*/
add_action('save_post', 'vk_forum_save_resolve_meta_box');
function vk_forum_save_resolve_meta_box($post_id){
    global $post;
    //設定したnonce を取得（CSRF対策）
    $noncename__forum_resolve = isset($_POST['noncename__forum_resolve']) ? $_POST['noncename__forum_resolve'] : null;
    //nonce を確認し、値が書き換えられていれば、何もしない（CSRF対策）
    if(!wp_verify_nonce($noncename__forum_resolve, wp_create_nonce(__FILE__))) {
        return $post_id;
    }
    //自動保存ルーチンかどうかチェック。そうだった場合は何もしない（記事の自動保存処理として呼び出された場合の対策）
    if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) { return $post_id; }
    $field = 'forum_resolve';
    $field_value = ( isset( $_POST[$field] ) ) ? $_POST[$field] : '';
    // データが空だったら入れる
    if( get_post_meta($post_id, $field ) == ""){
        add_post_meta($post_id, $field , $field_value, true);
    // 今入ってる値と違ってたらアップデートする
    } elseif( $field_value != get_post_meta( $post_id, $field , true)){
        update_post_meta($post_id, $field , $field_value);
    // 入力がなかったら消す
    } elseif( $field_value == "" ){
        delete_post_meta($post_id, $field , get_post_meta( $post_id, $field , true ));
    }
}
/*
	タイトルに解決済み表を示する
*/
add_filter( 'the_title','vk_forum_title_custom' );
function vk_forum_title_custom( $title ) {
	// $title はフック対象のタイトル
	// カスタムフィールドの値を取得
	$resolve = get_post_meta( get_the_ID(), 'forum_resolve' );
	// チェックが入っていたら
	$resolve_value = ( isset( $resolve[0] ) ) ? $resolve[0] : '';

	if ( $resolve_value == true ) {

		if( !preg_match( '/解決済/',$title ) ){
		  //$titleのなかに解決済が含まれていない場合（これがないとパンくずリストに２重で[解決済み]がついてしまう）

			global $post;
			$title_post = $post->post_title; // 今表示しているページのタイトル
			$pattern =  '/'.$title_post.'/u';
			if( preg_match( $pattern, $title ) ){
				//$titleのなかに $post->post_title が含まれていない場合（これがないとトピックス詳細で他のtitle部分にも[解決済み]がついてしまう）
				// タイトルの先頭に [ 解決済 ] を追加
				$title = '[ 解決済 ] '.$title;
			}
		}
	}
	// 値を返す
	return $title;
}
