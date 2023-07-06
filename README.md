# jericho.work
### setup
Initial Setup in codespace
```bash
# Upload the eru123-gpg folder and rename it as dev folder so it won't be tracked by git
gpg --import ./dev/private.key 

# List the keys
gpg --list-secret-keys --keyid-format LONG

whereis gpg
git config --global gpg.program /usr/bin/gpg
git config --global user.signingkey 7E8C532921D7F76A
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
