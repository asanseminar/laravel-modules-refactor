#!/bin/sh

# Copy Refactor command directory
rm -r "~/backups/Refactor"
cp -r "~/workspace/project/app/Console/Commands/Refactor" "~/backups"

# Clean changes
git restore --staged .
git checkout -- .
git clean -fd

# Write Refactor command content back
rm -r "~/workspace/project/app/Console/Commands/Refactor"
cp -r "~/backups/Refactor" "~/workspace/project/app/Console/Commands/"
