<?php
/*
 *  Made by Aberdeener
 *  https://github.com/NamelessMC/Nameless-Installer/
 *  Nameless-Installer version 1.0.1
 * 
 *  NamelessMC by Samerton
 *  https://github.com/NamelessMC/Nameless/
 *
 *  License: MIT
 */

// Don't allow rerunning if Nameless is currently installed
if (file_exists('./core/config.php')) {
    header('Location: ./');
}

// Display errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// This allows us to use header() without facing issues
ob_start();

// Ensure PHP version >= 7.4
if (version_compare(phpversion(), '7.4', '<')) {
    die('The Nameless Installer requires PHP version 7.4 or better.');
}


$version = $_GET['ver'] ?? 'null';
$step = $_GET['step'] ?? 'welcome';

$zip_url = '';
$zip_file = 'namelessmc-' . $version . '.zip';
// These will need to be updated with each NMC release
$zip_subdir = $version == 'v1' ? 'Nameless-1.0.21' : 'Nameless-2.0.0-pr7';

// Recursively copy a directory to another location. Used after extraction of the zip file
function moveDirectory($source, $dest) {    
    $result = false;

    if (is_file($source)) {
        if ($dest[strlen($dest) - 1] == '/') {
            if (!file_exists($dest)) cmfcDirectory::makeAll($dest, 0755, true);
            $__dest = $dest . "/" . basename($source);
        } else $__dest = $dest;

        $result = copy($source, $__dest);
        chmod($__dest, 0755);
    } elseif (is_dir($source)) {
        if ($dest[strlen($dest) - 1] == '/' && $source[strlen($source) - 1] != '/') {
            $dest = $dest . basename($source);
            mkdir($dest);
        } else mkdir($dest, 0755);

        $dirHandle = opendir($source);
        while ($file = readdir($dirHandle)) {
            if ($file != "." && $file != "..") {
                $__dest = $dest . "/" . $file;
                $result = moveDirectory($source . "/" . $file, $__dest);
            }
        }
        closedir($dirHandle);
    } else $result = false;

    return $result;
}

// Used to delete the original extracted zip dir
function deleteDirectory($dir) {
    if (!file_exists($dir)) return true;

    if (!is_dir($dir)) return unlink($dir);

    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') continue;
        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) return false;
    }

    return rmdir($dir);
}

// Used to display errors
function showError($message) { ?>
    <p style="color: red;">[ERROR]: <?php echo $message ?></p>
    <p>If this continues to happen, contact support in our <a href=" https://discord.gg/QWdS9CB" target="_blank">Discord</a>.</p>
    <a href="?step=select">Click here to try again.</a>
<?php }

// Used to display warnings
function showWarning($message) { ?>
    <p style="color: goldenrod;">[WARNING]: <?php echo $message ?></p>
<?php }

// Used to display debugging info
function showDebugging($message) { ?>
    <p style="color: green;">[DEBUG]: <?php echo $message ?></p>
<?php }

// Made this a function so we do not have messy php tags
function minorWarning() { ?>
    <p>Something minor went wrong, but you can continue. <a href="./">Click here</a>.</p>
    <hr>
<?php } ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Easy Install • NamelessMC</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <link rel="icon" href="https://namelessmc.com/favicon.ico">
</head>

