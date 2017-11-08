<?php

namespace MPHB;

use \MPHB\PostType;

class CustomPostTypes {

	/**
	 *
	 * @var PostTypes\RoomTypeCPT
	 */
	private $roomType;

	/**
	 *
	 * @var PostTypes\RoomCPT
	 */
	private $room;

	/**
	 *
	 * @var PostTypes\ServiceCPT
	 */
	private $service;

	/**
	 *
	 * @var PostTypes\BookingCPT
	 */
	private $booking;

	/**
	 *
	 * @var PostTypes\SeasonCPT
	 */
	private $season;

	/**
	 *
	 * @var PostTypes\RateCPT
	 */
	private $rate;

	/**
	 *
	 * @var PostTypes\PaymentCPT
	 */
	private $payment;

	public function __construct(){
		$this->booking	 = new PostTypes\BookingCPT();
		$this->roomType	 = new PostTypes\RoomTypeCPT();
		$this->season	 = new PostTypes\SeasonCPT();
		$this->rate		 = new PostTypes\RateCPT();
		$this->service	 = new PostTypes\ServiceCPT();
		$this->room		 = new PostTypes\RoomCPT();
		$this->payment	 = new PostTypes\PaymentCPT();
	}

	/**
	 *
	 * @return PostTypes\RoomTypeCPT
	 */
	public function roomType(){
		return $this->roomType;
	}

	/**
	 *
	 * @return PostTypes\RoomCPT
	 */
	public function room(){
		return $this->room;
	}

	/**
	 *
	 * @return PostTypes\ServiceCPT
	 */
	public function service(){
		return $this->service;
	}

	/**
	 *
	 * @return PostTypes\BookingCPT
	 */
	public function booking(){
		return $this->booking;
	}

	/**
	 *
	 * @return PostTypes\SeasonCPT
	 */
	public function season(){
		return $this->season;
	}

	/**
	 *
	 * @return PostTypes\RateCPT
	 */
	public function rate(){
		return $this->rate;
	}

	/**
	 *
	 * @return PostTypes\PaymentCPT
	 */
	public function payment(){
		return $this->payment;
	}

	public function flushRewriteRules(){
		$this->roomType->register();
		$this->service->register();
		$this->booking->register();
		flush_rewrite_rules();
	}

}
