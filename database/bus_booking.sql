CREATE DATABASE bus_booking_db;

USE bus_booking_db;

CREATE TABLE buses(
bus_id INT AUTO_INCREMENT PRIMARY KEY,
bus_name VARCHAR(50),
source VARCHAR(50),
destination VARCHAR(50),
departure_time TIME,
arrival_time TIME,
total_seats INT,
price INT
);

CREATE TABLE bookings(
booking_id INT AUTO_INCREMENT PRIMARY KEY,
bus_id INT,
passenger_name VARCHAR(50),
seat_number INT,
FOREIGN KEY(bus_id) REFERENCES buses(bus_id)
);

INSERT INTO buses(bus_name,source,destination,departure_time,arrival_time,total_seats,price)
VALUES
('VRL Travels','Bangalore','Belgaum','08:00','16:00',40,800),
('KSRTC Express','Belgaum','Pune','09:00','14:00',35,600),
('SRS Travels','Bangalore','Mysore','07:00','10:00',40,300);