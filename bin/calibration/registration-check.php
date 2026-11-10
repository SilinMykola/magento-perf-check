<?php

declare(strict_types=1);

/**
 * Whether a registration.php holds only the standard ComponentRegistrar::register() call
 * (change extend-calibration-corpus, design D3). The analysis loads the project's autoloader,
 * which runs every registration.php, so anything else is kept out of the corpus. The autoloader also
 * runs a module's cli_commands.php; build-corpus removes that file from the copy.
 */
function isStandardRegistration(string $code): bool
{
    // Code without comments and whitespace; inline output or a closing tag stays in and fails the match.
    $text = '';
    foreach (token_get_all($code) as $token) {
        if (!is_array($token)) {
            $text .= $token;
        } elseif (!in_array($token[0], [T_OPEN_TAG, T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
            $text .= $token[1];
        }
    }

    $registrar = '\\\\?(?:Magento\\\\Framework\\\\Component\\\\)?ComponentRegistrar';

    return preg_match(
        '~^(?:declare\(strict_types=1\);)?'
        . '(?:use\\\\?Magento\\\\Framework\\\\Component\\\\ComponentRegistrar;)?'
        . $registrar . '::register\(' . $registrar . '::(?:MODULE|THEME),'
        . '(?:\'[A-Za-z0-9_/]+\'|"[A-Za-z0-9_/]+"),__DIR__,?\);$~',
        $text,
    ) === 1;
}
