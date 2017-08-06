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
- [ ] ``POST /biography`` with a JSON, to add a new biography
- [x] ``PUT /biography`` with a JSON, to replace all biography data of the NEWEST entry by Created
- [ ] ``PATCH /biography`` with a JSON, to modify the short or full biography of the NEWEST entry by Created

Note that POST and DELETE will not be implemented on purpose.

## Videos

- [ ] ``GET /videos`` to return a list of all videos
- [ ] ``GET /videos/:id`` to return a specific video
- [ ] ``POST /videos`` with a JSON, to add a new video
- [ ] ``PUT /videos/:id`` with a JSON, to replace the full video details
- [ ] ``PATCH /videos/:id`` with a JSON, to partially update a video
- [ ] ``DELETE /videos/:id`` to remove a video

## Shop

- [ ] ``GET /shop`` to return all shop items
- [ ] ``GET /shop/:id`` to get a specific shop item
- [ ] ``POST /shop`` with a JSON, to add a new shop item
- [ ] ``PUT /shop/:id`` with a JSON, to replace a shop item
- [ ] ``PATCH /shop/:id`` with a JSON, to update an existing shop item
- [ ] ``DELETE /shop/:id`` to remove a shop item

Would be good to have a way to get also shop items from particular categories, like albums, shirts,
or something else. Probably with filters? Eg. ``GET /shop?category=1`` to get all shop items from
category 1 (maybe it can be a cleartext search?)

## Guestbook

- [ ] ``GET /guestbook`` to get all guestbook items
- [ ] ``GET /guestbook/:id`` to get a specifc guestbook item
- [ ] ``POST /guestbook`` with a JSON, to add a new guestbook entry
- [ ] ``PATCH /guestbook/:id`` with a JSON, to partially update a guestbook post.
- [ ] ``PATCH /guestbook/:id`` could also be used by a logged in admin to add a comment to the post?
- [ ] ``DELETE /guestbook/:id`` that only admin can call, to delete a guestbook post

## Visitor counter

Counting visitors will be done on a per-session basis. We will insert a new entry for each visit
so that we can keep timeperiod statistics.

- [ ] ``GET /visits`` to get the visit count details (today, week, month, total)
- [ ] ``POST /visits`` add current visit
