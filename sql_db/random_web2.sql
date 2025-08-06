use inventorydb;

-- Insert 10 random records into the orders table using only choclates1 and choclates2
INSERT INTO orders (nodatetime, item, quantity) VALUES
('2025-02-01 09:15:00', 'choclates1', 5.0),
('2025-02-02 11:30:00', 'choclates2', 7.5),
('2025-02-03 14:45:00', 'choclates1', 3.0),
('2025-02-04 08:20:00', 'choclates2', 9.5),
('2025-02-05 16:05:00', 'choclates1', 4.25),consumption
('2025-02-06 12:00:00', 'choclates2', 6.0),
('2025-02-07 10:10:00', 'choclates1', 8.75),
('2025-02-08 15:30:00', 'choclates2', 2.5),
('2025-02-09 13:45:00', 'choclates1', 10.0),
('2025-02-10 11:00:00', 'choclates2', 7.0);

-- Insert 10 random records into the temp_alerts table
INSERT INTO temp_alerts (datatime, temp_alert) VALUES
('2025-02-01 08:30:00', 'High temperature'),
('2025-02-02 09:45:00', 'Low temperature'),
('2025-02-03 10:15:00', 'Temperature spike'),
('2025-02-04 11:00:00', 'Cooling system failure'),
('2025-02-05 12:30:00', 'Temperature sensor error'),
('2025-02-06 07:45:00', 'Overheating detected'),
('2025-02-07 14:20:00', 'Under temperature warning'),
('2025-02-08 16:10:00', 'Temperature drop'),
('2025-02-09 10:05:00', 'Sudden temperature rise'),
('2025-02-10 13:15:00', 'Abnormal temperature reading');

-- Insert 10 random records into the security_alerts table
INSERT INTO security_alerts (datatime, security_alert) VALUES
('2025-02-01 07:00:00', 'Unauthorized access detected'),
('2025-02-02 08:10:00', 'Security breach attempt'),
('2025-02-03 09:20:00', 'Suspicious activity noted'),
('2025-02-04 10:30:00', 'System lockdown initiated'),
('2025-02-05 11:40:00', 'Alarm triggered'),
('2025-02-06 12:50:00', 'Door forced open'),
('2025-02-07 13:00:00', 'Camera malfunction'),
('2025-02-08 14:15:00', 'Intruder alert'),
('2025-02-09 15:25:00', 'Suspicious package found'),
('2025-02-10 16:35:00', 'Access control failure');

-- Insert 10 random records into the consumption table using only choclates1 and choclates2
INSERT INTO consumption (item, morning_weight, evening_weight, consumption, record_date) VALUES
('choclates1', 100.0, 90.0, 10.0, '2025-02-01 07:00:00'),
('choclates2', 150.0, 140.0, 10.0, '2025-02-02 07:30:00'),
('choclates1', 200.0, 180.0, 20.0, '2025-02-03 08:00:00'),
('choclates2', 120.0, 110.0, 10.0, '2025-02-04 08:30:00'),
('choclates1', 130.0, 125.0, 5.0, '2025-02-05 09:00:00'),
('choclates2', 180.0, 170.0, 10.0, '2025-02-06 09:30:00'),
('choclates1', 90.0, 85.0, 5.0, '2025-02-07 10:00:00'),
('choclates2', 160.0, 150.0, 10.0, '2025-02-08 10:30:00'),
('choclates1', 210.0, 205.0, 5.0, '2025-02-09 11:00:00'),
('choclates2', 95.0, 90.0, 5.0, '2025-02-10 11:30:00');
