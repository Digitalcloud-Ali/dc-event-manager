# DC Event Manager User Guide

## Table of Contents
1. [Installation](#installation)
2. [Basic Usage](#basic-usage)
3. [Features](#features)
   - [Event Creation](#event-creation)
   - [Event Calendar](#event-calendar)
   - [AR Integration](#ar-integration)
   - [Carbon Footprint Tracking](#carbon-footprint-tracking)
   - [Gamification](#gamification)
   - [Analytics](#analytics)
   - [Ticketing System](#ticketing-system)
   - [Recurring Events](#recurring-events)
   - [Check-in System](#check-in-system)
   - [Google Maps Integration](#google-maps-integration)
   - [Event Export](#event-export)
4. [Shortcodes](#shortcodes)
5. [Widgets](#widgets)
6. [API](#api)
7. [Troubleshooting](#troubleshooting)

## Installation

1. Upload the `dc-event-manager` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to DC Events > Settings to configure the plugin

## Basic Usage

After installation, you can start creating events by going to DC Events > Add New in your WordPress admin panel.

## Features

### Event Creation

1. Go to DC Events > Add New
2. Fill in the event details, including title, description, date, time, and location
3. Set the event category and tags
4. Add an AR model URL if you want to use AR features
5. Set ticket price and quantity if using the ticketing system
6. Use the map to set the exact location of your event
7. Publish the event

### Google Maps Integration

1. Go to DC Events > Settings and enter your Google Maps API key
2. When creating or editing an event, use the map interface to set the exact location
3. The map will automatically appear on the event page using the `[dc_event_map]` shortcode

### Event Export

1. On the event details page, users will see options to export the event to Google Calendar or iCal
2. Clicking these options will either open Google Calendar or download an .ics file

### Event Calendar

Use the `[dc_event_calendar]` shortcode to display an interactive calendar of your events on any page or post.

### AR Integration

1. Enable AR integration in DC Events > Settings > Advanced Features
2. When creating or editing an event, add an AR model URL
3. Use the `[dc_event_ar_view]` shortcode on your event page to display the AR view button

### Carbon Footprint Tracking

1. Enable carbon tracking in DC Events > Settings > Advanced Features
2. The plugin will automatically calculate the carbon footprint for each event
3. View the carbon footprint on the event page or use the `[dc_event_carbon_footprint]` shortcode

### Gamification

1. Enable gamification in DC Events > Settings > Advanced Features
2. Users will automatically earn badges for various actions (attending events, organizing events, etc.)
3. Use the `[dc_event_user_badges]` shortcode to display a user's earned badges

### Analytics

1. Go to DC Events > Analytics to view event performance data
2. Use the export button to download analytics data as a CSV file

### Ticketing System

1. When creating or editing an event, set the ticket price and quantity in the "Event Tickets" meta box
2. Use the `[dc_event_tickets]` shortcode on your event page to display the ticket purchase form

### Recurring Events

1. Enable recurring events in DC Events > Settings > Advanced Features
2. When creating or editing an event, set the recurrence pattern (daily, weekly, monthly, etc.)
3. The plugin will automatically create recurring events based on the pattern

### Check-in System

1. Enable the check-in system in DC Events > Settings > Advanced Features
2. Organizers can generate QR codes for each event
3. Attendees can scan the QR code to check-in at the event

## Shortcodes

- `[dc_event_calendar]`: Displays an interactive event calendar
- `[dc_event_list]`: Shows a list of upcoming events
- `[dc_event_details id="123"]`: Displays details for a specific event
- `[dc_event_submission_form]`: Renders a front-end event submission form
- `[dc_event_ar_view id="123"]`: Shows an AR view button for a specific event
- `[dc_event_carbon_footprint id="123"]`: Displays the carbon footprint for an event
- `[dc_event_user_badges]`: Shows the current user's earned badges
- `[dc_event_tickets id="123"]`: Displays the ticket purchase form for an event
- `[dc_event_map id="123"]`: Displays a Google Map for the event location

## Widgets

- Upcoming Events Widget: Displays a list of upcoming events in your sidebar or footer

## API

The plugin provides a REST API for external integrations. Available endpoints:

- `GET /wp-json/dc-event-manager/v1/events`: Retrieve all events
- `GET /wp-json/dc-event-manager/v1/event/{id}`: Retrieve a specific event by ID
- `POST /wp-json/dc-event-manager/v1/event`: Create a new event
- `PUT /wp-json/dc-event-manager/v1/event/{id}`: Update an existing event
- `DELETE /wp-json/dc-event-manager/v1/event/{id}`: Delete an event

## Troubleshooting

- If Google Maps features are not working, ensure you have entered a valid API key in the plugin settings
- For performance issues, try clearing the plugin's cache in DC Events > Settings > Performance
- If you encounter any errors, check the error log in DC Events > Settings > Logs
- If analytics data seems outdated, you can manually trigger a cache update by deactivating and reactivating the plugin

For more detailed information on specific features, please refer to the individual documentation files in the `docs` folder.