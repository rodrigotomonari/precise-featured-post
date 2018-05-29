Precise Featured Post WordPress Plugin
=============

This plugin allows the Wordpress editors to easily mark posts as featured posts.

It is intended for use by advanced developers who wish to create a highly personalized theme.

Features
=============

- Easy manage featured posts from posts list
- Add feature post to any section of your theme
- Supports Post/Page and all custom post types
- Easy to use in your customized theme
- Ajax powered featured status toggle

How it works?
=============

This plugin adds meta keys for all posts that are marked as featured. And these posts can be retrieved
using a custom meta query.

How to use?
=============

The default configuration creates the key `featured` to the `post` post type.

Create a custom query to retrieved the featured posts

```
$custom_query = new WP_Query(
    array(
        'post_type' => 'post',
        'meta_key' => precise_featured_post_key('featured'),
        'posts_per_page' => 5
    )
);
```

If your theme needs more keys or work with another post type add the filter 
`precise_featured_post_options` to the functions.php

```
add_filter('precise_featured_post_options', 'custom_featured_post_options');

function custom_featured_post_options($options) {
    return array(
        // Post Type
        'post' => array(
            // Keys
            array(
                'name'  => 'home',
                'label' => 'Home?',
            ),
            array(
                'name'  => 'sidebar',
                'label' => 'Sidebar?',
            )
        ),
        'page' => array(
            array(
                'name'  => 'featured',
                'label' => 'Featured?',
            )
        ),
        'movie' => array(
            array(
                'name'  => 'home',
                'label' => 'Home?',
            ),
            array(
                'name'  => 'movie_archive',
                'label' => 'Archive?',
            )
        )
    );
}
```

To-Do
=============

- Widget


Screenshots
=============

![Post listings](/screenshots/screenshot-1.png?raw=true "Post listings")

![Custom post type listings](/screenshots/screenshot-2.png?raw=true "Custom post type listings")

![Post edit form](/screenshots/screenshot-3.png?raw=true "Post edit form")
