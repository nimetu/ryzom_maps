//
// Ryzom Maps
// Copyright (c) 2011 Meelis Mägi <nimetu@gmail.com>
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
 * Cookies
 */
Ryzom.Cookie = {
	get: function(name){
		var value = null;
		var cookies = document.cookie.split(';');
		for(var i=0,len=cookies.length; i<len; i++){
			var cookie = OpenLayers.String.trim(cookies[i]);
			if(OpenLayers.String.startsWith(cookie, name+'=')){
				value = cookie.substring(name.length + 1);
				break;
			}
		}
		return value;
	},

	/**
	 * Options can have
	 * - expires  number number of days cookie expires
	 * - path     string
	 * - domain   string
	 * - secure   boolean
	 */
	set: function(name, value, options){
		options = options || {};
		if(value === null){
			value = '';
			options.expires = -1;
		}
		var expires = '';
		if(options.expires){
			var date = new Date();
			date.setTime(date.getTime() + (options.expires*24*60*60*1000));
			expires = '; expires='+date.toUTCString();
		}
		var path = options.path ? '; path='+(options.path) : '';
		var domain = options.domain ? '; domain='+(options.domain) : '';
		var secure = options.secure ? '; secure' : '';
		document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
	}
};
