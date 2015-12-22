<?php
/***************************************************************************
 *   Copyright (C) 2006-2009 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * PostgreSQL Error Codes.
 *
 * @ingroup DB
 *
 * @see http://www.postgresql.org/docs/current/interactive/errcodes-appendix.html
 **/
class PostgresError extends Enumeration
{
    // Class 00 - Successful Completion
    const SUCCESSFUL_COMPLETION = '00000';

    // Class 01 - Warning
    const WARNING = '01000';
    const DYNAMIC_RESULT_SETS_RETURNED = '0100C';
    const IMPLICIT_ZERO_BIT_PADDING = '01008';
    const NULL_VALUE_ELIMINATED_IN_SET_FUNCTION = '01003';
    const PRIVILEGE_NOT_GRANTED = '01007';
    const PRIVILEGE_NOT_REVOKED = '01006';

    // name changed due to collision with 22001
    const STRING_DATA_RIGHT_TRUNCATION_WARNING = '01004';
    const DEPRECATED_FEATURE = '01P01';

    // Class 02 - No Data (this is also a warning class per the SQL standard)
    const NO_DATA = '02000';
    const NO_ADDITIONAL_DYNAMIC_RESULT_SETS_RETURNED = '02001';

    // Class 03 - SQL Statement Not Yet Complete
    const SQL_STATEMENT_NOT_YET_COMPLETE = '03000';

    // Class 08 - Connection Exception
    const CONNECTION_EXCEPTION = '08000';
    const CONNECTION_DOES_NOT_EXIST = '08003';
    const CONNECTION_FAILURE = '08006';
    const SQLCLIENT_UNABLE_TO_ESTABLISH_SQLCONNECTION = '08001';
    const SQLSERVER_REJECTED_ESTABLISHMENT_OF_SQLCONNECTION = '08004';
    const TRANSACTION_RESOLUTION_UNKNOWN = '08007';
    const PROTOCOL_VIOLATION = '08P01';

    // Class 09 - Triggered Action Exception
    const TRIGGERED_ACTION_EXCEPTION = '09000';

    // Class 0A - Feature Not Supported
    const FEATURE_NOT_SUPPORTED = '0A000';

    // Class 0B - Invalid Transaction Initiation
    const INVALID_TRANSACTION_INITIATION = '0B000';

    // Class 0F - Locator Exception
    const LOCATOR_EXCEPTION = '0F000';
    const INVALID_LOCATOR_SPECIFICATION = '0F001';

    // Class 0L - Invalid Grantor
    const INVALID_GRANTOR = '0L000';
    const INVALID_GRANT_OPERATION = '0LP01';

    // Class 0P - Invalid Role Specification
    const INVALID_ROLE_SPECIFICATION = '0P000';

    // Class 21 - Cardinality Violation
    const CARDINALITY_VIOLATION = '21000';

