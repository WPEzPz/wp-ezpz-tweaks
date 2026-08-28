# Suggested Features for WP EzPz Tweaks

Here are several practical and highly-demanded features that would be excellent additions to WP EzPz Tweaks, focusing on performance, code cleanliness, and security:

## Performance & Code Cleanup

1. **Disable Emojis**: WordPress loads emoji-related JavaScript and CSS on every page. Disabling this is a very common tweak to reduce page weight and HTTP requests.
2. **Disable WordPress Heartbeat API**: The Heartbeat API runs constantly in the background (auto-saving, locking revisions, etc.) and can cause high CPU usage on the server. Allowing users to disable it or change its frequency (e.g., from 15s to 60s) is highly demanded.
3. **Remove Query Strings from Static Resources**: Removing `?ver=x.x` from CSS and JS files helps improve caching compatibility with some proxy caches and CDNs.
4. **Disable Dashicons on Frontend**: WordPress loads Dashicons on the frontend for logged-out users in some cases. A tweak to disable them for non-logged-in users saves bandwidth.
5. **Disable oEmbeds (Embeds)**: Removes the JS that allows others to embed your site's content and allows you to embed others, which is often unnecessary and adds bloat.

## Admin & Interface

6. **Restore Classic Widgets**: Since WordPress 5.8, widgets use the block editor. Many users prefer the classic widgets interface. A toggle to restore Classic Widgets (`add_filter('use_widgets_block_editor', '__return_false');`) is very popular.
7. **Maintenance / Coming Soon Mode**: A simple toggle to lock the frontend for non-administrators with a custom message.
8. **Hide Admin Bar**: Allow administrators to hide the WordPress admin bar on the frontend for specific user roles.

## Security

9. **Limit Login Attempts**: A basic mechanism to block IPs temporarily after a certain number of failed login attempts to prevent brute-force attacks.
10. **Disable File Execution in Uploads Folder**: Placing an `.htaccess` rule to prevent PHP execution in the `wp-content/uploads/` directory.
