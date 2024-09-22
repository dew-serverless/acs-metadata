#!/usr/bin/env bash

# Check if endpoint is set, otherwise default to international
BASE_URL=${BASE_URL:-"https://api.alibabacloud.com"}

# Check if language is set, otherwise default to "EN_US"
LANGUAGE=${LANGUAGE:-"EN_US"}

# Group metadata files by language, in lowercase
BASE_DIRECTORY=$(echo "${LANGUAGE}" | tr "[:upper:]" "[:lower:]")
PRODUCT_FILE="${BASE_DIRECTORY}/product.json"

# Speed up the update process
CONCURRENCY=${CONCURRENCY:-8}
counter=0

# Let's get started
echo "[-] Update metadata with language ${LANGUAGE}"

# Reset the directory for legacy product removal
rm -rf "${BASE_DIRECTORY}" && mkdir -p "${BASE_DIRECTORY}"

# Update product list
echo "[-] Updating product list"
curl -s "${BASE_URL}/meta/v1/products.json?language=${LANGUAGE}" -o "$PRODUCT_FILE"

# Update individual APIs
jq -c '.[]' "$PRODUCT_FILE" | while read -r product; do
  # Extract the product code
  code=$(echo "$product" | jq -r '.code')
  code_lowercase=$(echo "$code" | tr "[:upper:]" "[:lower:]")

  # Loop through each version for the current product
  for version in $(echo "$product" | jq -r '.versions[]'); do
    api_docs_url="${BASE_URL}/meta/v1/products/${code}/versions/${version}/api-docs.json?language=${LANGUAGE}"

    output_directory="${BASE_DIRECTORY}/${code_lowercase}/${version}"
    output_file="${output_directory}/api-docs.json"

    # Create the directory if it doesn't exist
    mkdir -p "$output_directory"

    echo "[-] Updating ${code} ${version}"
    curl -s "${api_docs_url}" -o "$output_file" &

    # Increment the counter
    ((counter++))

    # If the counter reaches the maximum number of jobs, wait for them
    # to finish before continuing
    if ((counter >= CONCURRENCY)); then
      wait
      counter=0
    fi
  done
done

# Wait for all background processes to finish before exiting
wait
echo "[-] Update done"
