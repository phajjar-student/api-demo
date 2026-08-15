#!/bin/bash

# This file works similar to quotes-examples.sh, but it's designed for the CI.  We get the HTTP codes in addition to the output.

# Check for existence of API key file:
if [ ! -f "./api-key.txt" ]; then
	echo "api-key.txt doesn't exist or is unreadable."
	echo "Make sure your API key is in this file."
	exit 1
fi

# Copy-Paste your API Key below:
#API_KEY=Test
# Or, read it from a file.
API_KEY=$(cat api-key.txt)

# An easy way to display a line.  
LINE=________________________________________________________________________________

# Make sure the URL matches your application.
URL=http://my-api-demo:60080/api
# Applies to quotes only:
ACTION=Quotes

rm -f response.txt
TEST_NAME="POST - Create a new quote:"
printf "$s\n" "$TEST_NAME"
HTTP_CODE=`curl -s -o response.txt -w "%{http_code}" -d 'quote_text=Diplomacy%20is%20the%20art%20of%20saying%20%27Nice%20doggie%27%20until%20you%20can%20find%20a%20rock.&quote_author=Will%20Rogers' --request POST \
    --url "$URL/$ACTION" \
    --header "authorization: Bearer $API_KEY"`
TEST_RESULT="PASS"
# Note that the valid HTTP response codes are only for this application.
if [[ ! "$HTTP_CODE" =~ ^2 ]]; then
	TEST_RESULT="FAILED"
fi
printf "\n%s\n\tHTTP Code is: %s\n\tOutput is %s\n\tTest Result is: %s\n%s\n\n" "$TEST_NAME" "$HTTP_CODE" "`cat response.txt`" "$TEST_RESULT" "$LINE"
if [[ "$TEST_RESULT" = "FAILED" ]]; then
	exit 1
fi



rm -f response.txt
TEST_NAME="POST - Create a second new quote:"
printf "$s\n" "$TEST_NAME"
HTTP_CODE=`curl -s -o response.txt -w"%{http_code}" -d 'quote_text=I%20hated%20every%20minute%20of%20training%2C%20but%20I%20said%2C%20%27Don%27t%20quit.%20Suffer%20now%20and%20live%20the%20rest%20of%20your%20life%20as%20a%20champion%27.&quote_author=Muhammad%20Ali' --request POST \
    --url "$URL/$ACTION" \
    --header "Authorization: Bearer $API_KEY"`
TEST_RESULT="PASS"
# Note that the valid HTTP response codes are only for this application.
if [[ ! "$HTTP_CODE" =~ ^2 ]]; then
        TEST_RESULT="FAILED"
fi
printf "\n%s\n\tHTTP Code is: %s\n\tOutput is %s\n\tTest Result is: %s\n%s\n\n" "$TEST_NAME" "$HTTP_CODE" "`cat response.txt`" "$TEST_RESULT" "$LINE"
if [[ "$TEST_RESULT" = "FAILED" ]]; then
        exit 1
fi



# Note that for this one, we want an error code.
rm -f response.txt
TEST_NAME="POST - try to create a quote that is too long (we want an error code here):"
HTTP_CODE=`curl -s -o response.txt -w"%{http_code}" -d 'quote_author=Nobody&quote_text=Lorem%20ipsum%20dolor%20sit%20amet%2C%20consectetur%20adipiscing%20elit.%20Donec%20ac%20ornare%20ante.%20Aliquam%20pellentesque%20eros%20id%20urna%20posuere%20ultricies.%20Nullam%20condimentum%20efficitur%20sollicitudin.%20Mauris%20pulvinar%20rutrum%20nibh%2C%20in%20dignissim%20enim%20euismod%20eget.%20Donec%20sed%20risus%20in%20orci%20vehicula%20suscipit%20non%20faucibus%20enim.%20Mauris%20vel%20malesuada%20enim%2C%20quis%20molestie%20massa.%20Nam%20odio%20enim%2C%20facilisis%20et%20auctor%20vitae%2C%20egestas%20at%20neque.%20Etiam%20vel%20nibh%20convallis%2C%20efficitur%20arcu%20nec%2C%20convallis%20metus.%20Fusce%20sem%20sapien%2C%20fermentum%20quis%20faucibus%20sit%20amet%2C%20tempor%20et%20mauris.%0A%0AIn%20maximus%20fermentum%20nisl%2C%20consectetur%20fringilla%20neque%20ultrices%20eget.%20Fusce%20in%20justo%20eu%20nisl%20consectetur%20posuere.%20Morbi%20iaculis%20auctor%20diam%2C%20ac%20semper%20sem%20porta%20et.%20Etiam%20eu%20quam%20nec%20metus%20tincidunt%20posuere.%20Sed%20fermentum%20luctus%20posuere.%20Sed%20ut%20enim%20at%20orci%20luctus%20feugiat%20vel%20et%20turpis.%20Aenean%20vel%20aliquam%20lectus.%20Fusce%20quis%20justo%20id%20lectus%20pharetra%20facilisis%20non%20sed%20ex.%20Sed%20viverra%20fringilla%20arcu%2C%20ut%20commodo%20eros.%20Etiam%20quis%20molestie%20neque.%20Donec%20non%20aliquam.' --request POST \
    --url "$URL/$ACTION" \
    --header "Authorization: Bearer $API_KEY"`
