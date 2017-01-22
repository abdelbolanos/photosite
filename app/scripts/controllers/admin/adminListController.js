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
    .config(function ($routeProvider){
        $routeProvider
            .when('/admin', {
                redirectTo: '/admin/list',
            })
            .when('/admin/list', {
                templateUrl: 'scripts/controllers/admin/views/adminList.html',
                controller: 'adminListController',
                resolve: {
                    'listData' : function(adminService) {
                        return adminService.listAdmin();
                    }
                }
            })
            .when('/admin/list/:page', {
                templateUrl: 'scripts/controllers/admin/views/adminList.html',
                controller: 'adminListController',
                resolve: {
                    'listData' : function($route, adminService) {
                        return adminService.listAdmin($route.current.params.page);
                    }
                }
            });
    })
    .controller('adminListController', [
        '$scope', '$log', '$routeParams', 'adminService', 'listData',
        function ($scope, $log, $routeParams, adminService, listData) {
        
            var range = function(start, end) {
                var input = [];
                var min = parseInt(start);
                var max = parseInt(end);
                for (var i=min; i<=max; i++){
                  input.push(i);
                }
                return input;
            }

            $scope.random = Math.random();

            $scope.delete = function(id) {
                adminService.deletePhoto(id).then(
                    function (response) {
                        adminService.listAdmin($scope.page).then(
                            function(listData) {
                                $scope.photoList = listData.data.photos;
                                $scope.page = listData.data.page;
                                $scope.total = listData.data.total;
                                $scope.totalPages = listData.data.totalPages;
                                $scope.pages = range(1, listData.data.totalPages);
                            }
                        );

                    },
                    function (error) {

                    }
                );
            }

            $scope.photoList = listData.data.photos;
            $scope.page = listData.data.page;
            $scope.total = listData.data.total;
            $scope.totalPages = listData.data.totalPages;
            $scope.pages = range(1, listData.data.totalPages);

    }]);
