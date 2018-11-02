# Constant Contact plugin for Craft CMS 3.x

Create a form and add contacts to your Contant Contact list. This is a very quick and basic integration and not fit for any particular use, if you get my drift.


## Requirements

This plugin requires Craft CMS 3.0.0-beta.23 or later.

## Installation

This plugin is not on packagist, so you'll need to create a local Composer repository to install. From [Andrew Welch's guide to Craft 3 plugins](https://nystudio107.com/blog/so-you-wanna-make-a-craft-3-plugin):

1. Download the plugin locally to a directory relative to your Craft installation. 

2. Add the following to the *require* section of your site's composer.json:

`"madebyraygun/constant-contact": "^0.0.1"`

3. Add your local directory to the list of Composer repositories like so:

```
"repositories": [
  {
    "type": "composer",
    "url": "https://asset-packagist.org"
  },
  {
    "type": "path",
    "url": "/app/dev/constant-contact/"
  }
]
```

Where `/app/dev/constant-contact/` is a valid path on your local filesystem.

4. Since the plugin requires a development version of the Constant Contact PHP SDK, you'll need to change your project's minimum stability:

```
"minimum-stability": "dev",
  "prefer-stable" : true,
```

4. Run composer update.

5. In the Control Panel, go to Settings → Plugins and click the “Install” button for Constant Contact.

## Configuring Constant Contact

Signup for Constant Contact API access at [Mashery](https://constantcontact.mashery.com/). From there you can generate your API key and get an authentication token. You can also get your list ID by visiting the [API tester](https://constantcontact.mashery.com/io-docs) and intiate a GET request against the `/lists` endpoint..

Configure the plugin by creating a new config file named constant-contact.php in your config folder, and add the following the config parameters:

### Example config file

```
<?php
return [
    'key' => 'xxxxxx',
    'list' => 'xxxxxx',
    'token' => 'xxxxxx',
];
```

## Using Constant Contact

Create a new form in a front-end template, like so:

```
<form action="" method="POST">
    {{ csrfInput() }}
    <input type="hidden" name="action" value="constant-contact/subscribe">
    {{ redirectInput('thanks') }}

      <label>Email:</label>
      <input type="email" name="email" value=""/>   
    <input type="submit" name="" value="Subscribe"/>
  </form>
 ```

### Displaying flash messages

When a contact form is submitted, the plugin will set a notice or success flash message on the user session. You can display it in your template like this:

```
{% if craft.app.session.hasFlash('notice') %}
    <p class="message notice">{{ craft.app.session.getFlash('notice') }}</p>
{% elseif craft.app.session.hasFlash('error') %}
    <p class="message error">{{ craft.app.session.getFlash('error') }}</p>
{% endif %}
```

### Ajax submitting

If the form is submitted with Ajax, the plugin will return a JSON object with the same keys as the template object described above.

Example:

```
$('form').on("submit", function(event) {
    event.preventDefault();
    $.ajax({url: '/', type: "POST", data: $(this).serialize(), dataType:"json", success: function (data) {
        if (!data.success) {
          //display error message
        } else {
          //hide form and display success message
        }
    }})
});
```
