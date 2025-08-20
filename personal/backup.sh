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
REMOTE="hmhc"
GDRIVE_FOLDER="Databases/ENCOMPOS-HMHC"
KEEP_LAST=7

# Timestamp
TIMESTAMP=$(date +"_%Y%m%d_%H%M%S")
BACKUP_FILE="${DB_NAME}${TIMESTAMP}.sql"

echo "→ Creating backup from container $CONTAINER..."
docker exec -i "$CONTAINER" mysqldump -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" > "/tmp/$BACKUP_FILE"

echo "→ Uploading backup to Google Drive..."
rclone copy "/tmp/$BACKUP_FILE" "$REMOTE:$GDRIVE_FOLDER"

# Cleanup old backups
BACKUPS=$(rclone lsf "$REMOTE:$GDRIVE_FOLDER" --files-only | sort)
TOTAL=$(echo "$BACKUPS" | wc -l)
if [ "$TOTAL" -gt "$KEEP_LAST" ]; then
    REMOVE=$(echo "$BACKUPS" | head -n $(($TOTAL - $KEEP_LAST)))
    for f in $REMOVE; do
        echo "→ Removing old backup: $f"
        rclone delete "$REMOTE:$GDRIVE_FOLDER/$f"
    done
fi

rm "/tmp/$BACKUP_FILE"
echo "✓ Backup completed successfully."
