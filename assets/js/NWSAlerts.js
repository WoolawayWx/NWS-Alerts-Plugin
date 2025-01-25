(function ($) {
    $(document).ready(function () {
        function fetchAndUpdateAlerts() {
            console.log('Plugin loaded with data:', nwsPluginData);

            // Parse the countyZones JSON string into an object
            var countyZonesObject = JSON.parse(nwsPluginData.countyZones);
            console.log('County Zones Object:', countyZonesObject);

            // Convert the object keys to an array
            var countyZonesArray = Object.keys(countyZonesObject);
            console.log('County Zones Array:', countyZonesArray);

            // Fetch colors data from colors.json
            var colorsUrl = nwsPluginData.pluginUrl + 'assets/json/colors.json';
            var colorsData = {};
            var customColors = JSON.parse(nwsPluginData.customColors || '{}');

            fetch(colorsUrl)
                .then(response => response.json())
                .then(data => {
                    colorsData = data;
                    console.log('Colors data:', colorsData);

                    // Fetch data from the NWS API for each county zone
                    var allAlerts = [];
                    var fetchPromises = countyZonesArray.map(function (zone) {
                        var apiUrl = `https://api.weather.gov/alerts/active/zone/${zone}`;
                        return fetch(apiUrl)
                            .then(response => response.json())
                            .then(data => {
                                console.log(`Data for zone ${zone}:`, data);
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
                                        <h3><strong>${alert.countyName}: </strong>${alert.properties.event} from ${new Date(alert.properties.effective).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })} till ${new Date(alert.properties.expires).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })} by ${alert.properties.senderName}</h3>
                                        <div class="additional-details" style="display: none;">
                                            <p>${alert.properties.description || 'No description available.'}</p>
                                            <p><strong>Effective:</strong> ${new Date(alert.properties.effective).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })}</p>
                                            <p><strong>Expires:</strong> ${new Date(alert.properties.expires).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })}</p>
                                            <p><strong>Instructions:</strong> ${alert.properties.instruction || 'No instructions available.'}</p>
                                            <p><strong>Severity:</strong> ${alert.properties.severity}</p>
                                            <p><strong>Certainty:</strong> ${alert.properties.certainty}</p>
                                            <p><strong>Urgency:</strong> ${alert.properties.urgency}</p>
                                        </div>
                                        
                                    </div>
                                `;
                                var buttonHtml = `<button class="toggle-details" style="white-space: nowrap;">Show Details</button>`;
                                container.html(alertHtml);

                                // Add click event to toggle additional details
                                $('.toggle-details').on('click', function () {
                                    var details = $(this).siblings('.additional-details');
                                    if (details.is(':visible')) {
                                        details.hide();
                                        $(this).text('Show Details');
                                        rotateInterval = setInterval(rotateAlerts, 10000); // Restart rotation
                                    } else {
                                        details.show();
                                        $(this).text('Hide Details');
                                        clearInterval(rotateInterval); // Stop rotation
                                    }
                                });
                            }

                            function rotateAlerts() {
                                showAlert(currentIndex);
                                currentIndex = (currentIndex + 1) % allAlerts.length;
                            }

                            // Show the first alert immediately
                            rotateAlerts();

                            // Rotate alerts every 10 seconds
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

        // Initial fetch and update
        fetchAndUpdateAlerts();

        // Update alerts every 5 minutes
        setInterval(fetchAndUpdateAlerts, 5 * 60 * 1000);
    });
})(jQuery);