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
define( 'DB_NAME', 'home_db' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

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
define( 'AUTH_KEY',         '9yTGY0IMRFVEho$iyyFe4V+x;f.T$HO}sbz6T/?jj8FjI}ct!L.~q3l~a]#U$=3m' );
define( 'SECURE_AUTH_KEY',  '4h}a%ED+5$5(2CLm%1!;@4DNaHpe++y0U#k,+S>*SV 3eQQE+uJx#yL2{n.~QmFu' );
define( 'LOGGED_IN_KEY',    'cTX|xI1825j2V}8}J7/vjah<9VJop~Foz3yA06vZ-QCQk8$fOLX>4&KRovaI*8k0' );
define( 'NONCE_KEY',        '?!5T+Dm[?(P(D ;)i*zI&H@9X76Tu.`DL0caZGHW3G-qk;lXPOMVy!+6TxY)QnJS' );
define( 'AUTH_SALT',        '{<D07Ae(.@)6{eD0W5V1Y2o@?5_hsyCU|fpaud]5h fX%Io(l%aSO|,o;AK,Rj5^' );
define( 'SECURE_AUTH_SALT', 'O6SM,Fr|;+4)GzP6oV;W^XX`U7-/~8ZNaXJS2Ur9zs<JWlG}p7dvTc?=7cA&8Tv_' );
define( 'LOGGED_IN_SALT',   '.dbC;zyA.f0[3bWh=<M>SWHAww.2EWN`Lf Z*&IVZVJ#Oz,BLmUfMs|ui;!^dTY-' );
define( 'NONCE_SALT',       '(GU<u=+xZVDh$uX,]4f)RI]B!:C=~s^ Y^1>1>]tSRudlNdc!&?SQ{(w(&x5Iudp' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

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
