DROP DATABASE vortech;
CREATE USER 'test'@'localhost' IDENTIFIED BY 'test';
GRANT ALL ON *.* TO 'test'@'localhost';

CREATE DATABASE vortech;
USE vortech;

CREATE TABLE News (
    NewsID int AUTO_INCREMENT,
    Title varchar(255),
    Contents text,
    Author varchar(255),
    Created datetime,
    Updated datetime,
    PRIMARY KEY (NewsID)
);

CREATE TABLE Categories (
    CategoryID int AUTO_INCREMENT,
    Category varchar(255),
    PRIMARY KEY (CategoryID)
);

CREATE TABLE NewsCategories (
    NewsCategoryID int AUTO_INCREMENT,
    NewsID int,
    CategoryID int,
    PRIMARY KEY (NewsCategoryID),
    FOREIGN KEY (NewsID) REFERENCES News(NewsID)
);