    // Class 22 - Data Exception
    const DATA_EXCEPTION = '22000';
    const ARRAY_SUBSCRIPT_ERROR = '2202E';
    const CHARACTER_NOT_IN_REPERTOIRE = '22021';
    const DATETIME_FIELD_OVERFLOW = '22008';
    const DIVISION_BY_ZERO = '22012';
    const ERROR_IN_ASSIGNMENT = '22005';
    const ESCAPE_CHARACTER_CONFLICT = '2200B';
    const INDICATOR_OVERFLOW = '22022';
    const INTERVAL_FIELD_OVERFLOW = '22015';
    const INVALID_ARGUMENT_FOR_LOGARITHM = '2201E';
    const INVALID_ARGUMENT_FOR_POWER_FUNCTION = '2201F';
    const INVALID_ARGUMENT_FOR_WIDTH_BUCKET_FUNCTION = '2201G';
    const INVALID_CHARACTER_VALUE_FOR_CAST = '22018';
    const INVALID_DATETIME_FORMAT = '22007';
    const INVALID_ESCAPE_CHARACTER = '22019';
    const INVALID_ESCAPE_OCTET = '2200D';
    const INVALID_ESCAPE_SEQUENCE = '22025';
    const NONSTANDARD_USE_OF_ESCAPE_CHARACTER = '22P06';
    const INVALID_INDICATOR_PARAMETER_VALUE = '22010';
    const INVALID_LIMIT_VALUE = '22020';
    const INVALID_PARAMETER_VALUE = '22023';
    const INVALID_REGULAR_EXPRESSION = '2201B';
    const INVALID_TIME_ZONE_DISPLACEMENT_VALUE = '22009';
    const INVALID_USE_OF_ESCAPE_CHARACTER = '2200C';
    const MOST_SPECIFIC_TYPE_MISMATCH = '2200G';
    const NULL_VALUE_NOT_ALLOWED = '22004';
    const NULL_VALUE_NO_INDICATOR_PARAMETER = '22002';
    const NUMERIC_VALUE_OUT_OF_RANGE = '22003';
    const STRING_DATA_LENGTH_MISMATCH = '22026';
    const STRING_DATA_RIGHT_TRUNCATION = '22001';
    const SUBSTRING_ERROR = '22011';
    const TRIM_ERROR = '22027';
    const UNTERMINATED_C_STRING = '22024';
    const ZERO_LENGTH_CHARACTER_STRING = '2200F';
    const FLOATING_POINT_EXCEPTION = '22P01';
    const INVALID_TEXT_REPRESENTATION = '22P02';
    const INVALID_BINARY_REPRESENTATION = '22P03';
    const BAD_COPY_FILE_FORMAT = '22P04';
    const UNTRANSLATABLE_CHARACTER = '22P05';

    // Class 23 - Integrity Constraint Violation
    const INTEGRITY_CONSTRAINT_VIOLATION = '23000';
    const RESTRICT_VIOLATION = '23001';
    const NOT_NULL_VIOLATION = '23502';
    const FOREIGN_KEY_VIOLATION = '23503';
    const UNIQUE_VIOLATION = '23505';
    const CHECK_VIOLATION = '23514';

    // Class 24 - Invalid Cursor State
    const INVALID_CURSOR_STATE = '24000';

    // Class 25 - Invalid Transaction State
    const INVALID_TRANSACTION_STATE = '25000';
    const ACTIVE_SQL_TRANSACTION = '25001';
    const BRANCH_TRANSACTION_ALREADY_ACTIVE = '25002';
    const HELD_CURSOR_REQUIRES_SAME_ISOLATION_LEVEL = '25008';
    const INAPPROPRIATE_ACCESS_MODE_FOR_BRANCH_TRANSACTION = '25003';
    const INAPPROPRIATE_ISOLATION_LEVEL_FOR_BRANCH_TRANSACTION = '25004';
    const NO_ACTIVE_SQL_TRANSACTION_FOR_BRANCH_TRANSACTION = '25005';
    const READ_ONLY_SQL_TRANSACTION = '25006';
    const SCHEMA_AND_DATA_STATEMENT_MIXING_NOT_SUPPORTED = '25007';
    const NO_ACTIVE_SQL_TRANSACTION = '25P01';
    const IN_FAILED_SQL_TRANSACTION = '25P02';

    // Class 26 - Invalid SQL Statement Name
    const INVALID_SQL_STATEMENT_NAME = '26000';

    // Class 27 - Triggered Data Change Violation
    const TRIGGERED_DATA_CHANGE_VIOLATION = '27000';

    // Class 28 - Invalid Authorization Specification
    const INVALID_AUTHORIZATION_SPECIFICATION = '28000';

    // Class 2B - Dependent Privilege Descriptors Still Exist
    const DEPENDENT_PRIVILEGE_DESCRIPTORS_STILL_EXIST = '2B000';
    const DEPENDENT_OBJECTS_STILL_EXIST = '2BP01';

    // Class 2D - Invalid Transaction Termination
    const INVALID_TRANSACTION_TERMINATION = '2D000';

    // Class 2F - SQL Routine Exception
    const SQL_ROUTINE_EXCEPTION = '2F000';
    const FUNCTION_EXECUTED_NO_RETURN_STATEMENT = '2F005';
    const MODIFYING_SQL_DATA_NOT_PERMITTED = '2F002';

