<?php

namespace MPHB;

/**
 * @todo+ recheck this class, refactoring
 */
class Translation {

	const WPML_STRING_DOMAIN = 'MotoPress Hotel Booking';

	private $beforeEmailLanguage;
	private $storedLanguage;
	private $locale;

	public function __construct(){
		add_filter( 'mphb_translate_string', array( $this, 'translateString' ), 10, 4 );
		add_action( 'plugins_loaded', array( $this, 'improveWMPLCompability' ) );
		add_filter( 'plugin_locale', array( $this, 'setLocaleForEmails' ), 10, 2 );
	}

	public function improveWMPLCompability(){

		if ( !$this->isActiveWPML() ) {
			return;
		}

		add_filter( '_mphb_translate_page_id', array( $this, 'wpmlTranslatePageId' ), 10, 2 );
		add_filter( '_mphb_translate_post_id', array( $this, 'wpmlTranslatePostId' ), 10, 2 );

		add_action( '_mphb_persistence_before_get_posts', array( $this, 'setWPMLDefaultLanguage' ) );
		add_action( '_mphb_persistence_after_get_posts', array( $this, 'resetWPMLStoredLanguage' ) );

		add_filter( '_mphb_translate_rate', array( $this, 'translateRate' ) );
		add_filter( '_mphb_translate_service', array( $this, 'translateService' ) );
		add_filter( '_mphb_translate_room_type', array( $this, 'translateRoomType' ) );

		add_action( '_mphb_before_dropdown_pages', array( $this, 'setupDefaultLanguage' ) );
		add_action( '_mphb_after_dropdown_pages', array( $this, 'restoreLanguage' ) );

		add_action( '_mphb_translate_admin_email_before_send', array( $this, 'changeLanguageForAdminEmail' ) );
		add_action( '_mphb_translate_admin_email_after_send', array( $this, 'resetLanguageAfterEmail' ) );

		add_action( '_mphb_translate_customer_email_before_send', array( $this, 'changeLanguageForCustomerEmail' ) );
		add_action( '_mphb_translate_customer_email_after_send', array( $this, 'resetLanguageAfterEmail' ) );

		add_filter( 'wpml_copy_from_original_custom_fields', array( $this, 'wpmlCopyPostMeta' ) );
	}

	/**
	 *
	 * @param string $name
	 * @param string $value
	 * @param string $context
	 * @param string $language Optional.
	 */
	public function registerWPMLString( $name, $value, $context = null ){
		if ( is_null( $context ) ) {
			$context = self::WPML_STRING_DOMAIN;
		}
		do_action( 'wpml_register_single_string', $context, $name, $value );
	}

	/**
	 *
	 * @param string $value
	 * @param string $name
	 * @param string $context
	 * @param string $language
	 * @return string
	 */
	public function translateString( $value, $name, $context = null, $language = null ){

		if ( !$this->isActiveWPML() ) {
			return $value;
		}

		if ( is_null( $context ) ) {
			$context = self::WPML_STRING_DOMAIN;
		}

		if ( is_null( $language ) ) {
			$language = $this->getCurrentLanguage();
		}

		return apply_filters( 'wpml_translate_single_string', $value, $context, $name, $language );
	}

	/**
	 *
	 * @param Entities\Booking $booking
	 */
	public function changeLanguageForAdminEmail( $booking ){

		$adminLanguage = $this->getDefaultLanguage();

		// now admin language is default language
//		if ( !$this->isActiveLanguage( $adminLanguage ) ) {
//			$adminLanguage = $this->getDefaultLanguage();
//		}

		if ( $adminLanguage !== $this->getCurrentLanguage() ) {
			$this->beforeEmailLanguage = $this->getCurrentLanguage();
			$this->switchLanguage( $adminLanguage );
		}

		$this->updateTextdomains();
	}

