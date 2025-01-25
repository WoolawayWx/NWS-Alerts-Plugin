![Title cover of the ticker in work](/supportingAssets/Images/EX3.png)
# NWS Alerts Plugin

This WordPress plugin provides real-time weather alerts from the National Weather Service (NWS) in the form of a colored ticker bar. Uses javascript to fetch the data using the [National Weather Service API](https://www.weather.gov/documentation/services-web-api), free of charge, but due to the amount of use, the request are limited. 

## Release
#### Current Version: *v0.1.0.0 - Pre-Release*
#### [View Releases](https://github.com/WoolawayWx/NWS-Alerts-Plugin/releases)
The plan is to get versions into WordPress Plugins Page, but taking it one step at a time. Pre-release versions will only be available on the GitHub. Only larger, stable releases will be submitted to WP.
## Features

- County Based NWS Alerts
- Works with Multiple Counties
- Easy integration with existing WordPress sites

## Installation

To install the plugin, follow these steps:

1. Download the plugin zip file.
2. In your WordPress admin dashboard, go to Plugins > Add New.
3. Click on "Upload Plugin" and choose the downloaded zip file.
4. Click "Install Now" and then "Activate" the plugin.

## Usage

Here is an example of how to use the plugin:

1. Install the plugin with the steps above.
2. Located the `NWS Alert Settings` in the WP-Admin menu.
3. Add your counties using the County Zone ID value. To find your county zone ID, use this [NWS Alerts Page](https://alerts.weather.gov/). The format will follow `SSCNNN`, where `SS` is the state abbrivation, `C` identifies the zone as a county, and `NNN` is the county number.
    - Ex: Barry County, Missouri: `MOC009`. ![Example of the colors and counties set up.](/supportingAssets/Images/ex2.png)
4. You can add as many counties as you would like. The plugin will display each alert for 10 seconds before rotating.
5. You can also edit the colors of the alerts, however, it is recommend to keep them the default as they match the [NWS Alert Colors](https://www.weather.gov/help-map/)
6. Make sure to hit save at the bottom of the settings page. 
7. Add a shortcode field into the location where you want it, and use `[nws_alerts_plugin]` to implement the ticker. Fonts *should* update with the sites theme. ![Image showing the shortcode.](/supportingAssets/Images/image.png)
8. Save your page.  

The plugin will now fetch and display real-time weather alerts on your site. If the site is static, like in a police or fire station, it will get updated info **every five (5) minutes** in order to keep a light load on servers. 

## Configuration

You can customize the plugin with the following options:

- `Zones`: List of counties that you'd like to have monitored.

## Contributing

Contributions are welcome! Please submit a pull request or open an issue to discuss your ideas.

## License

This project is licensed under the GNU GENERAL PUBLIC LICENSE.
