#!/bin/bash

set -e

# Check if brew is installed
if ! command -v brew >/dev/null 2>&1; then
  echo "Installing Homebrew for Linux..."

  # Install build tools (required for Linuxbrew)
  sudo apt-get update
  sudo apt-get install -y build-essential procps curl file git

  # Install Homebrew
  NONINTERACTIVE=1 /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

  # Add Homebrew to PATH
  echo 'eval "$(/home/linuxbrew/.linuxbrew/bin/brew shellenv)"' >> ~/.bashrc
  eval "$(/home/linuxbrew/.linuxbrew/bin/brew shellenv)"
fi

echo "Updating Homebrew..."
brew update

echo "Installing PHP..."
brew install php

echo "Installing MySQL..."
brew install mysql

echo "Starting MySQL..."
mysql.server start || echo "MySQL start may not be persistent without a proper init system."

echo "PHP version:"
php -v

echo "MySQL version:"
mysql --version

echo "All done!"