	/**
	 *
	 * @param Entities\Booking $booking
	 */
	public function changeLanguageForCustomerEmail( $booking ){
		$language = $booking->getLanguage();

		if ( !$this->isActiveLanguage( $language ) ) {
			$language = $this->getDefaultLanguage();
		}

		if ( $language !== $this->getCurrentLanguage() ) {
			$this->beforeEmailLanguage = $this->getCurrentLanguage();
			$this->switchLanguage( $language );
		}

		$this->updateTextdomains();
	}

	function updateTextdomains(){
		global $sitepress;
		$this->locale = $sitepress->get_locale( $this->getCurrentLanguage() );

		unload_textdomain( 'motopress-hotel-booking' );
		unload_textdomain( 'default' );

		MPHB()->loadTextDomain();
		load_default_textdomain( $this->locale );

		global $wp_locale;
		$wp_locale = new \WP_Locale();
	}

	/**
	 * Set correct locale code for emails
	 *
	 * @param string $locale
	 * @param string $domain
	 * @return string
	 */
	function setLocaleForEmails( $locale, $domain ){

		if ( $domain == 'motopress-hotel-booking' && $this->locale ) {
			$locale = $this->locale;
		}

		return $locale;
	}

	/**
	 *
	 * @param Entities\Booking $booking
	 */
	public function resetLanguageAfterEmail( $booking ){
		if ( !is_null( $this->beforeEmailLanguage ) ) {
			$this->switchLanguage( $this->beforeEmailLanguage );
			$this->updateTextdomains();
			$this->beforeEmailLanguage = null;
		}
	}

	/**
	 *
	 * @param string $language
	 * @return bool
	 */
	public function isActiveLanguage( $language ){
		return apply_filters( 'wpml_language_is_active', null, $language );
	}

	public function setupDefaultLanguage(){
		$this->storedLanguage = $this->getCurrentLanguage();
		$this->switchLanguage( $this->getDefaultLanguage() );
	}

	public function restoreLanguage(){
		$this->switchLanguage( $this->storedLanguage );
	}

	/**
	 * Fill data for copy original content button
	 *
	 * @param type $data
	 * @return string
	 */
	public function wpmlCopyPostMeta( $data ){
		$trid = filter_input( INPUT_POST, 'trid' );

		if ( get_post_type( $trid ) === MPHB()->postTypes()->rate()->getPostType() ) {
			$rate = MPHB()->getRateRepository()->findById( $trid );

			if ( $rate ) {
				$data['mphb_description'] = array(
					'editor_name'	 => 'mphb-mphb_description',
					'editor_type'	 => 'text',
					'value'			 => $rate->getDescription()
				);
			}
		}

		if ( get_post_type( $trid ) === MPHB()->postTypes()->roomType()->getPostType() ) {
			$roomType = MPHB()->getRoomTypeRepository()->findById( $trid );

			if ( $roomType ) {
				$data['mphb_view'] = array(
					'editor_name'	 => 'mphb-mphb_view',
					'editor_type'	 => 'text',
					'value'			 => $roomType->getView()
				);
			}
		}

		return $data;
	}

	/**
	 *
	 * @param type $atts
	 * @return type
	 */
	public function setWPMLDefaultLanguage( $atts ){

		if ( !isset( $atts['mphb_language'] ) ) {
			return;
		}

		$this->currentLanguage	 = $this->getCurrentLanguage();
		$originalLanguage		 = $this->getDefaultLanguage();

		switch ( $atts['mphb_language'] ) {
			case 'original':
				$toLanguage	 = $originalLanguage;
				break;
			default:
				$toLanguage	 = $atts['mphb_language'];
				break;
		}

		$this->switchLanguage( $toLanguage );
	}

	public function resetWPMLStoredLanguage( $atts ){

		if ( !isset( $atts['mphb_language'] ) ) {
			return;
		}

		$this->switchLanguage( $this->currentLanguage );
	}

	public function switchLanguage( $language = null ){
		if ( is_null( $language ) ) {
			$language = $this->getDefaultLanguage();
		}
		do_action( 'wpml_switch_language', $language );
	}

	/**
	 *
	 * @param int $id
	 * @param string $language Optional. Current Language by default.
	 * @return int
	 */
	public function wpmlTranslatePageId( $id, $language = null ){
		return $this->translateId( $id, 'page', $language );
	}

