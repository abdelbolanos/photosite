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
        });
    })
    .controller('adminListController', ['$scope', function ($scope) {
        $scope.awesomeThings = [
        'HTML5 Boilerplate',
        'AngularJS',
        'Karma'
        ];
    }]);