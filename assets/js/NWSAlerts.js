(function ($) {
    $(document).ready(function () {
        function fetchAndUpdateAlerts() {
            var countyZonesObject = JSON.parse(nwsPluginData.countyZones);
            console.log('County Zones Object:', countyZonesObject);
            var countyZonesArray = Object.keys(countyZonesObject);
            var colorsUrl = nwsPluginData.pluginUrl + 'assets/json/colors.json';
            var colorsData = {};
            var customColors = JSON.parse(nwsPluginData.customColors || '{}');

            fetch(colorsUrl)
                .then(response => response.json())
                .then(data => {
                    colorsData = data;

                    // Fetch data from the NWS API for each county zone
                    var allAlerts = [];
                    var fetchPromises = countyZonesArray.map(function (zone) {
                        var apiUrl = `https://api.weather.gov/alerts/active/zone/${zone}`;
                        return fetch(apiUrl)
                            .then(response => response.json())
                            .then(data => {
                                if (data.features && data.features.length > 0) {
                                    data.features.forEach(function (alert) {
                                        alert.countyName = countyZonesObject[zone];
                                    });
                                    allAlerts = allAlerts.concat(data.features);
                                }
                            })
                            .catch(error => {
                                console.error(`Error fetching data for zone ${zone}:`, error);
                            });
                    });

                    Promise.all(fetchPromises).then(function () {
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
                                        <h3><strong>${alert.countyName}: </strong>${alert.properties.event} from ${new Date(alert.properties.onset).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })} till ${new Date(alert.properties.ends).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })} by ${alert.properties.senderName}</h3>
                                        <div class="additional-details" style="display: none;">
                                            <p>${alert.properties.description || 'No description available.'}</p>
                                            <p><strong>Effective:</strong> ${new Date(alert.properties.onset).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })}</p>
                                            <p><strong>Expires:</strong> ${new Date(alert.properties.ends).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })}</p>
                                            <p><strong>Instructions:</strong> ${alert.properties.instruction || 'No instructions available.'}</p>
                                            <p><strong>Severity:</strong> ${alert.properties.severity}</p>
                                            <p><strong>Certainty:</strong> ${alert.properties.certainty}</p>
                                            <p><strong>Urgency:</strong> ${alert.properties.urgency}</p>
                                        </div>
                                        
                                    </div>
                                `;
                                var buttonHtml = `<button class="toggle-details" style="white-space: nowrap;">Show Details</button>`; // Button that toggles additional details, but doesn't format well so is not used.
                                container.html(alertHtml);

                                $('.toggle-details').on('click', function () { // Script to show and hide additional details when button is clicked.
                                    var details = $(this).siblings('.additional-details');
                                    if (details.is(':visible')) {
                                        details.hide();
                                        $(this).text('Show Details');
                                        rotateInterval = setInterval(rotateAlerts, 10000);
                                    } else {
                                        details.show();
                                        $(this).text('Hide Details');
                                        clearInterval(rotateInterval);
                                    }
                                });
                            }

                            function rotateAlerts() {
                                showAlert(currentIndex);
                                currentIndex = (currentIndex + 1) % allAlerts.length;
                            }
                            rotateAlerts();

                            var rotateInterval = setInterval(rotateAlerts, 10000);
                        } else {
                            $('#nws-alerts-plugin-container').empty();
                        }
                    });
                })
                .catch(error => {
                    console.error('Error fetching colors data:', error);
                    $('#nws-alerts-plugin-container').empty();
                });
        }
        fetchAndUpdateAlerts();

        // Update alerts every 5 minutes
        setInterval(fetchAndUpdateAlerts, 5 * 60 * 1000);
    });
})(jQuery);