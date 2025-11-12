<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'site1' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

define('DISABLE_WP_CRON', true);
define( 'YOAST_SEO_ENHANCED_REMOVAL', true );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'Q{%ujnUUjLD3Q2]ZoaV1kq/}>viD E]IRMt UQw-C.~mB@u,c.(Fryu?cC>lbTxP' );
define( 'SECURE_AUTH_KEY',  'i]8`<z}%GVKPa|?hv{nG%tp=7hRBiG~w?K:a9M7FYd7Y,Awud_>~1A;r}Z|v4eQ|' );
define( 'LOGGED_IN_KEY',    'yoMh,,n&3dI`*,#yKpZfMWN4>l6+qnSR5C9MK+vhU(cvt%0gOb$=q>5r(b7a@=R9' );
define( 'NONCE_KEY',        '+sc4aN5]B,58V}nw!m+jYp#reK+ On|idoZ2aqUflbzJ;`CFgL_DK+`@1YI)L8xH' );
define( 'AUTH_SALT',        '(~WG);J5c!pQzC;-h+t#!id7?*Gss,bZ~|~kDuUaqs+cV7tV}$VB(T6(T<-; cw2' );
define( 'SECURE_AUTH_SALT', 'zI ^E:R!{X fz+he}ko+V[m|:a:#!2p&I8~v=g@L> pYh_sgi1jNd2Ea5QyR&-sD' );
define( 'LOGGED_IN_SALT',   'p@@k>^imUv$s{Wc]+|<qyj2v#2J>Wm^d:,rP&*A&:-+JEYXM{H;eT?TVNprxWvwI' );
define( 'NONCE_SALT',       'K/ubvALs9ee<=LmY-_d[N<AlTfDWGh@:]eiW`D!>-AQ@)ZHf&q2_h;]o1ls{ZB_w' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
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
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
