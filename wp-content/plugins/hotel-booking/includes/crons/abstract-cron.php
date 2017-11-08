<?php

namespace MPHB\Crons;

abstract class AbstractCron {

	const ACTION_PREFIX = 'mphb_cron_';

	/**
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * Action hook to execute when cron is run.
	 *
	 * @var string
	 */
	protected $action;

	/**
	 * How often the event should recur. Registered WP Interval Name.
	 *
	 * @var string
	 */
	protected $interval;

	public function __construct( $id, $interval ){
		$this->id		 = $id;
		$this->action	 = self::ACTION_PREFIX . $this->id;
		$this->interval	 = $interval;

		add_action( $this->action, array( $this, 'doCronJob' ) );
	}

	/**
	 *
	 * @return string
	 */
	public function getId(){
		return $this->id;
	}

	/**
	 *
	 * @return string
	 */
	public function getAction(){
		return $this->action;
	}

	abstract public function doCronJob();

	public function schedule(){
		if ( !wp_next_scheduled( $this->action ) ) {
			wp_schedule_event( current_time( 'timestamp', true ), $this->interval, $this->action );
		}
	}

	public function unschedule(){
		wp_clear_scheduled_hook( $this->action );
	}

}
