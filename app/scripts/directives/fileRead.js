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
            fileRead: "=",
            fileName: "="
        },
        link: function (scope, element) {
            element.bind("change", function (changeEvent) {
                var reader = new FileReader();
                reader.onload = function (loadEvent) {
                    scope.$apply(function () {
                        scope.fileRead = loadEvent.target.result;
                        scope.fileName = element[0].value.split(/(\\|\/)/g).pop();
                        console.log(scope.fileRead);
                    });
                };
                reader.readAsDataURL(changeEvent.target.files[0]);
            });
        }
    };
}]);