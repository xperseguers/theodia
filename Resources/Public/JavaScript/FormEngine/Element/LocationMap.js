/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

define(['jquery', 'TYPO3/CMS/Theodia/Leaflet'],
    function ($, L) {
        'use strict';

        function LocationMap(options) {
            this.options = options || {};

            setTimeout(function () {
                this.initializeMap();
                this.initializeGeocoder();
                this.addMarker();
                this.triggerResizeOnActive();
                setTimeout(function () {
                    this.invalidateSize();
                }.bind(this.map), 10);
            }.bind(this), 500);
        }

        LocationMap.prototype.initializeMap = function () {
            this.map = L.map('map').setView([this.options.latitude, this.options.longitude], 15);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                maxZoom: 19
            }).addTo(this.map);
        };

        LocationMap.prototype.initializeGeocoder = function () {
            var that = this;
            $('#fetch-address').click(function (e) {
                e.preventDefault();

                const fieldPrefix = 'data[' + that.options.table + '][' + that.options.uid + ']';
                var address = '';
                for (var i = 0; i < that.options.addressFields.length; i++) {
                    const fieldName = fieldPrefix + '[' + that.options.addressFields[i] + ']';
                    var $field = $('*[data-formengine-input-name="' + fieldName + '"]');
                    if (!$field.val()) {
                        $field = $('*[name="' + fieldName + '"]');
                    }
                    address += $field.val().replace("\n", ' ') + ' ';
                }
                if (address.trim().length) {
                    $('#map-address').val(address.trim());
                    $('#geocode').trigger('click');
                }
            });
            $('#geocode').click(function (e) {
                e.preventDefault();

                var address = $('#map-address').val().trim();
                if (address) {
                    $.ajax({
                        url: 'https://nominatim.openstreetmap.org/search',
                        type: 'GET',
                        data: {
                            q: address,
                            limit: 1,
                            format: 'json'
                        }
                    }).done(function (data) {
                        if (data.length) {
                            //var bbox = data[0].boundingbox;
                            const latitude = data[0].lat;
                            const longitude = data[0].lon;

                            that.map.flyTo([latitude, longitude], 15);
                            that.options.latitude = latitude;
                            that.options.longitude = longitude;
                            that.addMarker();

                            that.updateCoordinateFields({target: that.marker});
                        }
                    });
                }
            });
        };

        LocationMap.prototype.addMarker = function () {
            if (typeof this.marker !== 'undefined') {
                this.map.removeLayer(this.marker);
            }
            this.marker = L.marker([this.options.latitude, this.options.longitude], {draggable: true});
            this.marker.addTo(this.map);
            this.marker.on('moveend', this.updateCoordinateFields.bind(this))
        };

        LocationMap.prototype.triggerResizeOnActive = function () {
            $('.t3js-tabmenu-item a').bind('click', function () {
                $('#' + $(this).attr('aria-controls')).trigger('cssActiveAdded');
            });

            $('#map').parents('.tab-pane').on('cssActiveAdded', function () {
                setTimeout(function () {
                    this.invalidateSize();
                }.bind(this.map), 10);
            }.bind(this));
        };

        LocationMap.prototype.updateCoordinateFields = function (event) {
            const movedMarker = event.target,
                  coordinates = movedMarker.getLatLng(),
                  fieldPrefix = 'data[' + this.options.table + '][' + this.options.uid + ']',
                  $latitudeField = $('*[data-formengine-input-name="' + fieldPrefix + '[' + this.options.fieldLatitude + ']"]'),
                  $longitudeField = $('*[data-formengine-input-name="' + fieldPrefix + '[' + this.options.fieldLongitude + ']"]');

            $latitudeField.val(coordinates.lat.toFixed(6)).trigger('change');
            $longitudeField.val(coordinates.lng.toFixed(6)).trigger('change');
        };

        return LocationMap;
    });