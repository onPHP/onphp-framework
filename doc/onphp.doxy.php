<?php
	/*
	 * $Id$
	 *
	 * Doxygen's helper file
	 */
	
	/**
	 * @mainpage onPHP
	 * 
	 * For support consider using our <a href="http://onphp.org/contacts.en.html">maling lists</a>.
	 *
	 * <hr>
	 *
	 * <h2>a brief overview:</h2>
	 *
	 * - quasi-persistent layer:
	 *  - OSQL query builder:
	 *   - SelectQuery;
	 *   - InsertQuery;
	 *   - UpdateQuery;
	 *   - DeleteQuery;
	 *  - DB abstraction layer:
	 *   - connectors:
	 *    - PgSQL;
	 *    - MySQL;
	 *    - SQLite;
	 *    - IBase (incubator);
	 *    - MSSQL (incubator);
	 *    - OraSQL (incubator);
	 *   - utils:
	 *    - Transaction:
	 *     - TransactionQueue (Queue);
	 *     - DBTransaction;
	 *    - DBPool;
	 *  - DAO hierarchies:
	 *   - GenericDAO workers:
	 *    - NullDaoWorker;
	 *    - CommonDaoWorker;
	 *    - TransparentDaoWorker:
	 *     - SmartDaoWorker;
	 *     - VoodooDaoWorker;
	 *   - SegmentHandler%s:
	 *    - MessageSegmentHandler;
	 *    - SharedMemorySegmentHandler;
	 *    - FileSystemSegmentHandler;
	 *    - ApcSegmentHandler;
	 *    - eAcceleratorSegmentHandler;
	 *    - XCacheSegmentHandler;
	 * - IdentifiableObject collections:
	 *  - UnifiedContainer;
	 * - Cache subsystem:
	 *  - peers:
	 *   - Memcached (and PeclMemcached);
	 *   - RubberFileSystem;
	 *   - SharedMemory;
	 *   - RuntimeMemory;
	 *  - locking thru SemaphorePool:
	 *   - SystemFiveLocker;
	 *   - FileLocker;
	 *   - DirectoryLocker;
	 *  - utils:
	 *   - AggregateCache;
	 *   - WatermarkedPeer;
	 * - web flow:
	 *  - Model:
	 *   - ModelAndView;
	 *  - View:
	 *   - PartViewer;
	 *   - ViewResolver:
	 *    - PhpViewResolver;
	 *   - RedirectView;
	 *   - RedirectToView;
	 *   - SimplePhpView;
	 *  - Controller:
	 *   - EditorController:
	 *    - CommandChain:
	 *    - AddCommand;
	 *    - SaveCommand;
	 *    - EditCommand;
	 *    - DropCommand;
	 *    - TakeCommand;
	 *    - CarefulCommand:
	 *     - CarefulDatabaseRunner;
	 *    - ForbiddenCommand;
	 *
	 * ...
	 *
	 * @defgroup Core Core classes
	 * Core classes and interfaces you just can't live without
	 *
	 * @defgroup Base Widely used base classes and interfaces
	 * @ingroup Core
	 *
	 * @defgroup Cache Application-wide cache subsystem
	 * @ingroup Core
	 *
	 * @defgroup Lockers Different locking methods implementation
	 * @ingroup Cache
	 *
	 * @defgroup DB Connectors and dialects for various databases
	 * @ingroup Core
	 *
	 * @defgroup Transaction Tools for working with transactions
	 * @ingroup DB
	 *
	 * @defgroup Exceptions Exceptions
	 * @ingroup Core
	 *
	 * @defgroup Form Data validation layer
	 * @ingroup Core
	 *
	 * @defgroup Filters Tools for primitive's filtration
	 * @ingroup Form
	 *
	 * @defgroup Primitives Base data types used in Form
	 * @ingroup Form
	 *
	 * @defgroup Logic Logical expressions used in OSQL and Form
	 * @ingroup Core
	 *
	 * @defgroup OSQL Dynamic query builder
	 * @ingroup Core
	 *
	 * @defgroup Types Basic types
	 * @ingroup Core
	 *
	 * @defgroup Main Higher level classes
	 * Useful stuff for building complex and scalable applications.
	 *
	 * @defgroup Helpers Common wrapper and helper classes
	 * @ingroup Main
	 *
	 * @defgroup DAOs Root classes for building DAO hierarchies
	 * @ingroup Main
	 *
	 * @defgroup Containers IdentifiableObject collections handlers
	 * @ingroup DAOs
	 * 
	 * @defgroup Criteria Object queries API
	 * @ingroup Main
	 *
	 * @defgroup Projections Object projections for use in Criteria queries
	 * @ingroup Criteria
	 *
	 * @defgroup onSPL Things based on Standard PHP Library
	 * @ingroup Main
	 *
	 * @defgroup Utils Various accompanying utilities
	 * @ingroup Main
	 *
	 * @defgroup Flow Spring-like webflow tools
	 * @ingroup Main
	 *
	 * @defgroup Crypto Diffie-Hellman Key Agreement Method (RFC-2631) implementation
	 * @ingroup Main
	 *
	 * @defgroup OpenId OpenId implementation
	 * @ingroup Main
	 *
	 * @defgroup Calendar Calendar representation's helpers
	 * @ingroup Main
	 *
	 * @defgroup Turing CAPTCHA's implementation
	 * @ingroup Utils
	 *
	 * @defgroup Net Internet standarts implementations
	 * @ingroup Main
	 *
	 * @defgroup Mail Mail utilities
	 * @ingroup Net
	 *
	 * @defgroup Http HTTP related utilities
	 * @ingroup Net
	 *
	 * @defgroup Ip IP related utilities
	 * @ingroup Net
	 *
	 * @defgroup Math Mathematical utilities
	 * @ingroup Main
	 *
	 * @defgroup Markup Various markups implementations
	 * @ingroup Main
	 *
	 * @defgroup Feed Feed's parsers
	 * @ingroup Markup
	 *
	 * @defgroup Html HTML parser
	 * @ingroup Markup
	 *
	 * @defgroup Meta MetaConfiguration
	 *
	 * @defgroup Builders Class builders
	 * @ingroup Meta
	 *
	 * @defgroup MetaBase MetaConfiguration's base classes
	 * @ingroup Meta
	 *
	 * @defgroup Patterns Patterns used to build classes
	 * @ingroup Meta
	 *
	 * @defgroup MetaTypes Supported meta-types
	 * @ingroup Meta
	 *
	 * @defgroup Module Classes implemented in PHP's extension.
	**/
?>
