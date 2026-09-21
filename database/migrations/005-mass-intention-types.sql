-- Retain historical types so existing requests remain readable.
ALTER TABLE mass_intentions MODIFY intention_type
    ENUM('For the Deceased', 'For the Living', 'Thanksgiving', 'Milestones & Celebrations', 'Special Intention', 'Thanksgiving Mass', 'Petition Mass', 'All Souls', 'For the Souls of') NOT NULL;
-- Calculated offerings and named-soul counts use the existing details JSON column.
