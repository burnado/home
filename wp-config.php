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
define( 'AUTH_KEY',         'g~3d[^-;/EO Y*2<!Gc@PuD5:[!ua:,&jwxh/6pehtovnqh~A0^df3)Bog)iqq2|' );
define( 'SECURE_AUTH_KEY',  '%:l6FNr_oA4Gka_fkI&vJ,YD6?(D rB91ofe5@<pIC5[q1R$Q@BkBzkj%S~ 3rYq' );
define( 'LOGGED_IN_KEY',    'O@;}RDC5XWNadmH J&$jmhe)iRiUZGa284+p}D>d:B# YOILSbg~>+bM`~|Bm)-V' );
define( 'NONCE_KEY',        '*5J%3f/JCb,>`6DcW2#MJ)Z_k]f^M&OKaE_]z;D7dmM|Vpp;>c+i.8$u;QzpKbsT' );
define( 'AUTH_SALT',        '&P?Z#5cdBhuRQmsTPJd#5[cvC]H}+^l.#4p67I}l?mEO6sBuKND+3(B`@c@h6~Y@' );
define( 'SECURE_AUTH_SALT', '6jGl,>wP@*EGqZ4H~G>;gHcz%UZCLWcd=qC5uIh7P!]1F0 /SHD%d]y!)Ofkkhdz' );
define( 'LOGGED_IN_SALT',   '2(R;y_M8 703kebk0YA*lf<x}8cW@Ub%P#989Xbmpn{a)<kzou<H0FJ@Lg(`Y7M|' );
define( 'NONCE_SALT',       'H_=9u1|9xW3QMm-lfHt.Oj(b77D(a@&F0a[@]c4Fv,ZZr]GOTS)hFl>-z(tL4eA-' );

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
