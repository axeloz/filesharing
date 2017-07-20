# Files Sharing

<p align="center"><img src="https://github.com/axeloz/filesharing/raw/master/public/img/capture.png" width="700" /></p>

Powered by Laravel
<p><img src="https://laravel.com/assets/img/components/logo-laravel.svg"></p>

## Description

This PHP application based on Laravel 5.4 allows to share files like Wetransfer. You may install it **on your own server**. It **does not require** any database system, it works with JSON files in the storage folder. It is **multilingual** and comes with english and french translations for now. You're welcome to help.

It comes with a droplet (based on Dropzone.js). You may drag and drop some files or directories into the droplet, your files will be uploaded to the server as a bundle.

The bundle is a various number of files between 1 and infinite (based on your configuration).
The bundle has a 2 weeks expiry date after the creation of the bundle. This value is not editable yet, this is a todo.

This application provides three links per upload bundle :
- a bundle preview link : you can send this link to your recipients who will see the bundle content. For example: http://yourdomain/bundle/dda2d646b6746b96ea9b?auth=965242. The recipient can see all the files of the bundle, can download one given file only or the entire bundle.
- a bundle download link : you can send this link yo your recipients who will download all the files of the bundle at once (without any preview). For example: http://yourdomain/bundle/dda2d646b6746b96ea9b/download?auth=965242.
- a deletion link : for you only, it invalidates the bundle. For example:
http://yourdomain/bundle/dda2d646b6746b96ea9b/delete?auth=ace6f22f5.

Each of these links come with a auth code. This code is the same for the preview and the download links. It is however different for the deletion link.

The application also comes with a Laravel Command (background task) who will physically removed expired bundle files of the storage disk. This command is included in Laravel scheduled commands.

*Sorry about the design, I know it's ugly*. You're welcome to help and participate.

## Features

- upload one or more files via drag and drop or browse
- creation of a bundle
- ability to keep adding files to the bundle until you close your browser tab, the links remain untouched
- bundle expiration
- sharing link with bundle content preview
- ability to download a single file of the bundle or the entire bundle
- direct download link (doesn't show the bundle content)
- deletion link
- garbage collector which removes the expired bundles as a background tasks
- multilingual (EN and FR)
- easy installation, no database required
- upload limitation based on client IP filtering
- secured by tokens, authentication codes and non-accessible files

## Requirements

Basically, nothing more than Laravel itself:
- PHP >= 5.6.4
- OpenSSL PHP Extension
- PDO PHP Extension
- Mbstring PHP Extension
- Tokenizer PHP Extension
- XML PHP Extension

Plus:
- JSON PHP Extension (included in PHP 5.2+)
- ZipArchive PHP Extension (included in PHP 5.3+)

The application also uses:
- http://www.dropzonejs.com/
- http://jquery.com/
- https://clipboardjs.com/

## Installation

- configure your domain name. For example: files.yourdomain.com
- clone the repo or download the sources into the webroot folder
- configure your webserver to point your domain name to the public/ folder
- run a `composer install`
- run a `npm install --production`
- make sure that the PHP process has write permission on the ./storage folder
- generate the Laravel KEY: `php artisan key:generate`
- start the Laravel scheduler (it will delete expired bundles of the storage). For example `* * * * * php /path-to-your-project/artisan schedule:run >> /dev/null 2>&1`

Use your browser to navigate to your domain name (example: files.yourdomain.com) and **that's it**.

## Configuration

In order to configure your application, copy the .env.example file into .env. Then edit the .env file. 

| Configuration | Description |
| ------------- | ----------- |
| `APP_ENV`     | change this to `production` when in production (`local` otherwise) |
| `APP_DEBUG` | change this to `false` when in production (`true` otherwise) |
| `TIMEZONE` | change this to your current timezone |
| `LOCALE` | change this to "fr" or "en" |
| `STORAGE_PATH` | (*optional*) changes this wherever you want to store the files |
| `UPLOAD_MAX_FILESIZE` | (*optional*) change this to the value you want (K, M, G, T, ...). Attention : you must configure your PHP settings too (`post_max_size`, `upload_max_filesize` and `memory_limit`) |
| `UPLOAD_LIMIT_IPS` | (*optional*) a comma separated list of IPs from which you may upload files. Different formats are supported : Full IP address (192.168.10.2), Wildcard format (192.168.10.*), CIDR Format (192.168.10/24 or 1.2.3.4/255.255.255.0) or Start-end IP (192.168.10.0-192.168.10.10) |

## Development

If your want to modify the sources, you can use the Laravel Mix features:
- configure your domain name. For example: files.yourdomain.com
- clone the repo or download the sources into the webroot folder
- configure your webserver to point your domain name to the public/ folder
- run a `composer install`
- run a `npm install`
- run a `npm run watch` in order to recompile the assets when changed

## Roadmap / Ideas / Improvements

There are many ideas to come. You are welcome to **participate**.
- make the expiry date editable per bundle
- limit upload permission by a password (or passwords)
- ability to send link to recipients
- add unit testing
- more testing on heavy files
- customizable (logo, name...)
- theming 
- responsiveness

## Licence

GPL-3.0

| Permissions     | Conditions                    | Limitations |
| --------------- | ----------------------------- | ----------- |
| Commercial use  | Disclose source               | Liability   |
| Distribution    | License and copyright notice  | Warranty    |      
| Modification    | Same license                  |             |
| Patent use      |  State changes                |             |
| Private use     |                               |             |

https://choosealicense.com/licenses/gpl-3.0/

## Welcome on board

If you want to **participate** or if you want to talk with me : sharing@mabox.eu
