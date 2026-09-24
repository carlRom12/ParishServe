-- Facility reservations are not a parish service; the type is gone from
-- includes/request-types.php. 001/002/004 still create and alter the
-- table, so this must stay last among them when re-running in order.
DROP TABLE IF EXISTS facility_reservations;
DELETE FROM request_documents WHERE request_type = 'facility';
