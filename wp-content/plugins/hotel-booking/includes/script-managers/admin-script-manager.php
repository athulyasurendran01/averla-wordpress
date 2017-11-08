<?php

namespace MPHB\ScriptManagers;

class AdminScriptManager extends ScriptManager {

	private $roomIds = array();

	public function __construct(){
		add_action( 'admin_enqueue_scripts', array( $this, 'register' ), 9 );
	}

	public function register(){

		wp_register_script( 'mphb-canjs', MPHB()->getPluginUrl( 'vendors/canjs/can.custom.min.js' ), array( 'jquery' ), MPHB()->getVersion(), true );
		$this->addDependency( 'mphb-canjs' );

		// @todo if possible concat kbwood scripts
		wp_register_script( 'mphb-kbwood-plugin', MPHB()->getPluginUrl( 'vendors/kbwood/datepick/jquery.plugin.min.js' ), array( 'jquery' ), MPHB()->getVersion(), true );
		wp_register_script( 'mphb-kbwood-datepick', MPHB()->getPluginUrl( 'vendors/kbwood/datepick/jquery.datepick.min.js' ), array( 'jquery', 'mphb-kbwood-plugin' ), MPHB()->getVersion(), true );
		$this->addDependency( 'mphb-kbwood-datepick' );
		$this->registerDatepickLocalization();

		wp_register_script( 'mphb-jquery-serialize-json', MPHB()->getPluginUrl( 'vendors/jquery.serializeJSON/jquery.serializejson.min.js' ), array( 'jquery' ), MPHB()->getVersion() );
		wp_register_script( 'mphb-bgrins-spectrum', MPHB()->getPluginUrl( 'vendors/bgrins-spectrum/build/spectrum-min.js' ), array( 'jquery' ), MPHB()->getVersion(), true );
		$this->addDependency( 'mphb-bgrins-spectrum' );

		wp_register_script( 'mphb-admin', MPHB()->getPluginUrl() . 'assets/js/admin/admin.min.js', $this->scriptDependencies, MPHB()->getVersion(), true );

		$this->registerStyles();
	}

	private function registerStyles(){
		wp_register_style( 'mphb-kbwood-datepick-css', MPHB()->getPluginUrl( 'vendors/kbwood/datepick/jquery.datepick.css' ), null, MPHB()->getVersion() );
		wp_register_style( 'mphb-bgrins-spectrum', MPHB()->getPluginUrl( 'vendors/bgrins-spectrum/build/spectrum_theme.css' ), null, MPHB()->getVersion() );
		wp_register_style( 'mphb-admin-css', MPHB()->getPluginUrl( 'assets/css/admin.min.css' ), array( 'mphb-kbwood-datepick-css', 'mphb-bgrins-spectrum' ), MPHB()->getVersion() );
	}

	public function enqueue(){
		if ( !wp_script_is( 'mphb-admin' ) ) {
			add_action( 'admin_print_footer_scripts', array( $this, 'localize' ), 5 );
		}
		wp_enqueue_script( 'mphb-admin' );

		// Styles
		wp_enqueue_style( 'mphb-kbwood-datepick-css' );
		wp_enqueue_style( 'mphb-admin-css' );
	}

	public function addRoomData( $roomId ){
		if ( !in_array( $roomId, $this->roomIds ) ) {
			$this->roomIds[] = $roomId;
		}
	}

	public function localize(){
		wp_localize_script( 'mphb-admin', 'MPHB', $this->getLocalizeData() );
	}

	public function getLocalizeData(){
		$data = array(
			'_data' => array(
				'version'		 => MPHB()->getVersion(),
				'prefix'		 => MPHB()->getPrefix(),
				'ajaxUrl'		 => MPHB()->getAjaxUrl(),
				'today'			 => mphb_current_time( 'Y-m-d' ),
				'nonces'		 => MPHB()->getAjax()->getAdminNonces(),
				'translations'	 => array(
					'roomTypeGalleryTitle'	 => __( 'Accommodation Type Gallery', 'motopress-hotel-booking' ),
					'addGalleryToRoomType'	 => __( 'Add Gallery To Accommodation Type', 'motopress-hotel-booking' ),
					'errorHasOccured'		 => __( 'An error has occurred', 'motopress-hotel-booking' )
				),
				'settings'		 => array(
					'firstDay'					 => MPHB()->settings()->dateTime()->getFirstDay(),
					'numberOfMonthCalendar'		 => 2,
					'numberOfMonthDatepicker'	 => 2,
					'dateFormat'				 => MPHB()->settings()->dateTime()->getDateFormatJS(),
					'dateTransferFormat'		 => MPHB()->settings()->dateTime()->getDateTransferFormatJS()
				)
			),
		);

		return $data;
	}

}
