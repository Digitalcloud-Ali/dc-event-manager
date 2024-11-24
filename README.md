# DC Event Manager

A comprehensive WordPress event management plugin with unique features like collaborative events, AR integration, and event gamification.

## Author

**Syed Ali**

- Website: [https://digitalcloud.no](https://digitalcloud.no)
- GitHub: [@Digitalcloud-Ali](https://github.com/Digitalcloud-Ali/)

## Features

- Event Creation & Management
- Event Calendar
- Event Pages
- Custom Shortcodes & Blocks
- Menu Links & Widgets
- Advanced Search & Filtering
- User Submissions (Front-End)
- Event Notifications & Reminders
- Mobile Responsiveness
- Collaborative Events
- Interactive Maps & Augmented Reality (AR)
- Event Networking Features
- Carbon Footprint Tracker
- Event Gamification
- Event Analytics: Track event views and attendance
- Enhanced Carbon Footprint Calculation: More accurate travel distance estimation
- Performance Optimization: Caching system for improved load times
- Event Analytics Dashboard: Visualize event views and attendance data
- REST API for external integrations
- Event Ticketing System

## Installation

1. Upload the `dc-event-manager` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the plugin settings in the WordPress admin area

## Usage

### Shortcodes

- `[dc_event_calendar]`: Displays an interactive event calendar
- `[dc_event_list]`: Shows a list of upcoming events
- `[dc_event_details id="123"]`: Displays details for a specific event
- `[dc_event_submission_form]`: Renders a front-end event submission form
- `[dc_event_ar_view id="123"]`: Shows an AR view button for a specific event
- `[dc_event_carbon_footprint id="123"]`: Displays the carbon footprint for an event
- `[dc_event_user_badges]`: Shows the current user's earned badges
- `[dc_event_tickets]`: Displays the ticket purchase form for an event

### Widgets

- Upcoming Events Widget: Displays a list of upcoming events in your sidebar or footer

### AR Integration

To use the AR features, you need to:
1. Enable AR integration in the plugin settings
2. Add an AR model URL when creating or editing an event
3. Use the `[dc_event_ar_view]` shortcode or AR view button on the event page

### Carbon Footprint Tracking

The plugin automatically calculates an estimated carbon footprint for each event based on:
- Number of attendees
- Event duration
- Estimated travel distances (placeholder implementation)

### Gamification

Users can earn badges for various actions:
- Attending events
- Organizing events
- Networking with other attendees
- Reducing their carbon footprint

## Analytics

The plugin now includes an analytics dashboard that provides visual representations of event data. To access the dashboard:

1. Go to the WordPress admin area
2. Navigate to DC Events > Analytics
3. View charts displaying event views and attendance data

## Performance Optimization

The plugin includes several performance optimization features:

### Caching

Frequently accessed data is cached to improve load times. The cache is automatically updated hourly via a WordPress cron job.

### Cron Jobs

A WordPress cron job runs hourly to update cached data, ensuring that the plugin's performance remains optimized even with large amounts of event data.

## Development

To set up the development environment:

1. Clone the repository
2. Install dependencies: `composer install` and `npm install`
3. Run tests: `phpunit`

### Logging

The plugin includes a logging system for better debugging and error tracking. Logs are stored in the `logs/event_manager.log` file within the plugin directory.

### Testing

To run the tests:

1. Set up a test environment for WordPress
2. Navigate to the WordPress root directory
3. Run `wp scaffold plugin-tests dc-event-manager`
4. Run `bash bin/install-wp-tests.sh wordpress_test root '' localhost latest`
5. Navigate to the plugin directory
6. Run `phpunit`

## Troubleshooting

If you encounter any issues with outdated data, you can manually trigger a cache update by deactivating and reactivating the plugin.

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

## License

[GPL-2.0+](http://www.gnu.org/licenses/gpl-2.0.txt)

## Configuration

### Google Maps API Key

To use the enhanced carbon footprint calculation feature, you need to set up a Google Maps API key:

1. Go to the [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable the Distance Matrix API
4. Create an API key
5. In the WordPress admin, go to DC Events > Settings and enter your API key in the "Google Maps API Key" field

## API Endpoints

The plugin provides the following REST API endpoints:

- `GET /wp-json/dc-event-manager/v1/events`: Retrieve all events
- `GET /wp-json/dc-event-manager/v1/event/{id}`: Retrieve a specific event by ID

## Event Ticketing

To use the event ticketing feature:

1. When creating or editing an event, set the ticket price and quantity in the "Event Tickets" meta box.
2. Use the `[dc_event_tickets]` shortcode on your event page to display the ticket purchase form.