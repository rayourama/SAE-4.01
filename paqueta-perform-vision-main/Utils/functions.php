<?php


/**
 * Fonction échappant les caractères html dans $message
 * @param string $message chaîne à échapper
 * @return string chaîne échappée
 */
function e($message)
{
    return htmlspecialchars($message, ENT_QUOTES);
}

/**
 * Vérifie l'accès de l'utilisateur basé sur la session.
 * 
 * Cette fonction vérifie si les identifiants de l'utilisateur et la session
 * sont définis et si le temps d'expiration de la session n'est pas dépassé. 
 * 
 * @return mixed Retourne l'objet $user si l'accès est validé, sinon false.
 */
function checkUserAccess()
{
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_token']) || $_SESSION['expire_time'] < time()) {
        return false;
    }

    $user = Model::getModel()->verifierToken(e($_SESSION['user_token']));

    if (!$user) {
        return false;
    }

    return $user;
}

/**
 * Foonction pour avoir le rôle de l'utilisateur.
 * 
 * Cette fonction détermine si l'utilisateur est un 'Formateur' ou un 'Client' en vérifiant
 * l'existence des détails du formateur dans la base de données.
 * 
 * @param array $user tableau avec les informations de l'utilisateur.
 * @return string Retourne 'Formateur' si l'utilisateur est un formateur, sinon 'Client'.
 */
function getUserRole($user)
{
    $formateurDetails = Model::getModel()->getFormateurById(e($user['id_utilisateur']));
    return ($formateurDetails) ? 'Formateur' : 'Client';
}

/**
 * Cette fonction vérifie si l'identifiant de l'utilisateur correspond à l'un des identifiants
 * des utilisateurs participant à la discussion.
 * 
 * @param int $userId Id de l'utilisateur.
 * @param array $discussion tableau avec les informations de la discussion, incluant
 *                          les identifiants des utilisateurs participants.
 * @return bool Retourne true si l'utilisateur participe à la discussion, sinon false.
 */
function isUserInDiscussion($userId, $discussion)
{
    return $userId == $discussion['id_utilisateur'] || $userId == $discussion['id_utilisateur_1'];
}



/**
 * Chiffre une chaîne de caractères à l'aide d'une clé publique avec la bibliothèque OpenSSL.
 *
 * @param string $str La chaîne de caractères à chiffrer.
 * @return string La chaîne chiffrée, encodée en base64.
 */
function encryptWithPublicKey($str) {
    $publicKey = openssl_pkey_get_public(file_get_contents("key.public"));
    openssl_public_encrypt($str, $crypted, $publicKey);
    return base64_encode($crypted);
}

/**
 * Déchiffre une chaîne de caractères à l'aide d'une clé privée avec la bibliothèque OpenSSL.
 *
 * @param string $str La chaîne chiffrée, encodée en base64.
 * @return string La chaîne déchiffrée.
 */
function decryptWithPrivateKey($str) {
    $privateKey = openssl_pkey_get_private(file_get_contents("key.private"));
    openssl_private_decrypt(base64_decode($str), $decrypted, $privateKey);
    return $decrypted;
}

/**
 * Génère une paire de clés RSA (publique et privée) sans utiliser de bibliothèque externe.
 *
 * @param int $bitLength La longueur en bits pour la génération des nombres premiers.
 * @return array Un tableau contenant la clé publique et la clé privée.
 * - 'publicKey' : ['e' => exponent, 'n' => modulus]
 * - 'privateKey' : ['d' => exponent, 'n' => modulus]
 */
function generateRSAKeys($bitLength) {
    $p = generatePrime($bitLength);
    $q = generatePrime($bitLength);

    $n = gmp_mul($p, $q);
    $phi = gmp_mul(gmp_sub($p, 1), gmp_sub($q, 1));

    $e = findCoprime($phi);
    $d = modInverse($e, $phi);
    
    return [
        'publicKey' => ['e' => gmp_strval($e), 'n' => gmp_strval($n)],
        'privateKey' => ['d' => gmp_strval($d), 'n' => gmp_strval($n)]
    ];
}

/**
 * Génère un nombre premier aléatoire de la longueur en bits spécifiée.
 *
 * @param int $bitLength Longueur en bits du nombre premier.
 * @return GMP Nombre premier généré.
 */

