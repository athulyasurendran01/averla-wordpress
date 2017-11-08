<?php

namespace MPHB\Repositories;

use \MPHB\Persistences;

abstract class AbstractPostRepository {

	protected $items = array();

	/**
	 *
	 * @var Persistences\CPTPersistence
	 */
	protected $persistence;

	public function __construct( Persistences\CPTPersistence $persistence ){
		$this->persistence = $persistence;
	}

	/**
	 *
	 * @param int $id
	 * @param bool $force
	 * @return
	 */
	public function findById( $id, $force = false ){

		if ( empty( $this->items[$id] ) || $force ) {
			$post				 = $this->persistence->getPost( $id );
			$entity				 = !is_null( $post ) ? $this->mapPostToEntity( $post ) : null;
			$this->items[$id]	 = $entity;
		}

		return $this->items[$id];
	}

	public function findAll( $atts = array() ){

		$posts = $this->persistence->getPosts( $atts );

		$entities = array_map( array( $this, 'mapPostToEntity' ), $posts );

		$entities = array_filter( $entities );

		foreach ( $entities as $entity ) {
			$this->items[$entity->getId()] = $entity;
		}

		return $entities;
	}

	/**
	 *
	 * @param type $entity
	 * @return int
	 */
	public function save( &$entity ){

		$postData = $this->mapEntityToPostData( $entity );

		$id = $this->persistence->createOrUpdate( $postData );

		if ( $id ) {

			$entity = $this->findById( $id, true );

			$this->items[$id] = $entity;
		}

		return $id;
	}

	abstract function mapPostToEntity( $post );

	/**
	 * @return \MPHB\Entities\WPPostData
	 */
	abstract function mapEntityToPostData( $entity );
}
