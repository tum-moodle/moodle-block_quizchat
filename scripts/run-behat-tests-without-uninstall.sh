#!/bin/bash

# Set the base directory for feature files
TEST_DIR="./blocks/quizchat/tests/behat"

# Get behat_dataroot from config.php (fix Windows path)
CFG_BEHAT_DATAROOT=$(php -r "define('CLI_SCRIPT', true); include 'config.php'; echo str_replace('\\\\', '/', \$CFG->behat_dataroot);")

# Construct path to behat.yml
BEHAT_YML="$CFG_BEHAT_DATAROOT/behat/behat.yml"

# Check if behat.yml exists
if [ ! -f "$BEHAT_YML" ]; then
    echo "Error: behat.yml not found at $BEHAT_YML"
    exit 1
fi

# Find all feature files excluding those with "uninstall" in the filename
FILES=$(find "$TEST_DIR" -type f -name "*.feature" ! -iname "uninstall*")

# Loop through and run each test file
for file in $FILES; do
    echo "Running: $file"
    vendor/bin/behat --config "$BEHAT_YML" "$(pwd)/$file" --profile=chrome
done
