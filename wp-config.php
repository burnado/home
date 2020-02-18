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
define( 'AUTH_KEY',         '=OT/IB6^?MDtM h`xIX/`%VArQj*H+EU#,&@)p?@=Z630p2zw*0LjdF#L`V> CS1' );
define( 'SECURE_AUTH_KEY',  '~6DjAGOIt n 07%hBDC0gjr]E[iF5eKS>Z&zJa!z~Gm&nY/}=)4ch@j%>~FGs`PT' );
define( 'LOGGED_IN_KEY',    '}_JU(Fs.aYr{U]ZxS)9{+C~HG56{<iJS-Vr*IwT,jg#~EkG-u8B4XzbH3vMF:}KZ' );
define( 'NONCE_KEY',        'tG8kle/~=>@hI<a/D^<.]N_SZ=$*//ZT9`]9LFznEsC`TaPr+$&H7.vsm#4y25jo' );
define( 'AUTH_SALT',        '! M.@9g@8*qLBQxljt9RPaHi3e1mLKx~V0q58wPy<=gBD&C=jzxXBYgQntC&fo0A' );
define( 'SECURE_AUTH_SALT', 'vKzVr)!S&BW,gqbxO0~bj6Ig5]2uPwg&lTt)tzCil:V<sq{E${dBq)=6$$x/Ub^7' );
define( 'LOGGED_IN_SALT',   '5kCun: <,*gT=VMI6wq1X9>mGvA,BT&JEr!C8tQYy/Y&_%KqNn-w(v2pKW^tWpo@' );
define( 'NONCE_SALT',       '*[F_869S4`LW4WH5K5}M/]aC(w Wl!rc2|iaa *}U-c(+33n:iIXX&MO1:`a6|^S' );

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
