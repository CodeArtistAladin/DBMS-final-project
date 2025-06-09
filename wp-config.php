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
define( 'DB_NAME', 'wordpress' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

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
define( 'AUTH_KEY',         'P&NH{+hM)/%}q1NUp8$8+jmmUhAKV*J=Bw/{XnRHpkX`MpD#iEp,DSy_iv2xT&|p' );
define( 'SECURE_AUTH_KEY',  '|i@GQLu9:BmQ~1oFz5t%Hl,_@)`S`{<4[/{j,oKan4c;hh?BkI$*xPiECH2m3 Z?' );
define( 'LOGGED_IN_KEY',    'qG g@}]q`hwqlO!?DNY8R8:h#h#IAtT&:eleR1/~fiqjb{M-,<dnS!wDjL~@eyPJ' );
define( 'NONCE_KEY',        '+q?!eq^f4%I=h)rPYyoZYU4:HJXVaXN!W6>rliU3H[hw(e+NHhjsCn1x=}jY5S$=' );
define( 'AUTH_SALT',        '%09{yo7 k#}5.g2mWSr:d{[y=drD{}n&3cdsD]wd*!NWe^J(kR!z0dV%a{tXiTpa' );
define( 'SECURE_AUTH_SALT', 'rm?[EO^AyDK77;}]2nE!0V#npcfCTVRvz<k*>Hi(`k?$?MH|O}E=Qc&/;/w`J_3I' );
define( 'LOGGED_IN_SALT',   'Ha_L}&>VU,^ws<3}_x>FvfZNH3Y<}oxB7@M$MMlN}8[Sti#Z:~-wdtky=+eN+qh5' );
define( 'NONCE_SALT',       ':3j9pXWRjp2t|rsl@,VEZ?pvF7dO3M]D?,{I)KYS/d9+-&A&0&>-3s@YiNDUneMt' );

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
define('WP_MEMORY_LIMIT', '768M');


/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';


