<?php

namespace MPHB\Emails;

use \MPHB\Library\Emogrifier;
use \MPHB\Admin\Fields;
use \MPHB\Admin\Groups;
use \MPHB\Admin\Tabs;

abstract class AbstractEmail {

	/**
	 *
	 * @var string
	 */
	protected $id;

	/**
	 *
	 * @var string
	 */
	protected $label;

	/**
	 *
	 * @var string
	 */
	protected $description = '';

	/**
	 *
	 * @var bool
	 */
	protected $active;

	/**
	 *
	 * @var string
	 */
	protected $defaultSubject = '';

	/**
	 *
	 * @var string
	 */
	protected $defaultHeaderText = '';

	/**
	 *
	 * @var \MPHB\Entities\Booking
	 */
	protected $booking;

	/**
	 *
	 * @var EmailTemplater
	 */
	protected $templater;

	/**
	 *
	 * @param array $args
	 * @param string $args['id'] ID of Email.
	 * @param string $args['label'] Label.
	 * @param string $args['description'] Optional. Email description.
	 * @param string $args['default_subject'] Optional. Default subject of email.
	 * @param string $args['default_header_text'] Optional. Default text in header.
	 * @param EmailTemplater $templater
	 */
	public function __construct( $args, EmailTemplater $templater ){

		$this->id		 = $args['id'];
		$this->templater = $templater;

		add_action( 'plugins_loaded', array( $this, 'initStrings' ) );
	}

	public function initStrings(){
		$this->initDescription();
		$this->initLabel();
	}

	/**
	 * Send Mail
	 *
	 * @return bool
	 */
	public function send(){
		return MPHB()->emails()->getMailer()->send(
				$this->getReceiver(), $this->getSubject(), $this->getMessage()
		);
	}

	/**
	 *
	 * @param \MPHB\Entities\Booking $booking
	 * @param array $atts
	 * @return bool
	 */
	public function trigger( $booking, $atts = array() ){
		if ( $this->isDisabled() ) {
			return false;
		}

		$this->setupBooking( $booking );

		if ( isset( $atts['payment'] ) ) {
			$this->templater->setupPayment( $atts['payment'] );
		}

		$isSended = $this->send();

		$this->log( $isSended );

		return $isSended;
	}

	/**
	 *
	 * @return string
	 */
	protected function getSubject(){

		$subject = $this->getOption( 'subject' );

		if ( empty( $subject ) ) {
			$subject = $this->getDefaultSubject();
		}

		$subject = $this->replaceTags( $subject );

		return $subject;
	}

	/**
	 *
	 * @return string
	 */
	protected function getMessage(){
		$message = $this->getMessageHeader();
		$message .= $this->getMessageContent();
		$message .= $this->getMessageFooter();

		$message = $this->applyStyles( $message );

		return $message;
	}

	/**
	 * Applies styles for mail html.
	 *
	 * @param string $html HTML of mail.
	 * @return string
	 */
	protected function applyStyles( $html ){
		// make sure we only inline CSS for html emails
		ob_start();
		require MPHB()->getPluginPath( 'includes/emails/templates/email-styles.php' );
		$styles = ob_get_clean();

		// apply CSS styles inline for picky email clients
		$emogrifier	 = new Emogrifier\Emogrifier( $html, $styles );
		$html		 = $emogrifier->emogrify();

		return $html;
	}

	/**
	 *
	 * @return string
	 */
	protected function getMessageHeader(){
		ob_start();
		$headerText	 = $this->getMessageHeaderText();
		require MPHB()->getPluginPath( 'includes/emails/templates/email-header.php' );
		$header		 = ob_get_contents();
		ob_end_clean();
		return $header;
	}

	/**
	 *
	 * @return string
	 */
	protected function getMessageContent(){
		$template	 = $this->getMessageTemplate();
		$content	 = $this->replaceTags( $template );
		return $content;
	}

	/**
	 *
	 * @return string
	 */
	protected function getMessageTemplate(){
		$template = $this->getOption( 'content' );

		if ( empty( $template ) ) {
			$template = $this->getDefaultMessageTemplate();
		}

		return $template;
	}

