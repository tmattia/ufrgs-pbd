<?php
ini_set('memory_limit', 1024*1024*1024);
error_reporting(E_ALL);

// uso php import.php arquivo.xml

// pequena consistência
if (!isset($argv[1])) die("Parâmetro faltando.\nUso: php ".$_SERVER["PHP_SELF"]." <arquivo.xml>\n");
if (!is_dir($argv[1])) die("Diretório \"$argv[1]\" não encontrado.\n");

// acesso ao banco de dados
$dbh = new PDO("mysql:dbname=shaks", "root", "root", array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));

#$dbh->query("set global max_allowed_packet=268435456");

$init = microtime(true);

foreach (glob($argv[1]."/*.xml") as $doc) {
    echo "Parsing document ".basename($doc)." (";
    
    // cria o parser e abre o arquivo
    $reader = new XMLReader();
    $reader->open($doc, null, XMLReader::SUBST_ENTITIES);

    // deleta do banco de dados caso já exista (propaga para as outras tabelas)
    $dbh->query("DELETE FROM docs_global WHERE document='".basename($doc)."'");
    
    // insere o documento no banco de dados
    $dbh->query("INSERT INTO docs_global SET document='".basename($doc)."'");
    $doc = $dbh->lastInsertId();
    echo $doc.")... ";
    
    $lv = $pathid = $id = 0;
    $path = $paths = $parent_id = $elements = $ins = $curr_el = array();
    
    $sqlx = '';
    $sql = "INSERT INTO edge_global (doc_id, id, parent_id, path_id, value, end_desc_id) VALUES";
    
    $parent_id[-1] = 0;
    while (@$reader->read()) {
        // para cada elemento (elemento ou texto) do documento...
        switch ($reader->nodeType) {
            case XMLReader::WHITESPACE: break;
            
            case XMLReader::SIGNIFICANT_WHITESPACE: break;
            
            case XMLReader::END_ELEMENT:
                $_id = $parent_id[$lv - 1];
                // terminando o documento, volta na árvore até a raiz
                while(!isset($elements[$_id])) {
                    $lv--;
                    $_id = $parent_id[$lv - 1];
                }
                #print 'fechando ' . $curr_el[sizeof($curr_el) - 1] . "\n";
                unset($curr_el[sizeof($curr_el) - 1]);
                
                
                $el = $elements[$_id];
                unset($elements[$_id]);
                
                $value = mysql_real_escape_string($el['value']);
                $sqlx .= " ({$el['doc_id']}, {$el['id']}, {$el['parent_id']}, {$el['path_id']}, '{$value}', {$el['end_desc_id']}),";
                if(strlen($sqlx) > 100 * 1024 * 1024) {
                    #fwrite($f, rtrim($sqlx, ','));
                    $dbh->query($sql . rtrim($sqlx, ','));
                    $sqlx = '';
                }
                unset($path[$lv], $parent_id[$lv]);
                
                $lv--;
                break;
            
            case XMLReader::ELEMENT:
                $curr_el[sizeof($curr_el)] = $id;
                #print 'abrindo ' . $curr_el[sizeof($curr_el) - 1] . "\n";
                
                $path[$lv] = $reader->name;
                $p = implode('/', $path);
                if(!isset($paths[$p])) {
                    $paths[$p] = $pathid++;
                    $dbh->query("INSERT INTO path_global SET doc_id = {$doc}, path_id = {$paths[$p]}, path = '{$p}'");
                }
                $parent_id[$lv] = $id;
                $elements[$id] = array(
                    'doc_id' => $doc,
                    'id' => $id,
                    'parent_id' => $lv > 0 ? $parent_id[$lv - 1] : 'null',
                    'path_id' => $paths[$p],
                    'value' => 'null',
                    'end_desc_id' => 'null'
                );
                if($lv > 0) {
                    $elements[$curr_el[sizeof($curr_el) - 2]]['end_desc_id'] = $id;
                }
                $id++;
                
                
                if ($reader->attributeCount) {
                    while ($reader->moveToNextAttribute()) {
                        $np = $p."/@".$reader->name;
                        if (!isset($paths[$np])) {
                            $paths[$np] = $pathid++;
                            $dbh->query("INSERT INTO path_global SET doc_id = {$doc}, path_id = {$paths[$np]}, path = '{$np}'");
                        }
                        $v = mysql_real_escape_string($reader->value);
                        $sqlx .= " ({$doc}, {$id}, null, {$paths[$np]}, '{$v}', null),";
                        $id++;
                    }
                }
                
                $lv++;
                break;
            
            case XMLReader::TEXT:
                if($elements[$curr_el[sizeof($curr_el) - 1]]['value'] != 'null') {
                    $elements[$curr_el[sizeof($curr_el) - 1]]['value'] .= mysql_real_escape_string($reader->value);
                } else {
                    $elements[$curr_el[sizeof($curr_el) - 1]]['value'] = mysql_real_escape_string($reader->value);
                }
                #print "lendo " . ($curr_el[sizeof($curr_el) - 1]) . "\n";
                break;
        }
    }
    
    if(strlen($sqlx) > 0) {
        $dbh->query($sql . rtrim($sqlx, ','));
        $sqlx = '';
    }
    
    echo " OK! [".(microtime(true) - $init)."]\n";
    
    $reader->close();
    
    #fclose($f);
}
echo "\nTempo Total: ".(microtime(true) - $init)."\n\n";
?>