function generatePrime($bitLength) {
    do {
        $randomNumber = gmp_random_bits($bitLength);
    } while (!gmp_prob_prime($randomNumber, 50));

    return $randomNumber;
}

/**
 * Trouve un copremier à phi.
 *
 * @param int|string $phi La valeur de phi.
 * @return GMP Un copremier à phi.
 */
function findCoprime($phi) {
    $e = gmp_init(65537);
    $phi = gmp_init($phi);

    while (gmp_cmp(gmp_gcd($e, $phi), 1) != 0) {
        $e = gmp_add($e, 1);
    }

    return $e;
}

/**
 * Calcule l'inverse modulaire de a mod m.
 *
 * @param int|string $a Valeur a.
 * @param int|string $m Modulo m.
 * @return GMP L'inverse modulaire de a mod m.
 */
function modInverse($a, $m) {
    $a = gmp_init($a);
    $m = gmp_init($m);

    $inv = gmp_invert($a, $m);

    return $inv;
}

/**
 * Calcule base^exposant mod modulo.
 *
 * @param int|string $base Base.
 * @param int|string $exposant Exposant.
 * @param int|string $modulo Modulo.
 * @return string Le résultat de base^exposant mod modulo.
 */

function modPow($base, $exposant, $modulo) {
    $base = gmp_init($base);
    $exposant = gmp_init($exposant);
    $modulo = gmp_init($modulo);

    $resultatFinal = gmp_init(1);

    while (gmp_cmp($exposant, 0) > 0) {

        if (gmp_cmp(gmp_mod($exposant, 2), 1) == 0) {
            $resultatFinal = gmp_mod(gmp_mul($resultatFinal, $base), $modulo);
        }

        $base = gmp_mod(gmp_mul($base, $base), $modulo);

        $exposant = gmp_div($exposant, 2);
    }

    return gmp_strval($resultatFinal);
}

/**
 * Convertit une chaîne en nombre.
 *
 * @param string $string La chaîne à convertir.
 * @return GMP Le nombre converti.
 */
function stringToNumber($string) {
    $result = gmp_init('0');
    $length = strlen($string);

    for ($i = 0; $i < $length; $i++) {
        $result = gmp_mul($result, '256');
        $result = gmp_add($result, ord($string[$i]));
    }

    return $result;
}

/**
 * Convertit un nombre en chaîne.
 *
 * @param GMP $number Le nombre à convertir.
 * @return string La chaîne convertie.
 */
function numberToString($number) {
    $result = '';

    while (gmp_cmp($number, 0) > 0) {
        $byte = gmp_mod($number, '256');
        $result = chr((int)gmp_strval($byte)) . $result;
        $number = gmp_div($number, '256', GMP_ROUND_ZERO);
    }

    return $result;
}

/**
 * Chiffre un message avec RSA.
 *
 * @param string $message Le message à chiffrer.
 * @param array $publicKey La clé publique (e, n).
 * @return string Le message chiffré.
 */
function encryptRSA($message, $publicKey) {
    $numericMessage = stringToNumber($message);
    $encryptedMessage = modPow($numericMessage, $publicKey['e'], $publicKey['n']);
    return $encryptedMessage;
}

/**
 * Déchiffre un message avec RSA.
 *
 * @param string $encryptedMessage Le message chiffré.
 * @param array $privateKey La clé privée (d, n).
 * @return string Le message déchiffré.
 */
function decryptRSA($encryptedMessage, $privateKey) {
    $decryptedNumericMessage = modPow($encryptedMessage, $privateKey['d'], $privateKey['n']);
    $decryptedMessage = numberToString($decryptedNumericMessage);
    return $decryptedMessage;
}

/*$keys = generateRSAKeys(1024);
$publicKey = $keys['publicKey'];
$privateKey = $keys['privateKey'];

$message = "hello, je suis trop fort";

$encryptedMessage = encryptRSA($message, $publicKey);
echo "Message chiffré : " . $encryptedMessage . PHP_EOL;

$decryptedMessage = decryptRSA($encryptedMessage, $privateKey);
echo "Message déchiffré : " . $decryptedMessage . PHP_EOL;*/
