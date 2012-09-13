<?php
/*
Plugin Name: Impostercide
Plugin URI: http://halfelf.org/plugins/impostercide/
Description: Impostercide prevents unauthenticated users from "signing" a comment with a registered users' email address.
Version: 2.0
Author: Mika Epstein, Scott Merrill
Author URI: http://www.ipstenu.org/
Network: true

Copyright 2005-07 Scott Merrill (skippy@skippy.net)
Copyright 2010-11 Mika Epstein (email: ipstenu@ipstenu.org)

    This file is part of Impostercide, a plugin for WordPress.

    Impostercide is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 2 of the License, or
    (at your option) any later version.

    Impostercide is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with WordPress.  If not, see <http://www.gnu.org/licenses/>.
*/


// Internationalization
add_action( 'init', 'ippy_impostercide_internationalization' );
function ippy_impostercide_internationalization() {
	load_plugin_textdomain('ippy_impostercide', false, 'impostercide/languages' );
}

add_filter ('preprocess_comment', 'impostercide_protect_email');

if (! function_exists ('impostercide_protect_email')) :
function impostercide_protect_email ($data) {
global $wpdb, $user_ID, $user_login, $user_email;

extract ($data);

if ('' != $comment_type) {
        // it's a pingback or trackback, let it through
        return $data;
}

get_currentuserinfo();

if ( is_user_logged_in() ) {
        // It's a logged in user, so it's good.
        return $data;
}

// Make this customizable
$imposter_message = '<h2>' . __('Possible Imposter', 'ippy_impostercide') .'</h2> <p>' . __('You are attempting to post a comment with information (i.e. email address or login ID) belonging to a registered user. If you have an account on this site, please login to make your comment. Otherwise, please try again with different information.', 'ippy_impostercide') .'</p>';

$imposter_error = __('Error: Imposter Detected');

// a name was supplied, so let's check the login names
if ('' != $comment_author) {
        $result = $wpdb->get_var("SELECT count(ID) FROM $wpdb->users WHERE user_login='$comment_author'");
        if ($result > 0) {
			wp_die( $imposter_message, $imposter_error );
        }
}

// an email was supplied, so let's see if we know about it
if ('' != $comment_author_email) {
        $result = $wpdb->get_var("SELECT count(ID) FROM $wpdb->users WHERE user_email='$comment_author_email'");
        if ($result > 0) {
			wp_die( $imposter_message, $imposter_error );
		}
}

return $data;
}
endif;

// donate link on manage plugin page
add_filter('plugin_row_meta', 'impostercide_donate_link', 10, 2);
function impostercide_donate_link($links, $file) {
        if ($file == plugin_basename(__FILE__)) {
                $donate_link = '<a href="https://www.wepay.com/donations/halfelf-wp">Donate</a>';
                $links[] = $donate_link;
        }
        return $links;
}
