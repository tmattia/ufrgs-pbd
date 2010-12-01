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

$dbh = new PDO("mysql:dbname=shaks","root","root");

# pega o doc_id
$doc = $dbh->query("SELECT doc_id FROM docs_global WHERE document='hamlet.xml'")->fetchColumn();

# pega o path_id
$path = $dbh->query("SELECT path_id FROM path_global WHERE doc_id={$doc} AND path = 'PLAY/ACT'")->fetchColumn();

// -------------------------------------------------

$time = microtime(true);
for($i = 0; $i < 10; $i++) {
    $els = $dbh->query("SELECT id, end_desc_id FROM edge_global WHERE path_id = {$path} AND doc_id = {$doc} ORDER BY id")->fetchAll();
    $primeiro = $els[0];
    $ultimo = $els[sizeof($els) - 1];

    if(!isset($copiar)) {
        $copiar = $dbh->query("SELECT * FROM edge_global WHERE doc_id = {$doc} AND id >= {$ultimo['id']} AND id <= {$ultimo['end_desc_id']}")->fetchAll();
        $diff = sizeof($copiar);
    }    
    $id = $primeiro['id'];

    # update id
    $dbh->query("UPDATE edge_global SET id = id + {$diff}, end_desc_id = end_desc_id + {$diff} WHERE doc_id = {$doc} AND id >= {$primeiro['id']} ORDER BY id DESC");

    # updare parent_id
    $dbh->query("UPDATE edge_global SET parent_id = parent_id + {$diff} WHERE doc_id = {$doc} AND parent_id >= {$primeiro['id']}");

    # insert copied elements
    $sql = "INSERT INTO edge_global (doc_id, id, parent_id, end_desc_id, path_id, value) VALUES";
    foreach($copiar as $el) {
        $parent_id = $el['parent_id'] - $diff;
        $end_desc_id = $el['end_desc_id'] - $diff;
        $sql .= " ({$doc}, {$id}, {$parent_id}, {$end_desc_id}, {$el['path_id']}, '{$el['value']}'),";
        $id++;
    }
    $sql = rtrim($sql, ',');
    $dbh->query($sql);
    if($i == 0) {
        print "\nTempo para inserção do primeiro: " . (microtime(true) - $time) . "\n";
    }
}
print "Tempo para inserção de 10: " . (microtime(true) - $time) . "\n";

$time = microtime(true);
$dbh->query("DELETE FROM edge_global WHERE doc_id = {$doc} AND id BETWEEN {$primeiro['id']} AND (".($primeiro['id'] + $diff * 10 - 1).")");
echo "Tempo para remoção dos 10 ultimos: ".(microtime(true) - $time)."\n";

?>
