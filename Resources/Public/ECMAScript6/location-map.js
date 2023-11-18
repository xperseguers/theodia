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

import 'leaflet';
import FormEngineValidation from '@typo3/backend/form-engine-validation.js';

/**
 * Module: @causal/theodia/location-map
 * @exports @causal/theodia/location-map
 */
class LocationMap {
    create(options) {
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

    initializeMap() {
        this.map = L.map('map').setView([this.options.latitude, this.options.longitude], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 19
        }).addTo(this.map);
    }

    initializeGeocoder() {

    }

    addMarker() {
        if (typeof this.marker !== 'undefined') {
            this.map.removeLayer(this.marker);
        }
        this.marker = L.marker([this.options.latitude, this.options.longitude], {draggable: true});
        this.marker.addTo(this.map);
        this.marker.on('moveend', this.updateCoordinateFields.bind(this))
    }

    triggerResizeOnActive() {
        setTimeout(function () {
            this.invalidateSize();
        }.bind(this.map), 10);
    }

    updateCoordinateFields(event) {
        const movedMarker = event.target,
            coordinates = movedMarker.getLatLng(),
            fieldPrefix = 'data[' + this.options.table + '][' + this.options.uid + ']',
            latitudeField = document.querySelector('*[data-formengine-input-name="' + fieldPrefix + '[' + this.options.fieldLatitude + ']"]'),
            longitudeField = document.querySelector('*[data-formengine-input-name="' + fieldPrefix + '[' + this.options.fieldLongitude + ']"]'),
            hiddenLatitudeField = document.querySelector('input[name="' + fieldPrefix + '[' + this.options.fieldLatitude + ']"]'),
            hiddenLongitudeField = document.querySelector('input[name="' + fieldPrefix + '[' + this.options.fieldLongitude + ']"]');
        latitudeField.value = coordinates.lat.toFixed(6);
        longitudeField.value = coordinates.lng.toFixed(6);
        hiddenLatitudeField.value = latitudeField.value
        hiddenLongitudeField.value = longitudeField.value
        FormEngineValidation.markFieldAsChanged(latitudeField);
        FormEngineValidation.markFieldAsChanged(longitudeField);
    }
}

export default new LocationMap();
