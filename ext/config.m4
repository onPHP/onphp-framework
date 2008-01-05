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
		src/core/Base/Aliased.c \
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
		src/core/Base/Ternary.c \
		src/core/Base/Instantiatable.c \
		src/core/DB/Dialect.c \
		src/core/DB/ImaginaryDialect.c \
		src/core/Form/PlainForm.c \
		src/core/Form/RegulatedForm.c \
		src/core/Form/Form.c \
		src/core/Form/FormField.c \
		src/core/Form/Filters/BaseFilter.c \
		src/core/Form/Filters/Filtrator.c \
		src/core/Form/Primitives/BasePrimitive.c \
		src/core/Form/Primitives/RangedPrimitive.c \
		src/core/Form/Primitives/ComplexPrimitive.c \
		src/core/Form/Primitives/ListedPrimitive.c \
		src/core/Form/Primitives/FiltrablePrimitive.c \
		src/core/Form/Primitives/PrimitiveNumber.c \
		src/core/OSQL/Castable.c \
		src/core/OSQL/DBBinary.c \
		src/core/OSQL/DBField.c \
		src/core/OSQL/DBValue.c \
		src/core/OSQL/DropTableQuery.c \
		src/core/OSQL/DialectString.c \
		src/core/OSQL/ExtractPart.c \
		src/core/OSQL/FieldTable.c \
		src/core/OSQL/FromTable.c \
		src/core/OSQL/FullText.c \
		src/core/OSQL/GroupBy.c \
		src/core/OSQL/Joiner.c \
		src/core/OSQL/OrderBy.c \
		src/core/OSQL/SelectField.c \
		src/core/OSQL/SQLTableName.c \
		src/core/OSQL/Query.c \
		src/core/OSQL/QueryIdentification.c \
		src/core/OSQL/QuerySkeleton.c
		src/core/Logic/LogicalObject.c \
		src/core/Logic/MappableObject.c \
		src/main/DAOs/DAOConnected.c \
		src/main/DAOs/FullTextDAO.c \
		src/main/DAOs/Handlers/SegmentHandler.c \
		src/main/Flow/ViewResolver.c \
		src/main/Markup/Html/Cdata.c \
		src/main/Markup/Html/SgmlTag.c \
		src/main/Markup/Html/SgmlToken.c \
		src/main/Markup/Html/SgmlEndTag.c \
	"
	ONPHP_INCLUDES="\
		-I@ext_srcdir@/src \
		-I@ext_srcdir@/src/core \
		-I@ext_srcdir@/src/core/Base \
		-I@ext_srcdir@/src/core/Form \
		-I@ext_srcdir@/src/core/Form/Filters \
		-I@ext_srcdir@/src/core/Form/Primitives \
		-I@ext_srcdir@/src/core/DB \
		-I@ext_srcdir@/src/core/OSQL \
		-I@ext_srcdir@/src/core/Logic \
		-I@ext_srcdir@/src/main \
		-I@ext_srcdir@/src/main/DAOs \
		-I@ext_srcdir@/src/main/DAOs/Handlers \
		-I@ext_srcdir@/src/main/Markup/Html \
	"
	ONPHP_SANITY="-Wall -Werror -fno-strict-aliasing"

	PHP_SUBST(ONPHP_INCLUDES)

	PHP_NEW_EXTENSION(onphp, $onphp_sources, $ext_shared,, $ONPHP_INCLUDES $ONPHP_SANITY)
	AC_DEFINE(HAVE_ONPHP, 1, [ ])
fi
