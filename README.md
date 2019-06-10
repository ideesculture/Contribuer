# Contribuer

A tribute to the Contribute plugin for CollectiveAccess Pawtucket

*Even if not enough used by us, we loved the Contribute plugin inside CollectiveAccess. These times were our first real use case for this nice plugin, but it no more distributed within CA zip. Things and use evolves, as we needed the functionality, it was time for us to build a simple fac-similé of it. As it was made in France, we call it Contribuer.*



## Preview

Plugin has 2 major functionalities : collect the contribution through a record input form and allow already stored objects edition.

![5cfe89947779e17151](https://i.loli.net/2019/06/11/5cfe89947779e17151.png)

Example of a *Contribuer* input form

## Installation

As with any CA plugin (either for Providence or Pawtucket), unzip it inside Pawtucket2 app/plugins folder.

## Configuration

This plugin is mostly configuration-driven through Contribuer/conf/contribuer.conf file.

Main variables inside the configuration file are :

`enabled = 1`

Plugin activation

`allow_anonymous_contributions = 0`

Restrict contributions to already logged user, most of the time proper value

`type_id = 31`

Single object type id for insertion. No text based value here, but the ID of the object_types value you want to use. Better to be done in the future.

`auto_numbering = 1`

Wether you want to use multipart_id_numbering or base the idno on a value inside the form

`allow_media_upload = 1`

Allows to add media to already inserted objects

`allow_deletion = 1`

Allows to delete objects from Pawtucket

`check_ownership = creation_user`

Check ownership of the record for the buttons Delete and Add media, either based on *creation_user* or on *last_modified_user*, *0* for disabled

`mappings = {...}`

Main variable for the configuration of the form. It uses AlpacaJS form javascript module to build up the form. Why not using the proper CA bundles ? We wanted a really fast to handle plugin configuration. For simple metadatas, this way of doing things is really easy. It will not scale well, for sure, but as AlpacaJS brings simple stylings with, that integrates well even inside really-advanced-themed pawtucket projects.

## Adding a link to the contribute form

```
$contribuer_add_url = __CA_URL_ROOT__."/index.php/Contribuer/Do/Add";
```

Use this URL in a link or a menu object through the edit of themes/XXXX/views/pageLayout/pageHeader.php (with XXXX being the name of your pawtucket theme).

## Adding buttons to objects display

This plugin uses an helper file to insert buttons inside the object display.

First, require the helper, around the beginning of the file, add :

```php
require_once(__CA_APP_DIR__."/plugins/Contribuer/helpers/displayHelpers.php");
```

Then, at the wanted position for the buttons, add :

```php
<?php ContribuerButtons($vn_id) ?>
```

![5cfe884e64a3b99496](https://i.loli.net/2019/06/11/5cfe884e64a3b99496.png)

On this capture, you can see "Add media" and "Delete" buttons.
