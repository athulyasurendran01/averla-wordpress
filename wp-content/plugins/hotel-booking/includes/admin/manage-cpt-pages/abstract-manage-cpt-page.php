<?php

namespace MPHB\Admin\ManageCPTPages;

use \MPHB\PostTypes\BookingCPT;
use \MPHB\Views;
use \MPHB\Entities;

abstract class AbstractManageCPTPage {

	const EMPTY_VALUE_PLACEHOLDER = '&#8212;';

	protected $postType;

	public function __construct( $postType, $args = array() ){

		$this->postType = $postType;

		$this->addActionsAndFilters();
	}

	protected function addActionsAndFilters(){
		add_filter( "manage_{$this->postType}_posts_columns", array( $this, 'filterColumns' ) );
		add_filter( "manage_edit-{$this->postType}_sortable_columns", array( $this, 'filterSortableColumns' ) );
		add_action( "manage_{$this->postType}_posts_custom_column", array( $this, 'renderColumns' ), 10, 2 );

		// views_{screen->id} filter
		add_filter( "views_edit-{$this->postType}", array( $this, 'filterViews' ) );
	}

	abstract public function filterColumns( $columns );

	abstract public function filterSortableColumns( $columns );

	abstract public function renderColumns( $column, $postId );

	/**
	 *
	 * @param array $views
	 * @return array
	 */
	public function filterViews( $views ){
		return $views;
	}

	public function isCurrentPage(){
		global $typenow, $pagenow;
		return is_admin() && $pagenow === 'edit.php' && $typenow === $this->postType;
	}

}
