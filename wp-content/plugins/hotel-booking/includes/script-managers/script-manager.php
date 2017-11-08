<?php

namespace MPHB\ScriptManagers;

abstract class ScriptManager {

	/**
	 *
	 * @var string[]
	 */
	protected $scriptDependencies = array( 'jquery' );

	public function addDependency( $dependency ){
		$this->scriptDependencies[] = $dependency;
	}

	/**
	 *
	 * @param string $locale Optional.
	 * @return string
	 */
	protected function getDatepickLocale( $locale = null ){
		$availableLocales = include('datepick-locales.php');
		if ( is_null( $locale ) ) {
			$locale = get_locale();
		}
		if ( !in_array( $locale, $availableLocales ) ) {
			$locale = substr( $locale, 0, 2 );
			if ( !in_array( $locale, $availableLocales ) ) {
				$locale = 'en_US';
			}
		}
		return $locale;
	}

	protected function registerDatepickLocalization(){

		$locale = $this->getDatepickLocale();

		if ( $locale === 'en_US' ) {
			// en_US is default locale for datepicker and not needs localization
			return;
		}

		$datepickStyleLocale = str_replace( '-', '_', $locale );

		wp_register_script( 'mphb-kbwood-datepick-localization', MPHB()->getPluginUrl( 'vendors/kbwood/datepick/jquery.datepick-' . $datepickStyleLocale . '.js' ), array( 'mphb-kbwood-datepick' ), MPHB()->getVersion(), true );
		$this->addDependency( 'mphb-kbwood-datepick-localization' );
	}

	abstract public function register();

	abstract public function enqueue();
}
