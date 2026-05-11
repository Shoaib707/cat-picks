# Cat Picks

A WordPress plugin built for the WJCT Full Stack Developer skills test.

## What It Does

- Registers a public custom post type named **Cat Picks**.
- Adds a **Featured By** meta field to Cat Picks in WordPress admin.
- Saves the field with nonce, capability, and sanitization checks.
- Displays the Featured By value on the front end when viewing a single Cat Pick.

Note: the prompt says to display the value on a single Staff Pick. Since the requested custom post type is Cat Picks, this plugin displays it on single Cat Pick views.

## Installation

1. Copy this folder into `wp-content/plugins/cat-picks`.
2. In WordPress admin, go to **Plugins**.
3. Activate **Cat Picks**.

## Testing

1. In WordPress admin, go to **Cat Picks > Add New**.
2. Add a title and content.
3. Enter a value in the **Featured By** meta box.
4. Publish the Cat Pick.
5. View the single Cat Pick on the front end and confirm the Featured By value appears below the content.

## With More Time

- I would add a small block or template part integration so themes can place the Featured By value more flexibly.
- I would add automated tests around post type registration and meta saving if this were going into a larger production codebase.

## AI Assistance

I used ChatGPT Codex to scaffold the plugin, check the WordPress implementation approach, and draft this README.
