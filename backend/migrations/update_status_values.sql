-- Migration Script: Update Issue Status Values
-- This script updates all 'open' status values to 'pending_review'
-- Run this migration to align database with new status naming

USE civic_connect;

-- First, check current status distribution
SELECT status, COUNT(*) as count 
FROM issues 
GROUP BY status;

-- Step 1: Modify the ENUM column to include new status values
-- This adds 'pending_review' to the allowed values
ALTER TABLE issues 
MODIFY COLUMN status ENUM('open', 'in_progress', 'resolved', 'closed', 'pending_review') DEFAULT 'pending_review';

-- Step 2: Update 'open' to 'pending_review'
UPDATE issues 
SET status = 'pending_review' 
WHERE status = 'open';

-- Step 3: If you previously had 'closed' status, update to 'resolved'
UPDATE issues 
SET status = 'resolved' 
WHERE status = 'closed';

-- Step 4: Remove old values from ENUM (optional, for cleanup)
-- This removes 'open' and 'closed' from the ENUM definition
ALTER TABLE issues 
MODIFY COLUMN status ENUM('pending_review', 'in_progress', 'resolved') DEFAULT 'pending_review';

-- Verify the update
SELECT status, COUNT(*) as count 
FROM issues 
GROUP BY status;

-- Expected result: Only 'pending_review', 'in_progress', and 'resolved'
