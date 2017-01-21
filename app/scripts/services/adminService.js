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
    .factory('adminService', [ 
        'apiService', 
        function (apiService) {

            var listAdmin = function(page) {
                return apiService.apiHttp(
                    'admin/list',
                    {'page': page}
                );
            };

            var getPhotoAdmin = function(id) {
                return apiService.apiHttp(
                    'admin/photo/' + id
                );
            };

            var addPhoto = function(photoData) {
                return apiService.apiHttp(
                    'admin/add',
                    photoData,
                    'POST'
                );
            };

            return {
                'listAdmin': listAdmin,
                'getPhotoAdmin': getPhotoAdmin,
                'addPhoto': addPhoto
            };
        }
    ]);