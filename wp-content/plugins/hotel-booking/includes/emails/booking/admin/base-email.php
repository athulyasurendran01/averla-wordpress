<?php

namespace MPHB\Emails\Booking\Admin;

use \MPHB\Emails;

abstract class BaseEmail extends Emails\AbstractEmail {

	/**
	 *
	 * @param array $args
	 * @param string $args['id'] ID of Email.
	 * @param string $args['label'] Label.
	 * @param string $args['description'] Optional. Email description.
	 * @param string $args['default_subject'] Optional. Default subject of email.
	 * @param string $args['default_header_text'] Optional. Default text in header.
	 * @param Emails\EmailTemplater $templater
	 */
	public function __construct( $args, Emails\EmailTemplater $templater ){
		parent::__construct( $args, $templater );
		add_action( 'mphb_generate_settings_admin_emails', array( $this, 'generateSettingsFields' ) );
	}

	/**
	 *
	 * @return string
	 */
	protected function getReceiver(){
		return MPHB()->settings()->emails()->getFromEmail();
	}

	/**
	 *
	 * @return bool
	 */
	public function send(){

		do_action( '_mphb_translate_admin_email_before_send', $this->booking );

		$isSended = parent::send();

		do_action( '_mphb_translate_admin_email_after_send', $this->booking );

		return $isSended;
	}

	/**
	 *
	 * @param bool $isSended
	 */
	protected function log( $isSended ){

		if ( $isSended ) {
			$this->booking->addLog( sprintf( __( '"%s" mail was sent to admin.', 'motopress-hotel-booking' ), $this->label ) );
		} else {
			$this->booking->addLog( sprintf( __( '"%s" mail sending to admin is failed.', 'motopress-hotel-booking' ), $this->label ) );
		}
	}

}
