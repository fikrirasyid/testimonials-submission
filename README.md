# Testimonials Submission

This plugin is made as complement of WooThemes' testimonials-by-woothemes plugin. WooThemes' plugin covers most basic things needed in displaying testimonial, but it has no feature to accept visitor's testimonial. I googled but don't find anything suitable for my need, so i built it myself.

## Usage Instructions

How to use this plugin:

1. Install testimonials-by-woothemes plugin
2. Install testimonials-submission
3. Put the shortcode `[testimonials_submission]` on the page / post's editor or put `testimonials_submission();` on the location you want on your theme.

Now you can try submitting your testimonial. This plugin follows these steps (no option available for the time being):

1. The page which contains `testimonials_submission` is opened. For the time being, spam protection available on the plugin is a mere adding empty field which will be modified by js. Hence, the plugin only supports visitor with javascript-support for the time being.
2. Visitor submits his/her testimonial. These three fields are mandatory: name, email, testimonial. Visitor should check his/her email for verification link.
3. The testimonial is added to wp_post as testimonial post_type and submission post_status
4. Visitor clicks the verification link on his/her email and redirected to thank you page. Testimonial is updated to draft post_status, and notification email is sent to email which is used on Dashboard > Settings > General
5. Administrator can later decide whether the plugin should be published, edited, or trashed.

## Changelog

### 1.0

- Initial Public Release

## License
This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; if not, write to:

Free Software Foundation, Inc. 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.