<?php
/***************************************************************************
 *   Copyright (C) 2008 by Tsyrulnik Y. Viatcheslav                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	final class GoogleGeoStatusCode extends Enumeration
	{
		const GOOGLE_GEO_SUCCESS					= 200;
		const GOOGLE_GEO_BAD_REQUEST				= 400;
		const GOOGLE_GEO_SERVER_ERROR				= 500;
		const GOOGLE_GEO_MISSING_QUERY				= 601;
		const GOOGLE_GEO_MISSING_ADDRESS			= 601;
		const GOOGLE_GEO_UNKNOWN_ADDRESS			= 602;
		const GOOGLE_GEO_UNAVAILABLE_ADDRESS		= 603;
		const GOOGLE_GEO_UNKNOWN_DIRECTIONS			= 604;
		const GOOGLE_GEO_BAD_KEY					= 610;
		const GOOGLE_GEO_TOO_MANY_QUERIES			= 620;
		
		protected $names = array(
			self::GOOGLE_GEO_SUCCESS => "No errors occurred; the address was successfully parsed and its geocode has been returned.",
			self::GOOGLE_GEO_BAD_REQUEST => "A directions request could not be successfully parsed. For example, the request may have been rejected if it contained more than the maximum number of waypoints allowed.",
			self::GOOGLE_GEO_SERVER_ERROR => "A geocoding or directions request could not be successfully processed, yet the exact reason for the failure is not known.",
			self::GOOGLE_GEO_MISSING_QUERY => "The HTTP q parameter was either missing or had no value. For geocoding requests, this means that an empty address was specified as input. For directions requests, this means that no query was specified in the input.",
			self::GOOGLE_GEO_MISSING_ADDRESS => "Synonym for GOOGLE_GEO_MISSING_QUERY.",
			self::GOOGLE_GEO_UNKNOWN_ADDRESS => "No corresponding geographic location could be found for the specified address. This may be due to the fact that the address is relatively new, or it may be incorrect.",
			self::GOOGLE_GEO_UNAVAILABLE_ADDRESS => "The geocode for the given address or the route for the given directions query cannot be returned due to legal or contractual reasons.",
			self::GOOGLE_GEO_UNKNOWN_DIRECTIONS => "The GDirections object could not compute directions between the points mentioned in the query. This is usually because there is no route available between the two points, or because we do not have data for routing in that region.",
			self::GOOGLE_GEO_BAD_KEY => "The given key is either invalid or does not match the domain for which it was given.",
			self::GOOGLE_GEO_TOO_MANY_QUERIES => "The given key has gone over the requests limit in the 24 hour period or has submitted too many requests in too short a period of time. If you're sending multiple requests in parallel or in a tight loop, use a timer or pause in your code to make sure you don't send the requests too quickly."
		);
	}
?>