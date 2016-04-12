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
define('WP_CACHE', true); //Added by WP-Cache Manager
define( 'WPCACHEHOME', '/sources/profile/wp-content/plugins/wp-super-cache/' ); //Added by WP-Cache Manager
define('DB_NAME', 'profile');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '123@cms');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

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
define('AUTH_KEY',         '2F2uk-_Lo|N7dw1Q+-yj;%z](]|<LL@D~|vi?%j<.>NRaUQ6h`K.OrHtC.d18v7%');
define('SECURE_AUTH_KEY',  'M+X{e[%KagG3*]r+nI#H3$Q~wZ19YcqfM4pBD)$Wq[LXJ(.|: h%ma{biod,1y;>');
define('LOGGED_IN_KEY',    '$LT4hG1IJ~sXo9k68mX+|eVnyJ;|!MS`DO+^S~M+bw MJ3X~1l:t$=JyNkEE8}>1');
define('NONCE_KEY',        '5[>Vw7Q/X%@w#r?k,.<pytI%4dQdtB6tt-;LNF?J`Jh(/BXKk$W9cfP-Sb6TmZSn');
define('AUTH_SALT',        '8AF8Qm-9rgl-f>A^*qo+qC-m?QhMa_vD~c(dPZIdCD+- %Y@oAm*PshjaC 2x#jd');
define('SECURE_AUTH_SALT', 'pFIBfEsn;7iqT]eA1[5ov2zf-;J,;6FxI/Oo$<bF-j+fN2/N|<!1zkj}NLBD#Ke:');
define('LOGGED_IN_SALT',   'r.y79?[,+-/Qz-vDH%L+EQIp>d9d6G*VT<gS?`K([`CgYmb`K2SMhgqH`-s>}O1j');
define('NONCE_SALT',       'HiL%jag<`S).D$gl8{Jj%vuzQ-2px-Lm@cFiCG9yVhQO)EhkL9+$&1eW@n,%Y$+e');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'profile_';

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
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
