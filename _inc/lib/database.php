<?php

class Database {

	private $last_query      = '';
	private $last_insert_id  = null;
	public $error           = array();
	public $conn              = null;
	public $num_rows         = 0;
	public $has_rows         = false;
	public $rows_effected = false;
	public $is_connected     = false;
	protected $dbuser;
	protected $dbpassword;
	protected $dbhost;
	protected $dbname;

	/**
	 * SQLSRV_DataBase constructor.
	 *
	 *
	 * @param string $dbuser       MSSQL database user
	 * @param string $dbpassword   MSSQL database password
	 * @param string $dbname       MSSQL database name
	 * @param string $dbhost       MSSQL database host
	 */
	public function __construct( $dbuser, $dbpassword, $dbname, $dbhost ) {
		$this->dbuser       = $dbuser;
		$this->dbpassword   = $dbpassword;
		$this->dbname       = $dbname;
		$this->dbhost       = $dbhost;

		$this->is_connected = $this->db_connect();
	}

	/**
	 * Connect to and select database
	 *
	 * @return bool
	 */
	public function db_connect() {
		$serverName = $this->dbhost;
		$connectionOptions = array(
			"Database" => $this->dbname,
			"UID"      => $this->dbuser,
			"PWD"      => $this->dbpassword,
			"Encrypt"  => true,
			"TrustServerCertificate" => true,
			"ConnectionPooling" => true,
		);

		// Create the connection resource
		$this->conn = sqlsrv_connect( $serverName, $connectionOptions );

		// If the connection fails we get a false value and build our error log
		if ( false === $this->conn )
		{
			/*
			 * We don't use log_error() here as the values passed from a failed connection
			 * are not compatible with the errors passed from a failed query
			 */
			$error = sqlsrv_errors();
			$this->error[] = $error;
			error_log("\n\n" . '<b>[Database] Exception:</b>: ' . print_r($error, true), 3, DIR_LOG . "sql.txt" );

		}
		sqlsrv_configure( 'WarningsReturnAsErrors', true );
		return true;
	}

	/**
	 * Prepare the DB class for a new query
	 *

	 *
	 * @return void
	 */
	private function prepare() {
		$this->error          = array();
		$this->last_insert_id = null;
		$this->last_query     = '';
		$this->num_rows       = 0;
		$this->has_rows       = false;
	}

	/**
	 * Log errors to the error container of the class and to the systems error log
	 *
	 * @param array $errors
	 *

	 *
	 * @return void
	*/
	private function log_error( $errors ) {
		foreach( $errors AS $error ) {
			$new_error = array(
				'DATETIME' => date("D d-M-Y h:i:s"),
				'SQLSTATE' => $error['SQLSTATE'],
				'code'     => $error['code'],
				'message'  => $error['message'],
				'query'    => $this->last_query
			);

			error_log( "\n\n" . '[Database Exception]: ' . var_export( $new_error, true ), 3, DIR_LOG . "sql.txt");
			$this->error[] = $new_error;
		}
	}

	/**
	 * Update values in a table that matches the give ncriterias
	 *
	 * @param string $table
	 * @param array  $what
	 * @param array  $where
	 *

	 *
	 * @return void
	 */
	public function update($table, $what, $where, $params ) {
		$set   = '';
		$check = '';

		foreach( $what AS $field => $value ) {
			$field = trim($field);
			$value = trim($value);

			if (!empty($set) ) {
				$set .= ', ';
			}
			$set .= $value . ' = ?';
		}

		foreach( $where AS $field => $value ) {
			$field = trim($field);
			$value = trim($value);

			if (!empty($check) ) {
				$check .= ' AND ';
			}
			$check .= $value . ' = ?';
		}

		$sql = " UPDATE " . $table . " SET " . $set . " WHERE " . $check . "";
		$result = $this->query($sql, $params, false );
	}

