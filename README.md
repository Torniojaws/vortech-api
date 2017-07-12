# Vortech API
The web host doesn't allow installing Python packages, so the backend needs to be done with PHP instead. So, a RESTful API it is then.

## Starting idea
Create a normal RESTful API with the standard CRUD way for paths and access, eg.
- Create new things: ``POST /news`` with a JSON attached
- Read data: ``GET /albums/:id`` which will return a JSON
- Update existing data: ``PUT /guestbook/:id/comment`` with a JSON attached, and return result JSON
- Delete something: ``DELETE /users/:id`` which will return HTTP status 204

## URL
The URL will most likely be http://www.vortechmusic.com/api/1.0 with future versions being either /1.1 or /2.0

## Auth
Since we cannot install anything on the web host, a less than optimal way must be implemented using user login instead of eg. OAuth2

## Database
All queries will be done using PDO. User passwords will be hashed using a PBFKD2 implementation, which should be very secure.

## Testing
Everything possible will be covered by PHPUnit tests.

## Versions
The versions are locked in place by the host and cannot be changed.
- PHP 5.4.40
- MySQL 5.5.48

## Frontend
The frontend will be in a separate repository. It will be done using ReactJS and Bootstrap.
