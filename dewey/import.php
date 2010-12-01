<?php

error_reporting(E_ALL);

if (!isset($argv[1])) die("Parâmetro faltando.\nUso: php ".$_SERVER["PHP_SELF"]." <arquivo.xml>\n");
if (!is_dir("../docs/".$argv[1])) die("Diretório \"$argv[1]\" não encontrado.\n");

include("pdowtimer.php");
$dbh = new PDOwTimer("mysql:dbname=projbds", "root", "toor", array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));

$reader = new XMLReader;

$init = microtime(true);
$docId = $dbh->query("SELECT doc_id FROM docs ORDER BY doc_id DESC")->fetchColumn();
$docId || $docId = 0;
$query = array();

function deweyGen($d,$limit = 7) {
	return array_merge($d,(sizeof($d) < $limit ? array_fill(0,($limit - sizeof($d)),'null') : array()));
}

foreach (glob("../docs/".$argv[1]."/*.xml") as $doc) {

	$docId++;
	echo "Parsing document ".basename($doc)." ({$docId})... ";
	
	$reader->open($doc, null, XMLReader::SUBST_ENTITIES);
	
	$query["docs"][] = "({$docId},'".basename($doc)."')";

	$lv = 0;
	$path = "";
	$dewey = $paths = array(); 
	
	while ($reader->read()) {
		
		// para cada elemento (elemento ou texto) do documento...
		switch ($reader->nodeType) {
	        case XMLReader::END_ELEMENT:
		        unset($dewey[$lv]);
		        $path = substr($path,0,strrpos($path,'/'));
				$lv--;
		        break;
			case XMLReader::ELEMENT:
		        $path .= "/".$reader->name;
				if (!isset($paths[$path])) {
					$paths[$path] = sizeof($paths);
					$query["path"][] = "({$docId},{$paths[$path]},'{$path}')";
				}
				@$dewey[$lv]++; $lv++;
				$query["edge"][] = "({$docId},{$paths[$path]},".implode(',', deweyGen($dewey)).",null)";
				
				// adiciona os atributos, caso existam
				if ($reader->attributeCount) {
					while ($reader->moveToNextAttribute()) {
					    $p = $path."/@".$reader->name;
						if (!isset($paths[$p])) {
							$paths[$p] = sizeof($paths);
							$query["path"][] = "({$docId},{$paths[$p]},'{$p}')";
						}
						@$dewey[$lv]++;
						$query["edge"][] = "({$docId},{$paths[$p]},".implode(',', deweyGen($dewey)).",\"".addslashes($reader->value)."\")";
					}
				}
				
				break;
		        // sem break. continua avaliando no bloco abaixo...
			case XMLReader::TEXT:
				@$dewey[$lv]++;
				$query["edge"][] = "({$docId},{$paths[$path]},".implode(',', deweyGen($dewey)).",\"".addslashes($reader->value)."\")";
		        break;
		    default: 
		    	break;
    	}
		if (sizeof(@$query["edge"]) > 40000) {
			$q = "";
			if (isset($query["docs"])) $q .= "INSERT INTO docs VALUES ".implode(",", $query["docs"]).";\n";
			if (isset($query["path"])) $q .= "INSERT INTO path VALUES ".implode(",", $query["path"]).";\n";
			$q .= "INSERT INTO edge VALUES ".implode(",",$query["edge"]).";";
			$dbh->query($q);
			$q = ""; $query = array();
        }
	}

	echo " OK! [".(microtime(true) - $init)."]\n";
	
}

if (isset($query["edge"])) {
	if (isset($query["docs"])) $q .= "INSERT INTO docs VALUES ".implode(",",$query["docs"]).";\n";
	if (isset($query["path"])) $q .= "INSERT INTO path VALUES ".implode(",",$query["path"]).";\n";
	$q .= "INSERT INTO edge VALUES ".implode(",",$query["edge"]).";";
	$dbh->query($q);
}
echo "\nTempo Total: ".(microtime(true) - $init)."\n";
echo "Interação com o SGBD: {$dbh->time}\n";

?>
