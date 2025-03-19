import './styles/app.scss';

// Fonction pour générer une clé AES
async function generateAESKey() {
    return await crypto.subtle.generateKey(
        {
            name: "AES-CBC",
            length: 256,
        },
        true,
        ["encrypt", "decrypt"]
    );
}

// Fonction pour chiffrer la clé AES avec la clé publique RSA
async function encryptAESKeyWithRSA(aesKey, publicKey) {
    const rawKey = await crypto.subtle.exportKey('raw', aesKey);

    const keyHex = Array.from(new Uint8Array(rawKey))
        .map(b => b.toString(16).padStart(2, '0'))
        .join('');

    const rsaPublicKey = await crypto.subtle.importKey(
        "spki",
        pemToArrayBuffer(publicKey),
        {
            name: "RSA-OAEP",
            hash: "SHA-256",
        },
        false,
        ["encrypt"]
    );

    const encoder = new TextEncoder();
    return await crypto.subtle.encrypt(
        {
            name: "RSA-OAEP",
        },
        rsaPublicKey,
        encoder.encode(keyHex)
    );
}

// Fonction pour chiffrer les données avec la clé AES
async function encryptData(data, key) {
    const iv          = crypto.getRandomValues(new Uint8Array(16)); // Générer un IV aléatoire pour AES
    const encoder     = new TextEncoder();
    const encodedData = encoder.encode(data); // Encoder les données en bytes

    // Chiffrer les données avec la clé AES en mode CBC
    const encrypted = await crypto.subtle.encrypt(
        {
            name: "AES-CBC",
            iv: iv,
        },
        key,
        encodedData
    );

    return arrayBufferToBase64(encrypted) + '|' + arrayBufferToBase64(iv);
}

function pemToArrayBuffer(pem) {
    const base64 = pem
        .replace('-----BEGIN PUBLIC KEY-----', '')
        .replace('-----END PUBLIC KEY-----', '')
        .replace(/\s+/g, '');
    return Uint8Array.from(atob(base64), c => c.charCodeAt(0)).buffer;
}

function arrayBufferToBase64(buffer) {
    const bytes = buffer instanceof Uint8Array ? buffer : new Uint8Array(buffer);
    let binary = '';
    for (let i = 0; i < bytes.length; i++) {
        binary += String.fromCharCode(bytes[i]);
    }
    return btoa(binary);
}

// Fonction principale pour envoyer les données chiffrées
async function sendEncryptedData(cardData) {
    try {
        const publicKeyPem        = await fetch('/public-key').then(res => res.text());

        const aesKey              = await generateAESKey();

        const encryptedNumber     = await encryptData(cardData.cardNumber, aesKey);
        const encryptedCvv        = await encryptData(cardData.cvv, aesKey);
        const encryptedHolderName = await encryptData(cardData.holderName, aesKey);

        const encryptedAESKey     = await encryptAESKeyWithRSA(aesKey, publicKeyPem);

        const response = await fetch("/save-card", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                encryptedAESKey:     arrayBufferToBase64(encryptedAESKey),
                encryptedCardNumber: encryptedNumber,
                encryptedCvv:        encryptedCvv,
                encryptedHolderName: encryptedHolderName,
                expirationMonth:     cardData.expirationMonth,
                expirationYear:      cardData.expirationYear
            })
        });

        return await response.json();
    } catch (error) {
        console.error("Erreur de chiffrement :", error);
        throw error;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const cardForm = document.querySelector('#credit-card-form');

    if (cardForm) {
        cardForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            try {
                const cardData = {
                    cardNumber:      cardForm.querySelector('#card_number')     .value.replace(/\s/g, ''),
                    cvv:             cardForm.querySelector('#cvv')             .value,
                    holderName:      cardForm.querySelector('#holder_name')     .value,
                    expirationMonth: cardForm.querySelector('#expiration_month').value,
                    expirationYear:  cardForm.querySelector('#expiration_year') .value
                };

                // const result = await sendEncryptedData(cardData);

                const response = await fetch("/save-card", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(cardData)
                });

                const result = await response.json();

                if (result.success) {
                    cardForm.reset();
                    window.location.reload();
                } else {
                    throw new Error(result.message || 'Erreur lors de l\'enregistrement de la carte');
                }
            } catch (error) {
                console.log(error);
            }
        });
    }
});