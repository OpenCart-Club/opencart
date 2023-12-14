<?php
namespace DB;

class PgsqlQuery implements \IQueryFetchable {
    /**
     * @var \PgSql\Result
     */
	private $resource;

    /**
     * @param \PgSql\Result $resource
     */
	public function __construct($resource) {
		$this->resource = $resource;
	}

    public function __destruct() {
        pg_free_result($this->resource);
    }

    /**
     * @return array
     */
	public function fetch() {
		return pg_fetch_assoc($this->resource);
	}
}
