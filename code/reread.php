<?php


class MMWWRereader {
	public function __construct() {
		add_filter( 'media_row_actions', array( $this, 'add_reread_action' ),10,3);
		//add_filter( 'get_edit_post_link', array( $this, 'reread_link'), 10, 3);
	}
	
	
	//$actions = apply_filters( 'media_row_actions', $actions, $post, $this->detached );
	//class-wp-media-list-table.php calls this guy
	
	/**
	 * result looks like this: 
	 *   $action['reread'] = http://host/wp-admin/post.php?post=999&action=reread&_wpnonce=54456a9879
	 * @param array $actions 
	 * @param Post $post
	 * @param boolean $detached
	 * @returns array of actions
	 */
	function add_reread_action($actions, $post, $detached) {
		$addlink = false;
		$url = $this->get_reread_metadata_post_link($post->ID);
		if (!empty($url)) {
			$actions['reread'] = '<a href="' . $url . '">' . __( 'Reread Metadata', 'mmww' ) . '</a>';
			$addlink = true;
		}
		if ($addlink) {
			/* reorder the links to make more sense */
			$result = array();
			$order = array ('edit', 'reread', 'delete', 'view');
			foreach ($order as $o) {
				if (array_key_exists($o, $actions)) {
					$result[$o] = $actions[$o];
					unset ($actions[$o]);
				}
			}
			foreach ($actions as $o => $v) {
				$result[$o] = $actions[$o];
			}
			return $result;
		}
		/* nothing to change, pass through */
		return $actions;
	}
	
	/**
	 * Retrieve reread-metadata link for post
	 * cribbed from link-template.php
	 *
	 * Could be used within the WordPress loop or outside of it, with an attachment post type.
	 *
	 * @since 2.9.0
	 *
	 * @param int $id Optional. Post ID.
	 * @return string or null if it makes no sense to try to reread the metadata
	 */
	function get_reread_metadata_post_link( $id = 0 ) {
		/* valid post */
		if ( !$post = get_post( $id ) )
			return;
		/* is it an attachment (media file) ? */
		if ($post->post_type != 'attachment')
			return;
		/* can we find, and read, the original media file */
		$file = $this->get_attachment_path( $id );
		if (empty($file))
			return;
		if (!file_exists($file))
			return;
		
		$post_type_object = get_post_type_object( $post->post_type );
		if ( !$post_type_object )
			return;
	
		if ( !current_user_can( $post_type_object->cap->edit_post, $post->ID ) )
			return;
	
		$action = 'reread';
	
		$reread_link = add_query_arg( 'action', $action, admin_url( sprintf( $post_type_object->_edit_link, $post->ID ) ) );
	
		return apply_filters( 'get_reread_post_link', wp_nonce_url( $reread_link, "$action-post_{$post->ID}" ), $post->ID );
	}
	
	
	
	/**
	 * 
	 * @param string $url
	 * @param Post $post
	 * @param string $context
	 * @return url string.
	 */
	function reread_link ( $url, $post, $context ) {
		//action=edit get changed to //action=reread
		$p = stripos ( $url, 'action=edit' );
		if ( ! $p === False ) {
			$s = preg_replace ( '/action=edit/', 'action=reread', $url );
			return $s;
		}

		return $url;
	}
	
	/**
	 * Retrieve the pathname in the file system for an attachment's file.
	 *   (cribbed from post.php)
	 * @since 2.1.0
	 *
	 * @param int $post_id Attachment ID.
	 * @return string
	 */
	private function get_attachment_path( $post_id = 0 ) {
		$post_id = (int) $post_id;
		if ( !$post =& get_post( $post_id ) )
			return false;
	
		if ( 'attachment' != $post->post_type )
			return false;
	
		$result = '';
		if ( $file = get_post_meta( $post->ID, '_wp_attached_file', true) ) { //Get attached file meta
			if ( ($uploads = wp_upload_dir()) && false === $uploads['error'] ) { //Get upload directory
				if ( 0 === strpos($file, $uploads['basedir']) ) { //Check that the upload base exists in the file location
					$result = $file;
				}
				elseif ( false !== strpos($file, 'wp-content/uploads') ) {
					$result = $uploads['basedir'] . substr( $file, strpos($file, 'wp-content/uploads') + 18 );
				}
				else {
					$result = $uploads['basedir'] . "/$file"; //Its a newly uploaded file, therefore $file is relative to the basedir.
				}
			}
		}
	
		if ( empty( $result ) )
			return false;
	
		return $result;
	}
	
}
new MMWWRereader ();