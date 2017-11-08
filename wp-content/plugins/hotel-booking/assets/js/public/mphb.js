(function( $ ) {
	$( function() {
		MPHB.DateRules = can.Construct.extend( {}, {
	dates: {},
	init: function( dates ) {
		this.dates = dates;
	},
	/**
	 *
	 * @param {Date} date
	 * @returns {Boolean}
	 */
	canCheckIn: function( date ) {
		var formattedDate = this.formatDate( date );
		if ( !this.dates.hasOwnProperty( formattedDate ) ) {
			return true;
		}
		return !this.dates[formattedDate].not_check_in && !this.dates[formattedDate].not_stay_in;
	},
	/**
	 *
	 * @param {Date} date
	 * @returns {Boolean}
	 */
	canCheckOut: function( date ) {
		var formattedDate = this.formatDate( date );
		if ( !this.dates.hasOwnProperty( formattedDate ) ) {
			return true;
		}
		return !this.dates[formattedDate].not_check_out;
	},
	/**
	 *
	 * @param {Date} date
	 * @returns {Boolean}
	 */
	canStayIn: function( date ) {
		var formattedDate = this.formatDate( date );
		if ( !this.dates.hasOwnProperty( formattedDate ) ) {
			return true;
		}
		return !this.dates[formattedDate].not_stay_in;
	},
	/**
	 *
	 * @param {Date} dateFrom
	 * @param {Date} stopDate
	 * @returns {Date}
	 */
	getNearestNotStayInDate: function( dateFrom, stopDate ) {
		var nearestDate = MPHB.Utils.cloneDate( stopDate );
		var dateFromFormatted = $.datepick.formatDate( 'yyyy-mm-dd', dateFrom );
		var stopDateFormatted = $.datepick.formatDate( 'yyyy-mm-dd', stopDate );

		$.each( this.dates, function( ruleDate, rule ) {
			if ( ruleDate > stopDateFormatted ) {
				return false;
			}
			if ( dateFromFormatted > ruleDate ) {
				return true;
			}
			if ( rule.not_stay_in ) {
				nearestDate = $.datepick.parseDate( 'yyyy-mm-dd', ruleDate );
				return false;
			}
		} );
		return nearestDate;
	},
	/**
	 *
	 * @param {Date} date
	 * @returns {string}
	 */
	formatDate: function( date ) {
		return $.datepick.formatDate( 'yyyy-mm-dd', date );
	}
} );

MPHB.Datepicker = can.Control.extend( {}, {
	form: null,
	hiddenElement: null,
	init: function( el, args ) {
		this.form = args.form;
		this.setupHiddenElement();
		this.initDatepick();
	},
	setupHiddenElement: function() {
		var hiddenElementId = this.element.attr( 'id' ) + '-hidden';
		this.hiddenElement = $( '#' + hiddenElementId );

		// fix date
		if ( !this.hiddenElement.val() ) {
//			this.element.val( '' );
		} else {
			var date = $.datepick.parseDate( MPHB._data.settings.dateTransferFormat, this.hiddenElement.val() );
			var fixedValue = $.datepick.formatDate( MPHB._data.settings.dateFormat, date );
			this.element.val( fixedValue );
		}
	},
	initDatepick: function() {
		var defaultSettings = {
			dateFormat: MPHB._data.settings.dateFormat,
			altFormat: MPHB._data.settings.dateTransferFormat,
			altField: this.hiddenElement,
			minDate: MPHB.HotelDataManager.myThis.today,
			monthsToShow: MPHB._data.settings.numberOfMonthDatepicker,
			firstDay: MPHB._data.settings.firstDay
		};
		var datepickSettings = $.extend( defaultSettings, this.getDatepickSettings() );
		this.element.datepick( datepickSettings );
	},
	/**
	 *
	 * @returns {Object}
	 */
	getDatepickSettings: function() {
		return {};
	},
	/**
	 * @return {Date|null}
	 */
	getDate: function() {
		var dateStr = this.element.val();
		var date = null;
		try {
			date = $.datepick.parseDate( MPHB._data.settings.dateFormat, dateStr );
		} catch ( e ) {
			date = null;
		}
		return date;
	},
	/**
	 *
	 * @param {string} format Optional. Datepicker format by default.
	 * @returns {String} Date string or empty string.
	 */
	getFormattedDate: function( format ) {
		if ( typeof (format) === 'undefined' ) {
			format = MPHB._data.settings.dateFormat;
		}
		var date = this.getDate();
		return date ? $.datepick.formatDate( format, date ) : '';
	},
	/**
	 * @param {Date} date
	 */
	setDate: function( date ) {
		this.element.datepick( 'setDate', date );
	},
	/**
	 * @param {string} option
	 */
	getOption: function( option ) {
		return this.element.datepick( 'option', option );
	},
	/**
	 * @param {string} option
	 * @param {mixed} value
	 */
	setOption: function( option, value ) {
		this.element.datepick( 'option', option, value );
	},
	/**
	 *
	 * @returns {Date|null}
	 */
	getMinDate: function() {
		var minDate = this.getOption( 'minDate' );
		return minDate !== null && minDate !== '' ? MPHB.Utils.cloneDate( minDate ) : null;
	},
	/**
	 *
	 * @returns {Date|null}
	 */
	getMaxDate: function() {
		var maxDate = this.getOption( 'maxDate' );
		return maxDate !== null && maxDate !== '' ? MPHB.Utils.cloneDate( maxDate ) : null;
	},
	/**
	 *
	 * @returns {undefined}
	 */
	clear: function() {
		this.element.datepick( 'clear' );
	},
	/**
	 * @param {Date} date
	 * @param {string} format Optional. Default 'yyyy-mm-dd'.
	 */
	formatDate: function( date, format ) {
		format = typeof (format) !== 'undefined' ? format : 'yyyy-mm-dd';
		return $.datepick.formatDate( format, date );
	},
	/**
	 *
	 * @returns {undefined}
	 */
	refresh: function() {
		$.datepick._update( this.element[0], true );
		$.datepick._updateInput( this.element[0], false );
	}

} );
MPHB.GlobalRules = can.Construct.extend( {}, {
	minDays: null,
	maxDays: null,
	checkInDays: null,
	checkOutDays: null,
	init: function( data ) {
		this.minDays = data.min_days;
		this.maxDays = data.max_days;
		this.checkInDays = data.check_in_days;
		this.checkOutDays = data.check_out_days;
	},
	/**
	 *
	 * @param {Date} date
	 * @returns {Boolean}
	 */
	isCheckOutSatisfy: function( date ) {
		var checkOutDay = date.getDay().toString();
		return $.inArray( checkOutDay, this.checkOutDays ) !== -1;
	},
	/**
	 *
	 * @param {Date} date
	 * @returns {Boolean}
	 */
	isCheckInSatisfy: function( date ) {
		var checkInDay = date.getDay().toString();
		return $.inArray( checkInDay, this.checkInDays ) !== -1;
	},
	/**
	 *
	 * @param {Date} checkInDate
	 * @param {Date} checkOutDate
	 * @returns {Boolean}
	 */
	isCorrect: function( checkInDate, checkOutDate ) {

		if ( typeof checkInDate === 'undefined' || typeof checkOutDate === 'undefined' ) {
			return true;
		}

		if ( !this.isCheckInSatisfy( checkInDate ) ) {
			return false;
		}

		if ( !this.isCheckOutSatisfy( checkOutDate ) ) {
			return false;
		}

		var minAllowedCheckOut = $.datepick.add( MPHB.Utils.cloneDate( checkInDate ), this.minDays );
		var maxAllowedCheckOut = $.datepick.add( MPHB.Utils.cloneDate( checkInDate ), this.maxDays );

		return checkOutDate >= minAllowedCheckOut && checkOutDate <= maxAllowedCheckOut;
	},
	/**
	 *
	 * @param {Date} checkInDate
	 * @returns {Date}
	 */
	getMinCheckOutDate: function( checkInDate ) {
		return $.datepick.add( MPHB.Utils.cloneDate( checkInDate ), this.minDays, 'd' );
	},
	/**
	 *
	 * @param {Date} checkInDate
	 * @returns {Date}
	 */
	getMaxCheckOutDate: function( checkInDate ) {
		return $.datepick.add( MPHB.Utils.cloneDate( checkInDate ), this.maxDays, 'd' );
	}
} );
MPHB.HotelDataManager = can.Construct.extend( {
	myThis: null,
	ROOM_STATUS_AVAILABLE: 'available',
	ROOM_STATUS_NOT_AVAILABLE: 'not-available',
	ROOM_STATUS_BOOKED: 'booked',
	ROOM_STATUS_PAST: 'past'
}, {
	today: null,
	roomTypesData: {},
	globalRules: null,
	dateRules: null,
	init: function( data ) {
		MPHB.HotelDataManager.myThis = this;
		this.initRoomTypesData( data.room_types_data );
		this.initRules( data.rules );
		this.setToday( $.datepick.parseDate( MPHB._data.settings.dateTransferFormat, data.today ) );
	},
	/**
	 *
	 * @returns {undefined}
	 */
	initRoomTypesData: function( roomTypesData ) {
		var self = this;
		$.each( roomTypesData, function( id, data ) {
			self.roomTypesData[id] = new MPHB.RoomTypeData( id, data );
		} );
	},
	initRules: function( rules ) {
		this.globalRules = new MPHB.GlobalRules( rules.global );
		this.dateRules = new MPHB.DateRules( rules.dates )
	},
	/**
	 *
	 * @param {Date} date
	 * @returns {undefined}
	 */
	setToday: function( date ) {
		this.today = date;
	},
	/**
	 *
	 * @param {int|string} id ID of roomType
	 * @returns {MPHB.RoomTypeData|false}
	 */
	getRoomTypeData: function( id ) {
		return this.roomTypesData.hasOwnProperty( id ) ? this.roomTypesData[id] : false;
	},
	/**
	 *
	 * @param {Object} dateData
	 * @param {Date} date
	 * @returns {Object}
	 */
	fillDateCellData: function( dateData, date ) {
		var rulesTitles = [ ];
		var rulesClasses = [ ];

		if ( !this.dateRules.canStayIn( date ) ) {
			rulesTitles.push( MPHB._data.translations.notStayIn );
			rulesClasses.push( 'mphb-not-stay-in-date' );
		}
		if ( !this.dateRules.canCheckIn( date ) || !this.globalRules.isCheckInSatisfy( date ) ) {
			rulesTitles.push( MPHB._data.translations.notCheckIn );
			rulesClasses.push( 'mphb-not-check-in-date' );
		}
		if ( !this.dateRules.canCheckOut( date ) || !this.globalRules.isCheckOutSatisfy( date ) ) {
			rulesTitles.push( MPHB._data.translations.notCheckOut );
			rulesClasses.push( 'mphb-not-check-out-date' );
		}

		if ( rulesTitles.length ) {
			dateData.title += ' ' + MPHB._data.translations.rules + ' ' + rulesTitles.join( ', ' );
		}

		if ( rulesClasses.length ) {
			dateData.dateClass += (dateData.dateClass.length ? ' ' : '') + rulesClasses.join( ' ' );
		}

		return dateData;
	},
} );
MPHB.Utils = can.Construct.extend( {
	/**
	 *
	 * @param {Date} date
	 * @returns {String}
	 */
	formatDateToCompare: function( date ) {
		return $.datepick.formatDate( 'yyyymmdd', date );
	},
	/**
	 *
	 * @param {Date} date
	 * @returns {Date}
	 */
	cloneDate: function( date ) {
		return new Date( date.getTime() );
	}
}, {} );
MPHB.Gateway = can.Construct.extend( {}, {
	amount: 0,
	paymentDescription: '',
	init: function( args ) {
		this.billingSection = args.billingSection;
		this.initSettings( args.settings );
	},
	initSettings: function( settings ) {
		this.amount = settings.amount;
		this.paymentDescription = settings.paymentDescription;
	},
	canSubmit: function() {
		return true;
	},
	updateData: function( data ) {
		this.amount = data.amount;
		this.paymentDescription = data.paymentDescription;
	}
} );
/**
 *
 * @requires ./gateway.js
 */
MPHB.StripeGateway = MPHB.Gateway.extend( {}, {
	publicKey: '',
	imageUrl: '',
	locale: '',
	allowRememberMe: false,
	needBillingAddress: false,
	useBitcoin: false,
	panelLabel: '',
	handler: null,
	init: function( args ) {
		this._super( args );
		this.initHandler();
	},
	initSettings: function( settings ) {
		this._super( settings );
		this.publicKey = settings.publicKey;
		this.imageUrl = settings.checkoutImageUrl;
		this.allowRememberMe = settings.allowRememberMe;
		this.needBillingAddress = settings.needBillingAddress;
		this.useBitcoin = settings.useBitcoin;
		this.locale = settings.locale;
	},
	initHandler: function() {

		var self = this;
		var configureAtts = {
			key: this.publicKey,
			image: this.imageUrl,
			locale: this.locale,
			name: MPHB._data.settings.siteName,
			bitcoin: this.useBitcoin,
			currency: MPHB._data.settings.currency.toLowerCase(),
			billingAddress: this.needBillingAddress,
			allowRememberMe: this.allowRememberMe,
//			closed: function() {},
		};
		if ( self.panelLabel ) {
			configureAtts['panelLabel'] = self.panelLabel;
		}
		this.handler = StripeCheckout.configure( configureAtts );

		// Close Checkout on page navigation:
		window.addEventListener( 'popstate', function() {
			self.handler.close();
		} );
	},
	openModal: function() {
		var self = this;
		this.handler.open( {
			amount: self.amount,
			description: self.paymentDescription,
			token: function( token, args ) {

				self.storeToken( token );

				if ( self.needBillingAddress ) {
					self.storeBillingData( args );
				}

				self.storeEmail( token.email );
				self.billingSection.parentForm.element.submit();
				self.billingSection.showPreloader();
			},
		} );
	},
	/**
	 *
	 * @returns {Boolean}
	 */
	canSubmit: function() {
		if ( this.isTokenStored() ) {
			return true;
		}

		try {
			this.openModal();
		} catch ( e ) {
			console.log( 'error:', e );
		}

		return false;
	},
	/**
	 *
	 * @param {Object} token
	 * @returns {undefined}
	 */
	storeToken: function( token ) {
		var $tokenEl = this.billingSection.billingFieldsWrapperEl.find( '[name="mphb_stripe_token"]' );
		$tokenEl.val( token.id );
	},
	/**
	 *
	 * @returns {Boolean}
	 */
	isTokenStored: function() {
		var $tokenEl = this.billingSection.billingFieldsWrapperEl.find( '[name="mphb_stripe_token"]' );
		return $tokenEl.length && $tokenEl.val() !== '';
	},
	/**
	 *
	 * @param {string} email
	 * @returns {undefined}
	 */
	storeEmail: function( email ) {
		this.billingSection.billingFieldsWrapperEl.find( '[name="mphb_stripe_email"]' ).val( email );
	},
	/**
	 *
	 * @param {Object} data
	 * @returns {undefined}
	 */
	storeBillingData: function( data ) {
		var self = this;
		var acceptableFields = [
			'billing_address_city',
			'billing_address_country',
			'billing_address_country_code',
			'billing_address_line1',
			'billing_address_line2',
			'billing_address_state',
			'billing_address_zip',
			'billing_name'
		];

		$.each( acceptableFields, function( key, field ) {
			if ( data.hasOwnProperty( field ) ) {
				var fieldEl = self.billingSection.billingFieldsWrapperEl.find( '[name="mphb_stripe_' + field + '"]' );
				if ( fieldEl.length ) {
					fieldEl.val( data[field] );
				}
			}
		} );

	}
} );
/**
 *
 * @requires ./gateway.js
 * @requires ./stripe-gateway.js
 */
MPHB.BillingSection = can.Control.extend( {}, {
	updateBillingFieldsTimeout: null,
	parentForm: null,
	billingFieldsWrapperEl: null,
	gateways: {},
	init: function( el, args ) {
		this.parentForm = args.form;
		this.billingFieldsWrapperEl = this.element.find( '.mphb-billing-fields' );
		this.initGateways( args.gateways );
	},
	initGateways: function( gateways ) {
		var self = this;
		$.each( gateways, function( gatewayId, gatewaySettings ) {
			var gateway = null;
			switch ( gatewayId ) {
				case 'stripe':
					gateway = new MPHB.StripeGateway( {
						'billingSection': self,
						'settings': MPHB._data.gateways[gatewayId]
					} );
					break;
				default:
					gateway = new MPHB.Gateway( {
						'billingSection': self,
						'settings': MPHB._data.gateways[gatewayId]
					} );
					break;
			}
			if ( typeof gateway !== 'undefined' ) {
				self.gateways[gatewayId] = gateway;
			}
		} );
	},
	'[name="mphb_gateway_id"] change': function( el, e ) {
		var self = this;
		var gatewayId = el.val();
		this.showPreloader();
		this.billingFieldsWrapperEl.empty().addClass( 'mphb-billing-fields-hidden' );
		clearTimeout( this.updateBillingFieldsTimeout );
		this.updateBillingFieldsTimeout = setTimeout( function() {
			var formData = self.parentForm.parseFormToJSON();
			$.ajax( {
				url: MPHB._data.ajaxUrl,
				type: 'GET',
				dataType: 'json',
				data: {
					action: 'mphb_get_billing_fields',
					mphb_nonce: MPHB._data.nonces.mphb_get_billing_fields,
					mphb_gateway_id: gatewayId,
					formValues: formData
				},
				success: function( response ) {
					if ( response.hasOwnProperty( 'success' ) ) {
						if ( response.success ) {
							self.billingFieldsWrapperEl.html( response.data.fields );
							if ( response.data.hasVisibleFields ) {
								self.billingFieldsWrapperEl.removeClass( 'mphb-billing-fields-hidden' );
							} else {
								self.billingFieldsWrapperEl.addClass( 'mphb-billing-fields-hidden' );
							}

						} else {
							self.showError( response.data.message );
						}
					} else {
						self.showError( MPHB._data.translations.errorHasOccured );
					}
				},
				error: function( jqXHR ) {
					self.showError( MPHB._data.translations.errorHasOccured );
				},
				complete: function( jqXHR ) {
					self.hidePreloader();
				}
			} );
		}, 500 );
	},
	hideErrors: function() {
		this.parentForm.hideErrors();
	},
	showError: function( message ) {
		this.parentForm.showError( message );
	},
	showPreloader: function() {
		this.parentForm.showPreloader();
	},
	hidePreloader: function() {
		this.parentForm.hidePreloader();
	},
	canSubmit: function() {
		var gatewayId = this.getSelectedGateway();
		return !this.gateways.hasOwnProperty( gatewayId ) || this.gateways[gatewayId].canSubmit();
	},
	getSelectedGateway: function() {
		return this.element.find( '[name="mphb_gateway_id"]:checked' ).val();
	},
	updateGatewaysData: function( gatewaysData ) {
		var self = this;
		$.each( gatewaysData, function( gatewayId, gatewayData ) {
			if ( self.gateways.hasOwnProperty( gatewayId ) ) {
				self.gateways[gatewayId].updateData( gatewayData );
			}
		} );
	}
} );
/**
 *
 * @requires ./billing-section.js
 */
MPHB.CheckoutForm = can.Control.extend( {
	myThis: null
}, {
	priceBreakdownTableEl: null,
	bookBtnEl: null,
	errorsWrapperEl: null,
	preloaderEl: null,
	billingSection: null,
	waitResponse: false,
	updateInfoTimeout: null,
	init: function( el, args ) {
		MPHB.CheckoutForm.myThis = this;
		this.bookBtnEl = this.element.find( 'input[type=submit]' );
		this.errorsWrapperEl = this.element.find( '.mphb-errors-wrapper' );
		this.preloaderEl = this.element.find( '.mphb-preloader' );
		this.priceBreakdownTableEl = this.element.find( 'table.mphb-price-breakdown' );
		if ( MPHB._data.settings.useBilling ) {
			this.billingSection = new MPHB.BillingSection( this.element.find( '#mphb-billing-details' ), {
				'form': this,
				'gateways': MPHB._data.gateways
			} );
		}

	},
	setTotal: function( value ) {
		var totalField = this.element.find( '.mphb-total-price-field' );
		if ( totalField.length ) {
			totalField.html( value );
		}
	},
	setDeposit: function( value ) {
		var depositField = this.element.find( '.mphb-deposit-amount-field' );
		if ( depositField.length ) {
			depositField.html( value );
		}
	},
	setupPriceBreakdown: function( priceBreakdown ) {
		this.priceBreakdownTableEl.replaceWith( priceBreakdown );
		this.priceBreakdownTableEl = this.element.find( 'table.mphb-price-breakdown' );
	},
	updateCheckoutInfo: function() {
		var self = this;
		self.hideErrors();
		self.showPreloader();
		clearTimeout( this.updateInfoTimeout );
		this.updateInfoTimeout = setTimeout( function() {
			var data = self.parseFormToJSON();
			$.ajax( {
				url: MPHB._data.ajaxUrl,
				type: 'GET',
				dataType: 'json',
				data: {
					action: 'mphb_update_checkout_info',
					mphb_nonce: MPHB._data.nonces.mphb_update_checkout_info,
					formValues: data
				},
				success: function( response ) {
					if ( response.hasOwnProperty( 'success' ) ) {
						if ( response.success ) {
							self.setTotal( response.data.total );
							self.setupPriceBreakdown( response.data.priceBreakdown );

							if ( MPHB._data.settings.useBilling ) {
								self.setDeposit( response.data.deposit );
								self.billingSection.updateGatewaysData( response.data.gateways );
							}
						} else {
							self.showError( response.data.message );
						}
					} else {
						self.showError( MPHB._data.translations.errorHasOccured );
					}
				},
				error: function( jqXHR ) {
					self.showError( MPHB._data.translations.errorHasOccured );
				},
				complete: function( jqXHR ) {
					self.hidePreloader();
				}
			} );
		}, 500 );
	},
	'[name="mphb_room_rate_id"] change': function( el, e ) {
		this.updateCheckoutInfo();
	},
	'.mphb_sc_checkout-services-list input, .mphb_sc_checkout-services-list select change': function( el, e ) {
		this.updateCheckoutInfo();
	},
	hideErrors: function() {
		this.errorsWrapperEl.empty().addClass( 'mphb-hide' );
	},
	showError: function( message ) {
		this.errorsWrapperEl.html( message ).removeClass( 'mphb-hide' );
	},
	showPreloader: function() {
		this.waitResponse = true;
		this.bookBtnEl.attr( 'disabled', 'disabled' );
		this.preloaderEl.removeClass( 'mphb-hide' );
	},
	hidePreloader: function() {
		this.waitResponse = false;
		this.bookBtnEl.removeAttr( 'disabled' );
		this.preloaderEl.addClass( 'mphb-hide' );
	},
	parseFormToJSON: function() {
		return this.element.serializeJSON();
	},
	'submit': function( el, e ) {
		if ( this.waitResponse ) {
			return false;
		}
		if ( MPHB._data.settings.useBilling && !this.billingSection.canSubmit() ) {
			return false;
		}
	}

} );
MPHB.ReservationForm = can.Control.extend( {
	MODE_SUBMIT: 'submit',
	MODE_NORMAL: 'normal',
	MODE_WAITING: 'waiting'
}, {
	/**
	 * @var jQuery
	 */
	formEl: null,
	/**
	 * @var MPHB.RoomTypeCheckInDatepicker
	 */
	checkInDatepicker: null,
	/**
	 * @var MPHB.RoomTypeCheckOutDatepicker
	 */
	checkOutDatepicker: null,
	/**
	 * @var jQuery
	 */
	reserveBtn: null,
	/**
	 * @var jQuery
	 */
	reserveBtnPreloader: null,
	/**
	 * @var jQuery
	 */
	errorsWrapper: null,
	/**
	 * @var String
	 */
	mode: null,
	/**
	 * @var int
	 */
	roomTypeId: null,
	/**
	 * @var MPHB.RoomTypeData
	 */
	roomTypeData: null,
	setup: function( el, args ) {
		this._super( el, args );
		this.mode = MPHB.ReservationForm.MODE_NORMAL;
	},
	init: function( el, args ) {
		this.formEl = el;
		this.roomTypeId = parseInt( this.formEl.attr( 'id' ).replace( /^booking-form-/, '' ) );
		this.roomTypeData = MPHB.HotelDataManager.myThis.getRoomTypeData( this.roomTypeId );
		this.errorsWrapper = this.formEl.find( '.mphb-errors-wrapper' );
		this.initCheckInDatepicker();
		this.initCheckOutDatepicker();
		this.initReserveBtn();

		var self = this;
		$( window ).on( 'mphb-update-date-room-type-' + this.roomTypeId, function() {
			self.reservationForm.refreshDatepickers();
		} );
	},
	'submit': function( el, e ) {

		if ( this.mode !== MPHB.ReservationForm.MODE_SUBMIT ) {
			e.preventDefault();
			e.stopPropagation();
			this.setFormWaitingMode();
			var self = this;
			$.ajax( {
				url: MPHB._data.ajaxUrl,
				type: 'GET',
				dataType: 'json',
				data: {
					action: 'mphb_check_room_availability',
					mphb_nonce: MPHB._data.nonces.mphb_check_room_availability,
					roomTypeId: self.roomTypeId,
					checkInDate: this.checkInDatepicker.getFormattedDate( MPHB._data.settings.dateTransferFormat ),
					checkOutDate: this.checkOutDatepicker.getFormattedDate( MPHB._data.settings.dateTransferFormat )
				},
				success: function( response ) {
					if ( response.hasOwnProperty( 'success' ) ) {
						if ( response.success ) {
							self.proceedToCheckout();
						} else {
							self.showError( response.data.message );
							if ( response.data.hasOwnProperty( 'updatedData' ) ) {
								self.roomTypeData.update( response.data.updatedData );
							}
							self.clearDatepickers();
						}
					} else {
						self.showError( MPHB._data.translations.errorHasOccured );
					}
				},
				error: function( jqXHR ) {
					self.showError( MPHB._data.translations.errorHasOccured );
				},
				complete: function( jqXHR ) {
					self.setFormNormalMode();
				}
			} );
		}
	},
	proceedToCheckout: function() {
		this.mode = MPHB.ReservationForm.MODE_SUBMIT;
		this.unlock();
		this.formEl.submit();
	},
	showError: function( message ) {
		this.clearErrors();
		var errorMessage = $( '<p>', {
			'class': 'mphb-error',
			'html': message
		} );
		this.errorsWrapper.append( errorMessage ).removeClass( 'mphb-hide' );
	},
	clearErrors: function() {
		this.errorsWrapper.empty().addClass( 'mphb-hide' );
	},
	lock: function() {
		this.element.find( '[name]' ).attr( 'disabled', 'disabled' );
		this.reserveBtn.attr( 'disabled', 'disabled' ).addClass( 'mphb-disabled' );
		this.reserveBtnPreloader.removeClass( 'mphb-hide' );
	},
	unlock: function() {
		this.element.find( '[name]' ).removeAttr( 'disabled' );
		this.reserveBtn.removeAttr( 'disabled', 'disabled' ).removeClass( 'mphb-disabled' );
		this.reserveBtnPreloader.addClass( 'mphb-hide' );
	},
	setFormWaitingMode: function() {
		this.mode = MPHB.ReservationForm.MODE_WAITING;
		this.lock();
	},
	setFormNormalMode: function() {
		this.mode = MPHB.ReservationForm.MODE_NORMAL;
		this.unlock();
	},
	initCheckInDatepicker: function() {
		var checkInEl = this.formEl.find( 'input[type="text"][name=mphb_check_in_date]' );
		this.checkInDatepicker = new MPHB.RoomTypeCheckInDatepicker( checkInEl, {'form': this} );
	},
	initCheckOutDatepicker: function() {
		var checkOutEl = this.formEl.find( 'input[type="text"][name=mphb_check_out_date]' );
		this.checkOutDatepicker = new MPHB.RoomTypeCheckOutDatepicker( checkOutEl, {'form': this} );
	},
	initReserveBtn: function() {
		this.reserveBtn = this.formEl.find( '.mphb-reserve-btn' );
		this.reserveBtnPreloader = this.formEl.find( '.mphb-preloader' );

		this.setFormNormalMode();
	},
	/**
	 *
	 * @param {bool} setDate
	 * @returns {undefined}
	 */
	updateCheckOutLimitations: function( setDate ) {
		if ( typeof setDate === 'undefined' ) {
			setDate = true;
		}
		var limitations = this.retrieveCheckOutLimitations( this.checkInDatepicker.getDate(), this.checkOutDatepicker.getDate() );

		this.checkOutDatepicker.setOption( 'minDate', limitations.minDate );
		this.checkOutDatepicker.setOption( 'maxDate', limitations.maxDate );
		this.checkOutDatepicker.setDate( setDate ? limitations.date : null );
	},
	/**
	 *
	 * @param {type} checkInDate
	 * @param {type} checkOutDate
	 * @returns {Object} with keys
	 *	- {Date} minDate
	 *	- {Date} maxDate
	 *	- {Date|null} date
	 */
	retrieveCheckOutLimitations: function( checkInDate, checkOutDate ) {

		var minDate = MPHB.HotelDataManager.myThis.today;
		var maxDate = null;
		var recommendedDate = null;

		if ( checkInDate !== null ) {
			var minDate = MPHB.HotelDataManager.myThis.globalRules.getMinCheckOutDate( checkInDate );

			var maxDate = MPHB.HotelDataManager.myThis.globalRules.getMaxCheckOutDate( checkInDate );
			maxDate = this.roomTypeData.getNearestLockedDate( checkInDate, maxDate );
			maxDate = this.roomTypeData.getNearestHaveNotPriceDate( checkInDate, maxDate );
			maxDate = MPHB.HotelDataManager.myThis.dateRules.getNearestNotStayInDate( checkInDate, maxDate );

			if ( this.isCheckOutDateNotValid( checkOutDate, minDate, maxDate ) ) {
				recommendedDate = this.retrieveRecommendedCheckOutDate( minDate, maxDate );
			} else {
				recommendedDate = checkOutDate;
			}
		}

		return {
			minDate: minDate,
			maxDate: maxDate,
			date: recommendedDate
		};
	},
	/**
	 *
	 * @param {Date} minDate
	 * @param {Date} maxDate
	 * @returns {Date|null}
	 */
	retrieveRecommendedCheckOutDate: function( minDate, maxDate ) {
		var recommendedDate = null;
		var expectedDate = MPHB.Utils.cloneDate( minDate );

		while ( MPHB.Utils.formatDateToCompare( expectedDate ) <= MPHB.Utils.formatDateToCompare( maxDate ) ) {

			var prevDay = $.datepick.add( MPHB.Utils.cloneDate( expectedDate ), -1, 'd' );

			if (
				!this.isCheckOutDateNotValid( expectedDate, minDate, maxDate ) &&
				this.roomTypeData.hasPriceForDate( prevDay )
				) {
				recommendedDate = expectedDate;
				break;
			}
			expectedDate = $.datepick.add( expectedDate, 1, 'd' );
		}

		return recommendedDate;

	},
	/**
	 *
	 * @param {Date} checkOutDate
	 * @param {Date} minDate
	 * @param {Date} maxDate
	 * @returns {Boolean}
	 */
	isCheckOutDateNotValid: function( checkOutDate, minDate, maxDate ) {
		return checkOutDate === null
			|| MPHB.Utils.formatDateToCompare( checkOutDate ) < MPHB.Utils.formatDateToCompare( minDate )
			|| MPHB.Utils.formatDateToCompare( checkOutDate ) > MPHB.Utils.formatDateToCompare( maxDate )
			|| !MPHB.HotelDataManager.myThis.globalRules.isCheckOutSatisfy( checkOutDate )
			|| !MPHB.HotelDataManager.myThis.dateRules.canCheckOut( checkOutDate )
	},
	clearDatepickers: function() {
		this.checkInDatepicker.clear();
		this.checkOutDatepicker.clear();
	},
	refreshDatepickers: function() {
		this.checkInDatepicker.refresh();
		this.checkOutDatepicker.refresh();
	}

} );
MPHB.RoomTypeCalendar = can.Control.extend( {}, {
	roomTypeData: null,
	roomTypeId: null,
	init: function( el, args ) {
		this.roomTypeId = parseInt( el.attr( 'id' ).replace( /^mphb-calendar-/, '' ) );
		this.roomTypeData = MPHB.HotelDataManager.myThis.getRoomTypeData( this.roomTypeId );
		var self = this;
		el.hide().datepick( {
			onDate: function( date, current ) {
				var dateData = {
					selectable: false,
					dateClass: 'mphb-date-cell',
					title: '',
				};

				if ( current ) {
					dateData = self.roomTypeData.fillDateData( dateData, date );
				} else {
					dateData.dateClass += ' mphb-extra-date';
				}

				return dateData;
			},
			'minDate': MPHB.HotelDataManager.myThis.today,
			'monthsToShow': MPHB._data.settings.numberOfMonthCalendar,
			'firstDay': MPHB._data.settings.firstDay
		} ).show();

		$( window ).on( 'mphb-update-room-type-data-' + this.roomTypeId, function( e ) {
			self.refresh();
		} );

	},
	refresh: function() {
		this.element.hide();
		$.datepick._update( this.element[0], true );
		this.element.show();
	}

} );
/**
 *
 * @requires ./../datepicker.js
 */
MPHB.RoomTypeCheckInDatepicker = MPHB.Datepicker.extend( {}, {
	/**
	 *
	 * @returns {Object}
	 */
	getDatepickSettings: function() {
		var self = this;
		return {
			onDate: function( date, current ) {
				var dateData = {
					dateClass: 'mphb-date-cell',
					selectable: false,
					title: ''
				}

				if ( current ) {
					var status = self.form.roomTypeData.getDateStatus( date );
					dateData = self.form.roomTypeData.fillDateData( dateData, date );

					var canCheckIn = status === MPHB.HotelDataManager.ROOM_STATUS_AVAILABLE &&
						MPHB.HotelDataManager.myThis.globalRules.isCheckInSatisfy( date ) &&
						MPHB.HotelDataManager.myThis.dateRules.canCheckIn( date );

					if ( canCheckIn ) {
						dateData.selectable = true;
					}

				} else {
					dateData.dateClass += ' mphb-extra-date';
				}

				if ( dateData.selectable ) {
					dateData.dateClass += ' mphb-date-selectable';
				}

				return dateData;
			},
			onSelect: function( dates ) {
				self.form.updateCheckOutLimitations();
			},
			pickerClass: 'mphb-datepick-popup mphb-check-in-datepick',
		};
	},
	/**
	 * @param {Date} date
	 */
	setDate: function( date ) {

		if ( date == null ) {
			return this._super( date );
		}

		if ( !MPHB.HotelDataManager.myThis.globalRules.isCheckInSatisfy( date ) ) {
			return this._super( null );
		}

		if ( !MPHB.HotelDataManager.myThis.dateRules.canCheckIn( date ) ) {
			return this._super( null );
		}

		return this._super( date );
	}

} );
/**
 *
 * @requires ./../datepicker.js
 */
MPHB.RoomTypeCheckOutDatepicker = MPHB.Datepicker.extend( {}, {
	/**
	 *
	 * @returns {Object}
	 */
	getDatepickSettings: function() {
		var self = this;
		return {
			onDate: function( date, current ) {
				var dateData = {
					dateClass: 'mphb-date-cell',
					selectable: false,
					title: ''
				};
				if ( current ) {
					var checkInDate = self.form.checkInDatepicker.getDate();
					var earlierThanMin = self.getMinDate() !== null && MPHB.Utils.formatDateToCompare( date ) < MPHB.Utils.formatDateToCompare( self.getMinDate() );
					var laterThanMax = self.getMaxDate() !== null && MPHB.Utils.formatDateToCompare( date ) > MPHB.Utils.formatDateToCompare( self.getMaxDate() );

					if ( checkInDate !== null && MPHB.Utils.formatDateToCompare( date ) === MPHB.Utils.formatDateToCompare( checkInDate ) ) {
						dateData.dateClass += ' mphb-check-in-date';
						dateData.title += MPHB._data.translations.checkInDate;
					}

					if ( earlierThanMin ) {
						var minStayDate = MPHB.HotelDataManager.myThis.globalRules.getMinCheckOutDate( checkInDate );
						if ( MPHB.Utils.formatDateToCompare( date ) < MPHB.Utils.formatDateToCompare( checkInDate ) ) {
							dateData.dateClass += ' mphb-earlier-min-date mphb-earlier-check-in-date';
						} else if ( MPHB.Utils.formatDateToCompare( date ) < MPHB.Utils.formatDateToCompare( minStayDate ) ) {
							dateData.dateClass += ' mphb-earlier-min-date';
							dateData.title += (dateData.title.length ? ' ' : '') + MPHB._data.translations.lessThanMinDaysStay;
						}
					}

					if ( laterThanMax ) {
						var maxStayDate = MPHB.HotelDataManager.myThis.globalRules.getMaxCheckOutDate( checkInDate );
						if ( MPHB.Utils.formatDateToCompare( date ) < MPHB.Utils.formatDateToCompare( maxStayDate ) ) {
							dateData.title += (dateData.title.length ? ' ' : '') + MPHB._data.translations.laterThanMaxDate;
						} else {
							dateData.title += (dateData.title.length ? ' ' : '') + MPHB._data.translations.moreThanMaxDaysStay;
						}
						dateData.dateClass += ' mphb-later-max-date';
					}

					dateData = self.form.roomTypeData.fillDateData( dateData, date );

					var canCheckOut = !earlierThanMin && !laterThanMax &&
						MPHB.HotelDataManager.myThis.globalRules.isCheckOutSatisfy( date ) &&
						MPHB.HotelDataManager.myThis.dateRules.canCheckOut( date );

					if ( canCheckOut ) {
						dateData.selectable = true;
					}
				} else {
					dateData.dateClass += ' mphb-extra-date';
				}

				if ( dateData.selectable ) {
					dateData.dateClass += ' mphb-selectable-date';
				} else {
					dateData.dateClass += ' mphb-unselectable-date';
				}

				return dateData;
			},
			pickerClass: 'mphb-datepick-popup mphb-check-out-datepick',
		};
	},
	/**
	 * @param {Date} date
	 */
	setDate: function( date ) {

		if ( date == null ) {
			return this._super( date );
		}

		if ( !MPHB.HotelDataManager.myThis.globalRules.isCheckOutSatisfy( date ) ) {
			return this._super( null );
		}

		if ( !MPHB.HotelDataManager.myThis.dateRules.canCheckOut( date ) ) {
			return this._super( null );
		}

		return this._super( date );
	},
} );
MPHB.RoomTypeData = can.Construct.extend( {}, {
	id: null,
	bookedDates: {},
	havePriceDates: {},
	activeRoomsCount: 0,
	/**
	 *
	 * @param {Object}	data
	 * @param {Object}	data.bookedDates
	 * @param {Object}	data.havePriceDates
	 * @param {int}		data.activeRoomsCount
	 * @returns {undefined}
	 */
	init: function( id, data ) {
		this.id = id;
		this.setRoomsCount( data.activeRoomsCount );
		this.setDates( data.dates );
	},
	update: function( data ) {
		if ( data.hasOwnProperty( 'activeRoomsCount' ) ) {
			this.setRoomsCount( data.activeRoomsCount );
		}

		if ( data.hasOwnProperty( 'dates' ) ) {
			this.setDates( data.dates );
		}

		$( window ).trigger( 'mphb-update-room-type-data-' + this.id );
	},
	/**
	 *
	 * @param {int} count
	 * @returns {undefined}
	 */
	setRoomsCount: function( count ) {
		this.activeRoomsCount = count;
	},
	/**
	 *
	 * @param {Object} dates
	 * @param {Object} dates.bookedDates
	 * @param {Object} dates.havePriceDates
	 * @returns {undefined}
	 */
	setDates: function( dates ) {
		this.bookedDates = dates.hasOwnProperty( 'booked' ) ? dates.booked : {};
		this.havePriceDates = dates.hasOwnProperty( 'havePrice' ) ? dates.havePrice : {};
	},
	/**
	 *
	 * @param {Date} dateFrom
	 * @param {Date} stopDate
	 * @returns {Date|false} Nearest locked room date if exists or false otherwise.
	 */
	getNearestLockedDate: function( dateFrom, stopDate ) {
		var nearestDate = stopDate;
		var self = this;

		var dateFromFormatted = $.datepick.formatDate( 'yyyy-mm-dd', dateFrom );
		var stopDateFormatted = $.datepick.formatDate( 'yyyy-mm-dd', stopDate );

		$.each( self.getLockedDates(), function( bookedDateFormatted, bookedRoomsCount ) {

			if ( stopDateFormatted < bookedDateFormatted ) {
				return false;
			}

			if ( dateFromFormatted > bookedDateFormatted ) {
				return true;
			}

			if ( bookedRoomsCount >= self.activeRoomsCount ) {
				nearestDate = $.datepick.parseDate( 'yyyy-mm-dd', bookedDateFormatted );
				return false;
			}

		} );
		return nearestDate;
	},
	/**
	 *
	 * @param {Date} dateFrom
	 * @param {Date} stopDate
	 * @returns {Date}
	 */
	getNearestHaveNotPriceDate: function( dateFrom, stopDate ) {
		var nearestDate = MPHB.Utils.cloneDate( stopDate );
		var expectedDate = MPHB.Utils.cloneDate( dateFrom );

		while ( MPHB.Utils.formatDateToCompare( expectedDate ) <= MPHB.Utils.formatDateToCompare( stopDate ) ) {
			if ( !this.hasPriceForDate( expectedDate ) ) {
				nearestDate = expectedDate;
				break;
			}
			expectedDate = $.datepick.add( expectedDate, 1, 'd' );
		}

		return nearestDate;
	},
	/**
	 *
	 * @returns {Object}
	 */
	getLockedDates: function() {
		var dates = {};
		return $.extend( dates, this.bookedDates );
	},
	/**
	 *
	 * @returns {Object}
	 */
	getHavePriceDates: function() {
		var dates = {};
		return $.extend( dates, this.havePriceDates );
	},
	/**
	 *
	 * @param {Date}
	 * @returns {String}
	 */
	getDateStatus: function( date ) {
		var status = MPHB.HotelDataManager.ROOM_STATUS_AVAILABLE;

		if ( this.isEarlierThanToday( date ) ) {
			status = MPHB.HotelDataManager.ROOM_STATUS_PAST;
		} else if ( this.isDateBooked( date ) ) {
			status = MPHB.HotelDataManager.ROOM_STATUS_BOOKED;
		} else if ( !this.hasPriceForDate( date ) ) {
			status = MPHB.HotelDataManager.ROOM_STATUS_NOT_AVAILABLE;
		}

		return status;
	},
	/**
	 *
	 * @param {Date} date
	 * @returns {Boolean}
	 */
	isDateBooked: function( date ) {
		var dateFormatted = $.datepick.formatDate( 'yyyy-mm-dd', date );
		return this.bookedDates.hasOwnProperty( dateFormatted ) && this.bookedDates[dateFormatted] >= this.activeRoomsCount;
	},
	/**
	 *
	 * @param {Date} date
	 * @returns {Boolean}
	 */
	hasPriceForDate: function( date ) {
		var dateFormatted = $.datepick.formatDate( 'yyyy-mm-dd', date );
		return $.inArray( dateFormatted, this.havePriceDates ) !== -1;
	},
	/**
	 *
	 * @param {Date} date
	 * @returns {int}
	 */
	getAvailableRoomsCount: function( date ) {
		var dateFormatted = $.datepick.formatDate( 'yyyy-mm-dd', date );
		var count = this.bookedDates.hasOwnProperty( dateFormatted ) ? this.activeRoomsCount - this.bookedDates[dateFormatted] : this.activeRoomsCount;
		if ( count < 0 ) {
			count = 0;
		}
		return count;
	},
	/**
	 *
	 * @param {Object} dateData
	 * @param {Date} date
	 * @returns {Object}
	 */
	fillDateData: function( dateData, date ) {
		var status = this.getDateStatus( date );
		var titles = [ ];
		var classes = [ ];

		switch ( status ) {
			case MPHB.HotelDataManager.ROOM_STATUS_PAST:
				classes.push( 'mphb-past-date' );
				titles.push( MPHB._data.translations.past );
				break;
			case MPHB.HotelDataManager.ROOM_STATUS_AVAILABLE:
				classes.push( 'mphb-available-date' );
				titles.push( MPHB._data.translations.available + '(' + this.getAvailableRoomsCount( date ) + ')' );
				break;
			case MPHB.HotelDataManager.ROOM_STATUS_NOT_AVAILABLE:
				classes.push( 'mphb-not-available-date' );
				titles.push( MPHB._data.translations.notAvailable );
				break;
			case MPHB.HotelDataManager.ROOM_STATUS_BOOKED:
				classes.push( 'mphb-booked-date' );
				titles.push( MPHB._data.translations.booked );
				break;
		}

		dateData.dateClass += (dateData.dateClass.length ? ' ' : '') + classes.join( ' ' );
		dateData.title += (dateData.title.length ? ', ' : '') + titles.join( ', ' );

		dateData = MPHB.HotelDataManager.myThis.fillDateCellData( dateData, date );

		return dateData;
	},
	appendRulesToTitle: function( date, title ) {
		var rulesTitles = [ ];

		if ( !MPHB.HotelDataManager.myThis.dateRules.canStayIn( date ) ) {
			rulesTitles.push( MPHB._data.translations.notStayIn );
		}
		if ( !MPHB.HotelDataManager.myThis.dateRules.canCheckIn( date ) ) {
			rulesTitles.push( MPHB._data.translations.notCheckIn );
		}
		if ( !MPHB.HotelDataManager.myThis.dateRules.canCheckOut( date ) ) {
			rulesTitles.push( MPHB._data.translations.notCheckOut );
		}

		if ( rulesTitles.length ) {
			title += ' ' + MPHB._data.translations.rules + ' ' + rulesTitles.join( ', ' );
		}

		return title;
	},
	/**
	 *
	 * @param {Date} date
	 * @returns {Boolean}
	 */
	isEarlierThanToday: function( date ) {
		return MPHB.Utils.formatDateToCompare( date ) < MPHB.Utils.formatDateToCompare( MPHB.HotelDataManager.myThis.today );
	},
} );
/**
 *
 * @requires ./../datepicker.js
 */
MPHB.SearchCheckInDatepicker = MPHB.Datepicker.extend( {}, {
	/**
	 *
	 * @returns {Object}
	 */
	getDatepickSettings: function() {
		var self = this;
		return {
			onSelect: function( dates ) {
				self.form.updateCheckOutLimitations();
			},
			onDate: function( date, current ) {
				var dateData = {
					dateClass: 'mphb-date-cell',
					selectable: false,
					title: ''
				};

				if ( current ) {

					var canCheckIn = MPHB.HotelDataManager.myThis.globalRules.isCheckInSatisfy( date ) &&
						MPHB.HotelDataManager.myThis.dateRules.canCheckIn( date );

					if ( canCheckIn ) {
						dateData.selectable = true;
					}

					dateData = MPHB.HotelDataManager.myThis.fillDateCellData( dateData, date );

				} else {
					dateData.dateClass += ' mphb-extra-date';
				}

				if ( dateData.selectable ) {
					dateData.dateClass += ' mphb-selectable-date';
				} else {
					dateData.dateClass += ' mphb-unselectable-date';
				}

				return dateData;
			},
			pickerClass: 'mphb-datepick-popup mphb-check-in-datepick',
		};
	}
} );
/**
 *
 * @requires ./../datepicker.js
 */
MPHB.SearchCheckOutDatepicker = MPHB.Datepicker.extend( {}, {
	/**
	 *
	 * @returns {Object}
	 */
	getDatepickSettings: function() {
		var self = this;
		return {
			onDate: function( date, current ) {
				var dateData = {
					dateClass: 'mphb-date-cell',
					selectable: false,
					title: ''
				};

				if ( current ) {

					var checkInDate = self.form.checkInDatepicker.getDate();
					var earlierThanMin = self.getMinDate() !== null && MPHB.Utils.formatDateToCompare( date ) < MPHB.Utils.formatDateToCompare( self.getMinDate() );
					var laterThanMax = self.getMaxDate() !== null && MPHB.Utils.formatDateToCompare( date ) > MPHB.Utils.formatDateToCompare( self.getMaxDate() );

					if ( checkInDate !== null && MPHB.Utils.formatDateToCompare( date ) === MPHB.Utils.formatDateToCompare( checkInDate ) ) {
						dateData.dateClass += ' mphb-check-in-date';
						dateData.title += MPHB._data.translations.checkInDate;
					}

					if ( earlierThanMin ) {
						if ( MPHB.Utils.formatDateToCompare( date ) < MPHB.Utils.formatDateToCompare( checkInDate ) ) {
							dateData.dateClass += ' mphb-earlier-min-date mphb-earlier-check-in-date';
						} else {
							dateData.dateClass += ' mphb-earlier-min-date';
							dateData.title += (dateData.title.length ? ' ' : '') + MPHB._data.translations.lessThanMinDaysStay;
						}
					}

					if ( laterThanMax ) {
						var maxStayDate = MPHB.HotelDataManager.myThis.globalRules.getMaxCheckOutDate( checkInDate );
						if ( MPHB.Utils.formatDateToCompare( date ) < MPHB.Utils.formatDateToCompare( maxStayDate ) ) {
							dateData.title += (dateData.title.length ? ' ' : '') + MPHB._data.translations.laterThanMaxDate;
						} else {
							dateData.title += (dateData.title.length ? ' ' : '') + MPHB._data.translations.moreThanMaxDaysStay;
						}
						dateData.dateClass += ' mphb-later-max-date';
					}

					dateData = MPHB.HotelDataManager.myThis.fillDateCellData( dateData, date );

					var canCheckOut = !earlierThanMin && !laterThanMax &&
						MPHB.HotelDataManager.myThis.globalRules.isCheckOutSatisfy( date ) &&
						MPHB.HotelDataManager.myThis.dateRules.canCheckOut( date );

					if ( canCheckOut ) {
						dateData.selectable = true;
					}

				} else {
					dateData.dateClass += ' mphb-extra-date';
				}

				if ( dateData.selectable ) {
					dateData.dateClass += ' mphb-selectable-date';
				} else {
					dateData.dateClass += ' mphb-unselectable-date';
				}

				return dateData;
			},
			pickerClass: 'mphb-datepick-popup mphb-check-in-datepick',
		};
	}
} );
MPHB.SearchForm = can.Control.extend( {}, {
	checkInDatepickerEl: null,
	checkOutDatepickerEl: null,
	checkInDatepicker: null,
	checkOutDatepicker: null,
	init: function( el, args ) {

		this.checkInDatepickerEl = this.element.find( '.mphb-datepick[name=mphb_check_in_date]' );
		this.checkOutDatepickerEl = this.element.find( '.mphb-datepick[name=mphb_check_out_date]' );

		this.checkInDatepicker = new MPHB.SearchCheckInDatepicker( this.checkInDatepickerEl, {'form': this} );
		this.checkOutDatepicker = new MPHB.SearchCheckOutDatepicker( this.checkOutDatepickerEl, {'form': this} );

	},
	/**
	 *
	 * @param {bool} isSetDate
	 * @returns {undefined}
	 */
	updateCheckOutLimitations: function( setDate ) {
		if ( typeof setDate === 'undefined' ) {
			setDate = true;
		}
		var limitations = this.retrieveCheckOutLimitations( this.checkInDatepicker.getDate(), this.checkOutDatepicker.getDate() );

		this.checkOutDatepicker.setOption( 'minDate', limitations.minDate );
		this.checkOutDatepicker.setOption( 'maxDate', limitations.maxDate );
		this.checkOutDatepicker.setDate( setDate ? limitations.date : null );
	},
	retrieveCheckOutLimitations: function( checkInDate, checkOutDate ) {

		var minDate = MPHB.HotelDataManager.myThis.today;
		var maxDate = null;
		var recommendedDate = null;

		if ( checkInDate !== null ) {
			var minDate = MPHB.HotelDataManager.myThis.globalRules.getMinCheckOutDate( checkInDate );

			var maxDate = MPHB.HotelDataManager.myThis.globalRules.getMaxCheckOutDate( checkInDate );
			maxDate = MPHB.HotelDataManager.myThis.dateRules.getNearestNotStayInDate( checkInDate, maxDate );

			if ( this.isCheckOutDateNotValid( checkOutDate, minDate, maxDate ) ) {
				recommendedDate = this.retrieveRecommendedCheckOutDate( minDate, maxDate );
			} else {
				recommendedDate = checkOutDate;
			}

		}

		return {
			minDate: minDate,
			maxDate: maxDate,
			date: recommendedDate
		};
	},
	retrieveRecommendedCheckOutDate: function( minDate, maxDate ) {
		var recommendedDate = null;
		var expectedDate = MPHB.Utils.cloneDate( minDate );

		while ( MPHB.Utils.formatDateToCompare( expectedDate ) <= MPHB.Utils.formatDateToCompare( maxDate ) ) {
			if ( !this.isCheckOutDateNotValid( expectedDate, minDate, maxDate ) ) {
				recommendedDate = expectedDate;
				break;
			}
			expectedDate = $.datepick.add( expectedDate, 1, 'd' );
		}

		return recommendedDate;

	},
	isCheckOutDateNotValid: function( checkOutDate, minDate, maxDate ) {
		return checkOutDate === null
			|| MPHB.Utils.formatDateToCompare( checkOutDate ) < MPHB.Utils.formatDateToCompare( minDate )
			|| MPHB.Utils.formatDateToCompare( checkOutDate ) > MPHB.Utils.formatDateToCompare( maxDate )
			|| !MPHB.HotelDataManager.myThis.globalRules.isCheckOutSatisfy( checkOutDate )
			|| !MPHB.HotelDataManager.myThis.dateRules.canCheckOut( checkOutDate );
	}

} );
new MPHB.HotelDataManager( MPHB._data );

if ( MPHB._data.page.isCheckoutPage ) {
	new MPHB.CheckoutForm( $( '.mphb_sc_checkout-form' ) );
}

var calendars = $( '.mphb-calendar.mphb-datepick' );
$.each( calendars, function( index, calendarEl ) {
	new MPHB.RoomTypeCalendar( $( calendarEl ) );
} );

var reservationForms = $( '.mphb-booking-form' );
$.each( reservationForms, function( index, formEl ) {
	new MPHB.ReservationForm( $( formEl ) );
} );

var searchForms = $( 'form.mphb_sc_search-form,form.mphb_widget_search-form' );
$.each( searchForms, function( index, formEl ) {
	new MPHB.SearchForm( $( formEl ) );
} );

	} );
})( jQuery );