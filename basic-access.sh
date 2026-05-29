#!/bin/bash

# Check for existence of API key file:
if [ ! -f "./api-key.txt" ]; then
	echo "api-key.txt doesn't exist or is unreadable."
	echo "Make sure your API key is in this file."
	exit 1
fi

# Copy-Paste your API Key below:
#API_KEY=Test
API_KEY=$(cat api-key.txt)

# Make sure the URL matches your application.
URL=http://my-api-demo:60080/api
# Replace with your desired action below:
#ACTION=Validate
ACTION=Help

printf "GET:\n"
# Add the -v argument after curl to be verbose.
OUTPUT=`curl -d 'key1=val1&key2=val2&key3=val3' --request GET \
    --url "$URL/$ACTION" \
    --header "Authorization: Bearer $API_KEY"`
printf "\nGET Method - Output is:\n$OUTPUT\n\n"

printf "POST:\n"
OUTPUT=`curl -d 'key1=val1&key2=val2&key3=val3' --request POST \
    --url "$URL/$ACTION" \
    --header "authorization: Bearer $API_KEY"`
printf "\nPOST Method - Output is:\n$OUTPUT\n\n"

printf "PUT:\n"
OUTPUT=`curl -d 'key1=val1&key2=val2&key3=val3' --request PUT \
    --url "$URL/$ACTION" \
    --header "Authorization: Bearer $API_KEY"`
printf "\nPUT Method - Output is:\n$OUTPUT\n\n"

printf "PATCH:\n"
OUTPUT=`curl -d 'key1=val1&key2=val2&key3=val3' --request PATCH \
    --url "$URL/$ACTION" \
    --header "authorization: Bearer $API_KEY"`
printf "\nPATCH Method - Output is:\n$OUTPUT\n\n"

printf "DELETE:\n"
OUTPUT=`curl -d 'key1=val1&key2=val2&key3=val3' --request DELETE \
    --url "$URL/$ACTION" \
    --header "AuthorizatioN: Bearer $API_KEY"`
printf "\nDELETE Method - Output is:\n$OUTPUT\n\n"


