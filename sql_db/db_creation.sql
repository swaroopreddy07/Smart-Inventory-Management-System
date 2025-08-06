-- Create the database (if it doesn't exist)
CREATE DATABASE IF NOT EXISTS inventorydb;

USE inventorydb;

-- Create the orders table
CREATE TABLE orders (
    sr INT AUTO_INCREMENT PRIMARY KEY,  
    nodatetime DATETIME DEFAULT CURRENT_TIMESTAMP,  
    item VARCHAR(255) NOT NULL,  
    quantity FLOAT NOT NULL  
);

-- Create the temp_alerts table
CREATE TABLE temp_alerts (
    serial_no INT AUTO_INCREMENT PRIMARY KEY,  
    datatime DATETIME DEFAULT CURRENT_TIMESTAMP,  
    temp_alert VARCHAR(255) NOT NULL  
);

-- Create the security_alerts table
CREATE TABLE security_alerts (
    serial_no INT AUTO_INCREMENT PRIMARY KEY,  
    datatime DATETIME DEFAULT CURRENT_TIMESTAMP,  
    security_alert VARCHAR(255) NOT NULL  
);

-- Create the consumption table
CREATE TABLE consumption (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item VARCHAR(255) NOT NULL,
    morning_weight FLOAT NOT NULL,
    evening_weight FLOAT NOT NULL,
    consumption FLOAT NOT NULL,
    record_date DATETIME DEFAULT CURRENT_TIMESTAMP
);