    // name changed due to collision with 38003
    const PROHIBITED_SQL_STATEMENT_ATTEMPTED_EXCEPTION = '2F003';

    // name changed due to collision with 38004
    const READING_SQL_DATA_NOT_PERMITTED_EXCEPTION = '2F004';

    // Class 34 - Invalid Cursor Name
    const INVALID_CURSOR_NAME = '34000';

    // Class 38 - External Routine Exception
    const EXTERNAL_ROUTINE_EXCEPTION = '38000';
    const CONTAINING_SQL_NOT_PERMITTED = '38001';

    // name changed due to collision with 2F002
    const MODIFYING_SQL_DATA_NOT_PERMITTED_EXTERNAL = '38002';
    const PROHIBITED_SQL_STATEMENT_ATTEMPTED = '38003';
    const READING_SQL_DATA_NOT_PERMITTED = '38004';

    // Class 39 - External Routine Invocation Exception
    const EXTERNAL_ROUTINE_INVOCATION_EXCEPTION = '39000';
    const INVALID_SQLSTATE_RETURNED = '39001';

    // name changed due to collision with 22004
    const NULL_VALUE_NOT_ALLOWED_EXTERNAL = '39004';
    const TRIGGER_PROTOCOL_VIOLATED = '39P01';
    const SRF_PROTOCOL_VIOLATED = '39P02';

    // Class 3B - Savepoint Exception
    const SAVEPOINT_EXCEPTION = '3B000';
    const INVALID_SAVEPOINT_SPECIFICATION = '3B001';

    // Class 3D - Invalid Catalog Name
    const INVALID_CATALOG_NAME = '3D000';

    // Class 3F - Invalid Schema Name
    const INVALID_SCHEMA_NAME = '3F000';

    // Class 40 - Transaction Rollback
    const TRANSACTION_ROLLBACK = '40000';
    const TRANSACTION_INTEGRITY_CONSTRAINT_VIOLATION = '40002';
    const SERIALIZATION_FAILURE = '40001';
    const STATEMENT_COMPLETION_UNKNOWN = '40003';
    const DEADLOCK_DETECTED = '40P01';

    // Class 42 - Syntax Error or Access Rule Violation
    const SYNTAX_ERROR_OR_ACCESS_RULE_VIOLATION = '42000';
    const SYNTAX_ERROR = '42601';
    const INSUFFICIENT_PRIVILEGE = '42501';
    const CANNOT_COERCE = '42846';
    const GROUPING_ERROR = '42803';
    const INVALID_FOREIGN_KEY = '42830';
    const INVALID_NAME = '42602';
    const NAME_TOO_LONG = '42622';
    const RESERVED_NAME = '42939';
    const DATATYPE_MISMATCH = '42804';
    const INDETERMINATE_DATATYPE = '42P18';
    const WRONG_OBJECT_TYPE = '42809';
    const UNDEFINED_COLUMN = '42703';
    const UNDEFINED_FUNCTION = '42883';
    const UNDEFINED_TABLE = '42P01';
    const UNDEFINED_PARAMETER = '42P02';
    const UNDEFINED_OBJECT = '42704';
    const DUPLICATE_COLUMN = '42701';
    const DUPLICATE_CURSOR = '42P03';
    const DUPLICATE_DATABASE = '42P04';
    const DUPLICATE_FUNCTION = '42723';
    const DUPLICATE_PREPARED_STATEMENT = '42P05';
    const DUPLICATE_SCHEMA = '42P06';
    const DUPLICATE_TABLE = '42P07';
    const DUPLICATE_ALIAS = '42712';
    const DUPLICATE_OBJECT = '42710';
    const AMBIGUOUS_COLUMN = '42702';
    const AMBIGUOUS_FUNCTION = '42725';
    const AMBIGUOUS_PARAMETER = '42P08';
    const AMBIGUOUS_ALIAS = '42P09';
    const INVALID_COLUMN_REFERENCE = '42P10';
    const INVALID_COLUMN_DEFINITION = '42611';
    const INVALID_CURSOR_DEFINITION = '42P11';
    const INVALID_DATABASE_DEFINITION = '42P12';
    const INVALID_FUNCTION_DEFINITION = '42P13';
    const INVALID_PREPARED_STATEMENT_DEFINITION = '42P14';
    const INVALID_SCHEMA_DEFINITION = '42P15';
    const INVALID_TABLE_DEFINITION = '42P16';
    const INVALID_OBJECT_DEFINITION = '42P17';

