param(
    [Parameter(Mandatory)][string[]]$Files,
    [Parameter(Mandatory)][string]$ManifestPath,
    [Parameter(Mandatory)][ValidatePattern('^[a-z0-9-]+\.zip$')][string]$Name
)

$ErrorActionPreference='Stop'
$root=[IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$destination=Join-Path ([Environment]::GetFolderPath('UserProfile')) 'Desktop\Zips'
[void](New-Item -ItemType Directory -Path $destination -Force)
$zipPath=Join-Path $destination $Name
if(Test-Path -LiteralPath $zipPath){throw 'O pacote já existe; use um nome novo para preservar a auditoria anterior.'}
if(-not (Test-Path -LiteralPath $ManifestPath -PathType Leaf)){throw 'Manifesto ausente.'}

$selected=[Collections.Generic.List[object]]::new()
foreach($relative in ($Files|Sort-Object -Unique)){
    $relative=$relative.Replace('\','/')
    if([IO.Path]::IsPathRooted($relative) -or $relative -match '(^|/)(\.\.?|\.env[^/]*|\.git|vendor|node_modules|storage|bootstrap/cache)(/|$)' -or
        $relative -match '(?i)(credentials|token|private.?key|dump|\.clixml$|\.(pem|key|p12|pfx|sql|sqlite|zip)$)'){
        throw ('Arquivo não permitido no pacote: '+$relative)
    }
    $absolute=[IO.Path]::GetFullPath((Join-Path $root $relative))
    if(-not $absolute.StartsWith($root+[IO.Path]::DirectorySeparatorChar,[StringComparison]::OrdinalIgnoreCase)) {throw 'Caminho fora do repositório.'}
    if(-not (Test-Path -LiteralPath $absolute -PathType Leaf)){throw ('Arquivo ausente: '+$relative)}
    [void]$selected.Add([pscustomobject]@{Relative=$relative;Absolute=$absolute;Hash=(Get-FileHash -LiteralPath $absolute -Algorithm SHA256).Hash.ToLowerInvariant()})
}
Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem
$stream=[IO.File]::Open($zipPath,[IO.FileMode]::CreateNew)
$archive=[IO.Compression.ZipArchive]::new($stream,[IO.Compression.ZipArchiveMode]::Create)
try{
    foreach($file in $selected){[void][IO.Compression.ZipFileExtensions]::CreateEntryFromFile($archive,$file.Absolute,$file.Relative,[IO.Compression.CompressionLevel]::Optimal)}
    [void][IO.Compression.ZipFileExtensions]::CreateEntryFromFile($archive,(Resolve-Path -LiteralPath $ManifestPath).Path,'MANIFESTO.md',[IO.Compression.CompressionLevel]::Optimal)
}finally{$archive.Dispose();$stream.Dispose()}

$read=[IO.Compression.ZipFile]::OpenRead($zipPath)
try{
    if($read.Entries.Count -ne $selected.Count+1){throw 'Quantidade de entradas divergente.'}
    foreach($file in $selected){
        $entry=$read.GetEntry($file.Relative)
        if(-not $entry){throw 'Entrada ausente.'}
        $entryStream=$entry.Open()
        try{$hash=[Convert]::ToHexString([Security.Cryptography.SHA256]::HashData($entryStream)).ToLowerInvariant()}finally{$entryStream.Dispose()}
        if($hash -ne $file.Hash){throw ('Hash divergente: '+$file.Relative)}
    }
}finally{$read.Dispose()}
[pscustomobject]@{path=$zipPath;entries=$selected.Count+1;sha256=(Get-FileHash -LiteralPath $zipPath -Algorithm SHA256).Hash.ToLowerInvariant();verified=$true}|ConvertTo-Json -Compress
