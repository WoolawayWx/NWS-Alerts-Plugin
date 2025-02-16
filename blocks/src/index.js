import { registerBlockType } from '@wordpress/blocks';
import { useEffect, useState } from '@wordpress/element';
import { RichText } from '@wordpress/block-editor';

// Register the Gutenberg block
registerBlockType('nws-alerts-plugin/nws-alerts-block', {
    title: 'NWS Alerts Block',
    icon: 'warning',
    category: 'widgets',
    attributes: {
        content: { type: 'string', source: 'html', selector: 'p' },
    },
    edit: ({ attributes, setAttributes }) => {
        return (
            <p style={{ padding: '10px', backgroundColor: '#ffeb3b', color: '#333' }}>
                NWS Alerts will be displayed here on the frontend.
            </p>
        );
    },
    save: () => {
        return <div id="nws-alerts-plugin-container"></div>;
    },
});

// Frontend Script (Enqueued Separately)
document.addEventListener("DOMContentLoaded", function () {
    (function ($) {
        function fetchAndUpdateAlerts() {
            var countyZonesObject = JSON.parse(nwsPluginData.countyZones);
            var countyZonesArray = Object.keys(countyZonesObject);
            var colorsUrl = nwsPluginData.pluginUrl + 'assets/json/colors.json';
            var colorsData = {};
            var customColors = JSON.parse(nwsPluginData.customColors || '{}');

            fetch(colorsUrl)
                .then(response => response.json())
                .then(data => {
                    colorsData = data;
                    var allAlerts = [];
                    var fetchPromises = countyZonesArray.map(zone => {
                        var apiUrl = `https://api.weather.gov/alerts/active/zone/${zone}`;
                        return fetch(apiUrl)
                            .then(response => response.json())
                            .then(data => {
                                if (data.features && data.features.length > 0) {
                                    data.features.forEach(alert => {
                                        alert.countyName = countyZonesObject[zone];
                                    });
                                    allAlerts = allAlerts.concat(data.features);
                                }
                            })
                            .catch(error => console.error(`Error fetching data for zone ${zone}:`, error));
                    });

                    Promise.all(fetchPromises).then(() => {
                        var container = $('#nws-alerts-plugin-container');
                        container.empty();

                        if (allAlerts.length > 0) {
                            var currentIndex = 0;

                            function showAlert(index) {
                                var alert = allAlerts[index];
                                var alertType = alert.properties.event;
                                var backgroundColor = customColors[alertType]?.background || colorsData[alertType]?.background || 'white';
                                var textColor = customColors[alertType]?.text || colorsData[alertType]?.text || 'black';

                                var alertHtml = `
                                    <div class="nws-alert" style="background-color: ${backgroundColor}; color: ${textColor}; padding: 10px; border-radius: 5px; margin-bottom: 10px;">
                                        <h3><strong>${alert.countyName}: </strong>${alert.properties.event}</h3>
                                        <p>From ${new Date(alert.properties.onset).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })} 
                                           till ${new Date(alert.properties.ends).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })} 
                                           by ${alert.properties.senderName}</p>
                                        <div class="additional-details" style="display: none;">
                                            <p>${alert.properties.description || 'No description available.'}</p>
                                            <p><strong>Severity:</strong> ${alert.properties.severity}</p>
                                            <p><strong>Certainty:</strong> ${alert.properties.certainty}</p>
                                            <p><strong>Urgency:</strong> ${alert.properties.urgency}</p>
                                        </div>
                                    </div>
                                `;
                                container.html(alertHtml);
                            }

                            function rotateAlerts() {
                                showAlert(currentIndex);
                                currentIndex = (currentIndex + 1) % allAlerts.length;
                            }
                            rotateAlerts();
                            setInterval(rotateAlerts, 10000);
                        }
                    });
                })
                .catch(error => console.error('Error fetching colors data:', error));
        }

        fetchAndUpdateAlerts();
        setInterval(fetchAndUpdateAlerts, 5 * 60 * 1000);
    })(jQuery);
});
