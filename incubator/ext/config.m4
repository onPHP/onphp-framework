dnl $Id$

PHP_ARG_ENABLE(onphp, whether to enable onPHP support,
[  --enable-onphp           Enable onPHP support])

if test "$PHP_ONPHP" != "no"; then

	onphp_sources="\
		src/onphp.c \
		src/onphp_core.c \
		src/core/Base/Identifiable.c \
		src/core/Base/Identifier.c \
		src/core/Base/IdentifiableObject.c \
		src/core/Base/Named.c \
	"

	ONPHP_INCLUDES="-I./src/"

	PHP_SUBST(ONPHP_INCLUDES)

	PHP_NEW_EXTENSION(onphp, $onphp_sources, $ext_shared,, $ONPHP_INCLUDES)
	AC_DEFINE(HAVE_ONPHP, 1, [ ])
fi
