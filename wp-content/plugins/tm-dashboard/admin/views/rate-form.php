<?php
/**
 * Represents the view for the `Rate Form` section.
 *
 * @package    TM_Dashboard
 * @subpackage Views
 * @author     Cherry Team
 * @version    1.0.0
 * @license    GPL-3.0+
 * @copyright  2012-2017, Cherry Team
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

?>
<form id="tm-rateform" class="tm-rate-form">
	<fieldset>
		<label for="tm_rate_author_email"><?php esc_html_e( 'Your e-mail:', 'tm-dashboard' ); ?></label>
		<input type="email" class="tm-rate-form__field widefat cherry-ui-text" name="tm_rate_author_email" placeholder="<?php esc_html_e( 'Enter your e-mail', 'tm-dashboard' ); ?>" required>
	</fieldset>

	<fieldset>
		<label for="tm_rate_title"><?php esc_html_e( 'Title:', 'tm-dashboard' ); ?></label>
		<input type="text" class="tm-rate-form__field widefat cherry-ui-text" name="tm_rate_title" placeholder="<?php esc_html_e( 'Enter title for your review', 'tm-dashboard' ); ?>" required>
	</fieldset>

	<fieldset>
		<label for="tm_rate_text"><?php esc_html_e( 'Text:', 'tm-dashboard' ); ?></label>
		<textarea name="tm_rate_text" class="tm-rate-form__field cherry-ui-textarea" cols="30" rows="10" placeholder="<?php esc_html_e( 'Please, write a few words about us', 'tm-dashboard' ); ?>" required></textarea>
	</fieldset>

	<div class="tm-rate-form__btns">
		<button class="tm-dashboard-btn tm-rate-form__btn cherry5-ui-button cherry5-ui-button-primary-style" type="submit">
			<span class="tm-dashboard-btn__text"><?php esc_html_e( 'Send', 'tm-dashboard' ); ?></span>
			<span class="tm-dashboard-btn__loader"></span>
			<span class="tm-dashboard-btn__icon tm-dashboard-btn__icon--yes dashicons dashicons-yes"></span>
			<span class="tm-dashboard-btn__icon tm-dashboard-btn__icon--no dashicons dashicons-no"></span>
		</button>
	</div>
</form>
