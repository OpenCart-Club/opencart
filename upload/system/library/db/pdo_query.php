<?php
namespace DB;

class PdoQuery implements \IQueryFetchable {
    /**
     * @var \PDOStatement
     */
	private $statement;

    /**
     * @param \PDOStatement $statement
     */
	public function __construct($statement) {
		$this->statement = $statement;
	}

    public function __destruct() {
    }

    /**
     * @return array
     */
	public function fetch() {
		return $this->statement->fetch(\PDO::FETCH_ASSOC);
	}
}
