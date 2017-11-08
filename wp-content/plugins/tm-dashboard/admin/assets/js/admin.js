( function( $, CherryJsCore ) {
	'use strict';

	CherryJsCore.utilites.namespace( 'tmAdminNotice' );
	CherryJsCore.tmAdminNotice = {
		/**
		 * Rendering notice message
		 *
		 * @param  {String} type    Message type
		 * @param  {String} message Message content
		 * @return {Void}
		 */
		noticeCreate: function( type, message, isPublicPage ) {
			var notice,
				rightDelta = 0,
				timeoutId,
				isPublic = isPublicPage || false;

			if ( ! message || 'true' === isPublic ) {
				return false;
			}

			notice = $( '<div class="cherry-handler-notice ' + type + '"><span class="dashicons"></span><div class="inner">' + message + '</div></div>' );

			$( 'body' ).prepend( notice );
			reposition();
			rightDelta = -1 * ( notice.outerWidth( true ) + 10 );
			notice.css( { 'right': rightDelta } );

			timeoutId = setTimeout( function() {
				notice.css( { 'right': 10 } ).addClass( 'show-state' );
			}, 100 );
			timeoutId = setTimeout( function() {
				rightDelta = -1 * ( notice.outerWidth( true ) + 10 );
				notice.css( { right: rightDelta } ).removeClass( 'show-state' );
			}, 15000 );
			timeoutId = setTimeout( function() {
				notice.remove();
				clearTimeout( timeoutId );
			}, 15500 );

			function reposition() {
				var topDelta = 100;

				$( '.cherry-handler-notice' ).each( function() {
					$( this ).css( { top: topDelta } );
					topDelta += $( this ).outerHeight( true );
				} );
			}
		}
	}

	CherryJsCore.utilites.namespace( 'rateForm' );
	CherryJsCore.rateForm = {
		ajaxInstance: null,
		handlerId: 'tm_rate_form_id',
		formId: '#tm-rateform',
		fieldClass: '.tm-rate-form__field',
		buttonClass: '.tm-rate-form__btn',

		init: function() {
			if ( window[ this.handlerId ] ) {
				this.ajaxInstance = new CherryJsCore.CherryAjaxHandler( {
					handlerId: this.handlerId,
					successCallback: this.sendSuccessCallback.bind( this )
				} );

				this.validateRateForm();
			}
		},

		validateRateForm: function() {

			if ( ! $.isFunction( jQuery.fn.validate ) || ! $( this.formId ).length ) {
				return ! 1;
			}

			var _this = this;

			$( this.formId ).validate({
				debug: true,
				errorElement: 'span',
				rules: {
					tm_rate_title: {
						minlength: 3
					}
				},
				submitHandler: function( form ) {
					_this.sendReviewHandler();
				}
			});
		},

		sendReviewHandler: function() {
			this.disableButton( this.buttonClass );
			this.ajaxInstance.sendFormData( this.formId );
		},

		sendSuccessCallback: function( response ) {
			if ( true === response.data.success ) {
				this.enableButton( this.buttonClass, 'success' );
			} else {
				this.enableButton( this.buttonClass, 'error' );
				response.message = this.ajaxInstance.handlerSettings.sys_messages.invalid_base_data;
				response.type    = 'error-notice';
			}
		},

		disableButton: function( button ) {
			$( button )
				.attr( 'disabled', 'disabled' )
				.addClass( 'processing' );
		},

		enableButton: function( button, state ) {
			var timer = null;

			$( button )
				.removeAttr( 'disabled' )
				.removeClass( 'processing' )
				.addClass( state );

			$( this.formId )[0].reset();
			$( this.fieldClass ).removeClass( 'valid' );

			timer = setTimeout(
				function() {
					$( button ).removeClass( state );
					clearTimeout( timer );
				},
				1000
			);
		}
	};

	CherryJsCore.utilites.namespace( 'verifiedTheme' );
	CherryJsCore.verifiedTheme = {

		verifiedThemeId: 'tm_verified_theme',
		themeForm: '.tm-updates__theme-form',
		themeFormControls: '.tm-updates__theme-form-controls',
		submiteButton: '.verified-theme',

		errorClass: '.error-field',

		verifiedTheme: null,

		init: function() {
			if ( window[ this.verifiedThemeId ] ) {
				this.verifiedTheme = new CherryJsCore.CherryAjaxHandler(
					{
						handlerId: this.verifiedThemeId,
						successCallback: this.checkVerifiedThemeCallback.bind( this )
					}
				);

				this.addEvents();
			}
		},

		addEvents: function() {
			$( this.themeForm )
				.on( 'click', this.submiteButton, this.checkVerifiedTheme.bind( this ) )
				.on( 'click', this.errorClass, this.removeErrorClass.bind( this ) );
		},

		checkVerifiedTheme: function( event ) {
			var button = event.target,
				form = $( event.delegateTarget ),
				data,
				validate;

			this.disableButton( button );

			data = form.serializeArray();
			validate = this.validateForm(data);

			if ( validate ) {
				$( validate, form ).addClass( 'error-field' );
				this.enableButton( button, 'error' );

				return !1;
			}

			$( '.utb-js' )
				.not( button )
				.attr( 'disabled', 'disabled' )
				.addClass( 'disabled' );

			this.verifiedTheme.sendData( data );

			return !1;
		},

		validateForm: function( data ) {
			var lenght = data.length -1,
				className = '',
				value,
				name;

			for (; lenght >= 0; lenght-- ) {
				value = data[ lenght ].value;
				name  = data[ lenght ].name;
				if ( ! value ) {
					className += ',[name="' + name + '"]';
				}
			}
			className = className.replace( /^,/, '' );

			return className;
		},

		removeErrorClass: function( event ) {
			var input = $( event.target ),
				className = this.errorClass.replace( /^\./, '');

			input.removeClass( className );
		},

		checkVerifiedThemeCallback: function( respons ) {
			var data     = respons.data,
				form     = $( '#'+ data.slug ),
				button   = $( this.submiteButton , form),
				controls = $( this.themeFormControls, form ),
				html     = data.htmlForm;

			$( '.utb-js' )
				.removeAttr( 'disabled' )
				.removeClass( 'disabled' );

			if ( ! data.verify ) {
				CherryJsCore.tmAdminNotice.noticeCreate( 'error-notice', data.message );
				this.enableButton( button, 'error' );
				return !1;
			}

			if ( html ) {
				controls
					.removeClass( 'show-form' )
					.addClass( 'hide-form' )
					.on( 'animationend', this.showControls.bind( this, controls, html ) );
			}

			CherryJsCore.tmAdminNotice.noticeCreate( 'success-notice', data.message );
			this.enableButton( $( button , form), 'success' );

			return !1;
		},

		showControls: function( controls, html ){
			controls.html( html )
				.removeClass( 'hide-form' )
				.addClass( 'show-form' );
		},

		disableButton: function( button ) {
			$( button )
				.attr( 'disabled', 'disabled' )
				.addClass( 'processing' );
		},

		enableButton: function( button, className ) {
			var timer = null;

			$( button )
				.removeAttr( 'disabled' )
				.removeClass( 'processing' )
				.addClass( className );

			timer = setTimeout(
				function() {
					$( button ).removeClass( className );
					clearTimeout( timer );
				},
				1000
			);
		}
	};

	CherryJsCore.utilites.namespace( 'updateTheme' );
	CherryJsCore.updateTheme = {
		ajaxBackupId: 'tm_theme_backup',
		ajaxBackupHandler: null,

		ajaxUpdateId: 'tm_update_theme',
		ajaxUpdateHandler: null,

		sectionClass: '.tm-updates__theme',
		wrapClass: '.tm-updates__theme-wrap',
		themeForm: '.tm-updates__theme-form',
		submiteButton: '.tm-update-theme',
		continueUpdateButton: '#update-theme-continue',
		popUp: '#tm-dashboard-update-popup',
		popUpBg: '.tm-dashboard-popup__background',
		closePopUpButton: '#update-theme-cancel',
		themeImage: '.tm-updates__theme-image',
		imageLable: '.tm-notification-image-label',

		processClass: 'tm-dashboard-processing',
		errorClass: '.error-field',

		tempData: null,

		init: function() {
			if ( window[ this.ajaxBackupId ] ) {
				this.ajaxBackupHandler = new CherryJsCore.CherryAjaxHandler(
					{
						handlerId: this.ajaxBackupId,
						successCallback: this.ajaxBackupHandlerCallback.bind( this )
					}
				);
			}

			if ( window[ this.ajaxUpdateId ] ) {
				this.ajaxUpdateHandler = new CherryJsCore.CherryAjaxHandler(
					{
						handlerId: this.ajaxUpdateId,
						successCallback: this.ajaxUpdateHandlerCallback.bind( this )
					}
				);
			}

			this.addEvents();
		},

		addEvents: function() {
			$( this.themeForm )
				.on( 'click', this.submiteButton, this.checkUpdateTheme.bind( this ) );

			$( 'body' )
				.on( 'click.tm-update', this.popUpBg, this.cancelUpdate.bind( this ) )
				.on( 'click.tm-update', this.closePopUpButton, this.cancelUpdate.bind( this ) )
				.on( 'click.tm-update', this.continueUpdateButton, this.startUpdate.bind( this ) );
		},

		checkUpdateTheme: function( event ) {
			var button = event.target,
				form   = $( event.delegateTarget ),
				data   = {
					slug: form.attr( 'name' ),
					version: $( '[name="version"]', form ).val(),
					updateVersion: $( '[name="update-version"]', form ).val()
				},
				autoBackup = form.data( 'autoBackup' ).toString();

			this.tempData = data;
			this.disableButton( button );

			$( '.tm-updates' )
				.addClass( this.processClass );

			$( '.utb-js' )
				.not( button )
				.attr( 'disabled', 'disabled' )
				.addClass( 'disabled' );

			if ( 'true' == autoBackup ) {
				this.startUpdate( event );

			} else {
				$( this.popUp ).addClass( 'show open' );
				$( window ).one( 'keyup', this.cancelUpdate.bind( this ) );
			}

			return !1;
		},

		startUpdate: function( event ) {
			var form       = $( event.delegateTarget ),
				autoBackup = form.data( 'autoBackup' ),
				button;

			if ( this.tempData ) {
				this.ajaxBackupHandler.sendData( this.tempData.slug );
			} else {
				this.tempData = null;
				button = $( '#check-theme-' + this.tempData.slug );
				this.enableButton( button, 'error' );
			}

			if ( 'true' != autoBackup ) {
				this.hidePopUp();
			}

			return !1;
		},

		cancelUpdate: function ( event ){
			if ( 'keyup' === event.type && 27 !== event.keyCode ){
				return !1;
			}

			if ( ! this.tempData ){
				return !1;
			}

			var button = $( '#check-theme-' + this.tempData.slug );
			this.tempData = null;

			this.hidePopUp();
			this.enableButton( button, 'error' );

			$( '.tm-updates' )
				.removeClass( this.processClass );

			$( '.utb-js' )
				.removeAttr( 'disabled' )
				.removeClass( 'disabled' );

			return !1;
		},

		hidePopUp: function() {
			$( this.popUp ).removeClass( 'open' );
			$( '.tm-dashboard-popup__inner', this.popUp ).one( 'animationend', this.hidePopUpWrapper.bind( this ) );
		},

		hidePopUpWrapper: function() {
			$( this.popUp ).removeClass( 'show' );
		},

		ajaxBackupHandlerCallback: function( response ) {
			var form     = $( '#'+ response.data.target ),
				button   = $( this.submiteButton, form ),
				table    = $( '[data-themename="' + response.data.target + '"]', $( this.sectionClass ) ),
				template = wp.template( 'new-backup-template' );
				// limit    = table.data( 'backupLimit' ),
				// rowCount = 0;

			if ( true === response.data.success ) {
				// rowCount = $( 'tbody tr', table );

				// if ( rowCount.length >= limit ) {

				// 	var diff = rowCount.length;
				// 	diff--;

				// 	rowCount.each( function( index, item ) {
				// 		if ( index == diff ) {
				// 			item.remove();
				// 		}
				// 	} )
				// }

				table
					.prepend( template( {
						name: response.data.extra.name,
						date: response.data.extra.date,
						version: response.data.extra.version,
					} ) )
					.removeClass( 'hide' );

				this.ajaxUpdateHandler.sendData( this.tempData );
				this.tempData = null;

			} else {
				response.message = this.ajaxBackupHandler.handlerSettings.sys_messages.invalid_base_data;
				response.type    = 'error-notice';

				this.enableButton( button, 'error' );

				$( '.tm-updates' )
					.removeClass( this.processClass );

				$( '.utb-js' )
					.removeAttr( 'disabled' )
					.removeClass( 'disabled' );
			}
		},

		ajaxUpdateHandlerCallback: function( response ) {
			var data     = response.data,
				form     = $( '#'+ data.slug ),
				controls = $( this.themeFormControls, form ),
				button   = $( this.submiteButton, form ),
				timeout,
				imageLable = form.closest( this.wrapClass ).siblings( this.themeImage ).find( this.imageLable );

			$( '.tm-updates' )
				.removeClass( this.processClass );

			$( '.utb-js' )
				.removeAttr( 'disabled' )
				.removeClass( 'disabled' );

			if ( true === data.error ) {
				this.enableButton( button, 'error' );
				CherryJsCore.tmAdminNotice.noticeCreate( 'error-notice', data.message );
				return !1;
			}

			$( '.current-version', form ).html( data.updateVersion );

			this.enableButton( button, 'success' );
			CherryJsCore.tmAdminNotice.noticeCreate( 'success-notice', data.message );

			timeout = setTimeout( function(){

				button
					.addClass( 'disabled' )
					.attr( 'disabled', 'disabled' )

				imageLable.remove();

				clearTimeout( timeout );
			}, 1000 );

		},

		removeErrorClass: function( event ) {
			var input = $( event.target ),
				className = this.errorClass.replace( /^\./, '');

			input.removeClass( className );
		},

		disableButton: function( button ) {
			$( button )
				.attr( 'disabled', 'disabled' )
				.addClass( 'processing' );
		},

		enableButton: function( button, className ) {
			var timer = null;

			$( button )
				.removeAttr( 'disabled' )
				.addClass( className )
				.removeClass( 'utb-js processing' );

			timer = setTimeout(
				function() {
					$( button ).removeClass( className );
					clearTimeout( timer );
				},
				1000
			);
		}
	};

	CherryJsCore.utilites.namespace( 'createBackup' );
	CherryJsCore.createBackup = {
		ajaxInstance: null,
		handlerId: 'tm_theme_backup',
		sectionClass: '.tm-updates__theme',
		themeNameClass: '.tm-backup-theme-name',
		buttonClass: '.tm-backup-theme-btn',
		buttonId: '#tm-backup-theme-btn-',
		statusClass: 'tm-dashboard-table__tr--adding',
		processClass: 'tm-dashboard-processing',

		init: function() {
			if ( window[ this.handlerId ] ) {
				this.ajaxInstance = new CherryJsCore.CherryAjaxHandler( {
					async: true,
					handlerId: this.handlerId,
					successCallback: this.successCallback.bind( this )
				} );

				this.addEvents();
			}
		},

		addEvents: function() {
			$( this.buttonClass )
				.on( 'click', 'button', this.sendHandler.bind( this ) );
		},

		sendHandler: function( event ) {
			var btn       = $( event.target ),
				formId    = btn.attr( 'form' ),
				form      = $( '#' + formId ),
				themeName = $( this.themeNameClass, form ).val();

			form
				.closest( this.sectionClass )
				.addClass( this.processClass );

			this.disableButton( event.target );
			this.ajaxInstance.sendData( themeName );
		},

		successCallback: function( response ) {
			var button   = this.buttonId + response.data.target,
				table    = $( '[data-themename="' + response.data.target + '"]', $( this.sectionClass ) ),
				template = wp.template( 'new-backup-template' );
				// limit    = table.data( 'backupLimit' ),
				// rowCount = 0;

			table
				.closest( this.sectionClass )
				.removeClass( this.processClass );

			if ( true === response.data.success ) {
				this.enableButton( button, 'success' );

				// rowCount = $( 'tbody tr', table );

				// if ( rowCount.length >= limit ) {

				// 	var diff = rowCount.length;
				// 	diff--;

				// 	rowCount.each( function( index, item ) {
				// 		if ( index == diff ) {
				// 			item.remove();
				// 		}
				// 	} )
				// }

				table
					.prepend( template( {
						name: response.data.extra.name,
						date: response.data.extra.date,
						version: response.data.extra.version,
					} ) )
					.removeClass( 'hide' );

			} else {
				this.enableButton( button, 'error' );
				response.message = this.ajaxInstance.handlerSettings.sys_messages.invalid_base_data;
				response.type    = 'error-notice';
			}
		},

		disableButton: function( button ) {
			$( button )
				.attr( 'disabled', 'disabled' )
				.addClass( 'processing' );
		},

		enableButton: function( button, state ) {
			var timer = null;

			if ( ! $( button ).length ) {
				return !1;
			}

			$( button )
				.removeAttr( 'disabled' )
				.removeClass( 'processing' )
				.addClass( state );

			timer = setTimeout(
				function() {
					$( button ).removeClass( state );
					clearTimeout( timer );
				},
				1000
			);
		}
	};

	CherryJsCore.utilites.namespace( 'saveBackupOptions' );
	CherryJsCore.saveBackupOptions = {
		ajaxInstance: null,
		handlerId: 'tm_backup_settings',
		sectionClass: '.tm-updates__theme',
		buttonClass: '.tm-backup-schedule-wrap--save-control',
		buttonId: '#tm-backup-save-btn-',
		processClass: 'tm-dashboard-processing',

		init: function() {
			if ( window[ this.handlerId ] ) {
				this.ajaxInstance = new CherryJsCore.CherryAjaxHandler( {
					handlerId: this.handlerId,
					successCallback: this.successCallback.bind( this )
				} );

				this.addEvents();
			}
		},

		addEvents: function() {
			$( this.buttonClass )
				.on( 'click', 'button', this.sendHandler.bind( this ) );
		},

		sendHandler: function( event ) {
			var form   = $( event.target ).attr( 'form' ),
				formId = '#' + form;

			$( formId )
				.closest( this.sectionClass )
				.addClass( this.processClass );

			this.disableButton( event.target );
			this.ajaxInstance.sendFormData( formId );
		},

		successCallback: function( response ) {
			var button = this.buttonId + response.data.target;

			$( button )
				.closest( this.sectionClass )
				.removeClass( this.processClass );

			if ( true === response.data.success ) {
				this.enableButton( button, 'success' );
				this.setDataAtts( response.data );

			} else {
				this.enableButton( button, 'error' );
				response.message = this.ajaxInstance.handlerSettings.sys_messages.invalid_base_data;
				response.type    = 'error-notice';
			}
		},

		setDataAtts: function( data ) {
			var form, table;

			if ( false === data.extra ) {
				return !1;
			}

			form  = $( '#' + data.target );
			table = $( '[data-themename="' + data.target + '"]', $( this.sectionClass ) );

			form.data( 'autoBackup', data.extra['auto-backup'] );
			table.data( 'backupLimit', data.extra['limit'] );
		},

		disableButton: function( button ) {
			$( button )
				.attr( 'disabled', 'disabled' )
				.addClass( 'processing' );
		},

		enableButton: function( button, state ) {
			var timer = null;

			if ( ! $( button ).length ) {
				return !1;
			}

			$( button )
				.removeAttr( 'disabled' )
				.removeClass( 'processing' )
				.addClass( state );

			timer = setTimeout(
				function() {
					$( button ).removeClass( state );
					clearTimeout( timer );
				},
				1000
			);
		}
	};

	CherryJsCore.utilites.namespace( 'restoreBackup' );
	CherryJsCore.restoreBackup = {
		tempData: null,
		ajaxInstance: null,
		handlerId: 'tm_theme_restore',

		sectionClass: '.tm-updates',
		tableClass: '.tm-dashboard-table',
		buttonClass: '.tm-dashboard-action-btn--restore',

		popUp: '#tm-dashboard-restore-popup',
		popUpInner: '.tm-dashboard-popup__inner',
		popUpBg: '.tm-dashboard-popup__background',
		restoreForm: '#tm-restore-form',
		restoreStart: '#tm-dashboard-restore-start',
		restoreCancel: '#tm-dashboard-restore-cancel',
		restoreSettings: '.cherry-checkbox-label, .cherry-checkbox-item',

		waitClass: 'tm-dashboard-action-btn--wait',
		statusClass: 'tm-dashboard-table__tr--restoring',
		processClass: 'tm-dashboard-processing',

		init: function() {
			if ( window[ this.handlerId ] ) {
				this.ajaxInstance = new CherryJsCore.CherryAjaxHandler( {
					async: true,
					handlerId: this.handlerId,
					successCallback: this.successCallback.bind( this )
				} );

				this.addEvents();
			}
		},

		addEvents: function() {
			$( this.tableClass )
				.on( 'click', this.buttonClass, this.sendHandler.bind( this ) );

			$( this.restoreForm )
				.on( 'click', this.restoreStart, this.startRestore.bind( this ) )
				.on( 'click', this.restoreCancel, this.cancelRestore.bind( this ) )
				.on( 'change', '.cherry-checkbox-input', this.toggleStartBtn.bind( this ) );

			$( 'body' )
				.on( 'click', this.popUpBg, this.cancelRestore.bind( this ) );
		},

		sendHandler: function( event ) {
			var btn   = $( event.target ),
				table = btn.closest( this.tableClass ),
				row   = btn.closest( 'tr' );

			this.tempData = {
				themeName: table.data( 'themename' ),
				archiveName: row.data( 'archivename' ),
				isAutoBackup: row.data( 'autobackup' ),
			};

			$( this.sectionClass )
				.addClass( this.processClass );

			row
				.addClass( this.statusClass );

			btn
				.addClass( this.waitClass );

			$( this.popUp ).addClass( 'show open' );
		},

		startRestore: function( event ) {
			var data = CherryJsCore.cherryHandlerUtils.serializeObject( $( this.restoreForm ) );

			if ( data ) {
				this.tempData.settings = data['tm-restore-settings'];
				this.ajaxInstance.sendData( this.tempData );

			} else {
				this.end( this.tempData.archiveName );
				this.tempData = null;
			}

			this.hidePopUp();
		},

		cancelRestore: function ( event ) {

			if ( 'keyup' === event.type && 27 !== event.keyCode ) {
				return !1;
			}

			if ( ! this.tempData ) {
				return !1;
			}

			this.end( this.tempData.archiveName );
			this.hidePopUp();
			this.tempData = null;
		},

		toggleStartBtn: function( event ) {
			var checkBoxes = $( '.cherry-checkbox-input', $( this.restoreForm ) );

			$( this.restoreStart )
				.prop( 'disabled', checkBoxes.filter( ':checked' ).length < 1 );
		},

		end: function( target ) {
			var row = $( '[data-archivename="' + target + '"]', $( this.tableClass ) ),
				btn = $( '.' + this.waitClass, row );

			$( this.sectionClass )
				.removeClass( this.processClass );

			row
				.removeClass();

			btn
				.removeClass( this.waitClass );
		},

		hidePopUp: function() {
			$( this.popUp ).removeClass( 'open' );
			$( this.popUpInner, this.popUp ).one( 'animationend', this.hidePopUpWrapper.bind( this ) );
		},

		hidePopUpWrapper: function() {
			$( this.popUp ).removeClass( 'show' );
		},

		successCallback: function( response ) {
			this.end( response.data.target );

			if ( true === response.data.success ) {
				this.changeThemeVersion( response.data.target );

			} else {
				response.message = response.data.empty_settings ? this.ajaxInstance.handlerSettings.sys_messages.empty_settings : this.ajaxInstance.handlerSettings.sys_messages.invalid_base_data;
				response.type    = 'error-notice';
			}
		},

		changeThemeVersion: function( archiveName ) {
			var archive = archiveName.split( '__' ),
				form    = $( '#' + archive[0] ),
				target  = $( '.current-version', form ),
				button  = $( '.tm-update-theme', form ),
				currentVersion = archive[1].slice( 1 ),
				updateVersion  = $( '[name="update-version"]', form ).val(),
				compare        = this.versionCompare( currentVersion, updateVersion );

			if ( target.length > 0 ) {
				target.html( currentVersion );
			}

			if ( -1 === compare && button.length > 0 ) {
				button
					.removeClass( 'disabled' )
					.removeAttr( 'disabled', 'disabled' );

			} else {
				button
					.addClass( 'disabled' )
					.attr( 'disabled', 'disabled' );

				form
					.closest( '.tm-updates__theme-wrap' )
					.siblings( '.tm-updates__theme-image' )
					.find( '.tm-notification-image-label' )
					.remove();
			}
		},

		/**
		 * Simply compares two string version values.
		 *
		 * Example:
		 * versionCompare('1.1', '1.2') => -1
		 * versionCompare('1.1', '1.1') =>  0
		 * versionCompare('1.2', '1.1') =>  1
		 * versionCompare('2.23.3', '2.22.3') => 1
		 *
		 * Returns:
		 * -1 = left is LOWER than right
		 *  0 = they are equal
		 *  1 = left is GREATER = right is LOWER
		 *  And FALSE if one of input versions are not valid
		 *
		 * @function
		 * @param {String} left  Version #1
		 * @param {String} right Version #2
		 * @return {Integer|Boolean}
		 * @author Alexey Bass (albass)
		 * @link https://gist.github.com/alexey-bass/1115557
		 */
		versionCompare: function( left, right ) {

			if ( typeof left + typeof right != 'stringstring' ) {
				return false;
			}

			var a = left.split( '.' ),
				b = right.split( '.' ),
				i = 0,
				len = Math.max( a.length, b.length );

			for ( ; i < len; i++ ) {

				if ( ( a[i] && !b[i] && parseInt( a[i] ) > 0 ) || ( parseInt(a[i]) > parseInt( b[i] ) ) ) {
					return 1;

				} else if ( ( b[i] && !a[i] && parseInt( b[i]) > 0 ) || ( parseInt( a[i]) < parseInt( b[i] ) ) ) {
					return -1;
				}
			}

			return 0;
		}
	};

	CherryJsCore.utilites.namespace( 'deleteBackup' );
	CherryJsCore.deleteBackup = {
		ajaxInstance: null,
		handlerId: 'tm_backup_delete',
		sectionClass: '.tm-updates__theme',
		tableClass: '.tm-dashboard-table',
		buttonClass: '.tm-dashboard-action-btn--delete',
		waitClass: 'tm-dashboard-action-btn--wait',
		statusClass: 'tm-dashboard-table__tr--removing',
		processClass: 'tm-dashboard-processing',

		init: function() {
			if ( window[ this.handlerId ] ) {
				this.ajaxInstance = new CherryJsCore.CherryAjaxHandler( {
					async: true,
					handlerId: this.handlerId,
					successCallback: this.successCallback.bind( this )
				} );

				this.addEvents();
			}
		},

		addEvents: function() {
			$( this.tableClass )
				.on( 'click', this.buttonClass, this.sendHandler.bind( this ) );
		},

		sendHandler: function( event ) {
			var btn   = $( event.target ),
				row   = btn.closest( 'tr' ),
				table = row.closest( this.tableClass );

			if ( ! window.confirm( tmDashbordVars.messages.deleteBackup ) ) {
				return !1;
			}

			table
				.closest( this.sectionClass )
				.addClass( this.processClass );

			row
				.addClass( this.statusClass );

			btn
				.addClass( this.waitClass );

			this.ajaxInstance.sendData( {
				themeName: table.data( 'themename' ),
				archiveName: row.data( 'archivename' ),
				isAutoBackup: row.data( 'autobackup' )
			} );
		},

		successCallback: function( response ) {
			var row   = $( '[data-archivename="' + response.data.target + '"]', $( this.tableClass ) ),
				btn   = $( '.' + this.waitClass, row ),
				table = row.closest( this.tableClass );

			row
				.closest( this.sectionClass )
				.removeClass( this.processClass );

			btn
				.removeClass( this.waitClass );

			if ( true === response.data.success ) {
				this.deleteRow( row );

				if ( true === response.data.empty ) {
					table.addClass( 'hide' );
				}

			} else {
				response.message = this.ajaxInstance.handlerSettings.sys_messages.invalid_base_data;
				response.type    = 'error-notice';
			}
		},

		deleteRow: function( row ) {

			if ( ! row.length ) {
				return !1;
			}

			row.fadeOut( 400, function() {
				this.remove();
			} );
		}
	};

	CherryJsCore.utilites.namespace( 'downloadBackup' );
	CherryJsCore.downloadBackup = {
		ajaxInstance: null,
		handlerId: 'tm_backup_download',
		sectionClass: '.tm-updates__theme',
		tableClass: '.tm-dashboard-table',
		buttonClass: '.tm-dashboard-action-btn--download',
		waitClass: 'tm-dashboard-action-btn--wait',
		statusClass: 'tm-dashboard-table__tr--downloading',
		processClass: 'tm-dashboard-processing',

		init: function() {
			if ( window[ this.handlerId ] ) {
				this.ajaxInstance = new CherryJsCore.CherryAjaxHandler( {
					async: true,
					handlerId: this.handlerId,
					successCallback: this.successCallback.bind( this )
				} );

				this.addEvents();
			}
		},

		addEvents: function() {
			$( this.tableClass )
				.on( 'click', this.buttonClass, this.sendBackupHandler.bind( this ) );
		},

		sendBackupHandler: function( event ) {
			var btn   = $( event.target ),
				row   = btn.closest( 'tr' ),
				table = row.closest( this.tableClass );

			table
				.closest( this.sectionClass )
				.addClass( this.processClass );

			row
				.addClass( this.statusClass );

			btn
				.addClass( this.waitClass );

			this.ajaxInstance.sendData( {
				themeName: table.data( 'themename' ),
				archiveName: row.data( 'archivename' ),
				isAutoBackup: row.data( 'autobackup' )
			} );
		},

		successCallback: function( response ) {
			var row = $( '[data-archivename="' + response.data.target + '"]', $( this.tableClass ) );

			row
				.removeClass()
				.find( '.' + this.waitClass )
				.removeClass( this.waitClass )
				.closest( this.sectionClass )
				.removeClass( this.processClass );

			if ( true === response.data.success ) {
				window.location = response.data.url;

			} else {
				response.message = this.ajaxInstance.handlerSettings.sys_messages.invalid_base_data;
				response.type    = 'error-notice';
			}
		}
	};

	CherryJsCore.utilites.namespace( 'togglePanel' );
	CherryJsCore.togglePanel = {
		panelClass: '.tm-updates__theme',
		buttonClass: '.tm-dashboard-toggle-backup-section',
		toggleClass: 'tm-dashboard-toggle-backup-section--hide',

		init: function() {
			this.addEvents();
		},

		addEvents: function() {
			$( this.panelClass )
				.on( 'click', this.buttonClass, this.sendBackupHandler.bind( this ) );
		},

		sendBackupHandler: function( event ) {
			$( event.target ).toggleClass( this.toggleClass );
		}
	};

	CherryJsCore.rateForm.init();
	CherryJsCore.updateTheme.init();
	CherryJsCore.verifiedTheme.init();
	CherryJsCore.createBackup.init();
	CherryJsCore.saveBackupOptions.init();
	CherryJsCore.restoreBackup.init();
	CherryJsCore.deleteBackup.init();
	CherryJsCore.downloadBackup.init();
	CherryJsCore.togglePanel.init();

}( jQuery, window.CherryJsCore ) );
