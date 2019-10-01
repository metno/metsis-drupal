// Example metsis.settings.php
<?php
/*
 * *******************************************************************
 * WARNING                                                           *
 *                                                                   *
 * Mistakes in this file WILL BREAK YOUR SITE.                       *
 *                                                                   *
 * *******************************************************************
 */
# METSIS global/shared settings
if (file_exists('sites/all/metsis-global-settings.php')) {
  include 'sites/all/metsis-global-settings.php';
}
/**
 * site-specific settings for metsis multi-sites.
 */
/**
 * This file is loaded after all other multisite global settings have been loaded
 * Place the follwing at the end of the site specific settings.php (after the globa/shared settings if block),
 * replaceing <mutisite site> with the name of the site.
 */
/**
 *
 * custom <multisite site> settings
 * if (file_exists('sites/<multisite site>.metsis.met.no/metsis.custom.settings/metsis.settings.php')) {
 * include 'sites/<multisite site>.metsis.met.no/metsis.custom.settings/metsis.settings.php';
 * }
 *
 */
global $metsis_conf;
