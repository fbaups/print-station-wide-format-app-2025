# Composer Private Packages (CakePHP projects)

This project uses **private GitHub repositories** that Composer must be able to read over **SSH**.
Follow these steps on any machine that needs to `composer install` this project.

---

## 1) Configure SSH host aliases

Create or edit your SSH config file:

- **Windows:** `C:\Users\<YourUser>\.ssh\config`
- **macOS/Linux:** `~/.ssh/config`

Add host entries (one per private repo). Example for the two packages used here:

```ssh
Host PhotoPackageAdapter
    HostName github.com
    User git
    IdentityFile ~/.ssh/github_ssh_photo-package-adapter
    IdentitiesOnly yes

Host XMPieUProduceClient
    HostName github.com
    User git
    IdentityFile ~/.ssh/github_ssh_xmpie-uproduce-client
    IdentitiesOnly yes
```

> The `Host` names (`PhotoPackageAdapter`, `XMPieUProduceClient`) are **aliases** you’ll reference from `composer.json` instead of raw Git URLs.

---

## 2) Generate SSH keys (one per repo is recommended)

Run and save with a distinct filename each time:

```bash
ssh-keygen -t rsa -b 4096 -C "you@example.com"
```

- **Private key** path must match the `IdentityFile` in the SSH config above.
- **Public key** (`.pub`) must be added in **GitHub → Repo → Settings → Deploy keys → Add key** → _Allow read access_.
  - Add the correct key to each repo:
    - `arajcany/PhotoPackageAdapter`
    - `arajcany/XMPie-uProduce-Client`

> Deploy keys are tied to a single repo. Using separate keys makes revocation/auditing simpler.

---

## 3) Update `composer.json`

Require the packages and declare the repositories using the SSH **host aliases** from step 1.

```jsonc
{
  "require": {
    "arajcany/photo-package-adapter": "^1.0",
    "arajcany/xmpie-uproduce-client": "^1.0"
  },
  "repositories": [
    {
      "type": "vcs",
      "url": "PhotoPackageAdapter:arajcany/PhotoPackageAdapter.git",
      "no-api": true
    },
    {
      "type": "vcs",
      "url": "XMPieUProduceClient:arajcany/XMPie-uProduce-Client.git",
      "no-api": true
    }
  ]
}
```

> You can keep your original alias (e.g., `PhotoPackageAdapter`) patterns; just ensure the **SSH config alias** matches the `url` host in the `repositories` block.

---

## 4) Trust GitHub host key

Run once per machine to add GitHub’s fingerprint to `known_hosts`:

```bash
ssh -T git@github.com
```

You should see a success/auth prompt and `github.com` will be added to `~/.ssh/known_hosts` (or Windows equivalent).

---

## 5) Install

```bash
composer install
```

If already installed before adding a new repo, you may need:

```bash
composer update arajcany/xmpie-uproduce-client --with-all-dependencies
```
