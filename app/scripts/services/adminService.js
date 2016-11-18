'use strict';

/**
 * @ngdoc function
 * @name photositeApp.controller:adminListController
 * @description
 * # adminListController
 * Controller of the admin photositeApp
 */
angular
    .module('photositeApp')
    .factory('adminService', ['$rootScope, $q, $http', function ($rootScope, $q, $http) {

    	function listAdmin () {
    		$http({
    			method: 'GET',
    			url: 'admin/list'
    		});


    	}

    	return {
    		'listAdmin': listAdmin
    	};
    }]);