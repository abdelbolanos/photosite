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
    .directive("fileRead", [function () {
    return {
        scope: {
            fileRead: "="
        },
        link: function (scope, element, attributes) {
            element.bind("change", function (changeEvent) {
                var reader = new FileReader();
                reader.onload = function (loadEvent) {
                    scope.$apply(function () {
                        scope.fileRead = loadEvent.target.result;
                    });
                }
                reader.readAsDataURL(changeEvent.target.files[0]);
            });
        }
    }
}]);