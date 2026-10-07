#!/usr/bin/env bash
# Turns the template into a real plugin: replaces the placeholders, renames the files
# that carry the slug and deletes itself.
#
#   bash bin/init.sh "Content Relations" content-relations ContentRelations
#
# 1. plugin name as shown in wp-admin and on wordpress.org
# 2. wordpress.org slug - also text domain, REST namespace and, with "-" turned into
#    "_", the prefix of options, hooks and public functions
# 3. PHP namespace segment after Palasthotel\WordPress\
set -euo pipefail

if [ $# -ne 3 ]; then
	sed -n '2,11p' "$0" | sed 's/^# \{0,1\}//'
	exit 1
fi

NAME=$1
SLUG=$2
NAMESPACE=$3
PREFIX=${SLUG//-/_}

[[ $SLUG =~ ^[a-z0-9]+(-[a-z0-9]+)*$ ]] || { echo "slug must be lowercase letters, digits and dashes: $SLUG" >&2; exit 1; }
[[ $NAMESPACE =~ ^[A-Z][A-Za-z0-9]*$ ]] || { echo "namespace must be one PascalCase segment: $NAMESPACE" >&2; exit 1; }
[[ $NAME != *$'\n'* && -n $NAME ]] || { echo "name must be a single line" >&2; exit 1; }

cd "$(dirname "$0")/.."

# The values reach perl through the environment and \Q...\E, so nothing in them is
# read as a regular expression or as perl code.
export NAME SLUG NAMESPACE PREFIX
files=()
while IFS= read -r -d '' f; do
	files+=("$f")
done < <(find . -path ./.git -prune -o -path ./node_modules -prune -o -type f \
	! -name LICENSE ! -name CODE_OF_CONDUCT.md ! -name '*.mo' ! -path ./bin/init.sh -print0)

perl -pi -e '
	s/\QMy Plugin\E/$ENV{NAME}/g;
	s/\Qmy-plugin\E/$ENV{SLUG}/g;
	s/\Qmy_plugin\E/$ENV{PREFIX}/g;
	s/\QMY_PLUGIN\E/\U$ENV{PREFIX}\E/g;
	s/\QMyPlugin\E/$ENV{NAMESPACE}/g;
' "${files[@]}"

while IFS= read -r -d '' f; do
	git mv "$f" "${f//my-plugin/$SLUG}" 2>/dev/null || mv "$f" "${f//my-plugin/$SLUG}"
done < <(find public -name '*my-plugin*' -print0)

if command -v msgfmt >/dev/null; then
	msgfmt -o "public/languages/$SLUG-de_DE.mo" "public/languages/$SLUG-de_DE.po"
else
	echo "msgfmt not found - recompile public/languages/$SLUG-de_DE.mo before the first release" >&2
fi

# The template's own instructions in README.md
perl -0pi -e 's/<!-- template:start -->.*?<!-- template:end -->\n*//s' README.md

rm -- "$0"
rmdir bin 2>/dev/null || true

echo "Done. Next: review the diff, then work through 'After init' in CONTRIBUTING.md."
