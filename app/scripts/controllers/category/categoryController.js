'use strict';

/**
 * @ngdoc function
 * @name photositeApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the photositeApp
 */
angular.module('photositeApp')
    .config(function ($routeProvider){
        $routeProvider
            .when('/category/:category', {
                templateUrl: 'scripts/controllers/category/views/categoryList.html',
                controller: 'categoryController',
        });
    })
  .controller( 
    'categoryController', 
    ['$scope', '$log', '$route', '$q', 'apiService', 
    function ($scope, $log, $route, $q, apiService) {
        var category = $route.current.params.category;

        if (category) {
            function getPhotosCategoryOk (response) {
                var responseCategory = response[0];
                var responseLastThree = response[1];

                $scope.category = responseCategory.data.category;
                $scope.photos = responseCategory.data.photos;
                $scope.newest = responseLastThree.data.newest;
            }

            function getPhotosCategoryFail (responseCategory, responseLastThree) {
                $log.debug(responseCategory);
                $log.debug(responseLastThree);
            }

            var promises = [
                apiService.apiHttp('/api/category/' + category),
                apiService.apiHttp('/api/photo/newest')
            ];

            var allPromises = $q.all(promises);
            allPromises.then(getPhotosCategoryOk, getPhotosCategoryFail);
        }

  }]);
