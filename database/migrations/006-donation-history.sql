-- Account ownership is assigned only from a verified server session.
-- Historical / guest donations remain unassigned; do not infer ownership by name.
ALTER TABLE donations ADD COLUMN IF NOT EXISTS user_id INT NULL;
ALTER TABLE donations ADD INDEX IF NOT EXISTS idx_donation_user_history (user_id, id);
