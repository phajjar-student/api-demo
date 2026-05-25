$apiKey = Get-Content api-key.txt -Raw

$url = "http://localhost:60080/api"

$action = "Help"

Write-Host "GET:" -NoNewLine
$output = Invoke-RestMethod -Method GET -Uri "$url/$action" -Headers @{ Authorization = "Bearer $apiKey" } -Body @{ key1 = "val1"; key2 = "val2"; key3 = "val3" }
Write-Host "`nGET Method - Output is:`n$output`n"

Write-Host "POST:" -NoNewLine
$output = Invoke-RestMethod -Method POST -Uri "$url/$action" -Headers @{ Authorization = "Bearer $apiKey" } -Body @{ key1 = "val1"; key2 = "val2"; key3 = "val3" }
Write-Host "`nPOST Method - Output is:`n$output`n"

Write-Host "PUT:" -NoNewLine
$output = Invoke-RestMethod -Method PUT -Uri "$url/$action" -Headers @{ Authorization = "Bearer $apiKey" } -Body @{ key1 = "val1"; key2 = "val2"; key3 = "val3" }
Write-Host "`nPUT Method - Output is:`n$output`n"

Write-Host "PATCH:" -NoNewLine
$output = Invoke-RestMethod -Method PATCH -Uri "$url/$action" -Headers @{ Authorization = "Bearer $apiKey" } -Body @{ key1 = "val1"; key2 = "val2"; key3 = "val3" }
Write-Host "`nPATCH Method - Output is:`n$output`n"

Write-Host "DELETE:" -NoNewLine
$output = Invoke-RestMethod -Method DELETE -Uri "$url/$action" -Headers @{ Authorization = "Bearer $apiKey" } -Body @{ key1 = "val1"; key2 = "val2"; key3 = "val3" }
Write-Host "`nDELETE Method - Output is:`n$output`n"

