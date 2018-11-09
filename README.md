# Constant Contact plugin for Craft CMS 3.x

Basic integration with Constant Contact API to allows you to add new contacts to your Contant Contact lists.

## Requirements

This plugin requires Craft CMS 3.0.0-RC1 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require madebyraygun/constant-contact

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Constant Contact.

## Configuring Constant Contact

Signup for Constant Contact API access at [Mashery](https://constantcontact.mashery.com/). From there you can generate your API key and get an authentication token. You can also get your list ID by visiting the [API tester](https://constantcontact.mashery.com/io-docs) and intiate a GET request against the `/lists` endpoint.

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
