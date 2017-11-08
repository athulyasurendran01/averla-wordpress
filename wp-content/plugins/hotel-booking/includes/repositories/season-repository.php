<?php

namespace MPHB\Repositories;

use \MPHB\Entities;

class SeasonRepository extends AbstractPostRepository {

	/**
	 *
	 * @param array $atts
	 * @return Entities\Season[]
	 */
	public function findAll( $atts = array() ){
		return parent::findAll( $atts );
	}

	/**
	 *
	 * @param int $id
	 * @return Entities\Season|null
	 */
	public function findById( $id, $force = false ){
		return parent::findById( $id, $force );
	}

	public function mapPostToEntity( $post ){

		$id = ( is_a( $post, '\WP_Post' ) ) ? $post->ID : $post;

		$startDate	 = get_post_meta( $id, 'mphb_start_date', true );
		$endDate	 = get_post_meta( $id, 'mphb_end_date', true );
		$days		 = get_post_meta( $id, 'mphb_days', true );

		$seasonArgs = array(
			'id'			 => $id,
			'title'			 => get_the_title( $id ),
			'description'	 => get_post_field( 'post_content', $id ),
			'start_date'	 => !empty( $startDate ) ? \DateTime::createFromFormat( 'Y-m-d', $startDate ) : null,
			'end_date'		 => !empty( $endDate ) ? \DateTime::createFromFormat( 'Y-m-d', $endDate ) : null,
			'days'			 => !empty( $days ) ? $days : array()
		);

		return new Entities\Season( $seasonArgs );
	}

	public function mapEntityToPostData( $entity ){
		// @todo
	}

}
