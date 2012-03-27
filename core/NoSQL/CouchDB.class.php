<?php
/***************************************************************************
 *	 Created by Alexey V. Gorbylev at 28.12.2011                           *
 *	 email: alex@gorbylev.ru, icq: 1079586, skype: avid40k                 *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * CouchDB connector.
 *
 * @see http://couchdb.apache.org/
 *
 * @ingroup NoSQL
**/
class CouchDB extends NoSQL {

	// methods
	const
		GET		= 1,
		POST	= 2,
		PUT		= 3,
		DELETE	= 4;

	// credentials
	protected $port		= 5984;

	// quering
	protected $_methods = array( self::GET=>self::GET, self::POST=>self::POST, self::PUT=>self::PUT, self::DELETE=>self::DELETE );


	public function  __construct() {
		if( !extension_loaded('curl') ) {
			throw new MissingModuleException('CouchDB needs CURL module for PHP');
		}
	}

	/**
	 * @param string $dbname
	 * @param string $id
	 * @return array
	 * @throws WrongArgumentException
	 */
	public function select() {
		// checking args
		if (func_num_args()!=2) {
			throw new WrongArgumentException('Only 2 arguments allowed');
		}

		// parse args
		$dbname = func_get_arg(0);
		$id = func_get_arg(1);

		// checking dbname
		Assert::isString($dbname);
		Assert::isNotEmpty($dbname);
		// checking id
		Assert::isScalar($id);
		Assert::isNotEmpty($id);

		return $this->exec( $this->getUrl($dbname, $id), self::GET );
	}

	/**
	 * @param string $dbname
	 * @param array $object
	 * @return array
	 * @throws WrongArgumentException
	 */
	public function insert() {
		// checking args
		if (func_num_args()!=2) {
			throw new WrongArgumentException('Only 2 arguments allowed');
		}

		// parse args
		$dbname = func_get_arg(0);
		$object = func_get_arg(1);

		// checking dbname
		Assert::isString($dbname);
		Assert::isNotEmpty($dbname);
		// checking object
		Assert::isArray($object);
		Assert::isNotEmptyArray($object);


		// remove id
		$id = urlencode($object['id']);
		unset( $object['id'] );

		$response = $this->exec( $this->getUrl($dbname, $id), self::PUT, json_encode($object) );
		$object['id'] = $response['id'];
		$object['_rev'] = $response['rev'];
		return $object;
	}

	/**
	 * @param string $dbname
	 * @param array $object
	 * @param string $rev
	 * @return array
	 * @throws WrongArgumentException
	 */
	public function update() {
		// checking args
		if (func_num_args()!=3) {
			throw new WrongArgumentException('Only 3 arguments allowed');
		}

		// parse args
		$dbname = func_get_arg(0);
		$object = func_get_arg(1);
		$rev = func_get_arg(2);

		// checking dbname
		Assert::isString($dbname);
		Assert::isNotEmpty($dbname);
		// checking object
		Assert::isArray($object);
		Assert::isNotEmptyArray($object);
		// checking rev
		Assert::isString($rev);
		Assert::isNotEmpty($rev);

		// remove id
		$id = urlencode($object['id']);
		unset( $object['id'] );
		// add rev
		$object['_rev'] = $rev;

		$response = $this->exec( $this->getUrl($dbname, $id), self::PUT, json_encode($object) );
		$object['id'] = $response['id'];
		$object['_rev'] = $response['rev'];
		return $object;
	}

	/**
	 * @param string $dbname
	 * @param string $id
	 * @param string $rev
	 * @return int
	 * @throws WrongArgumentException
	 */
	public function delete() {
		// checking args
		if (func_num_args()!=3) {
			throw new WrongArgumentException('Only 3 arguments allowed');
		}

		// parse args
		$dbname = func_get_arg(0);
		$id = urlencode(func_get_arg(1));
		$rev = func_get_arg(2);

		// checking dbname
		Assert::isString($dbname);
		Assert::isNotEmpty($dbname);
		// checking id
		Assert::isScalar($id);
		Assert::isNotEmpty($id);
		// checking rev
		Assert::isString($rev);
		Assert::isNotEmpty($rev);

		$response = $this->exec( $this->getUrl($dbname, $id, array('rev'=>$rev)), self::DELETE );

		return $response['ok'] ? 1 : 0;
	}

