# jericho.work
### setup
Initial Setup in codespace
```bash
# Upload the eru123-gpg folder and rename it as dev folder so it won't be tracked by git
gpg --import ./dev/private.key 

whereis gpg
git config --global gpg.program /usr/bin/gpg
git config user.signingkey 7E8C532921D7F76A
git config commit.gpgsign true
```