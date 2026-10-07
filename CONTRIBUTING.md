# Contributing

## Branching

`main` is the default branch and always reflects what is released (or about to be
released). Work on a feature branch and open a pull request against `main`.

## Commit messages

Releases and the changelog are generated from the commit history, so commit messages
follow [Conventional Commits](https://www.conventionalcommits.org/):

```
<type>[optional scope][!]: <description>

[optional body]

[optional footer]
```

| Type | Effect on the version | Appears in changelog |
|---|---|---|
| `fix:` | patch (1.0.0 → 1.0.1) | yes, "Bug Fixes" |
| `feat:` | minor (1.0.0 → 1.1.0) | yes, "Features" |
| `feat!:` or `BREAKING CHANGE:` footer | major (1.0.0 → 2.0.0) | yes, highlighted |
| `docs:`, `refactor:`, `chore:`, `deps:`, `style:`, `test:`, `ci:` | none | no |

A pull request that should trigger a release needs at least one `fix:` or `feat:`
commit. When squash-merging, make sure the squash commit message itself is a
conventional commit — that is the message release-please reads.

### Which changes get `fix:` or `feat:`

Only changes that matter to someone using the plugin. `fix:` and `feat:` decide the
version *and* write the line that ends up in the changelog on the wordpress.org plugin
page, so the question to ask before committing is whether a user of the plugin would care
about that line.

Everything else takes a type that releases nothing — workflows and CI, release tooling,
repository documentation, internal refactoring, and anything touching files that are not
shipped. As a rule of thumb, a change confined to files outside `public/` is almost never
a `fix:`.

`docs:` and `ci:` commits may go straight to `main` with a normal push. Never force-push
`main`: it cuts release-please off from the history it has already released.

## Repository layout

`public/` is exactly what ships to WordPress.org. Everything outside it is
repository-only.

| Path | Description |
|---|---|
| `public/plugin.php` | main file: plugin header, autoloader, the `Plugin` class |
| `public/classes/` | one class per feature, PSR-4 under `Palasthotel\WordPress\MyPlugin` |
| `public/classes/Components/` | base classes, see below - do not put features here |
| `public/public-functions.php` | the API for themes and other plugins |
| `public/templates/` | default templates, overridable in the theme under `plugin-parts/` |
| `public/assets/` | scripts and styles, plain JavaScript and CSS |
| `public/languages/` | translations; `my-plugin.pot` is generated with `wp i18n make-pot` |
| `public/uninstall.php` | removes everything the plugin stored when it is deleted - only from the payload: WordPress looks for it next to the main file, so the development wrapper never runs it |
| `public/readme.txt` | the wordpress.org listing |
| `plugin.php` | development wrapper, loads `public/`; never deployed |

The main file `public/plugin.php` must keep its name once the plugin is published.
WordPress identifies an installed plugin by `<directory>/<main file>` and stores that pair
in `active_plugins`; renaming it deactivates the plugin on every site at the next update.

## Class structure

`Plugin` is a singleton (`Plugin::instance()`) and the only place where components are
created, in `onCreate()`. It holds the constants for hook names, option names, asset
handles and template names, so every string that is part of the plugin's contract is
defined once.

Every feature is a class in `classes/` that extends `Components\Component`. Its
constructor stores the plugin in `$this->plugin` and calls `onCreate()`, which is where
the component registers its hooks. Components reach each other through the plugin, e.g.
`$this->plugin->database`.

| Class | Extends | Example of |
|---|---|---|
| `Assets` | `Component` | registering scripts and styles, translated strings for JS |
| `Settings` | `Component` | a settings page with the Settings API and a sanitize callback |
| `REST` | `Component` | REST routes with a capability check per route |
| `Templates` | `Component` | theme-overridable templates |
| `Database` | `Components\Database` | an own table |
| `Update` | `Components\Update` | data migrations between versions |

Delete what the plugin does not need, together with its line in `Plugin::onCreate()`, its
constants and its part of `uninstall.php`.

`classes/Components/` holds the base classes from
[palasthotel/wp-components](https://github.com/palasthotel/wp-components), copied into the
plugin's namespace on purpose: as a shared composer dependency, several plugins on one
site would load whichever version came first. Each file carries a `@version`; update it by
copying the newer file and changing the namespace again.

Activation: `Plugin::onSiteActivation()` runs for every site when the plugin is activated
network wide. An update through wordpress.org does **not** activate the plugin again, so
anything an update needs (new tables, migrated options) goes into `Update` as
`update_<n>()` and `DATA_VERSION` is raised.

### Conventions

- Escape at the point of output (`esc_html`, `esc_attr`, `esc_url`), sanitize every input,
  `$wpdb->prepare()` for every query with a variable.
- Every REST route has a `permission_callback` that checks a capability. Every form and
  admin-ajax action checks a nonce in addition to the capability.
- No jQuery. Plain JavaScript as long as it is a few files, TypeScript with a build
  beyond that.
- No `@wordpress/i18n` in scripts: translate in PHP and pass the strings with
  `wp_localize_script` (see `Assets::enqueue_admin()`).
- No Sass: plain CSS, PostCSS if a build is needed anyway.

## Adding a JavaScript build

Once the plugin needs Gutenberg components or TypeScript:

1. `npm install --save-dev @wordpress/scripts typescript` — every npm package goes into
   `devDependencies`; only the compiled bundle ships, and the `@wordpress/*` packages are
   provided by WordPress at runtime.
2. Sources in `src/`, output to `public/dist/`:
   `"build": "wp-scripts build src/editor.ts --output-path=public/dist"`, plus a
   `tsconfig.json` and `"lint": "tsc --noEmit"` — without them nothing checks the types.
3. `/public/dist` into `.gitignore`. Compiled files are never committed; the pipeline
   builds them.
4. In `pr.yml` and `wordpress-svn-release.yml` set
   `build-command: npm ci && npm run build`, and add the bundle (`dist/editor.js`) to
   `required-files` in `pr.yml`.
5. Add `npm` to `.github/dependabot.yml`.

`Components\Assets::registerScript()` reads `dist/<name>.asset.php`, so the script
dependencies the build extracts are registered automatically.

## Local setup

There is nothing to build or install. wp-env runs without a configuration file and
mounts the repository as the plugin:

```sh
WP_ENV_PORT=8892 WP_ENV_TESTS_PORT=8893 npx @wordpress/env start   # admin / password
```

Regenerate the translation template after changing strings, then update the `.po`
files and compile them:

```sh
wp i18n make-pot public public/languages/my-plugin.pot --slug=my-plugin --domain=my-plugin
msgmerge --update public/languages/my-plugin-de_DE.po public/languages/my-plugin.pot
msgfmt -o public/languages/my-plugin-de_DE.mo public/languages/my-plugin-de_DE.po
```

`npm run pack` stages the payload in `build/my-plugin/` and zips it to `my-plugin.zip`
— the same payload the release deploys. It runs the shared script from
[palasthotel/github-workflows](https://github.com/palasthotel/github-workflows), which has
to be checked out next to this repository.

## Versions

Never edit version numbers by hand. `package.json`, `CHANGELOG.md`, `public/plugin.php`
and the `Stable tag:` in `public/readme.txt` are all maintained by the release pipeline —
see [.github/WORKFLOWS.md](.github/WORKFLOWS.md).

`package.json` has to stay even without dependencies: release-please and the shared
release scripts read the version from it.

Content changes to `public/readme.txt` (description, FAQ, tested-up-to) are of course done
by hand; just leave `Stable tag:` and the `== Changelog ==` entries alone.

## Checks

Every PR runs `php -l` against PHP 8.1 to 8.4, packs the plugin, checks the payload and
the required files, and checks the version carriers agree.

## After init

Once, when a new repository is created from the template:

- [ ] `public/readme.txt`: description, tags, FAQ, `Contributors:` — `palasthotel` and
      `janaeggebrecht` stay, add further wordpress.org user names
- [ ] `Requires at least` / `Requires PHP` in the plugin header, `readme.txt` and
      `php-versions` in `pr.yml` agree
- [ ] `.release-please-manifest.json`, `package.json`, header and `Stable tag:` carry the
      same version; for a plugin that already exists on wordpress.org the current one, and
      the main file named as in its SVN trunk
- [ ] GitHub *About*: description, website `https://wordpress.org/plugins/my-plugin/`,
      topics `wordpress`, `wordpress-plugin`, include *Releases* on the home page
- [ ] Settings > General: default branch `main`
- [ ] Branch protection for `main` (as in wp-process-log): merges only via PR by
      `palasthotel/developers`, direct pushes only by `palasthotel/tavds`
- [ ] Settings > Advanced Security: enable everything, including private vulnerability
      reporting (see [SECURITY.md](SECURITY.md))
- [ ] Give the *Palasthotel Release Bot* app access to the repository, and make the
      organization secrets `RELEASE_BOT_PRIVATE_KEY`, `SVN_USERNAME`, `SVN_PASSWORD`
      available to it
- [ ] Do **not** enable release immutability: the deploy attaches the zip to the release
      after release-please has published it
- [ ] First release: a new plugin has to pass the wordpress.org review before the SVN
      repository exists; submit the zip from `npm run pack` there first
