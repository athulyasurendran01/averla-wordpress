<?php

namespace MPHB\Admin;

class Menus {

	public function __construct(){
		add_action( 'admin_menu', array( $this, 'addMenuSeparator' ), 10 );
		add_filter( 'menu_order', array( $this, 'reorderMenu' ) );
		add_filter( 'custom_menu_order', '__return_true' );
	}

	/**
	 * Add menu separator.
	 */
	public function addMenuSeparator(){
		global $menu;

		if ( current_user_can( MPHB()->getMainMenuCapability() ) ) {
			$menu[] = array( '', 'read', 'separator-mphb', '', 'wp-menu-separator mphb' );
		}
	}

	/**
	 * Reorder menu items in admin.
	 *
	 * @param array $menuOrder
	 * @return array
	 */
	public function reorderMenu( $menuOrder ){

		$customMenuOrder = array();

		$mphbSeparatorMenu	 = 'separator-mphb';
		$mphbSeparatorKey	 = array_search( $mphbSeparatorMenu, $menuOrder );

		$roomTypeMenu	 = add_query_arg( 'post_type', MPHB()->postTypes()->roomType()->getPostType(), 'edit.php' );
		$roomTypeMenuKey = array_search( $roomTypeMenu, $menuOrder );

		unset( $menuOrder[$mphbSeparatorKey] );
		unset( $menuOrder[$roomTypeMenuKey] );

		foreach ( $menuOrder as $index => $item ) {

			if ( ( ( MPHB()->getMainMenuSlug() ) == $item ) ) {
				$customMenuOrder[]	 = $mphbSeparatorMenu;
				$customMenuOrder[]	 = $roomTypeMenu;
				$customMenuOrder[]	 = $item;
			} else {
				$customMenuOrder[] = $item;
			}
		}

		return $customMenuOrder;
	}

}
