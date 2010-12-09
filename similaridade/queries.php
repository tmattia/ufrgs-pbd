<?php

/* gera os dados necessários para o calculo da curva de precisão/revocação.
** gera em um arquivo CSV (mas separado por tabulações) com a seguinte estrutura:
** NomeCerto, NomePesquisado, RegistrosPesquisados, RegistrosRelevantes, Porcentagem
** Sendo que registros relevantes são registros exatamente iguais ao NomeCerto
**
** O arquivo gerado pode ser aberto no excel
*/


error_reporting(E_ALL);

$dbh = new PDO("mysql:dbname=projbds","root","toor");
$sql = 
	"SELECT r.c, r.value
	FROM edge as r, qgramsaux as tq, qgrams as raq
	WHERE r.c = raq.c AND raq.gram = tq.gram AND
		  raq.p <= tq.p + 10 AND raq.p >= tq.p - 10 AND
		  LENGTH(r.value) <= LENGTH(:sigma) + 10 AND LENGTH(r.value) >= LENGTH(:sigma) - 10
	GROUP BY r.c, r.value
	HAVING COUNT(*) >= LENGTH(r.value) - 28 AND COUNT(*) >= LENGTH(:sigma) - 28
	ORDER BY COUNT(*) desc";
$sth = $dbh->prepare($sql);

$consultas = array(
	"HORATIO"=>array("ORACIO","HORACIO","OREITIO"),
	"MARCELLUS"=>array("MARCEL","MARCELO"),
	"ACT"=>array("ACT","ACTO","ATO"),
	"QUEEN GERTRUDE"=>array("GERTRUDES","GERTRUD","RAINHA GERTRUDES")
);

file_put_contents("results.tsv","NomeCerto\tNomePesquisado\tRegistrosPesquisados\tRegistrosRelevantes\tPorcentagem\n");

foreach ($consultas as $certo => $errados) {
	echo "{$certo} ===============================================\n";
	foreach ($errados as $errado) {
		$dbh->query("CALL ppl_qgramsaux('{$errado}')");
		echo "{$errado} ----\n";
		$sth->execute(array("sigma"=>$errado));
		$registros = $sth->fetchAll();
		$total = sizeof($registros);
		for ($i = 0; $i <= 1; $i += 0.1) {
			$pct = $els = floor($i * $total);
			echo "Recall {$pct}: ";
			$relevante = 0;
			while ($pct) {
				if ($registros[$pct]["value"] == $certo) $relevante++;
				$pct--;
			}
			file_put_contents("results.tsv","$certo\t$errado\t$els\t$relevante\t".round($relevante * 100 / $els,1)."\n",FILE_APPEND);
			echo "{$relevante}/{$els} (".round($relevante * 100 / $els,1)."%) relevantes\n";
		}
	}
}

?>
