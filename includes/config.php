<?php
/**
 * Configuration file for DC Event Manager plugin.
 *
 * @package DC_Event_Manager
 */

// Plugin version
define('DC_EVENT_MANAGER_VERSION', '1.0.0');

// Plugin directory
define('DC_EVENT_MANAGER_PLUGIN_DIR', plugin_dir_path(dirname(__FILE__)));

// Plugin URL
define('DC_EVENT_MANAGER_PLUGIN_URL', plugin_dir_url(dirname(__FILE__)));

// Cache expiration time (in seconds)
define('DC_EVENT_MANAGER_CACHE_EXPIRATION', 3600);

// Maximum number of events to display in widgets
define('DC_EVENT_MANAGER_MAX_WIDGET_EVENTS', 5);

// Default carbon footprint calculation method
define('DC_EVENT_MANAGER_DEFAULT_CARBON_CALCULATION', 'basic');