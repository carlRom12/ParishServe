<?php
/**
 * icons.php
 * ---------------------------------------------------------------------
 * All the little line-icons used across the sidebar + dashboard cards,
 * in ONE place. We're not pulling in an icon font/library (the group
 * agreed: no Bootstrap, no external UI kits, keep it vanilla) so these
 * are hand-drawn inline SVGs instead. Doing it this way means:
 *   - no extra HTTP requests / CDN dependency for something this small
 *   - icon color just follows CSS `color` (stroke="currentColor"), so
 *     the gold-on-active / white-on-hover sidebar states work for free
 *
 * Usage:  <?php ps_icon('home'); ?>   inside any markup.
 * Add a new icon by adding a new key to $GLOBALS['PS_ICONS'] below --
 * paste in the <path>/<circle>/<rect> children only, NOT the outer
 * <svg> tag (ps_icon() wraps that part for you).
 * ---------------------------------------------------------------------
 */

$GLOBALS['PS_ICONS'] = [
    'home'        => '<path d="M4 11.5 12 4l8 7.5"/><path d="M6 10v9a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1v-9"/><path d="M10 20v-6h4v6"/>',
    'megaphone'   => '<path d="M3 10v4h3l6 4V6L6 10H3z"/><path d="M14 9c1.2 1 1.2 5 0 6"/><path d="M17 7c2 2 2 8 0 10"/>',
    'calendar'    => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 9h18"/><path d="M8 3v4"/><path d="M16 3v4"/>',
    'calendar-check' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 9h18"/><path d="M8 3v4"/><path d="M16 3v4"/><path d="M8.5 14.5l2 2 4.5-4.5"/>',
    'ring'        => '<circle cx="12" cy="15" r="5"/><path d="M9.5 10 12 4l2.5 6"/>',
    'droplet'     => '<path d="M12 3c3 4 6 8.2 6 11.5A6 6 0 0 1 6 14.5C6 11.2 9 7 12 3z"/>',
    'flame'       => '<path d="M12 21c3.3 0 6-2.4 6-6 0-3-2-5-3-7 .3 2-1 3-2 2-1-.8-1-2.4 0-4-3 2-5 5-5 8 0 .8.2 1.5.5 2C7.6 15 7 15.8 7 17c0 2.5 2.2 4 5 4z"/>',
    'cross'       => '<path d="M12 3v18"/><path d="M7 8h10"/>',
    'people'      => '<circle cx="8.5" cy="9" r="3"/><circle cx="16" cy="10" r="2.5"/><path d="M3 20c0-3.3 2.5-6 5.5-6s5.5 2.7 5.5 6"/><path d="M14 15.2c2.4.3 4 2.3 4 4.8"/>',
    'chalice'     => '<path d="M7 4h10"/><path d="M7 4c0 4.5 2 7 5 7s5-2.5 5-7"/><path d="M12 11v6"/><path d="M8 20h8"/><path d="M9.5 17h5l-.5 3h-4z"/>',
    'building'    => '<rect x="4" y="8" width="7" height="13"/><rect x="13" y="3" width="7" height="18"/><path d="M6.5 11h2M6.5 14h2M6.5 17h2M15.5 6h2M15.5 9h2M15.5 12h2M15.5 15h2"/>',
    'heart'       => '<path d="M12 20s-7-4.4-9.5-9C1 8 2.5 4.5 6 4.5c2 0 3.5 1.2 4 2.5.5-1.3 2-2.5 4-2.5 3.5 0 5 3.5 3.5 6.5C19 15.6 12 20 12 20z"/>',
    'user'        => '<circle cx="12" cy="8" r="3.5"/><path d="M5 20c0-3.9 3.1-7 7-7s7 3.1 7 7"/>',
    'gear'        => '<circle cx="12" cy="12" r="3"/><path d="M12 3v2.2M12 18.8V21M21 12h-2.2M5.2 12H3M18.4 5.6l-1.5 1.5M7.1 16.9l-1.5 1.5M18.4 18.4l-1.5-1.5M7.1 7.1 5.6 5.6"/>',
    'logout'      => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/>',
    'clock'       => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/>',
    'check-circle'=> '<circle cx="12" cy="12" r="9"/><path d="M8 12.5l2.5 2.5L16 9.5"/>',
    'document'    => '<path d="M7 3h7l4 4v14a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z"/><path d="M14 3v4h4"/><path d="M9 13h6M9 16.5h6M9 9.5h3"/>',
    'phone'       => '<path d="M5 4h3.5l1.3 4-2.1 1.6a12 12 0 0 0 5.7 5.7l1.6-2.1 4 1.3V18a2 2 0 0 1-2.2 2C9.6 19.4 4.6 14.4 3 7.2A2 2 0 0 1 5 4z"/>',
    'arrow-right' => '<path d="M5 12h14"/><path d="M13 6l6 6-6 6"/>',
    'crest'       => '<path d="M12 2c1.6 1.6 3.5 2.2 5.5 2.2v6.3C17.5 15 15.3 19 12 21c-3.3-2-5.5-6-5.5-10.5V4.2C8.5 4.2 10.4 3.6 12 2z"/><path d="M12 8.5v6M9 11.5h6"/>',
    'church'      => '<path d="M12 2v3M10.5 4h3M4 21V11l8-6 8 6v10"/><path d="M4 21h16"/><path d="M9 21v-6h6v6"/><path d="M9 12h.01M15 12h.01"/>',

    /* added for the Announcements page (topbar, filters, featured
       carousel, list rows, sidebar widgets) -- kept in this same file
       since every future page will want at least some of these too */
    'bell'         => '<path d="M6 9a6 6 0 0 1 12 0c0 4 1.5 5.5 2 6.5H4c.5-1 2-2.5 2-6.5z"/><path d="M10 19a2 2 0 0 0 4 0"/>',
    'star'         => '<path d="M12 3.5l2.6 5.6 6.1.7-4.5 4.2 1.2 6-5.4-3-5.4 3 1.2-6-4.5-4.2 6.1-.7z"/>',
    'search'       => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="M20 20l-4.8-4.8"/>',
    'filter'       => '<path d="M4 5h16M7 12h10M10 19h4"/>',
    'chevron-down' => '<path d="M6 9l6 6 6-6"/>',
    'chevron-right'=> '<path d="M9 6l6 6-6 6"/>',
    'arrow-left'   => '<path d="M19 12H5"/><path d="M11 18l-6-6 6-6"/>',
    'bookmark'     => '<path d="M7 4h10a1 1 0 0 1 1 1v15l-6-4-6 4V5a1 1 0 0 1 1-1z"/>',

    /* added for the Parish Calendar page */
    'info'         => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5.5"/><path d="M12 7.8h.01"/>',

    /* added for the Wedding page */
    'book'         => '<path d="M4 5c2-1.2 5-1.2 7 0v14c-2-1.2-5-1.2-7 0z"/><path d="M20 5c-2-1.2-5-1.2-7 0v14c2-1.2 5-1.2 7 0z"/>',
    'footprints'   => '<ellipse cx="8" cy="8" rx="3" ry="4"/><ellipse cx="16" cy="16" rx="3" ry="4"/><circle cx="8" cy="3" r="1.1"/><circle cx="16" cy="11" r="1.1"/>',
    'question'     => '<circle cx="12" cy="12" r="9"/><path d="M9.3 9.2a2.7 2.7 0 1 1 3.7 2.5c-.9.4-1 .9-1 1.7"/><path d="M12 17h.01"/>',
    'check'        => '<path d="M5 13l4 4L19 7"/>',

    /* added for the Wedding Guidelines page */
    'id-card'      => '<rect x="3" y="6" width="18" height="12" rx="2"/><circle cx="8.5" cy="12" r="2"/><path d="M13 10.3h5M13 13.3h5"/>',
    'photo'        => '<rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="11" r="2"/><path d="M21 16l-5-4-4 3-3-2-6 5"/>',
    'shield'       => '<path d="M12 3l7 3v6c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6z"/>',
    'headset'      => '<path d="M4 13a8 8 0 0 1 16 0"/><rect x="3" y="13" width="4" height="7" rx="1.5"/><rect x="17" y="13" width="4" height="7" rx="1.5"/><path d="M20 20a4 4 0 0 1-4 3h-2"/>',

    /* added for the Wedding Request Step 3 (Review & Send) page */
    'message'      => '<path d="M4 5h16a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H9l-5 4v-4H4a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1z"/>',
    'send'         => '<path d="M4 12 20 4l-6 16-3-7-7-3z"/>',
];

/**
 * Echoes one icon as an inline <svg>. $class lets the caller add
 * extra CSS classes (sizing is normally handled by the parent
 * .ps-nav-link svg / .stat-icon svg rules in CSS, so most calls just
 * pass the icon key and nothing else).
 */
function ps_icon($key, $class = '') {
    $paths = $GLOBALS['PS_ICONS'][$key] ?? '';
    $classAttr = $class ? ' class="' . htmlspecialchars($class) . '"' : '';
    // stroke="currentColor" here is load-bearing: it's what makes an
    // icon's color "just follow the parent's CSS `color`" as promised
    // above, WITHOUT every single call site needing its own `stroke:
    // currentColor` CSS rule to not render invisible. A plain HTML
    // attribute has the lowest possible CSS specificity, so any real
    // stroke rule elsewhere (e.g. `.ps-card-title svg { stroke:
    // var(--gold-dark); }`) still overrides this default exactly like
    // before -- this only fixes the selectors that had no color rule
    // at all and were rendering fully invisible (fill=none + stroke
    // unset = nothing drawn).
    echo '<svg' . $classAttr . ' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths . '</svg>';
}
