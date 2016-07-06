'use strict';

/* Controllers */

angular.module('myApp.controllers', [])
.controller( 'MyCtrl1', [ '$scope', '$http', function MyCtrl1 ( $scope, $http ) {
	window.scope = $scope;
	$scope.iedb_list = null;
	$scope.papers_list = null;
	$scope.current_pubmed_id = 'dd';

	$http.get('ajax/iedb_list.json', { cache: true})
	.then(function(result) {
		$scope.iedb_list = result.data;
	});

	$scope.change_papers = function(pubmed_id) {
		$scope.current_pubmed_id = pubmed_id;
//$scope.$apply()
		//alert(pubmed_id);
		//$scope.papers_list = $scope.iedb_list;
		/*var parameters = {};
		parameters['pubmed_id'] = pubmed_id;


		$http.post('ajax/get_papers.php', parameters)
		.then(function(result) {
			$scope.papers_list = result.data;
			$scope.$apply()
			$scope.alert = 1;
			console.log($scope.papers_list);
		});*/

	};

}])
.controller('MyCtrl2', [function() {

}]);
