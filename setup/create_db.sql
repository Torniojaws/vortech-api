DROP DATABASE test_vortech;
CREATE DATABASE test_vortech;
USE test_vortech;

-- Testing account in local development
CREATE USER 'test'@'localhost' IDENTIFIED BY 'test';
GRANT ALL ON test_vortech.* TO 'test'@'localhost';

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

CREATE TABLE NewsCategoryValues (
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

-- Biography

CREATE TABLE Biography (
    BiographyID int AUTO_INCREMENT,
    Short text NOT NULL,
    Full text NOT NULL,
    Created datetime,
    Updated datetime,
    PRIMARY KEY (BiographyID)
);

-- Videos

CREATE TABLE Videos (
    VideoID int AUTO_INCREMENT,
    Title varchar(200) NOT NULL,
    URL text NOT NULL,
    Created datetime,
    Updated datetime,
    PRIMARY KEY (VideoID)
);

CREATE TABLE VideosCategories (
    VideoCategoryID int AUTO_INCREMENT,
    Category varchar(200) NOT NULL,
    PRIMARY KEY (VideoCategoryID)
);

CREATE TABLE VideosTags (
    TagID int AUTO_INCREMENT,
    VideoID int NOT NULL,
    VideoCategoryID int NOT NULL,
    PRIMARY KEY (TagID),
    CONSTRAINT fk_video FOREIGN KEY (VideoID)
        REFERENCES Videos(VideoID) ON DELETE CASCADE,
    CONSTRAINT fk_videocategory FOREIGN KEY (VideoCategoryID)
        REFERENCES VideosCategories(VideoCategoryID) ON DELETE CASCADE
);

-- Shop

CREATE TABLE ShopItems (
    ShopItemID int AUTO_INCREMENT,
    Title varchar(200) NOT NULL,
    Description text,
    Price decimal(10,2) NOT NULL,
    Currency varchar(3) NOT NULL,
    Image varchar(200),
    Created datetime,
    Updated datetime,
    PRIMARY KEY (ShopItemID)
);

CREATE TABLE ShopCategories (
    ShopCategoryID int AUTO_INCREMENT,
    Category varchar(200) NOT NULL,
    SubCategory varchar(200) NOT NULL,
    PRIMARY KEY(ShopCategoryID)
);

CREATE TABLE ShopItemCategories (
    ShopItemCategoryID int AUTO_INCREMENT,
    ShopItemID int NOT NULL,
    ShopCategoryID int NOT NULL,
    PRIMARY KEY(ShopItemCategoryID),
    CONSTRAINT fk_shopitem FOREIGN KEY (ShopItemID)
        REFERENCES ShopItems(ShopItemID) ON DELETE CASCADE,
    CONSTRAINT fk_shopcategory FOREIGN KEY (ShopCategoryID)
        REFERENCES ShopCategories(ShopCategoryID) ON DELETE CASCADE
);

-- These are 3rd party logos used in shopitems, like Spotify logo, BandCamp logo, etc.
CREATE TABLE ShopItemImages (
    ShopItemImageID int AUTO_INCREMENT,
    Image varchar(200) NOT NULL,
    Created datetime,
    Updated datetime,
    PRIMARY KEY (ShopItemImageID)
);

CREATE TABLE ShopItemURLs (
    ShopItemURLID int AUTO_INCREMENT,
    ShopItemID int NOT NULL,
    Title varchar(200) NOT NULL,
    URL text NOT NULL,
    ShopItemImageID int,
    PRIMARY KEY (ShopItemURLID),
    CONSTRAINT fk_shopitem_url FOREIGN KEY (ShopItemID)
        REFERENCES ShopItems(ShopItemID) ON DELETE CASCADE,
    CONSTRAINT fk_shopitem_img FOREIGN KEY (ShopItemImageID)
        REFERENCES ShopItemImages(ShopItemImageID) ON DELETE CASCADE
);

-- Photos

CREATE TABLE Photos (
    PhotoID int AUTO_INCREMENT,
    Image varchar(255) NOT NULL,
    Caption varchar(1000),
    TakenBy varchar(200),
    Country varchar(100),
    CountryCode varchar(2),
    City varchar(100),
    Created datetime,
    Updated datetime,
    PRIMARY KEY (PhotoID)
);

CREATE TABLE PhotoAlbums (
    AlbumID int AUTO_INCREMENT,
    Title varchar(200) NOT NULL,
    Created datetime,
    Updated datetime,
    PRIMARY KEY (AlbumID)
);

CREATE TABLE PhotosAlbumsMapping (
    MappingID int AUTO_INCREMENT,
    PhotoID int NOT NULL,
    AlbumID int NOT NULL,
    PRIMARY KEY (MappingID),
    CONSTRAINT fk_photos FOREIGN KEY (PhotoID)
        REFERENCES Photos(PhotoID) ON DELETE CASCADE,
    CONSTRAINT fk_albums FOREIGN KEY (AlbumID)
        REFERENCES PhotoAlbums(AlbumID) ON DELETE CASCADE
);

CREATE TABLE PhotoCategories (
    PhotoCategoryID int AUTO_INCREMENT,
    Category varchar(200) NOT NULL,
    PRIMARY KEY (PhotoCategoryID)
);

CREATE TABLE PhotoCategoryMapping (
    MappingID int AUTO_INCREMENT,
    PhotoID int NOT NULL,
    PhotoCategoryID int NOT NULL,
    PRIMARY KEY (MappingID),
    CONSTRAINT fk_photo FOREIGN KEY (PhotoID)
        REFERENCES Photos(PhotoID) ON DELETE CASCADE,
    CONSTRAINT fk_photo_categories FOREIGN KEY (PhotoCategoryID)
        REFERENCES PhotoCategories(PhotoCategoryID) ON DELETE CASCADE
);

-- Contacts refers to the Contacts page, where we have mostly info and links to tech documents

CREATE TABLE Contacts (
    ContactsID int AUTO_INCREMENT,
    Email varchar(100) NOT NULL,
    TechRider varchar(100),
    InputList varchar(100),
    Backline varchar(100),
    Created datetime,
    Updated datetime,
    PRIMARY KEY (ContactsID)
);

-- Subscribers will receive news directly to their email

CREATE TABLE Subscribers (
    SubscriberID int AUTO_INCREMENT,
    Email varchar(200) NOT NULL,
    UniqueID varchar(23) NOT NULL,
    Active BIT(1),
    Created datetime,
    Updated datetime,
    PRIMARY KEY (SubscriberID)
);

-- Setup some predefined values

INSERT INTO
    NewsCategoryValues(Category)
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

INSERT INTO
    VideosCategories(Category)
VALUES
    ("Music video"),
    ("Live video"),
    ("Lyric video"),
    ("Full show"),
    ("Rehearsal"),
    ("Studio"),
    ("Recording"),
    ("Video greeting"),
    ("How to play"),
    ("Interview");

INSERT INTO
    ShopCategories(Category, SubCategory)
VALUES
    ("Releases", "CD"),
    ("Releases", "CD-R"),
    ("Releases", "Digital"),
    ("Clothing", "T-Shirt"),
    ("Clothing", "Longsleeve"),
    ("Clothing", "Hoodie"),
    ("Clothing", "Girlie"),
    ("Boxsets", "Album and Shirt"),
    ("Boxsets", "Albums");

INSERT INTO
    ShopItemImages(Image)
VALUES
    ("paypal.png"),
    ("bandcamp.png");

INSERT INTO
    PhotoCategories(Category)
VALUES
    ("Promotional"),
    ("Live"),
    ("Rehearsal"),
    ("Studio"),
    ("Interesting");