	/**
	 * @param string $dbname
	 * @return array
	 * @throws WrongArgumentException
	 */
	public function getAllObjects() {
		// checking args
		if (func_num_args()!=1) {
			throw new WrongArgumentException('Only 1 argument allowed');
		}

		// parse args
		$dbname = func_get_arg(0);

		// checking dbname
		Assert::isString($dbname);
		Assert::isNotEmpty($dbname);

		$response = $this->exec( $this->getUrl($dbname, '_all_docs', array('include_docs' => 'true')), self::GET );
		$list = array();
		if( isset($response['total_rows']) && isset($response['rows']) ) {
            foreach ($response['rows'] as $row) {
                $list[] = $row['doc'];
            }
		}

		return $list;
	}

	/**
	 * @param string $dbname
	 * @return int
	 * @throws WrongArgumentException
	 */
	public function getTotalCount() {
		// checking args
		if (func_num_args()!=1) {
			throw new WrongArgumentException('Only 1 argument allowed');
		}

		// parse args
		$dbname = func_get_arg(0);

		// checking dbname
		Assert::isString($dbname);
		Assert::isNotEmpty($dbname);

		$response = $this->exec( $this->getUrl($dbname, '_all_docs'), self::POST, '{"keys":[]}' );
		$count = 0;
		if( isset($response['total_rows']) ) {
			$count = $response['total_rows'];
		}

		return $count;
	}

	/**
	 * @param string $dbname
	 * @param string $view
	 * @param array $params [optional]
	 * @return array
	 * @throws WrongArgumentException
	 */
	public function getCustomList() {
		// checking args
		if (func_num_args()!=2 && func_num_args()!=3) {
			throw new WrongArgumentException('Only 2 or 3 arguments allowed');
		}

		// parse args
		$dbname = func_get_arg(0);
		$view = func_get_arg(1);
		$params = null;

		// checking dbname
		Assert::isString($dbname);
		Assert::isNotEmpty($dbname);
		// checking view
		Assert::isString($view);
		Assert::isNotEmpty($view);

		// checking params
		if( func_num_args()==3 ) {
			$params = func_get_arg(2);
			// checking params
			Assert::isArray($params);
			Assert::isNotEmptyArray($params);
		}

        //die( $this->getUrl($dbname, $view, $params) );
		$response = $this->exec( $this->getUrl($dbname, $view, $params), self::GET );
		$list = array();
		if( isset($response['total_rows']) && isset($response['rows']) ) {
			$list = $response['rows'];
		}

		return $list;
	}

	/**
	 * @param string $dbname
	 * @param string $view
	 * @param array $params [optional]
	 * @return mixed
	 * @throws WrongArgumentException
	 */
	public function getCustomData() {
		// checking args
		if (func_num_args()!=2 && func_num_args()!=3) {
			throw new WrongArgumentException('Only 2 or 3 arguments allowed');
		}

		// parse args
		$dbname = func_get_arg(0);
		$view = func_get_arg(1);
		$params = null;

		// checking dbname
		Assert::isString($dbname);
		Assert::isNotEmpty($dbname);
		// checking view
		Assert::isString($view);
		Assert::isNotEmpty($view);

		// checking params
		if( func_num_args()==3 ) {
			$params = func_get_arg(2);
			// checking params
			Assert::isArray($params);
			Assert::isNotEmptyArray($params);
		}
		// reduce
		$params['reduce'] = 'false';

		// query
		$response = $this->exec( $this->getUrl($dbname, $view, $params), self::GET );
		$result = null;
		if( isset($response['rows']) ) {
			$result = $response['value'];
		}

		return $result;
	}

	public function obtainSequence($sequence) {
		$normalyzeSequence = mb_strtolower( trim( $sequence ) );
		if(
			'uuid' === $normalyzeSequence ||
			'uuid_id' === $normalyzeSequence
		) {
			return UuidUtils::generate();
		} else {
			$response = $this->exec( $this->getUrl('_uuids'), self::GET );
			return array_shift( $response );

		}
	}

