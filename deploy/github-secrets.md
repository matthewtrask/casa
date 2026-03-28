# GitHub Secrets required for the deploy workflow

Go to: GitHub repo → Settings → Secrets and variables → Actions → New repository secret

| Secret name       | Value                                              |
|-------------------|----------------------------------------------------|
| `SERVER_HOST`     | Your droplet IP (e.g. `143.198.12.34`)             |
| `SERVER_USER`     | SSH user (e.g. `root`)                             |
| `SSH_PRIVATE_KEY` | Full contents of your private key (`~/.ssh/id_ed25519`) |
| `DEPLOY_PATH`     | App path on server (e.g. `/var/www/casa`)          |

## Getting your private key

```bash
cat ~/.ssh/id_ed25519
```

Copy the entire output including the `-----BEGIN...` and `-----END...` lines.

## Sudo without password (required for systemctl reload)

The workflow runs `sudo systemctl reload php8.4-fpm` and `sudo systemctl reload nginx`.
On a fresh DO droplet running as root this works fine. If you create a deploy user, add this to /etc/sudoers:

```
deploy ALL=(ALL) NOPASSWD: /usr/bin/systemctl reload php8.4-fpm, /usr/bin/systemctl reload nginx, /usr/bin/tee /etc/cron.d/casa, /usr/bin/chmod 644 /etc/cron.d/casa
```