	/**
	 * Delete rows in a table based on the given criterias
	 *
	 * @param string $table
	 * @param array  $where
	 *

	 *
	 * @return void
	 */
	public function delete( $table, $where, $params ) {
		$check = '';
		foreach( $where AS $field => $value ) {

			$check .= ' AND ' . $table . '.' . $value;
			$check .= ' = ?';

		}

		$result = $this->query( "
			DELETE FROM
				" . $table . "
			WHERE
				1 = 1
				" . $check . "
		", $params, false );
	}

	/**
	 * Insert a new row and populate it with the given values
	 *
	 * @param string $table
	 *

	 *
	 * @return void
	 */
	public function insert( $table, $fieldo, $params, $direct = true ) {
		$fields = '';
		$values = '';
		if($direct){
			foreach( $fieldo AS $field => $value ) {

				if ( ! empty( $fields ) ) {
					$fields .= ', ';
					$values .= ', '; 
				}
				$values .= '? ';
				$fields .= $value;
			}
			$result = $this->query( "INSERT INTO " . $table . " ( " . $fields . " ) VALUES ( " . $values . " )", $params, false );
		}else{
			$result = $this->query( false );
		}
	}

	/**
	 * Get a single row from the database and return it in the given format
	 *
	 * @param string $query
	 * @param string $format
	 *

	 *
	 * @return array|bool|null|object
	 */
	public function get_row($query, $where = array(), $format = 'object' ) {
		$request = $this->query( $query , $where );

		if ( ! $this->has_error() ) {
			if ( 'array' == $format ) {
				$response = sqlsrv_fetch_array( $request, SQLSRV_FETCH_ASSOC );
			}
			else {
				$response = sqlsrv_fetch_object( $request );
			}
		}
		else {
			$response = false;
		}

		return $response;
	}

	/**
	 * Get all the rows returned by a query to the database
	 *
	 * @param string $query
	 * @param string $format
	 *

	 *
	 * @return array|bool
	 */
	public function get_results( $query, $where = array(), $format = 'object' ) {
		$response = array();

		$request = $this->query( $query , $where);

		if ( $this->has_error() ) {
			$response = false;
		}
		else {
			if ( 'array' == $format ) {
				while ( $answer = sqlsrv_fetch_array( $request, SQLSRV_FETCH_ASSOC ) ) {
					$response[] = $answer;
				}
			}
			else {
				while ( $answer = sqlsrv_fetch_object( $request ) ) {
					$response[] = $answer;
				}
			}
		}

		return $response;
	}

	/**
	 * Return the primary index value from a table
	 *
	 * @return bool|int
	 */
	public function last_insert_id() {
		if ( $this->has_error() || empty( $this->last_query ) ) {
			return false;
		}

		if ( empty( $this->last_insert_id ) ) {
			$this->last_insert_id = $this->get_row( "SELECT SCOPE_IDENTITY() AS [SCOPE_IDENTITY]" );
		}

		return $this->last_insert_id->SCOPE_IDENTITY;
	}

	/**

	 * @deprecated 0.2.0 Use last_insert_id()
	 * @see last_insert_id()
	 *
	 * @return bool|int
	 */
	public function get_last_id() {
		return $this->last_insert_id();
	}

	/**
	 * Runs the actual query against the database
	 *
	 * @param string $query
	 * @param bool   $can_get_rows
	 *

	 *
	 * @return bool|resource
	 */
	public function query( $query, $where = array(), $can_get_rows = true ) {
		// If no connection is found we try to restore it
		if ( ! $this->is_connected ) {
			$this->is_connected = $this->db_connect();

			// If we couldn't reconnect we break out early
			if ( ! $this->is_connected ) {
				return false;
			}
		}
		$this->prepare();
		$this->last_query = $query;
		$doing_query = sqlsrv_query( $this->conn, $query , $where);
		
		if ( false === $doing_query ) {
			if ( null != ( $errors = sqlsrv_errors() ) ) {
				$this->log_error( $errors );
			}
			throw new Exception(print_r( $this->error, true));
		}
		else {
			$this->has_rows = true;
			$this->num_rows = sqlsrv_num_rows( $doing_query );
		}

		if ( $can_get_rows ) {
			if (sqlsrv_has_rows($doing_query) ) {
				$this->has_rows = true;
			} else {
				$this->has_rows = false;
			}
		}else{
			$rows_affected = sqlsrv_rows_affected( $doing_query );
			if( $rows_affected === false) {
				$this->log_error( $this->error );
			} elseif( $rows_affected == -1) {
				  $this->rows_effected = false;
			} else {
				$this->rows_effected = $rows_affected;
			}
		}

		return $doing_query;
	}

	/**
	 * Return a list of errors encountered on the last query, or false
	 *
	 * @since 0.2.0
	 *
	 * @return array|bool
	 */
	public function has_error() {
		if ( ! empty( $this->error ) ) {
			return $this->error;
		}

		return false;
	}

	/**

	 * @deprecated 0.2.0 Use has_error() instead
	 * @see has_error()
	 *
	 * @return array|bool
	 */
	public function hasError() {
		return $this->has_error();
	}

	/**
	 * Return the last ran query in its entirety
	 *

	 *
	 * @return string
	 */
	public function get_last_query() {
		return $this->last_query;
	}
}