	/**
	 *
	 * @return string
	 */
	protected function getMessageHeaderText(){
		$headerText = $this->getOption( 'header' );

		if ( empty( $headerText ) ) {
			$headerText = $this->getDefaultMessageHeaderText();
		}

		$headerText = $this->replaceTags( $headerText );

		return $headerText;
	}

	/**
	 *
	 * @return string
	 */
	protected function getMessageFooter(){
		ob_start();
		$footerText	 = $this->getMessageFooterText();
		require MPHB()->getPluginPath( 'includes/emails/templates/email-footer.php' );
		$footer		 = ob_get_contents();
		ob_end_clean();
		return $footer;
	}

	/**
	 *
	 * @return string
	 */
	protected function getMessageFooterText(){
		return MPHB()->settings()->emails()->getFooterText();
	}

	/**
	 *
	 * @param string $template
	 * @return string
	 */
	protected function replaceTags( $template ){
		return $this->templater->replaceTags( $template );
	}

	/**
	 *
	 * @return string
	 */
	public function getDefaultMessageTemplate(){
		$templateName = str_replace( '_', '-', $this->id );
		ob_start();
		mphb_get_template_part( 'emails/' . $templateName );
		return ob_get_clean();
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
	 * @return bool
	 */
	public function isDisabled(){
		$disableOption = $this->getOption( 'disable', false );
		return filter_var( $disableOption, \FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * @note available after plugins_loaded
	 *
	 * @return string
	 */
	public function getLabel(){
		return $this->label;
	}

	/**
	 * @note available after plugins_loaded
	 *
	 * @return string
	 */
	public function getDescription(){
		return $this->description;
	}

	/**
	 *
	 * @param Tabs\SettingsTab $tab
	 */
	public function generateSettingsFields( Tabs\SettingsTab $tab ){
		$optionPrefix	 = 'mphb_email_' . $this->id;
		$group			 = new Groups\SettingsGroup(
			$optionPrefix, $this->label, $tab->getOptionGroupName(), $this->description . '<br/><br/>' . $this->templater->getTagsDescription()
		);

		$disableField = Fields\FieldFactory::create( $optionPrefix . '_disable', array(
				'type'			 => 'checkbox',
				'inner_label'	 => __( 'Disable this email notification', 'motopress-hotel-booking' )
			) );

		$subjectField = Fields\FieldFactory::create( $optionPrefix . '_subject', array(
				'type'			 => 'text',
				'label'			 => __( 'Subject', 'motopress-hotel-booking' ),
				'default'		 => $this->getDefaultSubject(),
				'placeholder'	 => $this->getDefaultSubject(),
				'size'			 => 'large',
				'translatable'	 => true
			) );

		$headerField = Fields\FieldFactory::create( $optionPrefix . '_header', array(
				'type'			 => 'text',
				'label'			 => __( 'Header', 'motopress-hotel-booking' ),
				'default'		 => $this->getDefaultMessageHeaderText(),
				'placeholder'	 => $this->getDefaultMessageHeaderText(),
				'size'			 => 'large',
				'translatable'	 => true
			) );

		$contentField = Fields\FieldFactory::create( $optionPrefix . '_content', array(
				'type'			 => 'rich-editor',
				'label'			 => __( 'Email Template', 'motopress-hotel-booking' ),
				'rows'			 => 24,
				'default'		 => $this->getDefaultMessageTemplate(),
				'translatable'	 => true
			) );

		$group->addField( $disableField );
		$group->addField( $subjectField );
		$group->addField( $headerField );
		$group->addField( $contentField );

		$tab->addGroup( $group );
	}

	/**
	 *
	 * @param string $name
	 * @param string $default Optional.
	 * @return mixed
	 */
	protected function getOption( $name, $default = '' ){

		$optionName = 'mphb_email_' . $this->id . '_' . $name;

		$optionValue = get_option( $optionName, $default );

		$optionValue = apply_filters( 'mphb_translate_string', $optionValue, $optionName );

		return $optionValue;
	}

	/**
	 *
	 * @param Booking $booking
	 */
	protected function setupBooking( $booking ){
		$this->booking = $booking;
		$this->templater->setupBooking( $booking );
	}

	abstract protected function getReceiver();

	abstract protected function log( $isSended );

	abstract public function getDefaultSubject();

	abstract public function getDefaultMessageHeaderText();

	abstract protected function initLabel();

	abstract protected function initDescription();
}
