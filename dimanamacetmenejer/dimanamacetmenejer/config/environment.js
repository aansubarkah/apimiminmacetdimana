/* jshint node: true */

module.exports = function (environment) {
	var ENV = {
		modulePrefix: 'dimanamacet-mimin-frontend',
		podModulePrefix: 'dimanamacet-mimin-frontend/pods',
		environment: environment,
		baseURL: '/',
		locationType: 'auto',
        apiURL: 'http://localhost:7654',
        //apiURL: 'http://apimenejer.macetdimana.com',
		EmberENV: {
			FEATURES: {
				// Here you can enable experimental features on an ember canary build
				// e.g. 'with-controller': true
			}
		},

		APP: {
			// Here you can pass flags/options to your application instance
			// when it is created
			//namespace: 'manager',
			//host: 'http://apitraffic.aansubarkah.net',
			//host: 'http://localhost:8765',// @todo change this on production server
			//apiVersion: '1.1'
		},

		contentSecurityPolicy: {
			'default-src': "'none'",
			'script-src': "'self' 'unsafe-inline' 'unsafe-eval' https://cdn.datatables.net https://code.jquery.com https://maxcdn.bootstrapcdn.com https://cdnjs.cloudflare.com *.googleapis.com maps.gstatic.com",
			'font-src': "'self' https://maxcdn.bootstrapcdn.com https://cdn.datatables.net http://fonts.gstatic.com https://fonts.gstatic.com https://code.ionicframework.com",
			'connect-src': "'self' http://localhost:7654 http://apimenejer.macetdimana.com",
			'img-src': "'self' *.googleapis.com maps.gstatic.com csi.gstatic.com *.twimg.com",
			'style-src': "'self' 'unsafe-inline' https://code.ionicframework.com https://maxcdn.bootstrapcdn.com https://cdn.datatables.net fonts.googleapis.com map.gstatic.com",
			'media-src': "'self'"
		},

		googleMap: {
			libraries: ['places', 'geometry', 'marker'],
			apiKey: 'AIzaSyA7dciHJOSiR8annWOSISIdKFF6T3cuyMQ'
		}
	};

	ENV['ember-simple-auth'] = {
		authorizer: 'authorizer:token',
		crossOriginWhitelist:['*'],
		//crossOriginWhitelist: ['http://localhost:7654'],// @todo change this on production server
		//crossOriginWhitelist: ['http://apimenejer.macetdimana.com'],
		store: 'session-store:local-storage',
        authenticationRoute: 'signin'
	};

	ENV['ember-simple-auth-token'] = {
		//serverTokenEndpoint: 'http://localhost:7654/users/token',// @todo change this on production server
		serverTokenEndpoint: 'http://apimenejer.macetdimana.com/users/token',
		identificationField: 'username',
		passwordField: 'password',
		tokenPropertyName: 'token',
		authorizationPrefix: 'Bearer ',
		authorizationHeaderName: 'Authorization',
		headers: {},
		refreshAccessTokens: true,
		//serverTokenRefreshEndpoint: 'http://localhost:7654/users/token',// @todo change this on production server
		serverTokenRefreshEndpoint: 'http://apimenejer.macetdimana.com/users/token',
		tokenExpireName: 'exp',
		refreshLeeway: 0, // Refresh the token 5 minutes (300s) before it expires.
		timeFactor: 1000
	};

	if (environment === 'development') {
		//ENV.APP.LOG_RESOLVER = true;
		//ENV.APP.LOG_ACTIVE_GENERATION = true;
		ENV.APP.LOG_TRANSITIONS = true;
		//ENV.APP.LOG_TRANSITIONS_INTERNAL = true;
		//ENV.APP.LOG_VIEW_LOOKUPS = true;
	}

	if (environment === 'test') {
		// Testem prefers this...
		ENV.baseURL = '/';
		ENV.locationType = 'none';
        //ENV.apiURL = 'http://localhost:7654';
        ENV.apiURL = 'http://apimenejer.macetdimana.com';

		// keep test console output quieter
		ENV.APP.LOG_ACTIVE_GENERATION = false;
		ENV.APP.LOG_VIEW_LOOKUPS = false;

		ENV.APP.rootElement = '#ember-testing';
	}

	if (environment === 'production') {

	}

	return ENV;
};
