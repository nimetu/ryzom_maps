//
// Ryzom Maps
// Copyright (c) 2009 Meelis Mägi <nimetu@gmail.com>
//
// This file is part of Ryzom Maps.
//
// Ryzom Maps is free software; you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as published by
// the Free Software Foundation; either version 3 of the License, or
// (at your option) any later version.
//
// Ryzom Maps is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Affero General Public License for more details.
//
// You should have received a copy of the GNU Affero General Public License
// along with this program; if not, write to the Free Software Foundation,
// Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301  USA
//

/**
 * LatLngControl class displays the LatLng and pixel coordinates
 * underneath the mouse within a container anchored to it.
 * @param {google.maps.Map} map Map to add custom control to.
 */
function LatLngControl(map) {
  /**
   * Offset the control container from the mouse by this amount.
   */
  this.ANCHOR_OFFSET_ = new google.maps.Point(8, 8);
  
  /**
   * Pointer to the HTML container.
   */
  this.node_ = this.createHtmlNode_();
  
  // Add control to the map. Position is irrelevant.
  map.controls[google.maps.ControlPosition.TOP].push(this.node_);
  
  // Bind this OverlayView to the map so we can access MapCanvasProjection
  // to convert LatLng to Point coordinates.
  this.setMap(map);
  
  // Register an MVC property to indicate whether this custom control
  // is visible or hidden. Initially hide control until mouse is over map.
  this.set('visible', false);
}

// Extend OverlayView so we can access MapCanvasProjection.
LatLngControl.prototype = new google.maps.OverlayView();
LatLngControl.prototype.draw = function() {};

/**
 * @private
 * Helper function creates the HTML node which is the control container.
 * @return {HTMLDivElement}
 */
LatLngControl.prototype.createHtmlNode_ = function() {
  var divNode = document.createElement('div');
  divNode.id = 'latlng-control';
  divNode.index = 100;
  return divNode;
};

/**
 * MVC property's state change handler function to show/hide the
 * control container.
 */
LatLngControl.prototype.visible_changed = function() {
  this.node_.style.display = this.get('visible') ? '' : 'none';
};

/**
 * Specified LatLng value is used to calculate pixel coordinates and
 * update the control display. Container is also repositioned.
 * @param {google.maps.LatLng} latLng Position to display
 */
LatLngControl.prototype.updatePosition = function(latLng) {
  var projection = this.getProjection();
  var w = this.getMap().getProjection().fromLatLngToPoint(latLng);
  var c = projection.fromLatLngToContainerPixel(latLng);
  var d  = projection.fromLatLngToDivPixel(latLng);
  var z=this.getMap().getZoom();
  
  // Update control position to be anchored next to mouse position.
  this.node_.style.left = c.x + this.ANCHOR_OFFSET_.x + 'px';
  this.node_.style.top = c.y + this.ANCHOR_OFFSET_.y + 'px';
  
  var m=Math.pow(2, z);
  
  // Update control to display latlng and coordinates.
  this.node_.innerHTML = [
    latLng.toUrlValue(4),
    '<br/>', 'Zoom: ', z, 
    '<br/>', 'Div: ', d.x,'px, ',d.y,'px',
    '<br/>', 'Container: ', c.x,'px, ',c.y,'px',
    '<br/>', 'World (base): ', w.x.toFixed(2), 'px, ', w.y.toFixed(2), 'px',
    '<br/>', 'World (@zoom ', z, '): ', (w.x*m).toFixed(2), 'px, ', (w.y*m).toFixed(2), 'px',
    '<br/>', 'Wordl width: ', projection.getWorldWidth(),
    '<br/>', 'LatLng: ', latLng
  ].join('');
};
