#!/bin/bash
set -euo pipefail

# Load .env from parent directory
ENV_FILE="../.env"
if [ ! -f "$ENV_FILE" ]; then
    echo "❌ .env not found!"
    exit 1
fi
set -a; source "$ENV_FILE"; set +a

# Config
CONTAINER="$DB_HOST"
DB_USER="$DB_USERNAME"
DB_NAME="$DB_DATABASE"
DB_PASS="$DB_PASSWORD"
REMOTE="encompos-personal"
GDRIVE_FOLDER="Databases/ENCOMPOS-HMHC"

# Find latest backup
echo "→ Fetching latest backup from Google Drive..."
LATEST_BACKUP=$(rclone lsf "$REMOTE:$GDRIVE_FOLDER" --files-only | sort | tail -n 1)
if [[ -z "$LATEST_BACKUP" ]]; then
    echo "❌ No backup found!"
    exit 1
fi
echo "→ Latest backup: $LATEST_BACKUP"

# Confirm restore
read -p "⚠️ Restore '$DB_NAME' from '$LATEST_BACKUP'? (yes/no): " CONFIRM
if [[ "$CONFIRM" != "yes" ]]; then
    echo "Restore cancelled."
    exit 0
fi

# Download backup
TMP_FILE="/tmp/$LATEST_BACKUP"
rclone copy "$REMOTE:$GDRIVE_FOLDER/$LATEST_BACKUP" "/tmp/"

# Restore
cat "$TMP_FILE" | docker exec -i "$CONTAINER" mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME"

rm "$TMP_FILE"
echo "✓ Restore completed successfully."
