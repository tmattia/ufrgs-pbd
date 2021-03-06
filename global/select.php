<?php

class Timer {
	function start() { $this->time = microtime(true); }
	function stop() { return microtime(true) - $this->time; }
}
$t = new Timer;

// acesso ao banco de dados
$dbh = new PDO("mysql:dbname=dblp", "root", "root", array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));

$sql = "SELECT *
		FROM edge_global e
		JOIN path_global p USING (path_id)
		WHERE e.doc_id = (SELECT doc_id FROM docs_global WHERE document=?)
		  AND p.path like ?";

$sth = $dbh->prepare($sql);

if (0) { // query for /dblp
	$t->start();
	$sth->execute(array("dblp.xml","dblp"));
	echo "/dblp\n\tTime: {$t->stop()}.\n\tFetched {$sth->rowCount()} Rows\n\n";
}
if (0) {
	// query for /dblp/incollection
	$t->start();
	$sth->execute(array("dblp.xml","dblp/incollection"));
	echo "/dblp/incollection\n\tTime: {$t->stop()}.\n\tFetched {$sth->rowCount()} Rows\n\n";
}
if (0) { // query for /dblp/article/i
	$t->start();
	$sth->execute(array("dblp.xml","dblp/article/title/i"));
	echo "/dblp/article/i\n\tTime: {$t->stop()}.\n\tFetched {$sth->rowCount()} Rows\n\n";
}

if (0) { // query for /dblp//author
	$t->start();
	$sth->execute(array("dblp.xml","dblp/%/author"));
	echo "/dblp//author\n\tTime: {$t->stop()}.\n\tFetched {$sth->rowCount()} Rows\n\n";
}

if (0) { // query for /dblp/inproceedings/author[text()='Jong-Deok Choi']
	$sql = "SELECT *
			FROM edge_global e
			JOIN path_global p USING (path_id)
			WHERE e.doc_id = (SELECT doc_id FROM docs_global WHERE document='dblp.xml')
			  AND p.path = 'dblp/inproceedings/author'
			  AND e.value = 'Jong-Deok Choi'";

	$t->start();
	$sth = $dbh->query($sql);
	echo "/dblp/inproceedings/author[text()='Jong-Deok Choi']\n\tTime: {$t->stop()}.\n\tFetched {$sth->rowCount()} Rows\n\n";
}

if (0) { // query for /dblp/inproceedings[@mdate=2006-11-09]
	$sql = "SELECT *
			FROM edge_global e
			JOIN path_global p USING (path_id)
			WHERE e.doc_id = (SELECT doc_id FROM docs_global WHERE document='dblp.xml')
			  AND p.path='dblp/inproceedings/@mdate' AND e.value='2006-11-09'";

	$t->start();
	$sth = $dbh->query("$sql");
	echo "/dblp/inproceedings[@mdate=2006-11-09]\n\tTime: {$t->stop()}.\n\tFetched {$sth->rowCount()} Rows\n\n";
}

if (0) { // query for /dblp/inproceedings[author[position()=3 and text()='Carlos A. Heuser]/author[1]
    $sql = "SELECT e1.*
            FROM edge_global AS e1, (
                  SELECT DISTINCT e.parent_id, e.path_id
                  FROM edge_global e
                  JOIN path_global p USING (path_id)
                  WHERE e.doc_id = (SELECT doc_id FROM docs_global WHERE document='dblp.xml')
                    AND p.path = 'dblp/inproceedings/author'
                    AND e.value = 'Carlos A. Heuser'
                ) AS e2
            WHERE e1.doc_id = (SELECT doc_id FROM docs_global WHERE document='dblp.xml')
              AND e1.parent_id = e2.parent_id
              AND e1.path_id = e2.path_id
    ";

	$t->start();
	$sth = $dbh->query($sql);
	$results = $sth->fetchAll();
	$counts = array();
	$ids = array();
	$selected = array();
	foreach($results as $r) {
	    if(!isset($counts[$r['parent_id']]))
	        $counts[$r['parent_id']] = 0;
	    $counts[$r['parent_id']]++;
	    $ids[$r['parent_id']][] = $r['id'];
	    if($counts[$r['parent_id']] == 3 && $r['value'] == 'Carlos A. Heuser') {
	        $selected[] = $ids[$r['parent_id']][2];
	    }
	}
	$total = count($selected);
	echo "/dblp/inproceedings[author[position()=3 and text()='Carlos A. Heuser]/author[1]\n";
	echo "\tTime: {$t->stop()}.\n\tFetched {$total} Rows\n\n";
}

?>
