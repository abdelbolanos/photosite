'use strict';

/**
 * @ngdoc function
 * @name photositeApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the photositeApp
 */
angular.module('photositeApp')
  .controller('MainCtrl', ['$scope', '$log', 'apiService', function ($scope, $log, apiService) {
    
  	function getNewestOk (response) {
  		$scope.newest = response.data.newest;	
  	}

  	function getNewestFail (response) {
  		$log.debug(response);
  	}

    var promise = apiService.apiHttp('/api/photo/newest');
    promise.then(getNewestOk, getNewestFail);

  }]);
