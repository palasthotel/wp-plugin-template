# CI/CD Workflows

The four workflows in `.github/workflows/` call the shared ones in
[palasthotel/github-workflows](https://github.com/palasthotel/github-workflows). How
they work, every input and what to do when a deploy fails is described there, in
[docs/wp-plugin.md](https://github.com/palasthotel/github-workflows/blob/main/docs/wp-plugin.md).

What is specific to this plugin:

| | |
|---|---|
| wordpress.org slug | `my-plugin` - in `pr.yml` and `wordpress-svn-release.yml`, check it with `svn ls https://plugins.svn.wordpress.org/my-plugin/` |
| version file | `package.json` (`release-type: node`) - keep it, release-please and the scripts read the version there |
| build step | none |
| required files | `assets/admin.js`, `assets/admin.css` and the translations - everything `Assets.php` enqueues |