TEST_RESULT="FAILED"
# Note that the valid HTTP response codes are only for this application.
if [[ "$HTTP_CODE" = "431" ]]; then
        TEST_RESULT="PASS"
fi
printf "\n%s\n\tHTTP Code is: %s\n\tOutput is %s\n\tTest Result is: %s\n%s\n\n" "$TEST_NAME" "$HTTP_CODE" "`cat response.txt`" "$TEST_RESULT" "$LINE"
if [[ "$TEST_RESULT" = "FAILED" ]]; then
        exit 1
fi



rm -f response.txt
TEST_NAME="GET - Try to read a non-numeric id (we want an error code here):"
HTTP_CODE=`curl -s -o response.txt -w"%{http_code}" -G -d 'id=abc' --request GET --url "$URL/$ACTION" \
    --header "aUthorization: Bearer $API_KEY"`
TEST_RESULT="FAILED"
# Note that the valid HTTP response codes are only for this application.
if [[ "$HTTP_CODE" = "400" ]]; then
        TEST_RESULT="PASS"
fi
printf "\n%s\n\tHTTP Code is: %s\n\tOutput is %s\n\tTest Result is: %s\n%s\n\n" "$TEST_NAME" "$HTTP_CODE" "`cat response.txt`" "$TEST_RESULT" "$LINE"
if [[ "$TEST_RESULT" = "FAILED" ]]; then
        exit 1
fi



rm -f response.txt
TEST_NAME="GET - Try to read an id of 0 or a negative number (we want an error code here):"
HTTP_CODE=`curl -s -o response.txt -w"%{http_code}" -G -d 'id=0' --request GET --url "$URL/$ACTION" \
    --header "aUthorization: Bearer $API_KEY"`
TEST_RESULT="FAILED"
# Note that the valid HTTP response codes are only for this application.
if [[ "$HTTP_CODE" = "400" ]]; then
        TEST_RESULT="PASS"
fi
printf "\n%s\n\tHTTP Code is: %s\n\tOutput is %s\n\tTest Result is: %s\n%s\n\n" "$TEST_NAME" "$HTTP_CODE" "`cat response.txt`" "$TEST_RESULT" "$LINE"
if [[ "$TEST_RESULT" = "FAILED" ]]; then
        exit 1
fi



rm -f response.txt
TEST_NAME="GET - Read a random quote (JSON response must be parsed with jq):"
HTTP_CODE=`curl -s -o response.txt -w"%{http_code}" --request GET \
    --url "$URL/$ACTION" \
    --header "Authorization: Bearer $API_KEY"`
# Read the ID into a variable using jq (install if needed):
QUOTE_ID=`cat response.txt | jq -r '.id'`
TEST_RESULT="PASS"
# Note that the valid HTTP response codes are only for this application.
if [[ ! "$HTTP_CODE" =~ ^2 ]]; then
        TEST_RESULT="FAILED"
fi
printf "\n%s\n\tHTTP Code is: %s\n\tOutput is %s\n\tQuote ID is: %s\n\tTest Result is: %s\n%s\n\n" "$TEST_NAME" "$HTTP_CODE" "`cat response.txt`" "$QUOTE_ID" "$TEST_RESULT" "$LINE"
if [[ "$TEST_RESULT" = "FAILED" ]]; then
        exit 1
fi



rm -f response.txt
TEST_NAME="GET - Read a specific quote:"
HTTP_CODE=`curl -s -o response.txt -w"%{http_code}" -G -d 'id=1' --request GET --url "$URL/$ACTION" \
    --header "aUthorization: Bearer $API_KEY"`
TEST_RESULT="PASS"
# Note that the valid HTTP response codes are only for this application.
if [[ ! "$HTTP_CODE" =~ ^2 ]]; then
        TEST_RESULT="FAILED"
fi
printf "\n%s\n\tHTTP Code is: %s\n\tOutput is %s\n\tTest Result is: %s\n%s\n\n" "$TEST_NAME" "$HTTP_CODE" "`cat response.txt`" "$TEST_RESULT" "$LINE"
if [[ "$TEST_RESULT" = "FAILED" ]]; then
        exit 1
fi  



rm -f response.txt
TEST_NAME="GET - Read a non-existent quote (we want an error code here):"
HTTP_CODE=`curl -s -o response.txt -w"%{http_code}" -G -d 'id=999999999999' --request GET --url "$URL/$ACTION" \
    --header "aUthorization: Bearer $API_KEY"`
TEST_RESULT="FAILED"
# Note that the valid HTTP response codes are only for this application.
if [[ "$HTTP_CODE" = "500" ]]; then
        TEST_RESULT="PASS"
