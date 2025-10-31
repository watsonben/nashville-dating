/**
 * Decode a base64url string to a Uint8Array
 * @param {string} input - Base64url encoded string
 * @returns {Uint8Array}
 */
export function base64UrlToUint8Array(input) {
    const base64 = input.replace(/-/g, '+').replace(/_/g, '/');
    const decoded = atob(base64);
    return Uint8Array.from(decoded, c => c.charCodeAt(0));
}

/**
 * Encode a Uint8Array or ArrayBuffer to a base64 string
 * @param {ArrayBuffer|Uint8Array} arrayBuffer
 * @returns {string}
 */
export function arrayBufferToBase64(arrayBuffer) {
    return btoa(String.fromCharCode(...new Uint8Array(arrayBuffer)));
}
