dnl $Id$

PHP_ARG_ENABLE(onphp, whether to enable onPHP support,
[  --enable-onphp           Enable onPHP support])

if test "$PHP_ONPHP" != "no"; then

	onphp_sources="\
		src/onphp.c \
		src/onphp_util.c \
		src/onphp_core.c \
		src/onphp_main.c \
		src/core/Exceptions.c \
		src/core/Base/Enumeration.c \
		src/core/Base/Identifiable.c \
		src/core/Base/Identifier.c \
		src/core/Base/IdentifiableObject.c \
		src/core/Base/Stringable.c \
		src/core/Base/Named.c \
		src/core/Base/NamedObject.c \
		src/core/Base/Prototyped.c \
		src/core/Base/Singleton.c \
		src/core/Base/StaticFactory.c \
		src/core/Base/Instantiatable.c \
		src/core/DB/Dialect.c \
		src/core/DB/ImaginaryDialect.c \
		src/core/OSQL/Castable.c \
		src/core/OSQL/DBValue.c \
		src/core/OSQL/DialectString.c \
		src/core/OSQL/FieldTable.c \
		src/core/OSQL/SQLTableName.c \
		src/core/OSQL/Query.c \
		src/core/OSQL/QueryIdentification.c \
		src/main/DAOs/Handlers/SegmentHandler.c \
		src/main/Flow/ViewResolver.c \
	"
	ONPHP_INCLUDES="\
		-I@ext_srcdir@/src \
		-I@ext_srcdir@/src/core \
		-I@ext_srcdir@/src/core/Base \
		-I@ext_srcdir@/src/core/DB \
		-I@ext_srcdir@/src/core/OSQL \
		-I@ext_srcdir@/src/main \
		-I@ext_srcdir@/src/main/DAOs \
		-I@ext_srcdir@/src/main/DAOs/Handlers \
	"
	ONPHP_SANITY="-Wall -Werror -fno-strict-aliasing"

	PHP_SUBST(ONPHP_INCLUDES)

	PHP_NEW_EXTENSION(onphp, $onphp_sources, $ext_shared,, $ONPHP_INCLUDES $ONPHP_SANITY)
	AC_DEFINE(HAVE_ONPHP, 1, [ ])
fi
