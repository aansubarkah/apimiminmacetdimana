/* global require, module */
var EmberApp = require('ember-cli/lib/broccoli/ember-app');

module.exports = function (defaults) {
	var app = new EmberApp(defaults, {
		// Add options here
		minifyCSS: {
			enabled: true
		},
		minifyJS: {
			enabled: false
		},
		sourcemaps: {
			enabled: false,
			extensions: ['js']
		}
	});

	// Use `app.import` to add additional libraries to the generated
	// output files.
	//
	// If you need to use different assets in different
	// environments, specify an object as the first parameter. That
	// object's keys should be the environment name and the values
	// should be the asset to use in that environment.
	//
	// If the library that you are including contains AMD or ES6
	// modules that you would like to import into your application
	// please specify an object with the list of modules as keys
	// along with the exports of each module as its value.

	// Bootstrap
	app.import('bower_components/bootstrap/dist/css/bootstrap.min.css');
	app.import('bower_components/bootstrap/dist/js/bootstrap.min.js');

	// AdminLTE
	app.import('bower_components/admin-lte/dist/css/AdminLTE.min.css');
	app.import('bower_components/admin-lte/dist/css/skins/_all-skins.min.css');
	app.import('bower_components/admin-lte/dist/js/app.min.js');

	// AdminLTE plugins
	app.import('bower_components/admin-lte/plugins/slimScroll/jquery.slimscroll.min.js');

	//app.import('bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js');
	//app.import('bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.css');
	//app.import('bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.min.js');
	//app.import('bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.css');

	// EmberTable
	//app.import('bower_components/ember-table/dist/ember-table.min.js');
	//app.import('bower_components/ember-table/dist/ember-table.css');
	//app.import('bower_components/admin-lte/dist/css/AdminLTE.min.css');
	//app.import('bower_components/admin-lte/dist/css/skins/_all-skins.min.css');

	// DataTables
	//app.import('bower_components/DataTables/media/css/jquery.dataTables.min.css');
	//app.import('bower_components/DataTables/media/css/dataTables.bootstrap.min.css');
	//app.import('bower_components/DataTables/media/js/jquery.dataTables.min.js');
	//app.import('bower_components/DataTables/media/js/dataTables.bootstrap.min.js');

	// Moment.js
	app.import('bower_components/moment/min/moment-with-locales.min.js');
	app.import('bower_components/moment-timezone/builds/moment-timezone-with-data.min.js');

	// Hashids
	app.import('bower_components/hashids/lib/hashids.min.js');

    // bignumber.js
    app.import('bower_components/bignumber.js/bignumber.min.js');
	// Google Maps API
	//app.import('vendor/gmaps.js');

	return app.toTree();
};
