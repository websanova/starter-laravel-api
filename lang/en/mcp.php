<?php

return [

    'app.instructions' => 'Tools for the authenticated user to manage their own account data. Bookmarks reference tags by id, never by name. Resolve tag names to ids with the list tags tool first, and create the tag if it does not exist.',

    'app.tools.bookmark.create.description' => 'Create a bookmark for the authenticated user.',
    'app.tools.bookmark.create.params.url' => 'The url of the bookmark. Must be unique among the user\'s bookmarks.',
    'app.tools.bookmark.create.params.title' => 'The title of the bookmark.',
    'app.tools.bookmark.create.params.is_favorited' => 'Whether the bookmark is favorited.',
    'app.tools.bookmark.create.params.tag_ids' => 'The ids of existing tags for the bookmark. Look them up with the list tags tool, never guess an id.',

    'app.tools.bookmark.delete.description' => 'Delete a bookmark the authenticated user owns.',
    'app.tools.bookmark.delete.params.bookmark' => 'The id of the bookmark to delete.',

    'app.tools.bookmark.list.description' => 'List the authenticated user\'s bookmarks with optional filtering and sorting.',
    'app.tools.bookmark.list.params.search' => 'Only return bookmarks matching this search term.',
    'app.tools.bookmark.list.params.favorited' => 'Only return bookmarks that are favorited or not favorited.',
    'app.tools.bookmark.list.params.tag_id' => 'Only return bookmarks with this tag id. Look it up with the list tags tool, never guess an id.',
    'app.tools.bookmark.list.params.sort_by' => 'The field to sort by.',
    'app.tools.bookmark.list.params.sort_dir' => 'The sort direction.',
    'app.tools.bookmark.list.params.per_page' => 'The number of bookmarks per page.',

    'app.tools.bookmark.update.description' => 'Update a bookmark the authenticated user owns. Only the fields provided are changed.',
    'app.tools.bookmark.update.params.bookmark' => 'The id of the bookmark to update.',
    'app.tools.bookmark.update.params.url' => 'The new url of the bookmark. Must be unique among the user\'s bookmarks.',
    'app.tools.bookmark.update.params.title' => 'The new title of the bookmark.',
    'app.tools.bookmark.update.params.is_favorited' => 'Whether the bookmark is favorited.',
    'app.tools.bookmark.update.params.tag_ids' => 'The full list of existing tag ids for the bookmark. Look them up with the list tags tool, never guess an id. Replaces the existing tags, and an empty list removes them all.',

    'app.tools.profile.show.description' => 'Show the authenticated user\'s profile, including their plan and subscriptions.',

    'app.tools.profile.update.description' => 'Update the authenticated user\'s profile. Only the fields provided are changed.',
    'app.tools.profile.update.params.first_name' => 'The new first name of the user.',
    'app.tools.profile.update.params.last_name' => 'The new last name of the user.',
    'app.tools.profile.update.params.locale' => 'The new locale of the user, such as "en".',
    'app.tools.profile.update.params.timezone' => 'The new timezone of the user as an IANA identifier, such as "America/Toronto".',

    'app.tools.tag.create.description' => 'Create a tag for the authenticated user. List the existing tags first, since tag names must be unique. Returns the new tag id to use in the bookmark tools.',
    'app.tools.tag.create.params.name' => 'The name of the tag. Up to 50 characters, must start with a letter or number, and must be unique among the user\'s tags.',

    'app.tools.tag.delete.description' => 'Delete a tag the authenticated user owns. The tag is removed from every bookmark that uses it.',
    'app.tools.tag.delete.params.tag' => 'The id of the tag to delete.',

    'app.tools.tag.list.description' => 'List the authenticated user\'s tags with their ids and names. Use this to find the ids needed for the tag_ids and tag_id fields on the bookmark tools.',

    'app.tools.tag.update.description' => 'Rename a tag the authenticated user owns.',
    'app.tools.tag.update.params.tag' => 'The id of the tag to update.',
    'app.tools.tag.update.params.name' => 'The new name of the tag. Up to 50 characters, must start with a letter or number, and must be unique among the user\'s tags.',

    'admin.instructions' => 'Tools for administrators to manage users and application data.',

    'admin.tools.hello.description' => 'Greet the authenticated admin. Use to confirm the connection works and which admin account it is authenticated as.',

];
