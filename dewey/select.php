<?php

class Timer {
	function start() { $this->time = microtime(true); }
	function stop() { return microtime(true) - $this->time; }
}
$t = new Timer;

// acesso ao banco de dados
$dbh = new PDO("mysql:dbname=projbd", "root", "toor", array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));

$sql = "SELECT *
		FROM edge e
		JOIN path p USING (path_id)
		WHERE e.doc_id = (SELECT doc_id FROM docs WHERE document=?)
		  AND p.path like ?";
$sth = $dbh->prepare($sql);

if (0) { // query for /dblp
	$t->start();
	$sth->execute(array("dblp.xml","/dblp"));
	echo "/dblp\n\tTime: {$t->stop()}.\n\tFetched {$sth->rowCount()} Rows\n\n";
}
if (0) {
	// query for /dblp/incollection
	$t->start();
	$sth->execute(array("dblp.xml","/dblp/incollection"));
	echo "/dblp/incollection\n\tTime: {$t->stop()}.\n\tFetched {$sth->rowCount()} Rows\n\n";
}
if (0) { // query for /dblp/article/i
	$t->start();
	$sth->execute(array("dblp.xml","/dblp/article/title/i"));
	echo "/dblp/article/i\n\tTime: {$t->stop()}.\n\tFetched {$sth->rowCount()} Rows\n\n";
}

if (0) { // query for /dblp//author
	$t->start();
	$sth->execute(array("dblp.xml","/dblp/%/author"));
	echo "/dblp//author\n\tTime: {$t->stop()}.\n\tFetched {$sth->rowCount()} Rows\n\n";
}

if (0) { // query for /dblp/inproceedings[@mdate=2006-11-09]
	$sql = "SELECT *
			FROM edge e
			JOIN path p USING (path_id)
			WHERE e.doc_id = (SELECT doc_id FROM docs WHERE document='dblp.xml')
			  AND p.path='/dblp/inproceedings/@mdate' AND e.value='2006-11-09'";

	$t->start();
	$sth = $dbh->query("$sql");
	echo "/dblp/inproceedings[@mdate=2006-11-09]\n\tTime: {$t->stop()}.\n\tFetched {$sth->rowCount()} Rows\n\n";
}

if (0) { // query for /dblp/inproceedings/author[text()='Jong-Deok Choi']
	$sql = "SELECT *
			FROM edge e
			JOIN path p USING (path_id)
			WHERE e.doc_id = (SELECT doc_id FROM docs WHERE document='dblp.xml')
			  AND p.path = '/dblp/inproceedings/author'
			  AND e.value = 'Jong-Deok Choi'";
			  
	$t->start();
	$sth = $dbh->query($sql);
	echo "/dblp/inproceedings/author[text()='Jong-Deok Choi']\n\tTime: {$t->stop()}.\n\tFetched {$sth->rowCount()} Rows\n\n";
}

if (1) { // query for /dblp/inproceedings[author[position()=3 and text()='Carlos A. Heuser]/author[1]
	$sql = "SELECT *
			FROM edge e
			JOIN path p USING (path_id)
			WHERE e.doc_id = (SELECT doc_id FROM docs WHERE document='dblp.xml')
			  AND p.path = '/dblp/inproceedings/author'
			  AND e.value = 'Carlos A. Heuser'
			  AND replace(concat_ws('.',d1,d2,d3,d4,d5,d6,d7),'.0','')  like '%.5.1'";
	$t->start();
	$sth = $dbh->query($sql);
	echo "/dblp/inproceedings[author[position()=3 and text()='Carlos A. Heuser]/author[1]\n";
	echo "\tTime: {$t->stop()}.\n\tFetched {$sth->rowCount()} Rows\n\n";
}

?>
