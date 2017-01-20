'use strict';

/**
 * @ngdoc function
 * @name photositeApp.controller:adminAddEditController
 * @description
 * # adminAddEditController
 * Controller of the admin photositeApp
 */
angular
    .module('photositeApp')
    .config(function ($routeProvider){
        $routeProvider
            .when('/admin/add', {
                templateUrl: 'scripts/controllers/admin/views/adminAddEdit.html',
                controller: 'adminAddEditController',
                resolve: {
                    'allCategories': function(apiService) {
                        return apiService.apiHttp('allCategories');
                    },
                    'action': function() {
                        return 'add';
                    },
                    'photoData' : function() {
                        return null;
                    }
                }
            })
            .when('/admin/edit/:id', {
                templateUrl: 'scripts/controllers/admin/views/adminAddEdit.html',
                controller: 'adminAddEditController',
                resolve: {
                    'allCategories': function(apiService) {
                        return apiService.apiHttp('allCategories');
                    },
                    'action': function() {
                        return 'edit';
                    },
                    'photoData' : function($route, adminService) {
                        return adminService.getPhotoAdmin($route.current.params.id);
                    }
                }
            });
    })
    .controller('adminAddEditController', [
        '$scope', '$log', '$routeParams', 'adminService', 'allCategories', 'action', 'photoData',
        function ($scope, $log, $routeParams, adminService, allCategories, action, photoData) {
        
            $scope.action = action;
            $scope.allCategories = allCategories.data.allCategories;

            if ($scope.action == 'edit') {
                $scope.photoData = photoData.data.photo;
            }

            $scope.add = function() {
                $log.debug($scope.photoData);
            }
    }]);
