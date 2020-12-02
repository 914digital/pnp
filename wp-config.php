<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'local' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', 'root' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'bZj2GQ2y8AH0c9Ogk78qfC0YSG+Xawr3+1wEon/YciigoZdV/Kj439ITnLkRRzpgrHU4sZcMWldU1fVcjA6MkA==');
define('SECURE_AUTH_KEY',  '/QjREID1QLQglxggkybtIJ+opLdE6Y36wMxUk6iIJVerq27XVM9rdZsLdwaYZD1764KMyld/1tBFDEpIGy6SnA==');
define('LOGGED_IN_KEY',    'ET75R3r+i54bBzabVh3LKTsFTq8fj3/bIpzCvLnzdjmQXkkwfvCv69zzrgTsYIxjr65Z6q/YTWiThfVoEYmQwQ==');
define('NONCE_KEY',        'ohBV+SDHGeO1Twh9hORkz12myCwsAbdelmXTMcRpvJepMR0dxBlZ1Ic8W6kjC3oHmTwmmrCi3zfNBjjZTUdIyw==');
define('AUTH_SALT',        'dorSUeCKXZTejI0wF+eJFTzIy5y/Xb6tiaCIf9/URwm53Kw8ZPh2twahxaGR6YU2tM9X90FpEfw+Pe/3jDuQTQ==');
define('SECURE_AUTH_SALT', 'uCIAR1ihWNRlD8mzkLYMD+Vz7pXs0r9P71Av6G0ubhSXQzivOxji26VMtqroYuMroq/9mBFu4Rh0qbXD5anuiw==');
define('LOGGED_IN_SALT',   'zkLferPHSsO6lYvEJtbM8A9a0Od4lQddTDIypP+GYJRfsXiACaL49eM78hG8vjADVs6fY3guxtODkrffRwbc3w==');
define('NONCE_SALT',       'B0LzLahPgNy/O1N8uVgVlA2o768tUZcXZQYd7fMq4SjiFXggjDQiPNaojRF401HA7E3dNiPr9VkF90HR6VmlAw==');

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';




/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
