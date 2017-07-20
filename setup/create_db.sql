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

CREATE TABLE Releases (
    ReleaseID int AUTO_INCREMENT,
    Title varchar(200),
    Date datetime,
    Artist varchar(200),
    Credits text,
    Created datetime,
    Updated datetime,
    PRIMARY KEY (ReleaseID)
);

CREATE TABLE Formats (
    FormatID int AUTO_INCREMENT,
    Title varchar(255),
    PRIMARY KEY (FormatID)
);

CREATE TABLE ReleaseFormats (
    ReleaseFormatID int AUTO_INCREMENT,
    FormatID int,
    ReleaseID int,
    PRIMARY KEY (ReleaseFormatID),
    CONSTRAINT fk_formats FOREIGN KEY (FormatID)
        REFERENCES Formats(FormatID) ON DELETE CASCADE,
    CONSTRAINT fk_releaseFormats FOREIGN KEY (ReleaseID)
        REFERENCES Releases(ReleaseID) ON DELETE CASCADE
);

CREATE TABLE ReleaseTypes (
    ReleaseTypeID int AUTO_INCREMENT,
    Type varchar(200),
    PRIMARY KEY (ReleaseTypeID)
);

CREATE TABLE ReleaseCategories (
    ReleaseCategoryID int AUTO_INCREMENT,
    ReleaseID int,
    ReleaseTypeID int,
    PRIMARY KEY (ReleaseCategoryID),
    CONSTRAINT fk_releaseReference FOREIGN KEY (ReleaseID)
        REFERENCES Releases(ReleaseID) ON DELETE CASCADE,
    CONSTRAINT fk_releaseCategory FOREIGN KEY (ReleaseTypeID)
        REFERENCES ReleaseTypes(ReleaseTypeID) ON DELETE CASCADE
);

-------------------------------- PEOPLE

CREATE TABLE People (
    PersonID int AUTO_INCREMENT,
    Name varchar(300),
    PRIMARY KEY (PersonID)
);

CREATE TABLE ReleasePeople (
    ReleasePeopleID int AUTO_INCREMENT,
    ReleaseID int,
    PersonID int,
    Instruments varchar(500),
    PRIMARY KEY (ReleasePeopleID),
    CONSTRAINT fk_release FOREIGN KEY (ReleaseID)
        REFERENCES Releases(ReleaseID) ON DELETE CASCADE,
    CONSTRAINT fk_releasePeople FOREIGN KEY (PersonID)
        REFERENCES People(PersonID) ON DELETE CASCADE
);

-------------------------------- SONGS

CREATE TABLE Songs (
    SongID int AUTO_INCREMENT,
    Title varchar(255),
    Duration int,
    PRIMARY KEY (SongID)
);

CREATE TABLE ReleaseSongs (
    ReleaseSongID int AUTO_INCREMENT,
    ReleaseID int,
    SongID int,
    PRIMARY KEY (ReleaseSongID),
    CONSTRAINT fk_songRelease FOREIGN KEY (ReleaseID)
        REFERENCES Releases(ReleaseID) ON DELETE CASCADE,
    CONSTRAINT fk_song FOREIGN KEY (SongID)
        REFERENCES Songs(SongID) ON DELETE CASCADE
);

-- Setup some predefined values

INSERT INTO
    Categories(Category)
VALUES
    ("Studio"),
    ("Live"),
    ("Recording"),
    ("Rehearsal"),
    ("Event");

INSERT INTO
    Formats(Title)
VALUES
    ("CD"),
    ("CD-R"),
    ("EP"),
    ("Digital"),
    ("FLAC"),
    ("MP3"),
    ("Streaming");

INSERT INTO
    ReleaseTypes(Type)
VALUES
    ("Full length"),
    ("Live album"),
    ("EP"),
    ("Demo"),
    ("Compilation"),
    ("Split");