	/**
	 *
	 * @param int $id
	 * @param string $language Optional. Current Language by default.
	 * @return int
	 */
	public function wpmlTranslatePostId( $id, $language = null ){
		return $this->translateId( $id, null, $language );
	}

	/**
	 *
	 * @return string
	 */
	public function getCurrentLanguage(){
		return apply_filters( 'wpml_current_language', $this->getWPLanguage() );
	}

	/**
	 *
	 * @return string
	 */
	public function getWPLanguage(){
		$locale = get_locale();
		return substr( $locale, 0, 2 );
	}

	/**
	 *
	 * @return string
	 */
	public function getDefaultLanguage(){
		return apply_filters( 'wpml_default_language', $this->getWPLanguage() );
	}

	/**
	 *
	 * @param int $id
	 * @param string $type
	 * @return int
	 */
	public function getOriginalId( $id, $type, $typeOfType = 'post' ){
//		return apply_filters( 'wpml_master_post_from_duplicate', $id );
		return $this->translateId( $id, $type, $this->getDefaultLanguage() );
	}

	/**
	 *
	 * @param int $id
	 * @return array Key is lang code, value is translation id.
	 */
	public function getAllTranslationIds( $id ){
		$translations = apply_filters( 'wpml_post_duplicates', $id );

		if ( !is_array( $translations ) ) {
			$translations = array();
		}

		return $translations;
	}

	/**
	 *
	 * @param int $id
	 * @param string $type Optional. Use post, page, {custom post type name}, nav_menu, nav_menu_item, category, tag, etc.
	 * 						You can also pass 'any', to let WPML guess the type, but this will only work for posts. 'any' by default.
	 * @param string $language Optional. Current language by default
	 * @return int
	 */
	public function translateId( $id, $type = null, $language = null ){
		if ( !$type ) {
			$type = 'any';
		}
		return apply_filters( 'wpml_object_id', $id, $type, true, $language );
	}

	/**
	 *
	 * @return bool
	 */
	public function isTranslationPage(){
		return $this->isActiveWPML() && $this->getCurrentLanguage() !== $this->getDefaultLanguage();
	}

	/**
	 *
	 * @param string $postType
	 * @return boolean
	 */
	public function isTranslatablePostType( $postType ){
		return (bool) apply_filters( 'wpml_is_translated_post_type', null, $postType );
	}

	/**
	 *
	 * @return bool
	 */
	public function isActiveWPML(){
		return defined( 'ICL_SITEPRESS_VERSION' );
	}

	/**
	 *
	 * @param Entities\Rate $rate
	 * @param string $language Optional. Current Language by default.
	 * @return Entities\Rate
	 */
	public function translateRate( $rate, $language = null ){

		$translatedId = $this->translateId( $rate->getId(), MPHB()->postTypes()->rate()->getPostType() );

		$translatedRate = MPHB()->getRateRepository()->findById( $translatedId );

		return !is_null( $translatedRate ) ? $translatedRate : $rate;
	}

	/**
	 * @param Entities\Service $service
	 * @param string $language Optional. Current Language by default.
	 * @return Entities\Service
	 */
	public function translateService( $service, $language = null ){

		$translatedId = $this->translateId( $service->getId(), MPHB()->postTypes()->service()->getPostType(), $language );

		$translatedService = MPHB()->getServiceRepository()->findById( $translatedId );

		return !is_null( $translatedService ) ? $translatedService : $service;
	}

	/**
	 *
	 * @param Entities\RoomType $roomType
	 * @param string $language Optional. Current Language by default.
	 * @return Entities\RoomType
	 */
	public function translateRoomType( $roomType, $language = null ){
		$translatedId = $this->translateId( $roomType->getId(), MPHB()->postTypes()->roomType()->getPostType(), $language );

		$translatedRoomType = MPHB()->getRoomTypeRepository()->findById( $translatedId );

		return !is_null( $translatedRoomType ) ? $translatedRoomType : $roomType;
	}

}
