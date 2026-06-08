#Requires -Version 5.1
<#
  generate-showcase-assets.ps1 — Temporäres Kie.ai Showcase-Asset Generator
  Nutzung: .\bin\generate-showcase-assets.ps1
#>
$ErrorActionPreference = "Stop"

$apiKey = $env:KIE_AI_API_KEY
if (-not $apiKey) {
    Write-Error "KIE_AI_API_KEY nicht gesetzt. Abbruch."
    exit 1
}

$assets = @(
    @{
        name = "orbit-protocol"
        file = "assets/showcase/portfolio/scifi-music-v1.jpg"
        prompt = "Epic spacecraft drifting through a vibrant purple nebula, cinematic wide shot, volumetric lighting, lens flare, deep space atmosphere, ultra detailed, dark background with cyan and purple accents"
    },
    @{
        name = "tokyo-dusk"
        file = "assets/showcase/portfolio/golden-social-v1.jpg"
        prompt = "Tokyo cityscape rooftop view at golden hour, warm amber sunlight reflecting on glass buildings, cinematic color grading, subtle lens flare, urban photography style, shallow depth of field"
    },
    @{
        name = "midnight-fizz"
        file = "assets/showcase/portfolio/tiktok-viral-v1.jpg"
        prompt = "Neon pink and blue studio lighting, close-up of a soda can opening with dramatic fizz splash, slow motion feel, high contrast, dark background with colorful backlighting"
    },
    @{
        name = "luminex-pro"
        file = "assets/showcase/portfolio/product-ad-v1.jpg"
        prompt = "Sleek wireless headphones on a white marble surface, soft professional studio lighting, premium product photography, subtle shadow, minimalist composition, high-end commercial look"
    }
)

function Invoke-KieGenerate($prompt) {
    $body = @{
        prompt = $prompt
        model = "flux-kontext-pro"
        aspectRatio = "16:9"
        outputFormat = "jpeg"
        safetyTolerance = 2
    } | ConvertTo-Json -Compress

    $headers = @{
        "Authorization" = "Bearer $apiKey"
        "Content-Type" = "application/json"
        "Accept" = "application/json"
    }

    $resp = Invoke-RestMethod -Uri "https://api.kie.ai/api/v1/flux/kontext/generate" -Method POST -Headers $headers -Body $body -TimeoutSec 30
    return $resp.data.taskId
}

function Invoke-KiePoll($taskId) {
    $headers = @{
        "Authorization" = "Bearer $apiKey"
        "Accept" = "application/json"
    }
    $url = "https://api.kie.ai/api/v1/flux/kontext/record-info?taskId=$taskId"

    $maxAttempts = 60
    for ($i = 1; $i -le $maxAttempts; $i++) {
        Start-Sleep -Seconds 2
        $resp = Invoke-RestMethod -Uri $url -Method GET -Headers $headers -TimeoutSec 30
        $flag = $resp.data.successFlag

        if ($flag -eq 1) {
            return $resp.data.response.resultImageUrl
        } elseif ($flag -eq 2 -or $flag -eq 3) {
            throw "Kie.ai Generierung fehlgeschlagen (Flag: $flag)"
        }
        Write-Host "  ... Poll $i / $maxAttempts (Flag: $flag)"
    }
    throw "Timeout nach $maxAttempts Versuchen"
}

