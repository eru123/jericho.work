# jericho.work
This repository will be the base of all my web projects, research and experiment before it goes to production.

### This repository features the following:
 - **Frontend** - Usage of different Fron-end frameworks (Vue, React.js) together with PHP
 - **VHosts** - In this project I use a default apache2 configuration where all apache2 vhosts points to a single DocRoot and use a Domain plugin to handle different domains
 - **Static Files** - In addition to `VHosts`, it will be also packed with Static file serving where we can serve files more securely (Middlewares, File Traversal Protection)
 - **CDN** and **Cloud Storage** - In able to support Storage and High Availability of static files, we built this project on top of Cloudflare CDN and Cloudflare R2 Storage

### Purpose:
 - I support Open Source and as for Transparency of our free services, this repository will be open to the public and open for Contributions
 - Create Light weight, Fast and Alternative Technology for Web Applications
 - Help developers and students on their Research and Experiments

# For Production
 - Copy the Project into your Apache2 Document Root `/var/www`
 - Make sure that the `/var/www/html` folder is the default Server Directory
 - Enable SSL with DNS verification for wildcard subdomains
 - Configure the database
 - Configure the memcached on localhost level
 - Configure the cloudflare CDN caching
 - Configure the cloudflare R2 for file storage
 - Generate required API keys including the cache purge, cache configure and R2
 - Configure the .env
 - Install the project dependencies
```bash
cd html
composer install
pnpm install
gulp install
gulp build
```

### setup
Initial Setup in codespace
```bash
# Upload the eru123-gpg folder and rename it as dev folder so it won't be tracked by git
gpg --import ./dev/private.key 

# List the keys
gpg --list-secret-keys --keyid-format LONG

whereis gpg
git config --global gpg.program /usr/bin/gpg
git config --global user.signingkey <keyid>
git config --global commit.gpgsign true

# Memcached
sudo add-apt-repository ppa:ondrej/php
sudo apt-get update
sudo apt-get install php-memcached
php -m | grep memcached
```

### Install memcached in Ubuntu
```bash
sudo apt-get install memcached php-memcache php-memcached
sudo service memcached status
sudo service memcached stop
sudo service memcached start
```
