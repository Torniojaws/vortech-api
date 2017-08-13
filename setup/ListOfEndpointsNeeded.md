# Endpoints needed

After working on the features a bit, a lot of needs have come up. So to make implementing them easier
to follow, here's a list of what is probably needed. Updated as new things come up.

## News

- [x] ``GET /news`` to return all news items
- [x] ``GET /news/:id`` to return a specific news item
- [x] ``POST /news`` with a JSON, to add a new news item
- [x] ``PUT /news/:id`` to replace a news post with a new version
- [x] ``PATCH /news/:id`` with JSON, to update an existing news partially
- [x] ``DELETE /news/:id`` to remove a news item completely

## Releases

- [x] ``GET /releases`` to return all releases
- [x] ``GET /releases/:id`` to retun a specific release
- [x] ``POST /releases`` with a JSON, to add a release
- [x] ``PUT /releases/:id`` with a JSON, to replace a release with new data
- [x] ``PATCH /releases/:id`` with a JSON, to partially update a release (only - not related tables!)
- [x] ``DELETE /releases/:id`` to remove a release (the deletion will cascade to related tables)

For the related details, we should have some easy endpoints:

- [x] ``GET /releases/:id/people`` to get which people were on a release and what they played
- [x] ``GET /releases/:id/formats`` to get the formats the release was on
- [x] ``GET /releases/:id/categories`` to get the categories the album is in
- [x] ``GET /releases/:id/songs`` to get the songs the album has

When a new Release is added, it will also create some extra data in other tables. We need some endpoints
for updating them too. Eg.

- [x] ``PATCH /releases/:id/people`` to update who played on the album and what instrument(s)
- [x] ``PATCH /releases/:id/formats`` to update what formats the release is in
- [x] ``PATCH /releases/:id/categories`` to update which categories the release is in
- [x] ``PUT /releases/:id/songs`` to fully replace the song list

It *might* be useful to have an endpoint purely for adding songs, unrelated to adding new albums. For example
if there is a song that is a one-off thing for something, but has no specific Vortech release.

- [x] ``GET /songs`` to list all songs
- [x] ``GET /songs/:id`` to list a specific song (might not be so useful, as you'd need to know the ID)
- [x] ``POST /songs`` to add song(s)
- [x] ``PUT /songs/:id`` to update a song details
- [x] ``PATCH /songs/:id`` to change a specific detail of a song, like the name or duration

## Shows

- [x] ``GET /shows`` to return all live shows
- [x] ``GET /shows/:id`` to return a specifc live show
- [x] ``POST /shows`` with a JSON, to add a new show
- [x] ``PUT /shows/:id`` with a JSON, to replace a show with new data
- [x] ``PATCH /shows/:id`` with a JSON, to update a show partially
- [x] ``DELETE /shows/:id`` to remove a show

## People

Since People only have one detail to change (their name), PATCH will not be implemented, as it would
do the exact same thing that PUT does.

- [x] ``GET /people`` to get all the people
- [x] ``GET /people/:id`` to get a specific person
- [x] ``POST /people`` with a JSON, to add a person
- [x] ``PUT /people/:id`` with a JSON, to update an existing person. In practice, rename them
- [x] ``DELETE /people/:id`` to delete a person

## Biography

- [x] ``GET /biography`` to return all current biography data
- [x] ``POST /biography`` with a JSON, to add a new biography
- [x] ``PUT /biography`` with a JSON, to replace all biography data of the NEWEST entry by Created
- [x] ``PATCH /biography`` with a JSON, to modify the short or full biography of the NEWEST entry by Created

Note that DELETE will not be implemented on purpose.

## Videos

- [x] ``GET /videos`` to return a list of all videos
- [x] ``GET /videos/:id`` to return a specific video
- [x] ``POST /videos`` with a JSON, to add a new video
- [x] ``PUT /videos/:id`` with a JSON, to replace the full video details
- [x] ``PATCH /videos/:id`` with a JSON, to partially update a video
- [x] ``DELETE /videos/:id`` to remove a video

## Shop

- [x] ``GET /shop`` to return all shop items
- [x] ``GET /shop?category=<string>`` to return all shop items that are in category <string>
- [x] ``GET /shop/:id`` to get a specific shop item
- [x] ``POST /shop`` with a JSON, to add a new shop item
- [x] ``PUT /shop/:id`` with a JSON, to replace a shop item
- [x] ``PATCH /shop/:id`` with a JSON, to update an existing shop item
- [x] ``DELETE /shop/:id`` to remove a shop item

## Photos

Photos will always be returned grouped by their assigned albums.

- [ ] ``GET /photos`` to get all photos
- [ ] ``GET /photos/:id`` to get a specific photo
- [ ] ``GET /photos/:category`` to get all photos of :category
- [ ] ``POST /photos`` with a JSON with the files being uploaded during the process, the JSON has the metadata
- [ ] ``PATCH /photos/:id`` with a JSON, to edit a photo details (probably the Caption text)
- [ ] ``DELETE /photos/:id`` to delete the photo DB entry and the file itself

## Contacts

Contacts is quite simple. Mostly used to retrieve the most recent values.

- [ ] ``GET /contacts`` to get the data of the most recent contacts data (by Created)
- [ ] ``POST /contacts`` to add an updated contacts dataset
- [ ] ``PATCH /contacts`` to update the most recent contact info

## Guestbook

- [ ] ``GET /guestbook`` to get all guestbook items
- [ ] ``GET /guestbook/:id`` to get a specifc guestbook item
- [ ] ``POST /guestbook`` with a JSON, to add a new guestbook entry as a Guest
- [ ] ``PATCH /guestbook/:id`` with a JSON, to partially update a guestbook post.
- [ ] ``PATCH /guestbook/:id`` could also be used by a logged in admin to add a comment to the post?
- [ ] ``DELETE /guestbook/:id`` that only admin can call, to delete a guestbook post

## Visitor counter

Counting visitors will be done on a per-session basis. We will insert a new entry for each visit
so that we can keep timeperiod statistics.

- [ ] ``GET /visits`` to get the visit count details (today, week, month, total)
- [ ] ``POST /visits`` add current visit

## Votes

When users vote for things, we need to count them. At first, it will be just for releases, but it
can be expanded for other things too. Maybe songs, shopitems, and something else?

- [ ] ``GET /votes/releases`` to get all vote results for all albums.
- [ ] ``GET /votes/releases/:id`` to get the vote results for a specific album
- [ ] ``POST /votes/releases`` with a JSON, to add a vote for an album

## Release download count

Whenever a release is downloaded, we'll add an entry to the DB. Can we have some extra details, like
which country the request came from?

- [ ] ``GET /downloads/releases`` to get the download count of all releases
- [ ] ``GET /downloads/releases/:id`` to get the download count of a specific release
- [ ] ``POST /downloads/releases`` with a JSON, add to download count

## Todo

Maybe implement PATCH for all endpoints with the "action" style, eg. ``PATCH /shop/123`` with a JSON
something like this:
```
[
    {"op": "add", "target": "categories", "value": 1},
    {"op": "replace", "target": "title", "value": "New value"},
    {"op": "remove", "target": "urls"}
]
```