	/**
	 * @param string $dbname
	 * @param string $path
	 * @return string
	 */
	protected function getUrl( $dbname, $path=null, array $params=null ) {
		Assert::isString($dbname);

		$urlPath = '/';
		if( !empty($dbname) ) {
			$urlPath .= $dbname;
			if( !empty($path) ) {
				$urlPath .= '/'.$path;
			}
		}

		$urlParams = null;
		if( !empty($params) ) {
			$strParams = array();
			foreach( $params as $key=>$value ) {
				$strParams[] = urlencode($key).'='.urlencode($value);
			}
			$urlParams = implode('&', $strParams);
			unset($strParams);
		}

		return
			HttpUrl::create()
				->setScheme('http')
				->setAuthority( $this->username.':'.$this->password )
				->setHost( $this->hostname )
				->setPort( $this->port )
				->setPath( $urlPath )
				->setQuery( $urlParams )
				->toString();
	}

	/**
	 * Execeutes CURL request
	 * @param string $url
	 * @param string $method
	 * @param string|null $data
	 * @return array
	 * @throws NoSQLException|ObjectNotFoundException
	 */
	protected function exec( $url, $method, $data=null ) {
		if( !in_array($method, $this->_methods) ) {
			throw new WrongArgumentException("Wrong menthod '{$method}'!");
		}

		// create resource
		$ch = curl_init();

		// options
		$options = array(
			CURLOPT_RETURNTRANSFER => true,
			CURLINFO_HEADER_OUT => true,
			//CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_URL => $url,
			CURLOPT_PORT => $this->port,
			CURLOPT_USERAGENT => 'onPHP::'.__CLASS__
		);

		if ( !is_null($this->username) && !is_null($this->password) ) {
			$options[CURLOPT_USERPWD] = $this->username . ':' . $this->password;
		}

		switch( $method ) {
			case self::GET: {
				$options[CURLOPT_HTTPGET] = true;
			} break;
			case self::POST: {
				$options[CURLOPT_CUSTOMREQUEST] = 'POST';
				$options[CURLOPT_POSTFIELDS] = $data;
				$options[CURLOPT_HTTPHEADER] = array( 'Content-type: application/json' );
			} break;
			case self::PUT: {
				$options[CURLOPT_CUSTOMREQUEST] = 'PUT';
				$options[CURLOPT_POSTFIELDS] = $data;
				$options[CURLOPT_HTTPHEADER] = array( 'Content-type: application/json' );
			} break;
			case self::DELETE: {
				$options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
			} break;
		}

		// set options
		curl_setopt_array( $ch, $options );

		// curl exec
		$response = curl_exec( $ch );
		$status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );

		// checking status
		$answer = false;
		switch( $status ) {
			case 0: {
				throw new NoSQLException( 'CouchDB server is asleep' );
			} break;
			case 200:
			case 201:
			case 202:
			case 304: {
				// decoding answer
				$answer = json_decode( $response, true );
			} break;
			case 400: {
				throw new NoSQLException( 'Bad Request' );
			} break;
			case 401: {
				throw new NoSQLException( 'Unauthorized' );
			} break;
			case 403: {
				throw new NoSQLException( 'Forbidden' );
			} break;
			case 404: {
				$answer = json_decode( $response, true );
				$reason = isset($answer['reason']) ? $answer['reason'] : null;
				throw new ObjectNotFoundException( $reason );
			} break;
			case 405: {
				throw new NoSQLException( 'Resource Not Allowed, url: '.$url );
			} break;
			case 406: {
				throw new NoSQLException( 'Not Acceptable' );
			} break;
			case 409: {
				throw new NoSQLException( 'Conflict' );
			} break;
			case 412: {
				throw new NoSQLException( 'Precondition Failed' );
			} break;
			case 415: {
				throw new NoSQLException( 'Bad Content Type' );
			} break;
			case 401: {
				throw new NoSQLException( 'Unauthorized' );
			} break;
			case 500: {
				throw new NoSQLException( 'CouchDB server error: '.var_export($response, true) );
			} break;
			default: {
				throw new NoSQLException( 'CouchDB fatal error. Code: '.$status.'  Info:'.var_export($response, true) );
			} break;
		}
		// closing
		curl_close($ch);

		return $answer;
	}

}
