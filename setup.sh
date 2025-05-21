#!/bin/bash

set -e

echo "Checking for Homebrew..."
if ! command -v brew >/dev/null 2>&1; then
  echo "Homebrew not found. Installing Homebrew..."
  /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
  echo 'eval "$(/opt/homebrew/bin/brew shellenv)"' >> ~/.zprofile
  eval "$(/opt/homebrew/bin/brew shellenv)"
else
  echo "Homebrew already installed."
fi

echo "Updating Homebrew..."
brew update

echo "Installing PHP..."
brew install php

echo "Installing MySQL..."
brew install mysql

echo "Starting MySQL service..."
brew services start mysql

echo "Verifying installations..."
php -v
mysql --version

echo "Setup complete."
