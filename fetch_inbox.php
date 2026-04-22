<?php
require 'auth.php';
require 'db.php';
require 'blockchain.php';

$userId = $_SESSION['user_id'];
$blockchain = new Blockchain();

$imapHost = '{imap.gmail.com:993/imap/ssl}INBOX';
$imapUser = 'ronaktech69@gmail.com';
$imapPass = 'vyaxxdfpkmahrxgx';

if (!function_exists('imap_open')) {
    die("PHP IMAP extension is not enabled. Enable extension=imap in php.ini first.");
}

$inbox = @imap_open($imapHost, $imapUser, $imapPass);

if (!$inbox) {
    die("IMAP connection failed: " . htmlspecialchars(imap_last_error()));
}

$emailCount = imap_num_msg($inbox);
$newImported = 0;

for ($msgNo = 1; $msgNo <= $emailCount; $msgNo++) {
    $overview = imap_fetch_overview($inbox, $msgNo, 0);

    if (!$overview || !isset($overview[0])) {
        continue;
    }

    $mail = $overview[0];
    $uid = (string) imap_uid($inbox, $msgNo);

    // Prevent duplicate imports
    $check = $conn->prepare("SELECT id FROM received_emails WHERE uid = ? LIMIT 1");
    $check->bind_param("s", $uid);
    $check->execute();
    $existing = $check->get_result();

    if ($existing->num_rows > 0) {
        continue;
    }

    $fromRaw = $mail->from ?? 'Unknown';
    $subjectRaw = $mail->subject ?? '(No Subject)';

    $senderEmail = extractEmailAddress($fromRaw);
    $decodedSubject = decodeMimeText($subjectRaw);

    // FIXED BODY EXTRACTION
    $body = getCleanMessageBody($inbox, $msgNo);

    $stmt = $conn->prepare("
        INSERT INTO received_emails (user_id, sender_email, subject, message, uid)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("issss", $userId, $senderEmail, $decodedSubject, $body, $uid);
    $stmt->execute();

    $blockchain->addBlock([
        "action" => "receive_mail",
        "user_id" => $userId,
        "sender_email" => $senderEmail,
        "subject" => $decodedSubject,
        "message_hash" => hash("sha256", $body),
        "uid" => $uid,
        "time" => date("Y-m-d H:i:s")
    ]);

    $newImported++;
}

imap_close($inbox);

header("Location: received.php?msg=" . urlencode($newImported . " new email(s) imported successfully."));
exit();

/**
 * Extract clean email address from "Name <email@example.com>"
 */
function extractEmailAddress(string $fromRaw): string
{
    if (preg_match('/<([^>]+)>/', $fromRaw, $matches)) {
        return trim($matches[1]);
    }
    return trim($fromRaw);
}

/**
 * Decode MIME encoded subject/header text
 */
function decodeMimeText(string $text): string
{
    $decoded = imap_mime_header_decode($text);
    if (!is_array($decoded)) {
        return $text;
    }

    $result = '';
    foreach ($decoded as $part) {
        $result .= $part->text;
    }

    return trim($result);
}

/**
 * Main function to get a readable body
 */
function getCleanMessageBody($inbox, int $msgNo): string
{
    $structure = imap_fetchstructure($inbox, $msgNo);

    if (!$structure) {
        $body = imap_body($inbox, $msgNo);
        return cleanEmailText($body);
    }

    // 1. Prefer plain text part
    $plainText = findPart($inbox, $msgNo, $structure, 'PLAIN');
    if ($plainText !== '') {
        return cleanEmailText($plainText);
    }

    // 2. Fallback to HTML part
    $htmlText = findPart($inbox, $msgNo, $structure, 'HTML');
    if ($htmlText !== '') {
        return cleanEmailHtml($htmlText);
    }

    // 3. Final fallback
    $body = imap_body($inbox, $msgNo);
    return cleanEmailText($body);
}

/**
 * Recursively search for a MIME subtype like PLAIN or HTML
 */
function findPart($inbox, int $msgNo, $structure, string $subtype, string $partNumber = ''): string
{
    if (!isset($structure->parts)) {
        if ($structure->type == 0 && strtoupper($structure->subtype ?? '') === $subtype) {
            $body = imap_body($inbox, $msgNo);
            return decodeImapData($body, $structure->encoding ?? 0);
        }
        return '';
    }

    foreach ($structure->parts as $index => $part) {
        $currentPartNumber = $partNumber === '' ? (string)($index + 1) : $partNumber . '.' . ($index + 1);

        // Match requested subtype
        if ($part->type == 0 && strtoupper($part->subtype ?? '') === $subtype) {
            $data = imap_fetchbody($inbox, $msgNo, $currentPartNumber);
            return decodeImapData($data, $part->encoding ?? 0);
        }

        // Recursive check for nested multipart sections
        if (isset($part->parts)) {
            $result = findPart($inbox, $msgNo, $part, $subtype, $currentPartNumber);
            if ($result !== '') {
                return $result;
            }
        }
    }

    return '';
}

/**
 * Decode body based on encoding
 */
function decodeImapData(string $data, int $encoding): string
{
    switch ($encoding) {
        case 3: // BASE64
            return base64_decode($data) ?: '';
        case 4: // QUOTED-PRINTABLE
            return quoted_printable_decode($data);
        default:
            return $data;
    }
}

/**
 * Clean plain text email body
 */
function cleanEmailText(string $text): string
{
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = preg_replace("/\r\n|\r/", "\n", $text);
    $text = preg_replace("/\n{3,}/", "\n\n", $text);
    return trim($text);
}

/**
 * Clean HTML email body and convert to readable text
 */
function cleanEmailHtml(string $html): string
{
    // Remove scripts/styles/head/title/meta
    $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
    $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html);
    $html = preg_replace('/<head\b[^>]*>(.*?)<\/head>/is', '', $html);
    $html = preg_replace('/<title\b[^>]*>(.*?)<\/title>/is', '', $html);
    $html = preg_replace('/<meta\b[^>]*>/is', '', $html);

    // Convert common block tags to line breaks
    $html = preg_replace('/<(br|\/p|\/div|\/tr|\/li)\s*\/?>/i', "\n", $html);
    $html = preg_replace('/<(p|div|tr|li|h1|h2|h3|h4|h5|h6)[^>]*>/i', "\n", $html);

    // Strip remaining tags
    $text = strip_tags($html);

    // Decode HTML entities
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    // Clean whitespace
    $text = preg_replace("/\r\n|\r/", "\n", $text);
    $text = preg_replace("/[ \t]+/", " ", $text);
    $text = preg_replace("/\n{3,}/", "\n\n", $text);

    return trim($text);
}
?>