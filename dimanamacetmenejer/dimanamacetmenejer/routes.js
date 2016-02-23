// add followed line to components/g-maps.js
//import GMapRoutes        from 'ember-cli-g-maps/mixins/g-maps/routes';
import Ember           from 'ember';
import childCollection from 'ember-cli-g-maps/utils/g-maps/child-collection';

const { isArray } = Ember;

export default Ember.Mixin.create(
	childCollection.create({
		model: 'routes',

		namespace: 'route',

		/* Supported:
		 props: [ 'clickable', 'draggable', 'editable', 'fillColor', 'fillOpacity', 'geodesic', 'paths', 'strokeColor', 'strokeOpacity', 'strokePosition', 'strokeWeight', 'visible', 'zIndex' ],

		 events: [ 'click', 'rightclick', 'dblclick', 'drag', 'dragend', 'dragstart', 'mousedown', 'mouseout', 'mouseover', 'mouseup', 'mousemove' ],
		 */

		validate: function validateRoutes() {
			//var directionsDisplay=new google.maps.DirectionsRenderer;
			//google.maps.DirectionsRenderer.setMap(null);
			//this.get('map').map.DirectionsRenderer.setMap(null);
			const routes = this.get('routes');
			if (routes && !isArray(routes)) {
				throw new Error('g-maps component expects routes to be an Ember Array');
			}
		},

		destroy: function destroyRoutes() {
			google.maps.event.clearListeners(this.get('map').map, 'closeclick');
		},

		removeItem: function removeRoute(m) {
			//if (m) {
			//m.setMap(null);
			//	console.log(m);
			//}
			if (m.infoWindow) {
				m.infoWindow.setMap(null);
				m.infoWindow = null;
			}
		},

		addedItem: function addRoute(m, route, map) {
			// If route has visible window, trigger open
			/*if(route.infoWindow && route.infoWindow.visible) {
			 route.infoWindow.addListener('closeclick', function() {
			 route.infoWindow.set('visible', false);
			 });
			 route.infoWindow.open(map.map, route);
			 route.addListener('click', function toggleInfoWindow() {
			 if(!route.infoWindow) { return; }
			 if(route.infoWindow.get('visible') === false) {
			 route.infoWindow.open(map.map, route);
			 route.infoWindow.set('visible', true);
			 } else {
			 route.infoWindow.close(map.map, route);
			 route.infoWindow.set('visible', false);
			 }
			 });
			 }*/
		}
	})
);
