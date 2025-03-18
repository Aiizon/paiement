import './styles/app.scss';

// Fonction pour générer une clé AES
async function generateAESKey() {
    const key = await crypto.subtle.generateKey(
        {
            name: "AES-CBC",
            length: 256, // Clé AES de 256 bits
        },
        true, // Permettre l'exportation de la clé
        ["encrypt", "decrypt"] // Opérations autorisées pour cette clé
    );
    return key;
}

// Fonction pour chiffrer la clé AES avec la clé publique RSA
async function encryptAESKeyWithRSA(aesKey, publicKey) {
    const encoder = new TextEncoder();
    const encodedKey = encoder.encode(aesKey);

    const rsaPublicKey = await crypto.subtle.importKey(
        "spki",
        publicKey,
        {
            name: "RSA-OAEP",
            hash: "SHA-256",
        },
        false,
        ["encrypt"]
    );

    const encryptedKey = await crypto.subtle.encrypt(
        {
            name: "RSA-OAEP",
        },
        rsaPublicKey,
        encodedKey
    );

    return encryptedKey;
}

// Fonction pour chiffrer les données avec la clé AES
async function encryptData(cardNumber, key) {
    const iv = crypto.getRandomValues(new Uint8Array(16)); // Générer un IV aléatoire pour AES
    const encoder = new TextEncoder();
    const encodedData = encoder.encode(cardNumber); // Encoder les données en bytes

    // Chiffrer les données avec la clé AES en mode CBC
    const encryptedData = await crypto.subtle.encrypt(
        {
            name: "AES-CBC",
            iv: iv,
        },
        key,
        encodedData
    );

    // Retourner les données chiffrées et l'IV
    return {
        encryptedData: new Uint8Array(encryptedData), // Convertir en tableau d'octets
        iv: iv
    };
}

// Fonction principale pour envoyer les données chiffrées
async function sendEncryptedData(cardNumber) {
    const publicKey = await fetch('/public-key').then(res => res.text()); // Récupérer la clé publique du serveur
    const key = await generateAESKey(); // Générer la clé AES côté client

    // Chiffrer la clé AES avec la clé publique RSA
    const encryptedAESKey = await encryptAESKeyWithRSA(key, publicKey);

    const { encryptedData, iv } = await encryptData(cardNumber, key); // Chiffrer les données avec la clé AES

    // Envoyer l'IV, les données chiffrées et la clé AES chiffrée au serveur
    const response = await fetch("/save-payment", {
        method: "POST",
        body: JSON.stringify({
            encryptedCardNumber: encryptedData,
            iv: iv,
            encryptedAESKey: encryptedAESKey, // Clé AES chiffrée
        }),
        headers: { "Content-Type": "application/json" },
    });

    const result = await response.json();
    console.log(result);
}
