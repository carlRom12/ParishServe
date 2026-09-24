<?php
require __DIR__ . '/../includes/request-types.php';
function expect_slot($condition, $message) { if (!$condition) throw new Exception($message); }
expect_slot(ps_bookings_overlap('wedding', [480,600], 'funeral', [660,750]), 'One-hour gap must conflict');
expect_slot(!ps_bookings_overlap('wedding', [480,600], 'funeral', [720,810]), 'Exact two-hour gap allowed');
expect_slot(ps_bookings_overlap('funeral', [660,750], 'wedding', [480,600]), 'Gap must work in reverse');
expect_slot(!ps_bookings_overlap('baptism', [480,540], 'baptism', [480,540]), 'Same group ceremony can be shared');
expect_slot(ps_bookings_overlap('baptism', [510,570], 'baptism', [480,540]), 'Different group starts conflict');
expect_slot(ps_bookings_overlap('wedding', [60,180], 'funeral', [-90,0]), 'Previous-day cleanup protected');
$slots=ps_service_slots('wedding', [['type'=>'regular_mass','window'=>[480,480]]]);
$byTime=array_column($slots,null,'value');
expect_slot(!$byTime['08:00']['available'], 'Mass start unavailable');
expect_slot(!$byTime['07:00']['available'], 'Service crossing Mass unavailable');
expect_slot(!isset($byTime['06:30']) && !isset($byTime['18:30']) && !isset($byTime['23:30']), 'Outside service start hours excluded');
expect_slot(array_key_first($byTime) === '07:00' && array_key_last($byTime) === '18:00', 'Start-time boundaries are inclusive');
expect_slot(!isset($byTime['08:17']), 'Arbitrary times not listed');
echo "Service slot and preparation-gap checks passed.\n";
