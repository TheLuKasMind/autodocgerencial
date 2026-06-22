<?php

require_once './vendor/autoload.php';

function logBackup($mensagem)
{
    $arquivoLog = __DIR__ . '/backup_google_drive.log';

    file_put_contents(
        $arquivoLog,
        '[' . date('d/m/Y H:i:s') . '] ' . $mensagem . PHP_EOL,
        FILE_APPEND
    );
}


function enviarBackupGoogleDrive(string $arquivoZip): bool
{
    try {
        if (!file_exists($arquivoZip) || filesize($arquivoZip) === 0) {
            throw new Exception("Arquivo inválido ou vazio: " . basename($arquivoZip));
        }

        $client = new Google\Client();
        $client->setAuthConfig(__DIR__ . '/oauth-credentials.json');  // ← Mudado
        $client->addScope(Google\Service\Drive::DRIVE);

        // Carrega o token gerado
        $tokenPath = __DIR__ . '/token.json';
        if (!file_exists($tokenPath)) {
            throw new Exception("token.json não encontrado. Rode o gerar-token.php primeiro.");
        }

        $accessToken = json_decode(file_get_contents($tokenPath), true);
        $client->setAccessToken($accessToken);

        // Atualiza o token se expirou
        if ($client->isAccessTokenExpired()) {
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                file_put_contents($tokenPath, json_encode($client->getAccessToken()));
            } else {
                throw new Exception("Refresh token não disponível. Gere o token novamente.");
            }
        }

        $drive = new Google\Service\Drive($client);

        $fileMetadata = new Google\Service\Drive\DriveFile([
            'name' => basename($arquivoZip),
            'parents' => ['184g3ZcRcbkR1LU6dGwSg1kY71BfMeS1z']
        ]);

        $content = file_get_contents($arquivoZip);

        $file = $drive->files->create($fileMetadata, [
            'data'       => $content,
            'mimeType'   => 'application/zip',
            'uploadType' => 'multipart',
            'fields'     => 'id,name,size'
        ]);

        logBackup(
            'UPLOAD OK | Arquivo: ' . basename($arquivoZip) .
            ' | Drive ID: ' . $file->id .
            ' | Tamanho: ' . number_format($file->size ?? 0) . ' bytes'
        );

        return true;

    } catch (Google\Service\Exception $e) {
        $errorMsg = $e->getMessage();
        if ($errors = $e->getErrors()) {
            $errorMsg = json_encode($errors);
        }
        logBackup('ERRO GOOGLE API | ' . $errorMsg);
        return false;

    } catch (Exception $e) {
        logBackup('ERRO UPLOAD | ' . $e->getMessage());
        return false;
    }
}