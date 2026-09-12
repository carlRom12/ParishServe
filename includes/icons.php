<?php
/**
 * icons.php
 * ---------------------------------------------------------------------
 * ps_icon($name, $class) echoes one inline 24x24 stroke SVG. These are
 * the same icons the .html pages carry inline (their markup was
 * expanded from this helper), so the PHP admin pages and the static
 * pages look identical. Add new icons to $icons below using the same
 * stroke style; an unknown name renders nothing and logs a warning.
 * ---------------------------------------------------------------------
 */

function ps_icon($name, $class = '') {
    static $icons = [
        'arrow-right'    => '<path d="M5 12h14"/><path d="M13 6l6 6-6 6"/>',
        'bell'           => '<path d="M6 9a6 6 0 0 1 12 0c0 4 1.5 5.5 2 6.5H4c.5-1 2-2.5 2-6.5z"/><path d="M10 19a2 2 0 0 0 4 0"/>',
        'building'       => '<rect x="4" y="8" width="7" height="13"/><rect x="13" y="3" width="7" height="18"/><path d="M6.5 11h2M6.5 14h2M6.5 17h2M15.5 6h2M15.5 9h2M15.5 12h2M15.5 15h2"/>',
        'calendar'       => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 9h18"/><path d="M8 3v4"/><path d="M16 3v4"/>',
        'calendar-check' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 9h18"/><path d="M8 3v4"/><path d="M16 3v4"/><path d="M8.5 14.5l2 2 4.5-4.5"/>',
        'check-circle'   => '<circle cx="12" cy="12" r="9"/><path d="M8 12.5l2.5 2.5L16 9.5"/>',
        'chevron-down'   => '<path d="M6 9l6 6 6-6"/>',
        'church'         => '<path d="M12 2v3M10.5 4h3M4 21V11l8-6 8 6v10"/><path d="M4 21h16"/><path d="M9 21v-6h6v6"/><path d="M9 12h.01M15 12h.01"/>',
        'clock'          => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/>',
        'close'          => '<path d="M6 6l12 12"/><path d="M18 6L6 18"/>',
        'crest'          => '<path d="M12 2c1.6 1.6 3.5 2.2 5.5 2.2v6.3C17.5 15 15.3 19 12 21c-3.3-2-5.5-6-5.5-10.5V4.2C8.5 4.2 10.4 3.6 12 2z"/><path d="M12 8.5v6M9 11.5h6"/>',
        'cross'          => '<path d="M12 3v18"/><path d="M7 8h10"/>',
        'document'       => '<path d="M7 3h7l4 4v14a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z"/><path d="M14 3v4h4"/><path d="M9 13h6M9 16.5h6M9 9.5h3"/>',
        'droplet'        => '<path d="M12 3c3 4 6 8.2 6 11.5A6 6 0 0 1 6 14.5C6 11.2 9 7 12 3z"/>',
        'gear'           => '<circle cx="12" cy="12" r="3"/><path d="M12 3v2.2M12 18.8V21M21 12h-2.2M5.2 12H3M18.4 5.6l-1.5 1.5M7.1 16.9l-1.5 1.5M18.4 18.4l-1.5-1.5M7.1 7.1 5.6 5.6"/>',
        'heart'          => '<path d="M12 20s-7-4.4-9.5-9C1 8 2.5 4.5 6 4.5c2 0 3.5 1.2 4 2.5.5-1.3 2-2.5 4-2.5 3.5 0 5 3.5 3.5 6.5C19 15.6 12 20 12 20z"/>',
        'home'           => '<path d="M4 11.5 12 4l8 7.5"/><path d="M6 10v9a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1v-9"/><path d="M10 20v-6h4v6"/>',
        'info'           => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5.5"/><path d="M12 7.8h.01"/>',
        'logout'         => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/>',
        'megaphone'      => '<path d="M3 10v4h3l6 4V6L6 10H3z"/><path d="M14 9c1.2 1 1.2 5 0 6"/><path d="M17 7c2 2 2 8 0 10"/>',
        'people'         => '<circle cx="8.5" cy="9" r="3"/><circle cx="16" cy="10" r="2.5"/><path d="M3 20c0-3.3 2.5-6 5.5-6s5.5 2.7 5.5 6"/><path d="M14 15.2c2.4.3 4 2.3 4 4.8"/>',
        'photo'          => '<rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="8.5" cy="10" r="1.5"/><path d="M21 16l-5-5-8 8"/>',
        'ring'           => '<circle cx="12" cy="15" r="5"/><path d="M9.5 10 12 4l2.5 6"/>',
        'search'         => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="M20 20l-4.8-4.8"/>',
        'upload'         => '<path d="M7 17a4.5 4.5 0 0 1-1-8.9A5.5 5.5 0 0 1 16.5 8H17a4 4 0 0 1 1 7.9"/><path d="M12 12v7"/><path d="M9 15l3-3 3 3"/>',
    ];

    if (!isset($icons[$name])) {
        error_log('ps_icon: unknown icon "' . $name . '"');
        return;
    }

    $classAttr = $class !== '' ? ' class="' . htmlspecialchars($class) . '"' : '';
    echo '<svg' . $classAttr . ' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
        . $icons[$name] . '</svg>';
}
