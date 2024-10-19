<?php

// Chemins des dossiers nécéssaires
$sourceFolder = __DIR__ . DIRECTORY_SEPARATOR . 'medias_output';


echo "Source Folder: $sourceFolder\n";
organizeFilesByMonth($sourceFolder);


function organizeFilesByMonth($sourceFolder) {
    // Vérification de la source d'entrée
    if (!is_dir($sourceFolder)) {
        die("Source folder does not exist: $sourceFolder");
    }


    // Parcours récursif des dossiers dans medias_input
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sourceFolder));

    foreach ($files as $file) {
        // Vérification si c'est  un fichier
        if ($file->isFile()) {
            // Obtenir le chemin absolu
            $filePath = $file->getRealPath();
            // Récupérer la date de création du fichier
            $creationDate = getCreationDate($filePath);

            if ($creationDate) {
            $year = $creationDate->format('Y');
                $month = $creationDate->format('m');

                // Tableau pour les noms de mois
                $months = [
                    '01' => '01-janvier',
                    '02' => '02-février',
                    '03' => '03-mars',
                    '04' => '04-avril',
                    '05' => '05-mai',
                    '06' => '06-juin',
                    '07' => '07-juillet',
                    '08' => '08-août',
                    '09' => '09septembre',
                    '10' => '10-octobre',
                    '11' => '11-novembre',
                    '12' => '12-décembre'
                ];

                $monthName = $months[$month] ?? 'inconnu'; // 'inconnu' si le mois n'est pas reconnu
                $monthFolder = $sourceFolder . DIRECTORY_SEPARATOR . $year . DIRECTORY_SEPARATOR . $monthName;

                // Création du dossier année/mois si n'existe pas
                if (!is_dir($monthFolder)) {
                    mkdir($monthFolder, 0777, true);
                }

                // Définir le nouveau nom de fichier
                $newFileName = $monthFolder . DIRECTORY_SEPARATOR . basename($filePath);

                // Déplacement du fichier
                if (rename($filePath, $newFileName)) {
                    echo "Moved $filePath to $newFileName\n";
                } else {
                    echo "Failed to move $filePath\n";
                }
            }
        }
    }
}

function getCreationDate($filePath) {
    // Extraire l'extension du fichier
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    // Renvoyer la bonne méthode en fonction du type (photos ou video)
    if (in_array($extension, ['png', 'jpg', 'jpeg', 'tiff', 'bmp', 'gif'])) {
        return getImageCreationDate($filePath);
    } elseif (in_array($extension, ['mp4', 'avi', 'mov', 'mkv'])) {
        return getVideoCreationDate($filePath);
    }
    return null;
}

function getImageCreationDate($filePath) {
    // Vérification si la méthode exif_read_data est disponible dans l'environnement PHP
    if (function_exists('exif_read_data')) {
        // Obtenir la date de la photo
        $exifData = @exif_read_data($filePath);
        // Si les infos sont disponibles on retourne la date
        if ($exifData !== false && isset($exifData['DateTimeOriginal'])) {
            return DateTime::createFromFormat('Y:m:d H:i:s', $exifData['DateTimeOriginal']);
        }
    }
    return null;
}

function getVideoCreationDate($filePath) {
    $output = [];
    $returnVar = null;
    // Extraire les infos dans fichier multimédia
    exec("ffprobe -v error -show_entries format=creation_time -of default=noprint_wrappers=1:nokey=1 " . escapeshellarg($filePath), $output, $returnVar);
    // Si les infos sont disponibles on retourne la date
    if ($returnVar == 0 && !empty($output)) {
        return DateTime::createFromFormat('Y-m-d\TH:i:s\Z', trim($output[0]));
    }
    return null;
}
?>
