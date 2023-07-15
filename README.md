# jericho.work
This repository will be the base of all my web projects, research and experiment before it goes to production.

### This repository features the following:
 - **Frontend** - Usage of different Fron-end frameworks (Vue, React.js) together with PHP
 - **VHosts** - In this project I use a default apache2 configuration where all apache2 vhosts points to a single DocRoot and use a Domain plugin to handle different domains
 - **Static Files** - In addition to `VHosts`, it will be also packed with Static file serving where we can serve files more securely (Middlewares, File Traversal Protection)
 - **CDN** and **Cloud Storage** - In able to support Storage and High Availability of static files, we built this project on top of Cloudflare CDN and Cloudflare R2 Storage (When I get enough funds for this project I might use Cloudflare Cache Reserved and increase API limits of our CDN Service)

### Purpose:
 - I support Open Source and as for Transparency of our free services, this repository will be open to the public and open for Contributions
 - Create Light weight, Cosft Effective, Fast and Alternative Technology for Web Applications
 - Help developers and students on their Research and Experiments

## Frontend Development
For the frontend development, we wanted to support different frameworks and technologies. We will be using Vue.js as our primary framework frontend as I the owner of this repository is more familiar with Vue.js (Ako batas dito). Technically speaking, anything that vitejs supports, it should be able to run in this project as well (but need to be tested).

For the environment, we will be using `pnpm` as our package manager. It is a fast, disk space efficient and node_modules efficient package manager. It is also compatible with `npm` and `yarn` so you can still use your favorite package manager (it's just that I prefer `pnpm`).

We will be using `gulp` as our task runner. It will be used to install the project dependencies, build the project and run the project. It will also be used to run the project in development mode in parallel if one or more of the frontend frameworks are used.

Ubuntu 20.04 LTS is the primary OS for this project. It is the most stable and most supported OS for this project. It is also the most used OS in the world so it is the best choice for this project.

We recommend to use a VPS or a Cloud Server for this project. It is not recommended to use a shared hosting for this project as it will be hard to configure and it will be hard to maintain.

Another reason for using VPS is that we will be taking advantage of having multiple domains and wildcard domains in a single server. I know that this might not be recommended because if the app is compromised or down, all other apps or domains will be affected, and we understand that well. But one of the purpose of this project is to create a lightweight, cost effective and alternative technology for web applications. So we will be using a single server for all of our apps and domains. If the a scaling issues arises, we will be using a load balancer and multiple servers to handle the load. As of now our main focus is the development and the research of the project.

### PORTS Used
Please ensure that the following ports are not used by other apps, in the development
 - 3000

## CDN Frontend Development
```bash
cd html
```

Change CDN mode to Development.
```env
# .env
CDN_PROD=false
```
Run the project frontend in development mode.
```bash
gulp dev
```

Lastly, tunnel your localhost to the VPS server so you can use access the project in your local machine. If you are using remote vscode, you can use the port forwarding easily.

If you are going to tunnel or serve the dev server using different methods, make sure to change `client` property that you passed onto the vite function in the `cdn.domain.php` file.

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
