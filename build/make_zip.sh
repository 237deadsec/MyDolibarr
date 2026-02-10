#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT_DIR"

rm -f dist/pressing-dolibarr21.zip dist/pressing-dolibarr21-htdocs.zip

# Format attendu par Dolibarr: dossier top-level pressing/
zip -r dist/pressing-dolibarr21.zip pressing -x '*/.gitkeep' >/dev/null

# Variante alternative: htdocs/pressing/
TMPDIR="$(mktemp -d)"
mkdir -p "$TMPDIR/htdocs"
cp -R pressing "$TMPDIR/htdocs/pressing"
(
  cd "$TMPDIR"
  zip -r "$ROOT_DIR/dist/pressing-dolibarr21-htdocs.zip" htdocs >/dev/null
)
rm -rf "$TMPDIR"

echo "ZIP générés:"
echo "- dist/pressing-dolibarr21.zip"
echo "- dist/pressing-dolibarr21-htdocs.zip"
