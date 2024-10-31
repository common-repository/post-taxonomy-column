=== Post Taxonomy Column ===
Contributors: marcus.downing
Tags: admin, taxonomies
Requires at least: 3.0
Tested up to 3.2.1
Stable tag: trunk

Add columns to the All Posts, All Pages and custom post types for any taxonomies.

== Description ==

This plugin improves the All Posts or All Pages view in the admin section by adding columns for
any taxonomy you wish. There are two ways to use it:

1. Use the setting page to select the taxonomies.
1. Write code to add a column in exactly the right place.

== Installation ==

Install the plugin the normal way:

1. Upload the `post-taxonomy-column` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Visit the settings page to switch columns on and off.

Alternatively, you can use the `manage_edit-post_type_columns` filter to adjust the
columns more precisely.

Suppose you have a custom post type called *Movies*,
and a custom taxonomy called *Year of Release* with the code `year`,
and you'd like to add this to the 'All Movies' listing in the admin.

Put the following code into `functions.php` or a plugin:

    add_filter("manage_edit-movie_columns", "add_movie_columns");
    function add_movie_columns ($columns) {
        $columns['year'] = 'Year of Release';
        return $columns;
    }

This plugin will then fill in values for the Year column.

== Screenshots ==

1. A *Keywords* column added to the All Pages listing.
1. The settings page.

== Frequently Asked Questions ==

= Where do these taxonomies come from? =

You'll need to add them, either with code in your theme's `functions.php` or using a plugin.
This plugin does not register or manage taxonomies.

= Does this work with custom post types? =

Yes.

= Can I use this plugin to create custom post types? =

No, this plugin does not register or manage custom post types.
You'll need to add them either with code in your theme's `functions.php` or using a plugin.

= My taxonomy/post type isn't showing up. What's wrong? =

Make sure your taxonomies and post types are public:

     register_post_type('foo', array(
       ...
       'public' => true
     ));

= What will this do if I add a column code that isn't a taxonomy? =

Nothing. You'll get a blank cell.

= Can I have more than one taxonomy column? =

Yes, as many as you like as long as the column's ID matches up with the taxonomy ID.

= Can I control the order of the columns? How do I add a column in the middle rather than the end? =

To fine-tune the order of the columns, you will need to control them yourself.
Turn off the checkboxes in the settings page, and instead use the `manage_edit-`*xxx*`_columns`
filter which lets you adjust the columns any way you wish.

For example, to add the `year` column after the `author` column:

    add_filter("manage_edit-movie_columns", "add_movie_columns");
    function add_movie_columns ($columns) {
        $out = array();
        foreach ($columns as $key => $value) {
          $out[$key] = $value;
          if ($key == "author")
            $out["year"] = "Year of Release";
        }
        return $out;
    }

Make sure your filter returns a value of some sort, or you'll end up with no columns at all.

== Changelog ==

= 1.1 =

Added a settings page and code to add columns for you. No more code required!

= 1.0 =

The first release. Required you to add much of the code yourself.
