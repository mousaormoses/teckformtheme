<?php
define( 'WP_CACHE', true );

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'u867084844_T1mf1' );

/** Database username */
define( 'DB_USER', 'u867084844_iGK9D' );

/** Database password */
define( 'DB_PASSWORD', 'ikau863oD4' );

/** Database hostname */
define( 'DB_HOST', '127.0.0.1' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

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
define( 'AUTH_KEY',          '38LHN%=Gm6LR?A5,r$p%i$ZfgOwH.+Oi ;7kry#4$vV4IOnmC0!*g%WVd@ Wzi*l' );
define( 'SECURE_AUTH_KEY',   'O#u95u{Z74z;D G.xO.PKc+OoJq>Lb?-s(U]#lE^PC#3[._Gk+_$v]4ce`w@)Vlf' );
define( 'LOGGED_IN_KEY',     'G.<y%wHD<i;vz uqlpel Mgw?NVT;J~WGykqis]0}9+.S:PwvMt()qR2-Cf*Gm,{' );
define( 'NONCE_KEY',         './%f<x_r0abVeRDk.}Sr)1<{5V@buAxyOE|x&F7|e1xAEF%d.U-_E;FNv:4?ooL+' );
define( 'AUTH_SALT',         'eC0Ob[S84}jj J2sT/;UZwJUpA<PXcvjLn q=0)n&;C]p-{hHulOB58yH(%;5TfX' );
define( 'SECURE_AUTH_SALT',  '=0)%^V8a7*(pxB`:&2Q51P&R7`Q&9|hhZFwo?w6?UGYIvSE1=Z9|jDi$.;)L3D<U' );
define( 'LOGGED_IN_SALT',    'l#s[#haZ(^qVp WC@/_b}2ee:ivXHzPO:7eNXEu[Er56nh{)]8y .u(9T<3~~n|p' );
define( 'NONCE_SALT',        'd;3u/NZHdoJS8cqZ,?&td`s8!MNC5fGpB-<^PQRMC17;}-;=KE%EGZW=Ja!Sqo7p' );
define( 'WP_CACHE_KEY_SALT', 'THv6 P?h>v:LLS}g+8@QiUFxKR&o2W{B[1>5LWlR7;zWjAqa6=[|eJ90IixTMRE_' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'FS_METHOD', 'direct' );
define( 'COOKIEHASH', '0bca2c5f9b049fa3746733b0b9ce7443' );
define( 'WP_AUTO_UPDATE_CORE', 'minor' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
