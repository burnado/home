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
define( 'DB_NAME', 'epiz_25207785_w984' );

/** MySQL database username */
define( 'DB_USER', '25207785_1' );

/** MySQL database password */
define( 'DB_PASSWORD', '[t72m(GS1p' );

/** MySQL hostname */
define( 'DB_HOST', 'sql302.byetcluster.com' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '33pyexlpiy6eoktzumbsooztxeor1uksft7h9kwls0xlxlp4aeb5rer55xhnjx8c' );
define( 'SECURE_AUTH_KEY',  '6wd2am95fd5uhbk7aocie4bnuutj3t8oqh5smnxseohen8cxwjuy2lx3zfxq6eql' );
define( 'LOGGED_IN_KEY',    'ilwygandxj6gznnfy6ptcyf3gil7v1n0taqitc7ne7pvch3gojq4fwvvmvvcxk0j' );
define( 'NONCE_KEY',        'kuzam835hcosfhsexg24wrmexaxpywok75feeqziqnebq87afauycwyshjsvetkm' );
define( 'AUTH_SALT',        'zpppgtdh2utbjzkuwvhpvnlzpoitrnf5msawn7vrc4owkehbs5yxx5s2ovxauq4b' );
define( 'SECURE_AUTH_SALT', 'bqtphjzplx4xx5nlfq5ng4odxtgjiowrmbsdykmneydu1lb4h0kt2tsmmmppycxt' );
define( 'LOGGED_IN_SALT',   'edmsrowohvld5qcjzhbkagj1ljzwrkgb8ofnji9ksekot18odnbdol0qe2hpxene' );
define( 'NONCE_SALT',       '7edqz5xs02gcbnxhzxm9ijgcswwjlef4ctxhqageahu68p581emdeu1ht0z2cxpa' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wpjx_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
