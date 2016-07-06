<?php
#header('Content-type: text/json'); 
include "../../../inc.php";
set_time_limit(0);

$file = "iedb_list.json";
$iedb_list = file_get_contents($file);
$iedb_list = json_decode($iedb_list, true);

$list = array();
foreach($iedb_list as $v) {
	$list[$v['Pubmed ID']] = $v['3D Structure of Complex'];
}

function get_valid_papers() {
	global $db;
	$transition_sql = $db->select()
					->from('iedb_citations')
					->where("(abstract LIKE '%mutagenisis%' OR abstract LIKE '%mutation%' OR abstract LIKE '%mutate%' OR abstract LIKE '%alanine%') ")
					->order('pubmed_id', 'DESC');
	$citations = $transition_sql->query()->fetchall();
	return $citations;
}

$papers = get_valid_papers();

$valid_pdbs = array();
foreach($papers as $paper) {
	$valid_pdbs[$paper['pubmed_id']] = $list[$paper['pubmed_id']];
}
file_put_contents('valid_pdbs.json', json_encode($valid_pdbs));
echo json_encode($valid_pdbs);
?>