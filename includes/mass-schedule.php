<?php
// Regular parish Mass start times, supplied by the parish notice.
// Durations and sacrament booking windows must be confirmed separately.
function ps_regular_mass_times(string $date): array {
    $day = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
    if (!$day || $day->format('Y-m-d') !== $date) return [];
    return $day->format('w') === '0'
        ? ['05:00', '06:30', '08:00', '09:30', '11:00', '14:30', '16:00', '17:30', '19:00']
        : ['06:15', '17:30'];
}

function ps_regular_mass_events(string $from, string $to): array {
    $events = [];
    $end = new DateTimeImmutable($to);
    for ($day = new DateTimeImmutable($from); $day <= $end; $day = $day->modify('+1 day')) {
        $date = $day->format('Y-m-d');
        foreach (ps_regular_mass_times($date) as $time) {
            $events[] = [
                'date' => $date,
                'time' => DateTimeImmutable::createFromFormat('!H:i', $time)->format('g:i A'),
                'type' => 'regular_mass',
                'label' => $day->format('w') === '0' ? 'Sunday Mass' : 'Daily Mass',
                'category' => 'mass',
                'status' => 'scheduled',
                'statusLabel' => 'Regular parish schedule',
            ];
        }
    }
    return $events;
}
