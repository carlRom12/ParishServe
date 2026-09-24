<?php
require __DIR__ . '/../includes/request-types.php';
require __DIR__ . '/../includes/mass-schedule.php';
function check($condition) { if (!$condition) throw new Exception('Mass conflict check failed'); }
check(ps_bookings_overlap('wedding', [360,480], 'regular_mass', [375,375]));
check(ps_bookings_overlap('baptism', [375,435], 'regular_mass', [375,375]));
check(!ps_bookings_overlap('funeral', [285,375], 'regular_mass', [375,375]));
check(!ps_bookings_overlap('baptism', [600,660], 'regular_mass', [1050,1050]));
check(count(ps_regular_mass_times('2026-09-27')) === 9);
check(ps_regular_mass_times('2026-09-28') === ['06:15','17:30']);
echo "Mass start conflict checks passed.\n";
