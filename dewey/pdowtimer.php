<?php

class PDOwTimer extends PDO {
	public $time = 0;
	public $lastTime = 0;
	public $queries = 0;
	
	function __construct($dsn, $user=null, $pass=null, $options=null) {
		// mantém igual
		parent::__construct($dsn, $user, $pass, $options);
	}
	function query($sql) {
		// função query com contador de tempo de execução
		@$this->queries++;
		$micro = microtime(true);
		$stmt = parent::query($sql);
		$this->lastTime = microtime(true) - $micro;
		$this->time += $this->lastTime;
		return $stmt;
	}
}

?>
