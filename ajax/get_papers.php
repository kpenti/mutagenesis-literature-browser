<?php
#header('Content-type: text/json'); 
include "../../../inc.php";
set_time_limit(0);
/*
$file = "iedb_list.json";
$contents = file_get_contents($file);
$contents = json_decode($contents, true);
*/
function get_citations($pubmed_id) {
	global $db;
	$transition_sql = $db->select()
					->from(array('s' => 'iedb_citations'))
					->where("(abstract LIKE '%mutagenisis%' OR abstract LIKE '%mutation%' OR abstract LIKE '%mutate%' OR abstract LIKE '%alanine%') ")
					->where('pubmed_id = ?', $pubmed_id);
					#->limit(2);
					#->limitPage($page, 5);
	$citations =	$transition_sql->query()->fetchall();
	
	
	$citatation_list = array();
	foreach($citations as $citation) {
		$citatation_list[$citation['citation_id']] = get_journal_details($citation);
	}

	return $citatation_list;
}

function get_journal_details($citation) {
	$xml = new SimpleXMLElement($citation['xml']);	

	$json = json_encode($xml);
	$data = json_decode($json, TRUE);

	$ArticleIdList = $xml->PubmedArticle->PubmedData->ArticleIdList;
	
	$doi = '';
	foreach($ArticleIdList->ArticleId as $ArticleId) {
		if((string) $ArticleId['IdType'] == 'doi') {
			$doi = (string) $ArticleId;
		}
	}
#print_r($data);die();
	$date_submitted = $data['PubmedArticle']['MedlineCitation']['Article']['Journal']['JournalIssue']['PubDate'];
	$article_title = $data['PubmedArticle']['MedlineCitation']['Article']['ArticleTitle'];
	$journal = @$data['PubmedArticle']['Article']['Journal']['Title'];
	$abstract = @$data['PubmedArticle']['MedlineCitation']['Article']['Abstract']['AbstractText'];
	$author = @$data['PubmedArticle']['MedlineCitation']['Article']['AuthorList']['Author'][0]['LastName'] . ' ' .@$data['PubmedArticle']['MedlineCitation']['Article']['AuthorList']['Author'][0]['Initials'];
	$MeshHeadings = array();
	if(isset($data['PubmedArticle']['MedlineCitation']['MeshHeadingList']['MeshHeading']))
		$MeshHeadings = $data['PubmedArticle']['MedlineCitation']['MeshHeadingList']['MeshHeading'];
	$article_mesh = array();
	foreach($MeshHeadings as $MeshHeading) {
		$article_mesh[] = $MeshHeading['DescriptorName'];
	}
	if(is_array($abstract))
		$abstract = implode("\n\n", $abstract);
	$article_mesh = implode(", ", $article_mesh);

	$journal_details = array();
	$journal_details['journal'] = $journal;
	$journal_details['article_mesh'] = $article_mesh;
	$journal_details['date_submitted'] = implode(" ", $date_submitted);
	$journal_details['title'] = $article_title;
	$journal_details['abstract'] = $abstract;
	$journal_details['author'] = $author;
	#$journal_details['xml'] = $contents;
	if($doi != '') {
		$journal_details['doi'] = 'http://dx.doi.org/'.$doi;
	} else {
		$journal_details['doi'] = 'http://www.ncbi.nlm.nih.gov/pubmed/'.$citation['citation_id'];
	}

	$checks = array(
		'mutagenisis', 'mutation', 'mutate', 'alanine', 'x-ray', 'crystallography', 'kcal', 'energy', 'affinity', 'Kd', 'K(d)', 'kJ'
	);
	$matches = array();
	foreach($checks as $check) {
		if (stripos($abstract, $check) !== false || stripos($article_mesh, $check) !== false) {
			$matches[] = $check;
		}
	}

	$checks_abstract = array(
		'alanine', 'kcal', 'Kd', 'K(d)', 'kJ'
	);
	$matches_abstract = array();
	foreach($checks_abstract as $check) {
		if (stripos($abstract, $check) !== false || stripos($article_mesh, $check) !== false) {
			$matches_abstract[] = $check;
		}
	}

	$sentences = explode('. ', $abstract);
	$matched = array();
	foreach($matches_abstract as $matches_text) {
		foreach($sentences as $sentence){
			$offset = stripos($sentence, $matches_text);
			if($offset){ $matched[] = $sentence; }
		}
	}



	foreach($matched as $matched_text){
		$matched_text = trim($matched_text);
		$journal_details['abstract'] = str_replace($matched_text, "<b>$matched_text</b>", $journal_details['abstract']);
	}

	#$journal_details['sentences'] = $sentences;
	$journal_details['matches'] = implode(', ', $matches);
	return $journal_details;
}

#$page = $argv[1];
$data = file_get_contents("php://input");
$data = json_decode($data, true);
$pubmed_id = $data['pubmed_id'];
$citations = get_citations($pubmed_id);
echo json_encode($citations);

?>