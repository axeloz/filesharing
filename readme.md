# Files Sharing

Powered by Laravel
<p><img src="https://laravel.com/assets/img/components/logo-laravel.svg"></p>

## Description

This PHP application based on Laravel 5.4 allows to share files like Wetransfer. You may install it on your own server. It does not require any database system, it works with JSON files in the storage folder.

It comes with a droplet (based on Dropzone.js). You may drag and drop some files or directories into the droplet, your files will be uploaded to the server as a bundle. 

The bundle is a various number of files between 1 and infinite (based on your configuration). 
The bundle has a 2 weeks expiry date after the creation of the bundle. This value is not editable yet, this is a todo.

This application provides three links per upload bundle : 
- a bundle preview link : you can send this link to your recipients who will see the bundle content. For example: http://yourdomain/bundle/dda2d646b6746b96ea9b?auth=965242
- a bundle download link : you can send this link yo your recipients who will download all the files of the bundle at once. For example: http://yourdomain/bundle/dda2d646b6746b96ea9b/download?auth=965242
- a deletion link : for you only, it invalidates the bundle. For example: 
http://yourdomain/bundle/dda2d646b6746b96ea9b/delete?auth=ace6f22f5

Each of these links come with a auth code. This code is the same for the preview and the download links. It is however different for the deletion link.

The application also comes with a Laravel Command (background task) who will physically removed expired bundle files of the storage disk.

Sorry about the design, I know it's ugly. You're welcome to help and participate.

## Requirements

Basically, nothing more than Laravel itself:
- PHP >= 5.6.4
- OpenSSL PHP Extension
- PDO PHP Extension
- Mbstring PHP Extension
- Tokenizer PHP Extension
- XML PHP Extension
- JSON PHP Extension

The application also uses:
- http://www.dropzonejs.com/
- http://jquery.com/
- https://clipboardjs.com/

## Installation

- configure your domain name. For example: files.yourdomain.com 
- clone the repo or download the sources into the webroot folder
- configuration your webserver to point your domain name to the public/ folder
- run a `composer install`
- run a `npm install --production`
- start the Laravel scheduler (it will delete expired bundles of the storage). For example `* * * * * php /path-to-your-project/artisan schedule:run >> /dev/null 2>&1`

Use your browser to navigate to your domain name (example: files.yourdomain.com) and that's it.

## Development

If your want to modify the sources, you can use the Laravel Mix features:
- configure your domain name. For example: files.yourdomain.com 
- clone the repo or download the sources into the webroot folder
- configuration your webserver to point your domain name to the public/ folder
- run a `composer install`
- run a `npm install`
- run a `npm run watch` in order to recompile the assets when changed

## Roadmap

There are many ideas to come. You are welcome to participate. 
- make the expiry date editable per bundle
- limit upload permission based on an IP address (or IP range) or by a password (or passwords)
- ability to send link to recipients 

## Licence

https://choosealicense.com/licenses/gpl-3.0/

## Welcome on board

If you want to participate or if you want to talk with me : sharing@mabox.eu
