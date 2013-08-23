<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wordpress');

/** MySQL database username */
define('DB_USER', 'wordpress');

/** MySQL database password */
define('DB_PASSWORD', 'wp_pwd');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'ny|t0s3/G9$H7>JX=8ET6sC GjtR2%%JBJnenW~&)%//wAZ>~DnjI`;Zj}:Sy,[W');
define('SECURE_AUTH_KEY',  '|FrtJ*1S;;A+3v[@Q2H5HF,oQKOygY)O:5=#Ea7[p5^DbC9cnc2,>Isq/0&O,rnM');
define('LOGGED_IN_KEY',    ';2+5i#r&9-0lhVdm;6)N}:NGjR{@cVuotcVG[^HDGpix2Myi6z2FmMFMoDGJ*Z50');
define('NONCE_KEY',        'VIYtJyr0RPXg/jQfO-nV?buXqQ?]3`fMKZ,-Y(R1; :4wWnkI95qB|$^hAE%Q()E');
define('AUTH_SALT',        'dN8lyk1Jku?Q&6chl8ooBoSl/cYS&ehxz)t{abz1PV24[3;qTgsp#9|[:A,B )x}');
define('SECURE_AUTH_SALT', '{&^-6x%+^ld(e-P,MM3}+0Dn< 9.A2Vr@hw4_}A^&;(,}i![APK66q{JD95&E$3:');
define('LOGGED_IN_SALT',   'NQ(K 8 3r/cK{2X#tEL/nn`k>N`8uWC`+smY+kHstDZFVw[~LlU;2;(z+P8kpI#V');
define('NONCE_SALT',       '.mU/12@hLBuu; ,<No4l9[vd<vLl?nQYTu<~xj{QmXfs6DHu/j7(8&d|$6UI^db~');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', 'uk');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
