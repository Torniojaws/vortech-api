DROP DATABASE vortech;
CREATE DATABASE vortech;
USE vortech;

-- Testing account in local development
CREATE USER 'test'@'localhost' IDENTIFIED BY 'test';
GRANT ALL ON vortech.* TO 'test'@'localhost';

-------------------------------- NEWS

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
    CONSTRAINT fk_newsCategory FOREIGN KEY (NewsID)
        REFERENCES News(NewsID) ON DELETE CASCADE
);

CREATE TABLE NewsComments (
    CommentID int AUTO_INCREMENT,
    NewsID int,
    Contents varchar(500),
    AuthorID int,
    Created datetime,
    Updated datetime,
    PRIMARY KEY (CommentID),
    CONSTRAINT fk_newsComment FOREIGN KEY (NewsID)
        REFERENCES News(NewsID) ON DELETE CASCADE
);

-------------------------------- RELEASES



-- Setup some predefined values
INSERT INTO
    Categories(Category)
VALUES
    ("Studio"),
    ("Live"),
    ("Recording"),
    ("Rehearsal"),
    ("Event");
