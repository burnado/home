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
define( 'AUTH_KEY',         'S);ouI{+l]z(p_wz2nRR$j@%T`n{5&68rIZ!m*}I}N!x|/ iCU6)tH4=FXl0Wp{M' );
define( 'SECURE_AUTH_KEY',  'obc7r>r wABX4C<HZG}`=~~q(Ts 2 }HeE#Lp,Bs[!$XqCOJ,Mz(aYx^@M+T#Q3/' );
define( 'LOGGED_IN_KEY',    'w<$%erkCh)cxOH*t-_sY:ne_.H-^-?Hgo]G0%hX,L>4agsB:m[6b?h;6tH^<x-!i' );
define( 'NONCE_KEY',        'Pek.Z=v/_}8Q~N<|Z~* 1OjI$E6*%_GHFquO0!Kp+)T=:a$Adncv(|!?[s1&>`b8' );
define( 'AUTH_SALT',        '*b:C$ZdLBOpxE-X/euYhZ=L2WZijF;w)6=uU)(x0~A}rvx9?rQ>USL[?Nl@l4{Lq' );
define( 'SECURE_AUTH_SALT', '; iYirM4HU1~ZV+z5^fYRkY@6E[TJO58Aas0tWuM?0&kOTun;UYxvb=$ .baoOHt' );
define( 'LOGGED_IN_SALT',   '~5n/I_E#N!NV=I.Wl:-EcAl|q2vZO`cHe%r/^.?y578h^<3:Xlqh/KgQnr_qE#e^' );
define( 'NONCE_SALT',       ' SS/xKj]c_oGI|$Sp8x:8/^H(cvk~C23 }=Y_=)}8DfinY4J%HE/(U~{.aNl:|#H' );

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
