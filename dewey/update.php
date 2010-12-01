<?php
/*	# descobre o primeiro e o último registro
	SELECT * FROM edge WHERE path_id=12 AND doc_id=8 ORDER BY d2;
	# ultimo=1.10 // primeiro=1.6

	# move todos os valores 1 casa para baixo
	UPDATE edge SET d2=d2+1 WHERE d1=1 AND d2>5 AND doc_id=8 AND path_id=12;
	# ultimo=1.11

	# Insere os novos elementos copiados
	INSERT INTO edge 
	SELECT doc_id,path_id,d1,6 as d2,d3,d4,d5,d6,d7,value FROM edge WHERE d2=11 AND doc_id=8;
*/

$dbh = new PDO("mysql:dbname=projbd","root","toor");

# pega o doc_id
$doc = $dbh->query("SELECT doc_id FROM docs WHERE document='hamlet.xml'")->fetchColumn();

# pega o path_id
$path = $dbh->query("SELECT path_id FROM path WHERE doc_id={$doc} AND PATH like '/PLAY/ACT'")->fetchColumn();

// -------------------------------------------------

$time = microtime(true);

$i = 10;
while ($i--) {
	# descobre o primeiro e o último registro
	$r = $dbh->query("SELECT * FROM edge WHERE path_id={$path} AND doc_id={$doc} ORDER BY d2")->fetchAll();
	$primeiro = $r[0]["d2"];
	$ultimo = $r[(sizeof($r) - 1)]["d2"];

	# move os registros alvo para baixo 1 unidade
	$dbh->query("UPDATE edge SET d2=d2+1 WHERE doc_id={$doc} AND d2>={$primeiro}");
	$ultimo++;

	# copia as tretas
	$dbh->query("INSERT INTO edge (SELECT doc_id,path_id,d1,{$primeiro} as d2,d3,d4,d5,d6,d7,value FROM edge WHERE d2={$ultimo} AND doc_id={$doc})");
	
	if ($i == 9) echo "Tempo para inserção de 1: ".(microtime(true) - $time)."\n";
}
echo "Tempo para inserção de 10: ".(microtime(true) - $time)."\n";

$time = microtime(true);
$dbh->query("DELETE FROM edge WHERE doc_id={$doc} AND d2 BETWEEN {$primeiro} AND (".($primeiro + 10).")");
echo "Tempo para remoção dos 10 ultimos: ".(microtime(true) - $time)."\n";

?>
