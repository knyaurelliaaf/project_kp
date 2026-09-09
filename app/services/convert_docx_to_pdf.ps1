param (
    [string]$docxPath,
    [string]$pdfPath
)

$word = New-Object -ComObject Word.Application
$word.Visible = $false
$word.DisplayAlerts = 0

try {
    $doc = $word.Documents.Open($docxPath)
    $doc.SaveAs([ref]$pdfPath, [ref]17) # 17 = wdFormatPDF
    $doc.Close([ref]0) # 0 = wdDoNotSaveChanges
} catch {
    Write-Error $_.Exception.Message
    exit 1
} finally {
    $word.Quit()
}
