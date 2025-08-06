create database yearconsumption;

USE yearconsumption;

CREATE TABLE IF NOT EXISTS consumption_table (
    item VARCHAR(50),
    consumed_quantity INT,
    consumption_date DATE
);

-- Define the start and end dates for the year
SET @start_date = '2025-01-01';
SET @end_date = '2025-12-31';


-- Temporary table to store holidays and festival peak dates
CREATE TEMPORARY TABLE special_days (
    special_date DATE,
    peak_factor INT
);

-- Insert sample holidays, weekends, and festival days with peak factors (higher value means higher consumption)
INSERT INTO special_days (special_date, peak_factor) VALUES
    ('2025-01-01', 2),   -- New Year
    ('2025-03-29', 3),   -- Holi
    ('2025-04-13', 2),   -- Vishu
    ('2025-07-15', 3),   -- Eid
    ('2025-08-15', 2),   -- Independence Day
    ('2025-10-02', 2),   -- Gandhi Jayanti
    ('2025-11-01', 3),   -- Diwali
    ('2025-12-25', 3);   -- Christmas

-- Insert data for each day of the year
INSERT INTO consumption_table (item, consumed_quantity, consumption_date)
SELECT 
    items.item,
    ROUND(
        50 + 
        (CASE 
            WHEN WEEKDAY(dates.selected_date) IN (5,6) THEN 30  -- Increase consumption on weekends (Saturday, Sunday)
            WHEN s.peak_factor IS NOT NULL THEN 50 * s.peak_factor -- Increase consumption on special days
            ELSE 0 
        END) + 
        RAND() * 20  -- Add some randomness to consumption
    ) AS consumed_quantity,
    dates.selected_date
FROM 
    (SELECT @start_date + INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY AS selected_date
     FROM (SELECT 0 a UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 
           UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a
     CROSS JOIN (SELECT 0 a UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 
                 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) b
     CROSS JOIN (SELECT 0 a UNION SELECT 1 UNION SELECT 2) c
     WHERE @start_date + INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY <= @end_date
    ) AS dates
CROSS JOIN 
    (SELECT 'Potatoes' AS item UNION SELECT 'Chocolates' UNION SELECT 'Paneer') AS items
LEFT JOIN 
    special_days s ON dates.selected_date = s.special_date;

-- Drop the temporary table after use
DROP TEMPORARY TABLE special_days;

-- Verify the data
SELECT * FROM consumption_table ORDER BY consumption_date;
