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

# Toggle verbosity
#VERBOSE=-v
VERBOSE=

# An easy way to display a line.  Yes, the 2 backslashes are not a typo.
LINE=________________________________________________________________________________\\n

# Make sure the URL matches your application.
URL=http://my-api-demo:60080/api
# Applies to quotes only:
ACTION=Quotes

printf "POST - Create a new quote:\n"
OUTPUT=`curl $VERBOSE -d 'quote_text=Diplomacy%20is%20the%20art%20of%20saying%20%27Nice%20doggie%27%20until%20you%20can%20find%20a%20rock.&quote_author=Will%20Rogers' --request POST \
    --url "$URL/$ACTION" \
    --header "authorization: Bearer $API_KEY"`
printf "\nPOST Method - Output is:\n$OUTPUT\n$LINE\n"

printf "POST - Create a second new quote:\n"
OUTPUT=`curl $VERBOSE -d 'quote_text=I%20hated%20every%20minute%20of%20training%2C%20but%20I%20said%2C%20%27Don%27t%20quit.%20Suffer%20now%20and%20live%20the%20rest%20of%20your%20life%20as%20a%20champion%27.&quote_author=Muhammad%20Ali' --request POST \
    --url "$URL/$ACTION" \
    --header "Authorization: Bearer $API_KEY"`
printf "\nPOST Method - Output is:\n$OUTPUT\n$LINE\n"

# Too long of a quote.
printf "POST - try to create a quote that is too long:\n"
OUTPUT=`curl $VERBOSE -d 'quote_author=Nobody&quote_text=Lorem%20ipsum%20dolor%20sit%20amet%2C%20consectetur%20adipiscing%20elit.%20Donec%20ac%20ornare%20ante.%20Aliquam%20pellentesque%20eros%20id%20urna%20posuere%20ultricies.%20Nullam%20condimentum%20efficitur%20sollicitudin.%20Mauris%20pulvinar%20rutrum%20nibh%2C%20in%20dignissim%20enim%20euismod%20eget.%20Donec%20sed%20risus%20in%20orci%20vehicula%20suscipit%20non%20faucibus%20enim.%20Mauris%20vel%20malesuada%20enim%2C%20quis%20molestie%20massa.%20Nam%20odio%20enim%2C%20facilisis%20et%20auctor%20vitae%2C%20egestas%20at%20neque.%20Etiam%20vel%20nibh%20convallis%2C%20efficitur%20arcu%20nec%2C%20convallis%20metus.%20Fusce%20sem%20sapien%2C%20fermentum%20quis%20faucibus%20sit%20amet%2C%20tempor%20et%20mauris.%0A%0AIn%20maximus%20fermentum%20nisl%2C%20consectetur%20fringilla%20neque%20ultrices%20eget.%20Fusce%20in%20justo%20eu%20nisl%20consectetur%20posuere.%20Morbi%20iaculis%20auctor%20diam%2C%20ac%20semper%20sem%20porta%20et.%20Etiam%20eu%20quam%20nec%20metus%20tincidunt%20posuere.%20Sed%20fermentum%20luctus%20posuere.%20Sed%20ut%20enim%20at%20orci%20luctus%20feugiat%20vel%20et%20turpis.%20Aenean%20vel%20aliquam%20lectus.%20Fusce%20quis%20justo%20id%20lectus%20pharetra%20facilisis%20non%20sed%20ex.%20Sed%20viverra%20fringilla%20arcu%2C%20ut%20commodo%20eros.%20Etiam%20quis%20molestie%20neque.%20Donec%20non%20aliquam.' --request POST \
    --url "$URL/$ACTION" \
    --header "Authorization: Bearer $API_KEY"`
printf "\nPOST Method - Output is:\n$OUTPUT\n$LINE\n"

# Non-integer ID
printf "GET - Try to read a non-numeric id:\n"
OUTPUT=`curl $VERBOSE -G -d 'id=abc' --request GET --url "$URL/$ACTION" \
    --header "aUthorization: Bearer $API_KEY"`
printf "\nGET Method - Output is:\n$OUTPUT\n$LINE\n"

# Non-non-negative ID
printf "GET - Try to read an invalid id:\n"
OUTPUT=`curl $VERBOSE -G -d 'id=0' --request GET --url "$URL/$ACTION" \
    --header "aUthorization: Bearer $API_KEY"`
printf "\nGET Method - Output is:\n$OUTPUT\n$LINE\n"

# Random Quote
printf "GET - Read a random quote:\n"
OUTPUT=`curl $VERBOSE --request GET \
    --url "$URL/$ACTION" \
    --header "Authorization: Bearer $API_KEY"`
# Read the ID into a variable using jq (install if needed):
QUOTE_ID=`echo $OUTPUT | jq -r '.id'`
printf "Quote ID is [$QUOTE_ID]\n"
printf "\nGET Method - Output is:\n$OUTPUT\n$LINE\n"

# Specific Quote
printf "GET - Read a specific quote:\n"
OUTPUT=`curl $VERBOSE -G -d 'id=1' --request GET --url "$URL/$ACTION" \
    --header "aUthorization: Bearer $API_KEY"`
printf "\nGET Method - Output is:\n$OUTPUT\n$LINE\n"

# Non-existent quote
printf "GET - Read a non-existent quote:\n"
OUTPUT=`curl $VERBOSE -G -d 'id=999999999999' --request GET --url "$URL/$ACTION" \
    --header "aUthorization: Bearer $API_KEY"`
printf "\nGET Method - Output is:\n$OUTPUT\n$LINE\n"

# Insert a misquote and then update it.
printf "POST - Create a new quote:\n"
OUTPUT=`curl $VERBOSE -d 'quote_text=If%20it%27s%20on%20the%20Internet%20then%20it%20must%20be%20true%21&quote_author=George%20Washington' --request POST \
    --url "$URL/$ACTION" \
    --header "authorization: Bearer $API_KEY"`
QUOTE_ID=`echo $OUTPUT | jq -r '.id'`
printf "Quote ID is [$QUOTE_ID]\n"
printf "\nPOST Method - Output is:\n$OUTPUT\n$LINE\n"

printf "PATCH - Update a quote:\n"
OUTPUT=`curl --request PATCH \
    --url "$URL/$ACTION?id=3&quote_author=Abraham%20Lincoln&quote_text=The%20best%20way%20to%20destroy%20an%20enemy%20is%20to%20make%20him%20a%20friend." \
    --header "authorization: Bearer $API_KEY"`
printf "\nPATCH Method - Output is:\n$OUTPUT\n$LINE\n"

# Get Updated Quote:
printf "GET - Re-Read updated quote:\n"
OUTPUT=`curl $VERBOSE -G -d 'id=3' --request GET --url "$URL/$ACTION" \
    --header "aUthorization: Bearer $API_KEY"`
printf "\nGET Method - Output is:\n$OUTPUT\n$LINE\n"

#printf "PUT:\n"
#OUTPUT=`curl -d 'key1=val1&key2=val2&key3=val3' --request PUT \
#    --url "$URL/$ACTION" \
#    --header "Authorization: Bearer $API_KEY"`
#printf "\nPUT Method - Output is:\n$OUTPUT\n$LINE\n"

#printf "DELETE:\n"
#OUTPUT=`curl --request DELETE \
#    --url "$URL/$ACTION?id=3" \
#    --header "AuthorizatioN: Bearer $API_KEY"`
#printf "\nDELETE Method - Output is:\n$OUTPUT\n$LINE\n"


