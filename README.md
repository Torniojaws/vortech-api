[![Build Status](https://travis-ci.org/Torniojaws/vortech-api.svg?branch=master)](https://travis-ci.org/Torniojaws/vortech-api)
[![Coverage Status](https://coveralls.io/repos/github/Torniojaws/vortech-api/badge.svg?branch=master)](https://coveralls.io/github/Torniojaws/vortech-api?branch=master)

# Vortech API
The 3rd party webhost doesn't allow installing Python packages after all, so I will do the backend with PHP instead. It will be a RESTful API.

## Starting idea
Create a normal RESTful API with the standard CRUD way for paths and access, eg.
- Create new things: ``POST /news`` with a JSON attached
- Read data: ``GET /albums/:id`` which will return a JSON
- Update existing data: ``PUT /guestbook/:id/comment`` with a JSON attached, and return result JSON
- Delete something: ``DELETE /users/:id`` which will return HTTP status 204

User's own actions happen via ``/me`` eg.
- ``GET /me/guestbook`` to get all guestbook posts from the user logged in
- ``PUT /me/guestbook/:id`` to update a guestbook post
- ``DELETE /me/comments/:id`` for example when deleting a comment
- TODO: Should POST also happen via ``/me`` or should it (probably) go to eg. ``POST /comments`` of some sort

## Updated ideas
After starting to implement some of the features, the need for extra features has come up:
- Updating only partial data is quite common. Should implement eg. ``PATCH /releases/:id`` that receives a JSON
- Some endpoints will build from a few different tables. Should implement that also.

## URL
The URL will be http://www.vortechmusic.com/api/1.0 with future versions being either /1.1 or /2.0

## Documentation
The API documentation is at: http://localhost/api/1.0

## Auth
Since we cannot install anything on the 3rd party webhost, a less-than-optimal way must be implemented using user login instead of eg. OAuth2. But this is not yet implemented, and must be implemented before putting to production.

## Database
All queries will be done using PDO. User passwords will be hashed using a PBFKD2 implementation, which should be very secure.

## Testing
Everything possible will be covered by PHPUnit 6.* tests. Run the tests in the project root with ``phpunit tests``
For coding standards, PSR2 is used. Run the check in the project root with ``phpcs apps/ --standard=PSR2``

## Versions
- PHP 7.1
- MySQL 5.5.48

## Frontend
The frontend will be in a separate repository. It will be done using ReactJS and Bootstrap.

## Setup instructions
See the file ``setup/SettingUpFromNothing.txt`` for thorough instructions.