fi
printf "\n%s\n\tHTTP Code is: %s\n\tOutput is %s\n\tTest Result is: %s\n%s\n\n" "$TEST_NAME" "$HTTP_CODE" "`cat response.txt`" "$TEST_RESULT" "$LINE"
if [[ "$TEST_RESULT" = "FAILED" ]]; then
        exit 1
fi



rm -f response.txt
TEST_NAME="POST - Create a misquote:"
HTTP_CODE=`curl -s -o response.txt -w"%{http_code}" -d 'quote_text=If%20it%27s%20on%20the%20Internet%20then%20it%20must%20be%20true%21&quote_author=George%20Washington' --request POST \
    --url "$URL/$ACTION" \
    --header "authorization: Bearer $API_KEY"`
QUOTE_ID=`cat response.txt | jq -r '.id'`
if [[ ! "$HTTP_CODE" =~ ^2 ]]; then
        TEST_RESULT="FAILED"
fi
printf "\n%s\n\tHTTP Code is: %s\n\tOutput is %s\n\tQuote ID is: %s\n\tTest Result is: %s\n%s\n\n" "$TEST_NAME" "$HTTP_CODE" "`cat response.txt`" "$QUOTE_ID" "$TEST_RESULT" "$LINE"
if [[ "$TEST_RESULT" = "FAILED" ]]; then
        exit 1
fi



# Make sure QUOTE_ID does NOT change from previous test!
rm -f response.txt
TEST_NAME="PATCH - Update a quote (same quote id as previous test):"
HTTP_CODE=`curl -s -o response.txt -w"%{http_code}" --request PATCH \
    --url "$URL/$ACTION?id=$QUOTE_ID&quote_author=Abraham%20Lincoln&quote_text=The%20best%20way%20to%20destroy%20an%20enemy%20is%20to%20make%20him%20a%20friend." \
    --header "authorization: Bearer $API_KEY"`
if [[ ! "$HTTP_CODE" =~ ^2 ]]; then
        TEST_RESULT="FAILED"
fi
printf "\n%s\n\tHTTP Code is: %s\n\tOutput is %s\n\tQuote ID is: %s\n\tTest Result is: %s\n%s\n\n" "$TEST_NAME" "$HTTP_CODE" "`cat response.txt`" "$QUOTE_ID" "$TEST_RESULT" "$LINE"
if [[ "$TEST_RESULT" = "FAILED" ]]; then
        exit 1
fi



rm -f response.txt
TEST_NAME="GET - Re-Read updated quote (same quote id as previous test):"
HTTP_CODE=`curl -s -o response.txt -w"%{http_code}" -G -d "id=$QUOTE_ID" --request GET --url "$URL/$ACTION" \
    --header "aUthorization: Bearer $API_KEY"`
if [[ ! "$HTTP_CODE" =~ ^2 ]]; then
        TEST_RESULT="FAILED"
fi
printf "\n%s\n\tHTTP Code is: %s\n\tOutput is %s\n\tQuote ID is: %s\n\tTest Result is: %s\n%s\n\n" "$TEST_NAME" "$HTTP_CODE" "`cat response.txt`" "$QUOTE_ID" "$TEST_RESULT" "$LINE"
if [[ "$TEST_RESULT" = "FAILED" ]]; then
        exit 1
fi



rm -f response.txt
TEST_NAME="DELETE (same quote id as previous test):"
HTTP_CODE=`curl -s -o response.txt -w"%{http_code}" --request DELETE \
    --url "$URL/$ACTION?id=$QUOTE_ID" \
    --header "AuthorizatioN: Bearer $API_KEY"`
if [[ ! "$HTTP_CODE" =~ ^2 ]]; then
        TEST_RESULT="FAILED"
fi
printf "\n%s\n\tHTTP Code is: %s\n\tOutput is %s\n\tQuote ID is: %s\n\tTest Result is: %s\n%s\n\n" "$TEST_NAME" "$HTTP_CODE" "`cat response.txt`" "$QUOTE_ID" "$TEST_RESULT" "$LINE"
if [[ "$TEST_RESULT" = "FAILED" ]]; then
        exit 1
fi



rm -f response.txt
TEST_NAME="GET - Re-Read deleted quote (same quote id as previous test):"
HTTP_CODE=`curl -s -o response.txt -w"%{http_code}" -G -d "id=$QUOTE_ID" --request GET --url "$URL/$ACTION" \
    --header "aUthorization: Bearer $API_KEY"`
TEST_RESULT="FAILED"
# Note that the valid HTTP response codes are only for this application.
if [[ "$HTTP_CODE" = "500" ]]; then
        TEST_RESULT="PASS"
fi  
printf "\n%s\n\tHTTP Code is: %s\n\tOutput is %s\n\tTest Result is: %s\n%s\n\n" "$TEST_NAME" "$HTTP_CODE" "`cat response.txt`" "$TEST_RESULT" "$LINE"
if [[ "$TEST_RESULT" = "FAILED" ]]; then
        exit 1
fi

rm -f response.txt
echo "If you made it here, we should be all good."
exit 0