function Compress-Image($sourcePath, $destPath, $maxWidth = 640, $maxHeight = 360, $quality = 85, $maxSizeKB = 300) {
    Add-Type -AssemblyName System.Drawing
    $img = [System.Drawing.Image]::FromFile($sourcePath)

    # Resize to 640x360 maintaining aspect ratio (fill/crop to 16:9)
    $targetRatio = $maxWidth / $maxHeight
    $srcRatio = $img.Width / $img.Height

    if ($srcRatio -gt $targetRatio) {
        $newHeight = $maxHeight
        $newWidth = [int]($img.Width * ($maxHeight / $img.Height))
    } else {
        $newWidth = $maxWidth
        $newHeight = [int]($img.Height * ($maxWidth / $img.Width))
    }

    $bmp = New-Object System.Drawing.Bitmap($maxWidth, $maxHeight)
    $g = [System.Drawing.Graphics]::FromImage($bmp)
    $g.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
    $g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::HighQuality
    $g.PixelOffsetMode = [System.Drawing.Drawing2D.PixelOffsetMode]::HighQuality

    # Center crop
    $srcX = [int](($newWidth - $maxWidth) / 2)
    $srcY = [int](($newHeight - $maxHeight) / 2)
    if ($srcX -lt 0) { $srcX = 0 }
    if ($srcY -lt 0) { $srcY = 0 }

    $g.DrawImage($img, -$srcX, -$srcY, $newWidth, $newHeight)
    $g.Dispose()
    $img.Dispose()

    # Encode JPEG with quality
    $codec = [System.Drawing.Imaging.ImageCodecInfo]::GetImageEncoders() | Where-Object { $_.FormatDescription -eq "JPEG" }
    $encoderParams = New-Object System.Drawing.Imaging.EncoderParameters(1)
    $encoderParams.Param[0] = New-Object System.Drawing.Imaging.EncoderParameter([System.Drawing.Imaging.Encoder]::Quality, $quality)

    # Save
    $tmpPath = $destPath + ".tmp"
    $bmp.Save($tmpPath, $codec, $encoderParams)
    $bmp.Dispose()

    # If still too large, lower quality iteratively
    $sizeKB = (Get-Item $tmpPath).Length / 1KB
    if ($sizeKB -gt $maxSizeKB -and $quality -gt 50) {
        $newQuality = $quality - 10
        Compress-Image -sourcePath $sourcePath -destPath $destPath -maxWidth $maxWidth -maxHeight $maxHeight -quality $newQuality -maxSizeKB $maxSizeKB
        Remove-Item $tmpPath -ErrorAction SilentlyContinue
        return
    }

    Move-Item $tmpPath $destPath -Force
    $finalSize = (Get-Item $destPath).Length / 1KB
    Write-Host "  Saved: $destPath ($('{0:N1}' -f $finalSize) KB, ${maxWidth}x${maxHeight}, Q=$quality)"
}

# ── Main ──────────────────────────────────────────────────────────────────────

$targetName = $args[0]
if (-not $targetName) {
    Write-Error "Usage: .\bin\generate-showcase-assets.ps1 <orbit-protocol|tokyo-dusk|midnight-fizz|luminex-pro|all>"
    exit 1
}

$targets = if ($targetName -eq "all") { $assets } else { $assets | Where-Object { $_.name -eq $targetName } }
if (-not $targets) {
    Write-Error "Unbekanntes Asset: $targetName"
    exit 1
}

foreach ($asset in $targets) {
    Write-Host "`n=== Generating: $($asset.name) ==="
    Write-Host "Prompt: $($asset.prompt)"

    # Step 1: Generate
    Write-Host "Step 1: API-Call (Kie.ai flux-kontext-pro)..."
    $taskId = Invoke-KieGenerate -prompt $asset.prompt
    Write-Host "  TaskId: $taskId"

    # Step 2: Poll
    Write-Host "Step 2: Polling..."
    $imageUrl = Invoke-KiePoll -taskId $taskId
    Write-Host "  Image URL received (length: $($imageUrl.Length))"

    # Step 3: Download raw
    $rawPath = $asset.file + ".raw.jpg"
    Write-Host "Step 3: Downloading to $rawPath ..."
    Invoke-WebRequest -Uri $imageUrl -OutFile $rawPath -TimeoutSec 30
    $rawSize = (Get-Item $rawPath).Length / 1KB
    Write-Host "  Raw size: $('{0:N1}' -f $rawSize) KB"

    # Step 4: Inspect
    Add-Type -AssemblyName System.Drawing
    $rawImg = [System.Drawing.Image]::FromFile($rawPath)
    Write-Host "  Raw resolution: $($rawImg.Width)x$($rawImg.Height)"
    $rawImg.Dispose()

    # Step 5: Compress to spec
    Write-Host "Step 4: Compressing to 640x360, max 300KB..."
    Compress-Image -sourcePath $rawPath -destPath $asset.file

    # Cleanup raw
    Remove-Item $rawPath -ErrorAction SilentlyContinue

    Write-Host "=== Done: $($asset.name) -> $($asset.file) ==="
}

Write-Host "`nAll requested assets completed."