    // Class 44 - WITH CHECK OPTION Violation
    const WITH_CHECK_OPTION_VIOLATION = '44000';

    // Class 53 - Insufficient Resources
    const INSUFFICIENT_RESOURCES = '53000';
    const DISK_FULL = '53100';
    const OUT_OF_MEMORY = '53200';
    const TOO_MANY_CONNECTIONS = '53300';

    // Class 54 - Program Limit Exceeded
    const PROGRAM_LIMIT_EXCEEDED = '54000';
    const STATEMENT_TOO_COMPLEX = '54001';
    const TOO_MANY_COLUMNS = '54011';
    const TOO_MANY_ARGUMENTS = '54023';

    // Class 55 - Object Not In Prerequisite State
    const OBJECT_NOT_IN_PREREQUISITE_STATE = '55000';
    const OBJECT_IN_USE = '55006';
    const CANT_CHANGE_RUNTIME_PARAM = '55P02';
    const LOCK_NOT_AVAILABLE = '55P03';

    // Class 57 - Operator Intervention
    const OPERATOR_INTERVENTION = '57000';
    const QUERY_CANCELED = '57014';
    const ADMIN_SHUTDOWN = '57P01';
    const CRASH_SHUTDOWN = '57P02';
    const CANNOT_CONNECT_NOW = '57P03';

    // Class 58 - System Error (errors external to PostgreSQL itself)
    const IO_ERROR = '58030';
    const UNDEFINED_FILE = '58P01';
    const DUPLICATE_FILE = '58P02';

    // Class F0 - Configuration File Error
    const CONFIG_FILE_ERROR = 'F0000';
    const LOCK_FILE_EXISTS = 'F0001';

    // Class P0 - PL/pgSQL Error
    const PLPGSQL_ERROR = 'P0000';
    const RAISE_EXCEPTION = 'P0001';

    // Class XX - Internal Error
    const INTERNAL_ERROR = 'XX000';
    const DATA_CORRUPTED = 'XX001';
    const INDEX_CORRUPTED = 'XX002';

