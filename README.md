[![GitHub version](https://badge.fury.io/gh/Torniojaws%2Fvortech-api.svg)](https://badge.fury.io/gh/Torniojaws%2Fvortech-api)
[![Build Status](https://travis-ci.org/Torniojaws/vortech-api.svg?branch=master)](https://travis-ci.org/Torniojaws/vortech-api)
[![Coverage Status](https://coveralls.io/repos/github/Torniojaws/vortech-api/badge.svg?branch=master)](https://coveralls.io/github/Torniojaws/vortech-api?branch=master)

# Vortech API
The idea is to build a mostly RESTful API (no resource browser per-se - at least yet) that will be
used in the Vortech website. As PHP7 is available on the webhost, that will be the target version.
Most of the things should work in PHP5.6 also, but there are some PHP7-features in use that probably
will not work in PHP5.x, such as typed parameters.

I would have gone with Python, but unfortunately the webhost does not allow installing programs in
a shared environment, so no Python packages (like Flask and SQLAlchemy) can be installed.

## Versions (dictated by the 3rd party webhost)
- PHP 7.1
- MySQL 5.5.48
- Apache 2.4

## Project versioning
Until the first production release is done, the versions will stay as ``0.x.x``. The first production
release will be tagged ``1.0.0``. The versioning will keep to the usual convention:
```
1.2.3
^ ^ ^
| | |- Small update - adds non-breaking extra features, does minor refactoring, or adds documentation
| |--- Big update - non-breaking major updates or database schema changes
|----- Major release - Something changed drastically, or there were breaking changes
```
The numbering will be incremental beyond 9, so after ``0.9.x`` comes ``0.10.x``.
A change in a value will reset the counter on the right side of it, so ``0.4.2`` becomes ``0.5.0``
and ``1.2.10`` becomes ``2.0.0``.

## Starting idea
Create a normal RESTful API with the standard CRUD way for paths and access, eg.
- Create new things: ``POST /news`` with a JSON attached
- Read data: ``GET /albums/:id`` which will return a JSON
- Update existing data: ``PUT /guestbook/:id/comment`` with a JSON attached, and return result JSON
- Delete something: ``DELETE /users/:id`` which will return HTTP status 204

User's own actions happen via ``/me`` eg.
- ``GET /me/guestbook`` to get all guestbook posts from the user logged in
- ``POST /me/releases/:id`` to post a comment about a release
- ``PUT /me/news/:id/comments/:id`` to update a news comment I already posted before
- ``DELETE /me/shows/:id/comments/:id`` for example when deleting a comment

## Updated ideas
After starting to implement some of the features, the need for extra features has come up:
- Updating only partial data is quite common. We'll implement eg. ``PATCH /releases/:id`` with a JSON
- Some endpoints will build from a few different tables. Should implement that also.

## URL
The URL will be https://www.vortechmusic.com/api/1.0 with future versions being either /1.1 or /2.0

## Documentation
The API documentation is also at: https://www.vortechmusic.com/api/1.0

## Auth
Since we cannot install anything on the 3rd party webhost, a less-than-optimal way must be
implemented using user login instead of eg. OAuth2. But this is not yet implemented, and must be
implemented before putting to production. Probably by logging in, and if login is valid, we create
a new session ID and then use that session to keep track.

## Database
All queries will be done using PDO. User passwords will be hashed using a PBKDF2 implementation,
which should be very secure.

## Testing
Everything possible will be covered by PHPUnit 6.* tests with 100 % code coverage as the guideline.
Run the tests in the project root with ``phpunit tests``. For coding standards, PSR2 is used. Run
the check in the project root with ``phpcs apps/ --standard=PSR2``

## Frontend
The frontend will be in a separate repository. It will be done using ReactJS and Bootstrap.

## Setup instructions
See the [instructions](setup/SettingUpFromNothing.md) to see how to setup from nothing.
