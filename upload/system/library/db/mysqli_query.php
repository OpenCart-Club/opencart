<?php
namespace DB;

class MysqliQuery implements \IQueryFetchable {
    /**
     * @var \mysqli_result
     */
	private $query;

    /**
     * @param \mysqli_result $query
     */
	public function __construct($query) {
		$this->query = $query;
	}

    public function __destruct() {
        $this->query->close();
    }

    /**
     * @return array
     */
	public function fetch() {
		return $this->query->fetch_assoc();
	}
}
