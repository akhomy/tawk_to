#!/bin/bash

set -e $DRUPAL_TI_DEBUG

# Ensure the right Drupal version is installed.
# Note: This function is re-entrant.
drupal_ti_ensure_drupal

echo "codesniffer standard Drupal"
$DRUPAL_TI_DRUPAL_DIR/vendor/bin/phpcs --report=full --warning-severity=0 --standard=Drupal "$DRUPAL_TI_DRUPAL_DIR/modules/$DRUPAL_TI_MODULE_NAME" || false
echo "codesniffer standard DrupalPractice"
$DRUPAL_TI_DRUPAL_DIR/vendor/bin/phpcs --report=full --standard=DrupalPractice "$DRUPAL_TI_DRUPAL_DIR/modules/$DRUPAL_TI_MODULE_NAME" || false
