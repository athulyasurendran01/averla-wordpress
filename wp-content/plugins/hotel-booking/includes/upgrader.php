<?php

namespace MPHB;

class Upgrader {

	const OPTION_MIN_DB_VERSION		 = '1.0.1';
	const OPTION_DB_VERSION			 = 'mphb_db_version';
	const OPTION_DB_VERSION_HISTORY	 = 'mphb_db_version_history';

	public function __construct(){
		add_action( 'admin_init', array( $this, 'upgrade' ) );
	}

	public function upgrade(){

		$dbVersion = $this->getCurrentDBVersion();

		if ( version_compare( $dbVersion, '1.1.0', '<' ) ) {
			$this->fixForV1_1_0();
		}
	}

	/**
	 * fix for 1.1.0
	 */
	private function fixForV1_1_0(){

		$deprecatedAction = 'mphb_abandon_bookings';
		if ( wp_next_scheduled( $deprecatedAction ) ) {
			wp_clear_scheduled_hook( $deprecatedAction );
			MPHB()->cronManager()->getCron( 'abandon_booking_pending_user' )->schedule();
		}

		$this->changeOptionName( 'mphb_user_cancel_redirect', 'mphb_user_cancel_redirect_page' );

		$this->updateDBVersion( '1.1.0' );
	}

	/**
	 *
	 * @param string $version
	 */
	private function updateDBVersion( $version ){

		if ( version_compare( $this->getCurrentDBVersion(), $version, '!=' ) ) {
			$this->addDBVersionToHistory( $version );
		}

		update_option( self::OPTION_DB_VERSION, $version );
	}

	/**
	 *
	 * @return string
	 */
	private function getCurrentDBVersion(){
		return get_option( self::OPTION_DB_VERSION, self::OPTION_MIN_DB_VERSION );
	}

	/**
	 *
	 * @param string $version
	 */
	private function addDBVersionToHistory( $version ){
		$dbVersionHistory	 = get_option( self::OPTION_DB_VERSION_HISTORY, array() );
		$dbVersionHistory[]	 = $version;
		update_option( self::OPTION_DB_VERSION_HISTORY, $dbVersionHistory );
	}

	/**
	 * @todo add support to false value of option
	 *
	 * @param string $oldName
	 * @param string $name
	 */
	private function changeOptionName( $oldName, $name ){
		$optionValue = get_option( $oldName );

		if ( false !== $optionValue ) {
			delete_option( $oldName );
			update_option( $name, $optionValue );
		}
	}

}
