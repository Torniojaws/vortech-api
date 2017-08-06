DROP DATABASE vortech;
CREATE DATABASE vortech;
USE vortech;

-- Testing account in local development
CREATE USER 'test'@'localhost' IDENTIFIED BY 'test';
GRANT ALL ON vortech.* TO 'test'@'localhost';

-- NEWS

CREATE TABLE News (
    NewsID int AUTO_INCREMENT,
    Title varchar(255) NOT NULL,
    Contents text NOT NULL,
    Author varchar(255) NOT NULL,
    Created datetime NOT NULL,
    Updated datetime,
    PRIMARY KEY (NewsID)
);

CREATE TABLE Categories (
    CategoryID int AUTO_INCREMENT,
    Category varchar(255) NOT NULL,
    PRIMARY KEY (CategoryID)
);

CREATE TABLE NewsCategories (
    NewsCategoryID int AUTO_INCREMENT,
    NewsID int NOT NULL,
    CategoryID int NOT NULL,
    PRIMARY KEY (NewsCategoryID),
    CONSTRAINT fk_newsCategory FOREIGN KEY (NewsID)
        REFERENCES News(NewsID) ON DELETE CASCADE
);

CREATE TABLE NewsComments (
    CommentID int AUTO_INCREMENT,
    NewsID int NOT NULL,
    Contents varchar(500) NOT NULL,
    AuthorID int,
    Created datetime NOT NULL,
    Updated datetime,
    PRIMARY KEY (CommentID),
    CONSTRAINT fk_newsComment FOREIGN KEY (NewsID)
        REFERENCES News(NewsID) ON DELETE CASCADE
);

-- RELEASES

CREATE TABLE Releases (
    ReleaseID int AUTO_INCREMENT,
    Title varchar(200) NOT NULL,
    Date datetime NOT NULL,
    Artist varchar(200) NOT NULL,
    Credits text,
    Created datetime,
    Updated datetime,
    PRIMARY KEY (ReleaseID)
);

CREATE TABLE Formats (
    FormatID int AUTO_INCREMENT,
    Title varchar(255) NOT NULL,
    PRIMARY KEY (FormatID)
);

CREATE TABLE ReleaseFormats (
    ReleaseFormatID int AUTO_INCREMENT,
    FormatID int,
    ReleaseID int NOT NULL,
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
    ReleaseID int NOT NULL,
    ReleaseTypeID int NOT NULL,
    PRIMARY KEY (ReleaseCategoryID),
    CONSTRAINT fk_releaseReference FOREIGN KEY (ReleaseID)
        REFERENCES Releases(ReleaseID) ON DELETE CASCADE,
    CONSTRAINT fk_releaseCategory FOREIGN KEY (ReleaseTypeID)
        REFERENCES ReleaseTypes(ReleaseTypeID) ON DELETE CASCADE
);

-- PEOPLE

CREATE TABLE People (
    PersonID int AUTO_INCREMENT,
    Name varchar(300) NOT NULL UNIQUE,
    PRIMARY KEY (PersonID)
);

CREATE TABLE ReleasePeople (
    ReleasePeopleID int AUTO_INCREMENT,
    ReleaseID int NOT NULL,
    PersonID int NOT NULL,
    Instruments varchar(500) NOT NULL,
    PRIMARY KEY (ReleasePeopleID),
    CONSTRAINT fk_release FOREIGN KEY (ReleaseID)
        REFERENCES Releases(ReleaseID) ON DELETE CASCADE,
    CONSTRAINT fk_releasePeople FOREIGN KEY (PersonID)
        REFERENCES People(PersonID) ON DELETE CASCADE
);

-- SONGS

CREATE TABLE Songs (
    SongID int AUTO_INCREMENT,
    Title varchar(255) NOT NULL UNIQUE,
    Duration int NOT NULL,
    PRIMARY KEY (SongID)
);

CREATE TABLE ReleaseSongs (
    ReleaseSongID int AUTO_INCREMENT,
    ReleaseID int NOT NULL,
    SongID int NOT NULL,
    PRIMARY KEY (ReleaseSongID),
    CONSTRAINT fk_songRelease FOREIGN KEY (ReleaseID)
        REFERENCES Releases(ReleaseID) ON DELETE CASCADE,
    CONSTRAINT fk_song FOREIGN KEY (SongID)
        REFERENCES Songs(SongID) ON DELETE CASCADE
);

-- Shows

CREATE TABLE Shows (
    ShowID int AUTO_INCREMENT,
    ShowDate datetime NOT NULL,
    CountryCode varchar(2) NOT NULL,
    Country varchar(100) NOT NULL,
    City varchar(100) NOT NULL,
    Venue varchar(200),
    PRIMARY KEY (ShowID)
);

CREATE TABLE ShowsSetlists (
    SetlistID int AUTO_INCREMENT,
    ShowID int NOT NULL,
    SongID int NOT NULL,
    SetlistOrder int,
    PRIMARY KEY(SetlistID),
    CONSTRAINT fk_show FOREIGN KEY (ShowID)
        REFERENCES Shows(ShowID) ON DELETE CASCADE,
    CONSTRAINT fk_showsong FOREIGN KEY (SongID)
        REFERENCES Songs(SongID) ON DELETE CASCADE
);

CREATE TABLE ShowsOtherBands (
    OtherBandsID int AUTO_INCREMENT,
    ShowID int NOT NULL,
    BandName varchar(200) NOT NULL,
    BandWebsite varchar(500),
    PRIMARY KEY (OtherBandsID),
    CONSTRAINT fk_showband FOREIGN KEY (ShowID)
        REFERENCES Shows(ShowID) ON DELETE CASCADE
);

CREATE TABLE ShowsPeople (
    ShowsPeopleID int AUTO_INCREMENT,
    ShowID int NOT NULL,
    PersonID int NOT NULL,
    Instruments varchar(500) NOT NULL,
    PRIMARY KEY(ShowsPeopleID),
    CONSTRAINT fk_showpeople FOREIGN KEY (ShowID)
        REFERENCES Shows(ShowID) ON DELETE CASCADE,
    CONSTRAINT fk_showperson FOREIGN KEY (PersonID)
        REFERENCES People(PersonID) ON DELETE CASCADE
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
