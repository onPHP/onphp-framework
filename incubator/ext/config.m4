dnl $Id$

PHP_ARG_ENABLE(onphp, whether to enable onPHP support,
[  --enable-onphp           Enable onPHP support])

if test "$PHP_ONPHP" != "no"; then

	onphp_sources="\
		src/onphp.c \
		src/onphp_util.c \
		src/onphp_core.c \
		src/core/Exceptions.c \
		src/core/Base/Identifiable.c \
		src/core/Base/Identifier.c \
		src/core/Base/IdentifiableObject.c \
		src/core/Base/Stringable.c \
		src/core/Base/Named.c \
		src/core/Base/NamedObject.c \
		src/core/Base/Singleton.c \
		src/core/Base/Instantiatable.c \
		src/core/DB/Dialect.c \
		src/core/OSQL/Castable.c \
		src/core/OSQL/DBValue.c \
		src/core/OSQL/DialectString.c \
		src/core/OSQL/SQLTableName.c \
	"

	ONPHP_INCLUDES="-I./src/"
	ONPHP_SANITY="-Wall -Wno-implicit-function-declaration -fno-strict-aliasing"

	PHP_SUBST(ONPHP_INCLUDES)

	PHP_NEW_EXTENSION(onphp, $onphp_sources, $ext_shared,, $ONPHP_INCLUDES $ONPHP_SANITY)
	AC_DEFINE(HAVE_ONPHP, 1, [ ])
fi