    protected $names = [
        // Class 00 - Successful Completion
        '00000' => 'SUCCESSFUL COMPLETION',

        // Class 01 - Warning
        '01000' => 'WARNING',
        '0100C' => 'DYNAMIC RESULT SETS RETURNED',
        '01008' => 'IMPLICIT ZERO BIT PADDING',
        '01003' => 'NULL VALUE ELIMINATED IN SET FUNCTION',
        '01007' => 'PRIVILEGE NOT GRANTED',
        '01006' => 'PRIVILEGE NOT REVOKED',
        '01004' => 'STRING DATA RIGHT TRUNCATION',
        '01P01' => 'DEPRECATED FEATURE',

        // Class 02 - No Data (this is also a warning class per the SQL standard)
        '02000' => 'NO DATA',
        '02001' => 'NO ADDITIONAL DYNAMIC RESULT SETS RETURNED',

        // Class 03 - SQL Statement Not Yet Complete
        '03000' => 'SQL STATEMENT NOT YET COMPLETE',

        // Class 08 - Connection Exception
        '08000' => 'CONNECTION EXCEPTION',
        '08003' => 'CONNECTION DOES NOT EXIST',
        '08006' => 'CONNECTION FAILURE',
        '08001' => 'SQLCLIENT UNABLE TO ESTABLISH SQLCONNECTION',
        '08004' => 'SQLSERVER REJECTED ESTABLISHMENT OF SQLCONNECTION',
        '08007' => 'TRANSACTION RESOLUTION UNKNOWN',
        '08P01' => 'PROTOCOL VIOLATION',

        // Class 09 - Triggered Action Exception
        '09000' => 'TRIGGERED ACTION EXCEPTION',

        // Class 0A - Feature Not Supported
        '0A000' => 'FEATURE NOT SUPPORTED',

        // Class 0B - Invalid Transaction Initiation
        '0B000' => 'INVALID TRANSACTION INITIATION',

        // Class 0F - Locator Exception
        '0F000' => 'LOCATOR EXCEPTION',
        '0F001' => 'INVALID LOCATOR SPECIFICATION',

        // Class 0L - Invalid Grantor
        '0L000' => 'INVALID GRANTOR',
        '0LP01' => 'INVALID GRANT OPERATION',

        // Class 0P - Invalid Role Specification
        '0P000' => 'INVALID ROLE SPECIFICATION',

        // Class 21 - Cardinality Violation
        '21000' => 'CARDINALITY VIOLATION',

        // Class 22 - Data Exception
        '22000' => 'DATA EXCEPTION',
        '2202E' => 'ARRAY SUBSCRIPT ERROR',
        '22021' => 'CHARACTER NOT IN REPERTOIRE',
        '22008' => 'DATETIME FIELD OVERFLOW',
        '22012' => 'DIVISION BY ZERO',
        '22005' => 'ERROR IN ASSIGNMENT',
        '2200B' => 'ESCAPE CHARACTER CONFLICT',
        '22022' => 'INDICATOR OVERFLOW',
        '22015' => 'INTERVAL FIELD OVERFLOW',
        '2201E' => 'INVALID ARGUMENT FOR LOGARITHM',
        '2201F' => 'INVALID ARGUMENT FOR POWER FUNCTION',
        '2201G' => 'INVALID ARGUMENT FOR WIDTH BUCKET FUNCTION',
        '22018' => 'INVALID CHARACTER VALUE FOR CAST',
        '22007' => 'INVALID DATETIME FORMAT',
        '22019' => 'INVALID ESCAPE CHARACTER',
        '2200D' => 'INVALID ESCAPE OCTET',
        '22025' => 'INVALID ESCAPE SEQUENCE',
        '22P06' => 'NONSTANDARD USE OF ESCAPE CHARACTER',
        '22010' => 'INVALID INDICATOR PARAMETER VALUE',
        '22020' => 'INVALID LIMIT VALUE',
        '22023' => 'INVALID PARAMETER VALUE',
        '2201B' => 'INVALID REGULAR EXPRESSION',
        '22009' => 'INVALID TIME ZONE DISPLACEMENT VALUE',
        '2200C' => 'INVALID USE OF ESCAPE CHARACTER',
        '2200G' => 'MOST SPECIFIC TYPE MISMATCH',
        '22004' => 'NULL VALUE NOT ALLOWED',
        '22002' => 'NULL VALUE NO INDICATOR PARAMETER',
        '22003' => 'NUMERIC VALUE OUT OF RANGE',
        '22026' => 'STRING DATA LENGTH MISMATCH',
        '22001' => 'STRING DATA RIGHT TRUNCATION',
        '22011' => 'SUBSTRING ERROR',
        '22027' => 'TRIM ERROR',
        '22024' => 'UNTERMINATED C STRING',
        '2200F' => 'ZERO LENGTH CHARACTER STRING',
        '22P01' => 'FLOATING POINT EXCEPTION',
        '22P02' => 'INVALID TEXT REPRESENTATION',
        '22P03' => 'INVALID BINARY REPRESENTATION',
        '22P04' => 'BAD COPY FILE FORMAT',
        '22P05' => 'UNTRANSLATABLE CHARACTER',

        // Class 23 - Integrity Constraint Violation
        '23000' => 'INTEGRITY CONSTRAINT VIOLATION',
        '23001' => 'RESTRICT VIOLATION',
        '23502' => 'NOT NULL VIOLATION',
        '23503' => 'FOREIGN KEY VIOLATION',
        '23505' => 'UNIQUE VIOLATION',
        '23514' => 'CHECK VIOLATION',

        // Class 24 - Invalid Cursor State
        '24000' => 'INVALID CURSOR STATE',

        // Class 25 - Invalid Transaction State
        '25000' => 'INVALID TRANSACTION STATE',
        '25001' => 'ACTIVE SQL TRANSACTION',
        '25002' => 'BRANCH TRANSACTION ALREADY ACTIVE',
        '25008' => 'HELD CURSOR REQUIRES SAME ISOLATION LEVEL',
        '25003' => 'INAPPROPRIATE ACCESS MODE FOR BRANCH TRANSACTION',
        '25004' => 'INAPPROPRIATE ISOLATION LEVEL FOR BRANCH TRANSACTION',
        '25005' => 'NO ACTIVE SQL TRANSACTION FOR BRANCH TRANSACTION',
        '25006' => 'READ ONLY SQL TRANSACTION',
        '25007' => 'SCHEMA AND DATA STATEMENT MIXING NOT SUPPORTED',
        '25P01' => 'NO ACTIVE SQL TRANSACTION',
        '25P02' => 'IN FAILED SQL TRANSACTION',

        // Class 26 - Invalid SQL Statement Name
        '26000' => 'INVALID SQL STATEMENT NAME',

        // Class 27 - Triggered Data Change Violation
        '27000' => 'TRIGGERED DATA CHANGE VIOLATION',

        // Class 28 - Invalid Authorization Specification
        '28000' => 'INVALID AUTHORIZATION SPECIFICATION',

        // Class 2B - Dependent Privilege Descriptors Still Exist
        '2B000' => 'DEPENDENT PRIVILEGE DESCRIPTORS STILL EXIST',
        '2BP01' => 'DEPENDENT OBJECTS STILL EXIST',

        // Class 2D - Invalid Transaction Termination
        '2D000' => 'INVALID TRANSACTION TERMINATION',

        // Class 2F - SQL Routine Exception
        '2F000' => 'SQL ROUTINE EXCEPTION',
        '2F005' => 'FUNCTION EXECUTED NO RETURN STATEMENT',
        '2F002' => 'MODIFYING SQL DATA NOT PERMITTED',
        '2F003' => 'PROHIBITED SQL STATEMENT ATTEMPTED',
        '2F004' => 'READING SQL DATA NOT PERMITTED',

        // Class 34 - Invalid Cursor Name
        '34000' => 'INVALID CURSOR NAME',

        // Class 38 - External Routine Exception
        '38000' => 'EXTERNAL ROUTINE EXCEPTION',
        '38001' => 'CONTAINING SQL NOT PERMITTED',
        '38002' => 'MODIFYING SQL DATA NOT PERMITTED',
        '38003' => 'PROHIBITED SQL STATEMENT ATTEMPTED',
        '38004' => 'READING SQL DATA NOT PERMITTED',

        // Class 39 - External Routine Invocation Exception
        '39000' => 'EXTERNAL ROUTINE INVOCATION EXCEPTION',
        '39001' => 'INVALID SQLSTATE RETURNED',
        '39004' => 'NULL VALUE NOT ALLOWED',
        '39P01' => 'TRIGGER PROTOCOL VIOLATED',
        '39P02' => 'SRF PROTOCOL VIOLATED',

        // Class 3B - Savepoint Exception
        '3B000' => 'SAVEPOINT EXCEPTION',
        '3B001' => 'INVALID SAVEPOINT SPECIFICATION',

        // Class 3D - Invalid Catalog Name
        '3D000' => 'INVALID CATALOG NAME',

        // Class 3F - Invalid Schema Name
        '3F000' => 'INVALID SCHEMA NAME',

        // Class 40 - Transaction Rollback
        '40000' => 'TRANSACTION ROLLBACK',
        '40002' => 'TRANSACTION INTEGRITY CONSTRAINT VIOLATION',
        '40001' => 'SERIALIZATION FAILURE',
        '40003' => 'STATEMENT COMPLETION UNKNOWN',
        '40P01' => 'DEADLOCK DETECTED',

        // Class 42 - Syntax Error or Access Rule Violation
        '42000' => 'SYNTAX ERROR OR ACCESS RULE VIOLATION',
        '42601' => 'SYNTAX ERROR',
        '42501' => 'INSUFFICIENT PRIVILEGE',
        '42846' => 'CANNOT COERCE',
        '42803' => 'GROUPING ERROR',
        '42830' => 'INVALID FOREIGN KEY',
        '42602' => 'INVALID NAME',
        '42622' => 'NAME TOO LONG',
        '42939' => 'RESERVED NAME',
        '42804' => 'DATATYPE MISMATCH',
        '42P18' => 'INDETERMINATE DATATYPE',
        '42809' => 'WRONG OBJECT TYPE',
        '42703' => 'UNDEFINED COLUMN',
        '42883' => 'UNDEFINED FUNCTION',
        '42P01' => 'UNDEFINED TABLE',
        '42P02' => 'UNDEFINED PARAMETER',
        '42704' => 'UNDEFINED OBJECT',
        '42701' => 'DUPLICATE COLUMN',
        '42P03' => 'DUPLICATE CURSOR',
        '42P04' => 'DUPLICATE DATABASE',
        '42723' => 'DUPLICATE FUNCTION',
        '42P05' => 'DUPLICATE PREPARED STATEMENT',
        '42P06' => 'DUPLICATE SCHEMA',
        '42P07' => 'DUPLICATE TABLE',
        '42712' => 'DUPLICATE ALIAS',
        '42710' => 'DUPLICATE OBJECT',
        '42702' => 'AMBIGUOUS COLUMN',
        '42725' => 'AMBIGUOUS FUNCTION',
        '42P08' => 'AMBIGUOUS PARAMETER',
        '42P09' => 'AMBIGUOUS ALIAS',
        '42P10' => 'INVALID COLUMN REFERENCE',
        '42611' => 'INVALID COLUMN DEFINITION',
        '42P11' => 'INVALID CURSOR DEFINITION',
        '42P12' => 'INVALID DATABASE DEFINITION',
        '42P13' => 'INVALID FUNCTION DEFINITION',
        '42P14' => 'INVALID PREPARED STATEMENT DEFINITION',
        '42P15' => 'INVALID SCHEMA DEFINITION',
        '42P16' => 'INVALID TABLE DEFINITION',
        '42P17' => 'INVALID OBJECT DEFINITION',

        // Class 44 - WITH CHECK OPTION Violation
        '44000' => 'WITH CHECK OPTION VIOLATION',

        // Class 53 - Insufficient Resources
        '53000' => 'INSUFFICIENT RESOURCES',
        '53100' => 'DISK FULL',
        '53200' => 'OUT OF MEMORY',
        '53300' => 'TOO MANY CONNECTIONS',

        // Class 54 - Program Limit Exceeded
        '54000' => 'PROGRAM LIMIT EXCEEDED',
        '54001' => 'STATEMENT TOO COMPLEX',
        '54011' => 'TOO MANY COLUMNS',
        '54023' => 'TOO MANY ARGUMENTS',

        // Class 55 - Object Not In Prerequisite State
        '55000' => 'OBJECT NOT IN PREREQUISITE STATE',
        '55006' => 'OBJECT IN USE',
        '55P02' => 'CANT CHANGE RUNTIME PARAM',
        '55P03' => 'LOCK NOT AVAILABLE',

        // Class 57 - Operator Intervention
        '57000' => 'OPERATOR INTERVENTION',
        '57014' => 'QUERY CANCELED',
        '57P01' => 'ADMIN SHUTDOWN',
        '57P02' => 'CRASH SHUTDOWN',
        '57P03' => 'CANNOT CONNECT NOW',

        // Class 58 - System Error (errors external to PostgreSQL itself)
        '58030' => 'IO ERROR',
        '58P01' => 'UNDEFINED FILE',
        '58P02' => 'DUPLICATE FILE',

        // Class F0 - Configuration File Error
        'F0000' => 'CONFIG FILE ERROR',
        'F0001' => 'LOCK FILE EXISTS',

        // Class P0 - PL/pgSQL Error
        'P0000' => 'PLPGSQL ERROR',
        'P0001' => 'RAISE EXCEPTION',

        // Class XX - Internal Error
        'XX000' => 'INTERNAL ERROR',
        'XX001' => 'DATA CORRUPTED',
        'XX002' => 'INDEX CORRUPTED'
    ];

    public static function getAnyId()
    {
        return self::SUCCESSFUL_COMPLETION;
    }

    public static function wrap($id)
    {
        return new self($id);
    }
}
