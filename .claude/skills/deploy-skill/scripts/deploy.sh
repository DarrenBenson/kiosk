#!/bin/bash
#
# Kiosk Deployment Script
# Deploys files to webserver1 via jumpbox using two-hop SSH
#

set -e

REMOTE_BASE="/DockerData/docker/WebSites/kiosk"
JUMPBOX="jumpbox"
TARGET="webserver1"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

usage() {
    echo "Usage: $0 [options] [files...]"
    echo ""
    echo "Options:"
    echo "  -a, --all       Deploy all project files"
    echo "  -b, --backup    Create backup on server before deploying"
    echo "  -g, --guid ID   Deploy to /kiosk/ID for testing"
    echo "  -h, --help      Show this help message"
    echo ""
    echo "Examples:"
    echo "  $0 api/bins.php                  # Deploy single file"
    echo "  $0 api/bins.php scripts/bins.js  # Deploy multiple files"
    echo "  $0 -b -a                         # Backup then deploy all"
    echo "  $0 -g abc123 -a                  # Deploy all to test GUID"
}

# SSH command wrapper
ssh_cmd() {
    ssh "$JUMPBOX" "source ~/.ssh/agent.env && ssh -A $TARGET \"$1\""
}

# Transfer a single file
transfer_file() {
    local src="$1"
    local dest="$2"

    if [[ ! -f "$src" ]]; then
        echo -e "${RED}Error: File not found: $src${NC}"
        return 1
    fi

    # Ensure remote directory exists
    local remote_dir=$(dirname "$dest")
    ssh_cmd "mkdir -p \"$remote_dir\""

    echo -e "${YELLOW}Transferring:${NC} $src -> $dest"
    cat "$src" | ssh "$JUMPBOX" "source ~/.ssh/agent.env && ssh $TARGET 'cat > \"$dest\"'"
}

# Create backup on server
create_backup() {
    local backup_name="kiosk-backup-$(date +%Y%m%d-%H%M%S)"
    echo -e "${YELLOW}Creating backup:${NC} $backup_name"
    ssh_cmd "mkdir -p /DockerData/docker/WebSites/backups && cp -r $REMOTE_BASE /DockerData/docker/WebSites/backups/$backup_name"
    echo -e "${GREEN}Backup created:${NC} /DockerData/docker/WebSites/backups/$backup_name"
}

# Deploy all files
deploy_all() {
    echo -e "${YELLOW}Deploying all project files...${NC}"

    # Ensure remote cache directory exists with proper permissions
    ssh_cmd "mkdir -p $REMOTE_BASE/cache && chmod 777 $REMOTE_BASE/cache"

    # Deploy all web files (excluding dev files)
    echo -e "${YELLOW}Deploying files...${NC}"
    tar -cf - \
        --exclude='.git' \
        --exclude='.gitignore' \
        --exclude='.claude' \
        --exclude='deploy.sh' \
        --exclude='CLAUDE.md' \
        --exclude='README.md' \
        --exclude='LICENSE.md' \
        --exclude='config.example.php' \
        --exclude='cache' \
        . | ssh "$JUMPBOX" "source ~/.ssh/agent.env && ssh $TARGET 'tar -xf - -C \"$REMOTE_BASE\"'"

    # Fix config.php permissions (may be restrictive from local)
    ssh_cmd "chmod 644 $REMOTE_BASE/config.php 2>/dev/null || true"

    echo -e "${GREEN}All files deployed successfully${NC}"
}

# Parse arguments
BACKUP=false
DEPLOY_ALL=false
GUID=""
FILES=()

while [[ $# -gt 0 ]]; do
    case $1 in
        -a|--all)
            DEPLOY_ALL=true
            shift
            ;;
        -b|--backup)
            BACKUP=true
            shift
            ;;
        -g|--guid)
            shift
            if [[ $# -gt 0 && ! "$1" =~ ^- ]]; then
                GUID="$1"
                shift
            else
                GUID=$(uuidgen)
            fi
            REMOTE_BASE="/DockerData/docker/WebSites/kiosk/$GUID"
            ;;
        -h|--help)
            usage
            exit 0
            ;;
        -*)
            echo -e "${RED}Unknown option: $1${NC}"
            usage
            exit 1
            ;;
        *)
            FILES+=("$1")
            shift
            ;;
    esac
done

# Validate arguments
if [[ "$DEPLOY_ALL" == false && ${#FILES[@]} -eq 0 ]]; then
    echo -e "${RED}Error: No files specified and --all not set${NC}"
    usage
    exit 1
fi

# Show target
if [[ -n "$GUID" ]]; then
    echo -e "${YELLOW}Target:${NC} TEST at https://kiosk.deskpoint.com/$GUID/"
else
    echo -e "${YELLOW}Target:${NC} PRODUCTION ($REMOTE_BASE)"
fi

# Create backup if requested
if [[ "$BACKUP" == true ]]; then
    create_backup
fi

# Deploy
if [[ "$DEPLOY_ALL" == true ]]; then
    deploy_all
else
    for file in "${FILES[@]}"; do
        dest="$REMOTE_BASE/$file"
        transfer_file "$file" "$dest"
    done
    echo -e "${GREEN}Deployment complete${NC}"
fi

# Show cleanup command for GUID deployments
if [[ -n "$GUID" ]]; then
    echo ""
    echo -e "${YELLOW}To delete test deployment:${NC}"
    echo "ssh jumpbox \"source ~/.ssh/agent.env && ssh webserver1 'rm -rf $REMOTE_BASE'\""
fi
