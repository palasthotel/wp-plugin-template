<!-- template:start -->
# Palasthotel WordPress plugin template

Starting point for a Palasthotel WordPress plugin that is released to wordpress.org: the
`public/` payload, the component class structure known from BlockX, release-please and
the shared SVN deploy from
[palasthotel/github-workflows](https://github.com/palasthotel/github-workflows).

## Using it

1. On GitHub: **Use this template** → repository `palasthotel/wp-<slug>`, where `<slug>`
   is exactly the wordpress.org slug. Clone it next to `github-workflows`.
2. Replace the placeholders:

   ```sh
   bash bin/init.sh "Content Relations" content-relations ContentRelations
   ```

   | Placeholder | Becomes | Used for |
   |---|---|---|
   | `My Plugin` | 1st argument | plugin name, readme title, settings page |
   | `my-plugin` | 2nd argument | wordpress.org slug, text domain, REST namespace, file names, repo URL |
   | `my_plugin` | slug with `_` | options, hooks, public functions, table name |
   | `MyPlugin` | 3rd argument | `Palasthotel\WordPress\<MyPlugin>`, the JS global |

   The script deletes itself and this section.
3. Delete the example components you do not need and work through *After init* in
   [CONTRIBUTING.md](CONTRIBUTING.md).

Everything below this section is the README of the plugin built from the template.
<!-- template:end -->
# My Plugin (WordPress-Plugin)

One sentence on what the plugin does. It is available on
[WordPress.org](https://wordpress.org/plugins/my-plugin/).

## Why

The problem the plugin solves, and why a plugin is the right place for it.

## How it works

What the plugin stores, where it hooks in and what an administrator sees.

| Hook | Type | Purpose |
|---|---|---|
| `my_plugin_add_templates_paths` | filter | additional directories searched for templates, after the theme |

| Function | Purpose |
|---|---|
| `my_plugin_plugin()` | the plugin instance |
| `my_plugin_render_example( $args )` | renders `templates/my-plugin-example.php` |

Templates can be overridden in the theme under `plugin-parts/`.

Deleting the plugin (`uninstall.php`) removes its options and its table, on every site of
a network.

## Repository layout

`public/` is exactly what ships to wordpress.org; everything else is repository-only.
`plugin.php` in the root is a development wrapper that loads `public/`, so the whole
repository can be symlinked into `wp-content/plugins` during development.

Releases are cut by release-please from conventional commits and deployed to the
wordpress.org SVN by GitHub Actions — see [.github/WORKFLOWS.md](.github/WORKFLOWS.md).
Contribution rules, the class structure and the local setup are in
[CONTRIBUTING.md](CONTRIBUTING.md).

## License

GPL-3.0-or-later, see [LICENSE](LICENSE).
