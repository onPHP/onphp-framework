<?php
/**
 * HP Vertica dialect (compatible with PgSQL)
 * @author Михаил Кулаковский <m@klkvsk.ru>
 * @date 04.10.13
 */

class VerticaDialect extends PostgresDialect {

	/** @return self */
	public static function me() {
		return Singleton::getInstance(__CLASS__);
	}

} 