<body style="background-color: #F3F6FA">

    <style>
        .card {
            cursor: pointer;
            width: 22rem;
        }

        .btn-version,
        .btn-version:hover {
            color: white;
            border-color: #90C2E7;
        }

        .btn-version:hover {
            border-color: white;
            outline: 5px;
        }
    </style>

    <div class="container" style="text-align: center;">
        <div class="row">
            <div class="col-md-2"></div>
            <div class="col-md-8">

                <br />
                <br />
                <div>
                    <h1>Easy Install • NamelessMC</h1>
                    <h3>Step: <?php echo ucfirst($step) ?></h3>
                    <?php if ($version != 'null') { ?>
                        <h3>Version: <?php echo $version ?></h3>
                    <?php } ?>
                    <hr>
                </div>

                <?php
                switch ($step) {
                    case 'welcome': {
                            if (!ini_get('allow_url_fopen')) { ?>
                                <p style="color: red;">[ERROR]: <kbd>allow_url_fopen</kbd> is blocked in your php.ini file. Please set this to <kbd>1</kbd> to continue with the Easy Installer.</p>
                                <p>If you cannot change this value, you can use an alternative download from <a href="https://namelessmc.com/download" target="_blank">here</a>.</p>
                            <?php break;
                            }
                            if (!class_exists(ZipArchive::class)) { ?>
                                <p style="color: red;">[ERROR]: The <kbd>ZipArchive</kbd> class does not exist. Please ensure you have the zip extension enabled to continue with the Easy Installer.</p>
                                <p>If you cannot install this extension, you can use an alternative download from <a href="https://namelessmc.com/download" target="_blank">here</a>.</p>
                            <?php break;
                            } ?>
                            <p><i>Welcome to NamelessMC!</i></p>
                            <p>This script will download and extract NamelessMC for you.</p>
                            <p>In the next step we will choose which version of NamelessMC to install.</p>
                            <a class="btn btn-primary" style="color: white;" href="?step=select">Continue »</a>
                        <?php break;
                        }

                    case 'select': { ?>

                            <p><i>Now you must choose which version of NamelessMC you want to install.</i></p>
                            <p>NamelessMC has two versions: <b>v1 (1.0.21)</b> and <b>v2 (pr7)</b>.</p>
                            <p><b>v2</b> is recommended by NamelessMC developers as it is a complete rewrite and provides many more functionalities - such as modules, widgets and beautiful templates.</p>
                            <br />
                            <div class="row">
                                <div class="card mx-auto" onclick="window.location.href='?step=verify&ver=v1'">
                                    <div class="card-body rounded" style="background-color: #2185D0">
                                        <h5 class="card-title" style="color: white">Legacy</h5>
                                        <img src="https://namelessmc.com/custom/templates/Nameless-Semantic/img/v1-homepage.jpg" class="card-img" alt="NamelessMC v1.0.21">
                                        <hr style="background-color: white">
                                        <a href="?step=verify&ver=v1" class="btn btn-outline btn-version">v1.0.21</a>
                                    </div>
                                </div>
                                <div class="card mx-auto" onclick="window.location.href='?step=verify&ver=v2'">
                                    <div class="card-body rounded" style="background-color: #21BA45">
                                        <h5 class="card-title" style="color: white">Recommended</h5>
                                        <img src="https://namelessmc.com/custom/templates/Nameless-Semantic/img/v2-homepage.jpg" class="card-img" alt="NamelessMC v2.0.0-pr7">
                                        <hr style="background-color: white">
                                        <a href="?step=verify&ver=v2" class="btn btn-outline btn-version">v2.0.0-pr7</a>
                                    </div>
                                </div>
                            </div>

                        <?php break;
                        }

                    case 'verify': { 
                            if ($version != 'v1' && $version != 'v2') {
                                header('Location: ./easy-install.php?step=select');
                                break;
                            } ?>
                            <p><i>NamelessMC <?php echo $version ?> will now download and extract itself.</i></p>
                            <p>It will automatically refresh, so please do not reload the page.</p>
                            <p>Click <a href="?step=download&ver=<?php echo $version ?>" onclick="statusUpdate()">here</a> to proceed.</p>
                            <div id="status" style="color: orange; font-size: large; font-weight:bold;">STANDBY</div>
                            <h4 id="no-reload" style="color: red; display: none"><b>DO NOT RELOAD</b></h4>

                    <?php break;
                        }

                    case 'download': {

                            if ($version == 'v1') $zip_url = 'https://github.com/NamelessMC/Nameless/archive/v1.0.21.zip';
                            else if ($version == 'v2') $zip_url = 'https://github.com/NamelessMC/Nameless/archive/v2.0.0-pr7.zip';

                            // Direct to selection screen if they went to an invalid version
                            else {
                                header('Location: ./easy-install.php?step=select');
                                break;
                            }

                            // Download the zip from Github, if this fails, probably a permission issue
                            if (copy($zip_url, $zip_file)) showDebugging("NamelessMC ($zip_file) downloaded...");
                            else {
                                showError("NamelessMC could not be downloaded. Please ensure your webserver has permission to write to your file system.");
                                break;
                            }

                            // Continue to extract, move and cleanup NMC files
                            $zip = new ZipArchive;
                            if ($zip->open($zip_file)) {
                                $zip->extractTo('./');
                                $zip->close();

                                $redirect = true;

                                showDebugging("Success extracting zip file...");

                                // If moving the directory failed, there may have been a corrupt file within it (uncommon)
                                if (moveDirectory($zip_subdir, '.')) {
                                    showDebugging("Success copying files from zip to root directory...");

                                    // If deleting the unzipped directory fails, it might have already been deleted..?
                                    if (deleteDirectory($zip_subdir)) showDebugging("Success deleting extracted zip...");
                                    else {
                                        showWarning("NamelessMC extracted folder could not be deleted, but it safe to continue.");
                                        $redirect = false;
                                    }

                                    // If deleting the zip fails, it is probably a weird permission issue
                                    if (unlink($zip_file)) showDebugging("Success deleting zip file...");
                                    else {
                                        showWarning("NamelessMC zip file could not be deleted, but it safe to continue.");
                                        $redirect = false;
                                    }

                                    // If a warning happened, they can continue, but we let them know. If not, we just redirect them
                                    if (!$redirect) minorWarning();
                                    else header('Location: ./');
                                } else showError("NamelessMC could not be moved from the extracted folder.");
                            } else showError("NamelessMC archive could not be extracted/opened.");
                            break;
                        }

                    default:
                        // Invalid path: Direct to main screen 
                        header('Location: ./easy-install.php');
                }

                // Back button only on certain pages
                if ($step != 'welcome' && $step != 'download') { ?>
                    <hr>
                    <div>
                        <button onclick="history.back();" class="btn btn-sm btn-secondary">« Back</button>
                    </div>
                <?php } ?>

                <div style="text-align:right;">
                    <p>Nameless-Installer | Version: 1.0.1</p>
                </div>
            </div>
            <div class="col-md-2"></div>
        </div>
    </div>

    <script>
        const status = document.getElementById("status");

        let installing = false;

        function statusUpdate() {
            status.innerHTML = "WORKING";
            status.style.color = "Green";
            installing = true;
            document.getElementById("no-reload").style.display = "block";
        }

        // This seems to only work in Firefox & Chrome, in Safari nothing changes from "STANDBY"
        let dotCount = 0;
        var dots = window.setInterval(function() {
            if (!installing) return;
            if (dotCount < 3) {
                ++dotCount;
                status.innerHTML += ".";
            } else {
                status.innerHTML = "WORKING";
                dotCount = 0;
            }
        }, 450);
    </script>

</body>

</html>