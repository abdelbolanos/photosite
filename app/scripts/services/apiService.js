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
    .factory('apiService', ['$rootScope' , '$q', '$http', function ($rootScope, $q, $http) {

        function apiHttp (url, params, method) {
            params = params || '';
            method = method || 'GET';
            var hostname = window.location.hostname;
            var port = 80;
            var protocol = 'http://';

            var configuration = {
                method: method,
                url: protocol + hostname + ':' + port + url,
                params: params
            };

            return $http(configuration);
        }

        return {
            'apiHttp': apiHttp
        };
    }]);