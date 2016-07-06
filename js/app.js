var myApp = angular.module('myApp', ['ngSanitize']);

myApp.controller('MyController', ['$scope', '$http', '$sce', function MyController($scope, $http, $sce){
    window.scope = $scope;

	$scope.iedb_list = null;
	$scope.pubmed_list = null;
	$scope.papers_list = null;
	$scope.current_paper = null;
	$scope.current_citation = null;
	$scope.current_abstract = '';

	$http.get('ajax/valid_pdbs.json', { cache: true})
	.then(function(result) {
		$scope.iedb_list = result.data;

		$http.get('ajax/pubmed_list.json', { cache: true})
		.then(function(result) {
			$scope.pubmed_list = result.data;
			$scope.change_papers(_.first(_.keys($scope.iedb_list)));
		});

	});

	$scope.change_papers = function(pubmed_id) {
		$scope.current_pubmed_id = pubmed_id;
		var parameters = {};
		parameters['pubmed_id'] = pubmed_id;

		$scope.papers_list = $scope.pubmed_list[pubmed_id];
		$scope.show_paper(_.last(_.keys($scope.papers_list)));
		/*$http.post('ajax/get_papers.php', parameters)
		.then(function(result) {
			
		});*/

	};

	$scope.show_paper = function(citation_id) {
		$scope.current_citation = citation_id;
		$scope.current_paper = $scope.papers_list[citation_id];
		$scope.current_abstract = $sce.trustAsHtml($scope.current_paper.abstract);
	};
}]);