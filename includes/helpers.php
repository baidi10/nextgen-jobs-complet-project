<?php
/**
 * Helper functions for the application
 */

require_once __DIR__ . '/config.php';

/**
 * Get theme colors for the application
 * @return array Array of theme colors
 */
function getThemeColors() {
    return Config::THEME_COLORS;
}
