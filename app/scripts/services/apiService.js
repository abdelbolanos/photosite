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
    .factory('apiService', ['$httpParamSerializerJQLike', '$q', '$http', function ( $httpParamSerializerJQLike, $q, $http) {

        function apiHttp (url, params, method) {
            $http.defaults.useXDomain = true;

            // Use x-www-form-urlencoded Content-Type
            $http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';

            params = params || '';
            method = method || 'GET';
            var hostname = window.location.hostname;
            var port = 80;
            var protocol = 'http://';

            var configuration = {
                method: method,
                url: protocol + hostname + ':' + port + '/api/' + url,
            };

            if (method === 'GET') {
                configuration['params'] = params;
            } else if (method === 'POST') {
                configuration['data'] =  $httpParamSerializerJQLike(params);
            }

            return $http(configuration);
        }

        return {
            'apiHttp': apiHttp
        };
    }]);